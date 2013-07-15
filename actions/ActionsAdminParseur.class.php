<?php

class ActionsAdminParseur extends ActionsAdminBase
{
    protected static $instance = false;
    
    public function __construct() {}
    
    public static function getInstance()
    {
        if(self::$instance === false) self::$instance = new ActionsAdminParseur();
        
        return self::$instance;
    }
    
    public function action($action)
    {
        switch($action)
        {
            case "maj_config":
                AdmParseur::getInstance()->update_config();
                break;
             case 'clear_cache' :
                 AdmParseur::getInstance()->clear_cache();
                 break;
             case 'check_cache' :
                 AdmParseur::getInstance()->check_cache();
                 break;
             case 'check_cache_dir' :
                 AdmParseur::getInstance()->check_cache_dir();
                break;
        }
    }
}