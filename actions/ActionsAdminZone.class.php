<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminZone extends ActionsAdminBase
{
    
    private static $instance = false;
    
    public function __construct() {}
    
    public static function getInstance()
    {
        if(self::$instance === false) self::$instance = new ActionsAdminZone();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "ajouter":
                ZoneAdmin::getInstance()->add($request->request->get('nom'));
                break;
            case "supprimer":
                ZoneAdmin::getInstance($request->query->get('id'))->delete();
                break;
        }
    }
}