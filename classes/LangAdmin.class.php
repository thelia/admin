<?php

class LangAdmin extends Lang
{
    
    public static function getInstance(){
        return new LangAdmin();
    }
    
    public function getList()
    {
        $query = "SELECT * from ".$this->table;
        return $this->query_liste($query);
    }
}

