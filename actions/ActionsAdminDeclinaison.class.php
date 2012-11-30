<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminDeclinaison extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return ActionsAdminDeclinaison
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminDeclinaison();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "modClassementDeclinaison":
                DeclinaisonAdmin::getInstance($request->query->get("id"))->modClassement($request->query->get("type"));
                break;
            case "supprimer":
                DeclinaisonAdmin::getInstance($request->query->get("id"))->delete();
                redirige("declinaison.php");
                break;
            case "modifier":
                DeclinaisonAdmin::getInstance($request->request->get("id"))->modifier(
                    $request->request->get("titre"),
                    $request->request->get("chapo"),
                    $request->request->get("description"),
                    $request->request->get("declinaisondesc_titre"),
                    $request->request->get("lang")
                );
                break;
        }
    }
}