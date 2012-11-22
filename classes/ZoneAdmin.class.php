<?php

class ZoneAdmin extends Zone
{
    
    public static function getInstance()
    {
        return new ZoneAdmin();
    }
    
    public function getList()
    {
        return $this->query_liste("SELECT * FROM ".$this->table);
    }
}
