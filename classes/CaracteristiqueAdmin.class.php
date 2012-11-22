<?php

class CaracteristiqueAdmin extends Caracteristique
{
    
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Caracteristique not found", TheliaAdminException::CARAC_NOT_FOUND);
            }
        }
    }
    
    /**
     * 
     * @return \CaracteristiqueAdmin
     */
    public static function getInstance($id = 0)
    {
        return new CaracteristiqueAdmin($id);
    }
    
    public function getProductList(\Produit $produit, $lang)
    {
        if($produit->id == "") return array();
        
        $return = array();
        
        $query = "select c.caracteristique as id, c.titre from ".Caracteristiquedesc::TABLE." c left join ".Rubcaracteristique::TABLE." rc on rc.caracteristique=c.caracteristique and c.lang=$lang where rc.rubrique=".$produit->rubrique;
        foreach($this->query_liste($query) as $caracteristique)
        {
            $return[$caracteristique->id]["titre"] = $caracteristique->titre;
            $query2 = "select c.id, c.caracteristique, cd.titre from ".Caracdisp::TABLE." c left join ".Caracdispdesc::TABLE." cd on cd.caracdisp = c.id and cd.lang = $lang where c.caracteristique=".$caracteristique->id." order by cd.classement";
            $resul = $this->query($query2);
            if($this->num_rows($resul))
            {
                while($resul && $row = $this->fetch_object($resul))
                {
                    $return[$caracteristique->id]["caracdisp"][] = array(
                        "caracdisp" => $row->id,
                        "titre" => $row->titre
                    );
                }
                
            } else {
                $return[$caracteristique->id]["caracdisp"] = null;
            }
        }
        return $return;
    }
    
    public function getList()
    {
        $return = array();
        
        $query = "SELECT c.id, c.classement FROM ".Caracteristique::TABLE." c order by c.classement";
        foreach($this->query_liste($query) as $caracteristique)
        {
            $caracdesc = new Caracteristiquedesc($caracteristique->id);
            
            $return[] = array(
                "id" => $caracteristique->id,
                "classement" => $caracteristique->classement,
                "titre" => $caracdesc->titre
            );
        }
        
        return $return;
    }
    
    public function modifyOrder($type)
    {
        $this->veirfyLoaded();
        
        $this->changer_classement($this->id, $type);
    }
    
    public function delete()
    {
        $this->veirfyLoaded();
        parent::delete();
        ActionsModules::instance()->appel_module("suppcaracteristique", $this);
    }
    
    /**
     * 
     * Verify if an admin is loaded 
     * 
     * @throws TheliaAdminException ADMIN_NOT_FOUND
     */
    protected function veirfyLoaded()
    {
        if(!$this->id) throw new TheliaAdminException("Caracteristique not found", TheliaAdminException::CARAC_NOT_FOUND);
    }
    
}