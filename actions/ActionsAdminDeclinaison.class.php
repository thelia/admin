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
            case "ajouter":
                DeclinaisonAdmin::getInstance()->ajouter($request->request->get("titre"), $request->request->get("ajoutrub"));
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
            case "delDeclidisp":
                DeclinaisonAdmin::getInstance($request->query->get("id"))->delDeclidisp(
                    $request->query->get("declidisp_id"),
                    $request->query->get("lang")
                );
                break;
            case "modClassementDeclidisp":
                DeclinaisonAdmin::getInstance($request->query->get("id"))->modclassementdeclidisp(
                    $request->query->get("declidispdesc"),
                    $request->query->get("type"),
                    $request->query->get("lang")
                );
                break;
            case "setclassementdeclidisp":
                DeclinaisonAdmin::getInstance($request->request->get("id"))->setclassementdeclidisp(
                    $request->request->get("desclidispdesc"), 
                    $request->request->get("newClassement"),
                    $request->request->get("lang")
                );
                break;
            case "ajDeclidisp":
                DeclinaisonAdmin::getInstance($request->request->get("id"))->ajDeclidisp(
                    $request->request->get("titre"),
                    $request->request->get("lang")
                );
                break;
        }
    }
}