<?php

class DeclinaisonAdmin extends Declinaison
{
    
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Declinaison not found", TheliaAdminException::DECLI_NOT_FOUND);
            }
        }
    }
    
    /**
     * 
     * @return \DeclinaisonAdmin
     */
    public static function getInstance($id = 0)
    {
        return new DeclinaisonAdmin($id);
    }
    
    public function modifier($titre, $chapo, $description, $declidisp, $lang)
    {
        $this->verifyLoaded();
        
        $declinaisondesc = new Declinaisondesc($this->id, $lang);
        
        $declinaisondesc->titre = $titre;
        $declinaisondesc->chapo = nl2br($chapo);
        $declinaisondesc->description = nl2br($description);
        
        if($declinaisondesc->id)
        {
            $declinaisondesc->maj();
        } else {
            $declinaisondesc->declinaison = $this->id;
            $declinaisondesc->lang = $lang;
            $declinaisondesc->id = $declinaisondesc->add();
        }
        
        ActionsModules::instance()->appel_module("moddeclinaison", $this);
        
        //declidisp
        
        if(!empty($declidisp) && is_array($declidisp))
        {
            foreach($declidisp as $declidisp_id => $titre)
            {
                $declidispdesc = new Declidispdesc($declidisp_id, $lang);
                
                $declidispdesc->titre = $titre;
                
                if($declidispdesc->id)
                {
                    $declidispdesc->maj();
                } else {
                    $declidispdesc->declidisp = $declidisp_id;
                    $declidispdesc->lang = $lang;
                    $declidispdesc->classement = $this->maxClassement($this->id, $lang)+1;
                    $declidispdesc->add();
                }
                
                $declidisp = new Declidisp($declidisp_id);
                ActionsModules::instance()->appel_module("moddeclidisp", $declidisp);
            }
        }
        
        redirige("declinaison_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    public function maxClassement($iddeclinaison, $lang)
    {
        $tdeclidispdesc = new Declidispdesc();
        $tdeclidisp = new Declidisp();

        $query = "
                select
                        max(ddd.classement) as maxClassement
                from
                        $tdeclidispdesc->table ddd
                left join
                        $tdeclidisp->table dd on dd.id = ddd.declidisp
                where
                        lang=$lang
                and
                        dd.declinaison=$iddeclinaison
        ";

        $resul = $tdeclidispdesc->query($query);

        return $resul ? intval($tdeclidispdesc->get_result($resul, 0, "maxClassement")) : 0;
    }
    
    public function getList()
    {
        $return = array();
        
        $query = "SELECT c.id, c.classement FROM ".Declinaison::TABLE." c order by c.classement";
        foreach($this->query_liste($query) as $declinaison)
        {
            $declidesc = new Declinaisondesc($declinaison->id);
            
            $return[] = array(
                "id" => $declinaison->id,
                "classement" => $declinaison->classement,
                "titre" => $declidesc->titre
            );
        }
        
        return $return;
    }
    
    public function delete() {
        parent::delete();
        
        ActionsModules::instance()->appel_module("suppdeclinaison", $this);
    }
    
    public function modClassement($type)
    {
        $this->verifyLoaded();
        
        $this->changer_classement($this->id, $type);
        
        redirige("declinaison.php");
    }
    
    protected function verifyLoaded()
    {
        if(!$this->id) throw new TheliaAdminException("Declinaison not found", TheliaAdminException::DECLI_NOT_FOUND);
    }
}