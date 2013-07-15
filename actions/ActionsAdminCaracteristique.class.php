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
            case "ajouter":
                CaracteristiqueAdmin::getInstance()->add(
                    $request->request->get("titre"), 
                    $request->request->get("affiche"), 
                    $request->request->get("ajoutrub")
                );
                break;
            case "setclassementcaracdisp":
                CaracteristiqueAdmin::getInstance($request->request->get("id"))->setClassementCaracdisp(
                    $request->request->get("caracdispdesc"), 
                    $request->request->get("newClassement"), 
                    $request->request->get("lang")
                );
                break;
            case "modClassementCaracdisp":
                CaracteristiqueAdmin::getInstance($request->query->get("id"))->modClassementCaracdisp(
                        $request->query->get("cacacdispdesc"), 
                        $request->query->get("type"), 
                        $request->query->get("lang")
                );
                break;
            case "modifier":
                CaracteristiqueAdmin::getInstance($request->request->get("id"))->modifier(
                    $request->request->get("titre"),
                    $request->request->get("chapo"), 
                    $request->request->get("description"), 
                    $request->request->get("affiche"),
                    $request->request->get("caracdispdesc_titre"),
                    $request->request->get("lang")
                );
                break;
            case "ajCaracdisp":
                CaracteristiqueAdmin::getInstance($request->request->get("id"))->addCaracdisp(
                    $request->request->get("titre"),
                    $request->request->get("lang")
                );
                break;
            case "delCaracdisp":
                CaracteristiqueAdmin::getInstance($request->query->get("id"))->delCaracdisp($request->query->get("caracdisp"), $request->query->get("lang"));
                break;
        }
    }
}