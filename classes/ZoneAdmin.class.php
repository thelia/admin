<?php

class ZoneAdmin extends Zone
{
    
    public function __construct($id = 0) {
        parent::__construct();
        
        if ($id)
        {
           $this->charger_id($id); 
        }
    }
    
    public static function getInstance($id = 0)
    {
        return new ZoneAdmin($id);
    }
    
    public function getList()
    {
        return $this->query_liste("SELECT * FROM ".$this->table);
    }
    
    public function delete()
    {
        $this->query("UPDATE ".Pays::TABLE." SET zone='-1' WHERE zone=".$this->id);
        
        parent::delete();
        
        redirige("zone.php");
    }
    
    public function add($nom)
    {
        $this->nom = $nom;
        $this->unite = 0;
        $this->id = parent::add();
        
        redirige("zone.php?id=".$this->id."&action=showZone#zone");
    }
}
