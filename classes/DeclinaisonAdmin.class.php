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
        
        ActionsModules::instance()->appel_module("moddeclinaison", new Declinaison($this->id));
        
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
        $declinaison = new Declinaison($this->id);
        parent::delete();
        
        ActionsModules::instance()->appel_module("suppdeclinaison", $declinaison);
    }
    
    public function getMaxRank()
    {
        $query = "SELECT MAX(classement) as maxClassement FROM ".$this->table;
        if($result = $this->query($query))
        {
            return $this->get_result($result, 0, "maxClassement");
        } else {
            return 0;
        }
    }
    
    public function ajouter($titre, $ajoutrub)
    {
        $this->classement = $this->getMaxRank()+1;
        $this->id = $this->add();
        
        $declinaisondesc = new Declinaisondesc();
        $declinaisondesc->titre = $titre;
        $declinaisondesc->declinaison = $this->id;
        $declinaisondesc->lang = ActionsAdminLang::instance()->get_id_langue_courante();
        $declinaisondesc->id = $declinaisondesc->add();
        
        if((intval($ajoutrub) == 1))
        {
            $query = "select id from ".Rubrique::TABLE;

            foreach($this->query_liste($query) as $row){
                   $rubdeclinaison = new Rubdeclinaison();
                   $rubdeclinaison->rubrique = $row->id;
                   $rubdeclinaison->declinaison = $this->id;
                   $rubdeclinaison->add();
            }
        }

        ActionsModules::instance()->appel_module("ajdeclinaison", new Declinaison($this->id));
        
        redirige("declinaison_modifier.php?id=".$this->id."&lang=".ActionsAdminLang::instance()->get_id_langue_courante());
        
    }
    
    public function modClassement($type)
    {
        $this->verifyLoaded();
        
        $this->changer_classement($this->id, $type);
        
        redirige("declinaison.php");
    }
    
    public function delDeclidisp($declidisp_id, $lang)
    {
        $tdeclidisp = new Declidisp($declidisp_id);
        $tdeclidisp->delete();

        ActionsModules::instance()->appel_module("suppdeclidisp", $tdeclidisp);
        
        redirige("declinaison_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    function modclassementdeclidisp($iddeclidispdesc, $type, $lang)
    {
        $declidispdesc = new Declidispdesc();

        if ($declidispdesc->charger($iddeclidispdesc, $lang))
        {
            $remplace = new Declidispdesc();

            if ($type == "M")
            {
                $where = "classement<" . $declidispdesc->classement . " order by classement desc";
            }
            else if ($type == "D")
            {
                $where  = "classement>" . $declidispdesc->classement . " order by classement";
            }

            $declidisp = new Declidisp();

            $query = "
                    select
                            *
                    from
                            $declidispdesc->table
                    where
                            lang=$lang
                    and
                            declidisp in (select id from $declidisp->table where declinaison = ".$this->id.")
                    and
                            $where
                    limit
                            0, 1
            ";

            if ($remplace->getVars($query))
            {
                $sauv = $remplace->classement;

                $remplace->classement = $declidispdesc->classement;
                $declidispdesc->classement = $sauv;

                $remplace->maj();
                $declidispdesc->maj();
            }
        }
        
        redirige("declinaison_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    public function setclassementdeclidisp($iddeclidispdesc, $classement, $lang)
    {
        $declidispdesc = new Declidispdesc();

        if ($declidispdesc->charger($iddeclidispdesc, $lang))
        {
            if ($classement == $declidispdesc->classement) return;

            if ($classement > $declidispdesc->classement)
            {
                $offset = -1;
                $between = "$declidispdesc->classement and $classement";
            }
            else
            {
                $offset = 1;
                $between = "$classement and $declidispdesc->classement";
            }

            $declidisp = new Declidisp();

            $query = "
                    select
                            id
                    from
                            $declidispdesc->table
                    where
                            lang=$lang
                    and
                            declidisp in (select id from $declidisp->table where declinaison = ".$this->id.")
                    and
                            classement BETWEEN $between
            ";

            $resul = $declidispdesc->query($query);

            $ddd = new Declidispdesc();

            while($resul && $row = $declidispdesc->fetch_object($resul))
            {
                if ($ddd->charger($row->id, $lang))
                {
                    $ddd->classement += $offset;
                    $ddd->maj();
                }
            }

            $declidispdesc->classement = $classement;
            $declidispdesc->maj();
        }
        
        redirige("declinaison_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    public function ajDeclidisp($titre, $lang)
    {
        $this->verifyLoaded();
        $tdeclidisp = new Declidisp();
        
        $tdeclidisp->declinaison = $this->id;
        $tdeclidisp->id = $tdeclidisp->add();;

        $tdeclidispdesc = new Declidispdesc();
        $tdeclidispdesc->declidisp = $tdeclidisp->id;
        $tdeclidispdesc->lang = $lang;
        $tdeclidispdesc->titre = $titre;

        $tdeclidispdesc->classement = $this->maxClassement($this->id, $lang) + 1;

        $tdeclidispdesc->add();

        ActionsModules::instance()->appel_module("ajdeclidisp", $tdeclidisp);
    }
    
    protected function verifyLoaded()
    {
        if(!$this->id) throw new TheliaAdminException("Declinaison not found", TheliaAdminException::DECLI_NOT_FOUND);
    }
}