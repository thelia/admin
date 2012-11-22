<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminCaracteristique extends ActionsAdminBase
{
    private static $instance = false;
    
    public function __construct() {}
    
    /**
     * 
     * @return \ActionsAdminCaracteristique
     */
    public static function getInstance()
    {
        if(self::$instance ===  false) self::$instance = new ActionsAdminCaracteristique();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "modClassementCaracteristique":
                CaracteristiqueAdmin::getInstance($request->query->get("id"))->modifyOrder($request->query->get("type"));
                redirige('caracteristique.php');
                break;
            case "supprimer":
                CaracteristiqueAdmin::getInstance($request->query->get("id"))->delete();
                redirige('caracteristique.php');
                break;
        }
    }
}