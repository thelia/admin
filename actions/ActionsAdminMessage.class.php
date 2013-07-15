<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminMessage extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return \ActionsAdminMessage
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminMessage();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "supprimer":
                MessageAdmin::getInstance($request->query->get("id"))->delete();
                break;
            case "modifier":
                MessageAdmin::getInstance($request->request->get("id"))->modify(
                    $request->request->get("lang"),
                    $request->request->get("intitule"),
                    $request->request->get("titre"), 
                    $request->request->get("chapo"),
                    $request->request->get("description"),
                    $request->request->get("descriptiontext")
                );
                break;
            case "ajouter":
                MessageAdmin::getInstance()->ajouter($request->request->get("nom"));
                break;
        }
    }
    
}