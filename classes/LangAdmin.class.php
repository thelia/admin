<?php

class LangAdmin extends Lang
{
    private static  $instance = null;
    private static  $defaultLangInstance = null;

    public static function getInstance()
    {
        if(self::$instance === null)
            self::$instance = new LangAdmin();

        return self::$instance;
    }

    public static function getDefaultLangInstance()
    {
        if(self::$defaultLangInstance === null) {
            $lang = new LangAdmin();
            $lang->charger_defaut();
            self::$defaultLangInstance = $lang;
        }

        return self::$defaultLangInstance;
    }
    
    public function getList()
    {
        $query = "SELECT * from ".$this->table;
        return $this->query_liste($query);
    }
}

