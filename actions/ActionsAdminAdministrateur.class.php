<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminAdministrateur extends ActionsAdminBase
{
    
    private static $instance = false;
    
    public function __construct() {}
    
    /**
     * 
     * @return \ActionsAdminAdministrateur
     */
    public static function getInstance()
    {
        if(self::$instance === false) self::$instance = new ActionsAdminAdministrateur();
        
        return self::$instance;
    }
    
    /**
     * 
     * controller for all actions on administrator
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "modifier":
                $noms = $request->request->get("nom");
                $error = array();
                foreach($noms as $id => $nom)
                {
                    try
                    {
                        AdministrateurAdmin::getInstance($id)->modify(
                            $nom, 
                            $request->request->get("prenom[".$id."]", null, true),
                            $request->request->get("identifiant[".$id."]", null, true),
                            $request->request->get("lang[".$id."]", null, true)
                        );
                    } catch(TheliaAdminException $e) {
                        $error[$id][] = $e->getCode();
                    }
                }
                
                if(!empty($error))
                {
                    throw new TheliaAdminException("multiple errors", TheliaAdminException::ADMIN_MULTIPLE_ERRORS, null, $error);
                }

                redirige("gestadm.php");
                break;
            case "delete":
                AdministrateurAdmin::getInstance($request->query->get("administrateur"))->delete();
                break;
            case "ajouter":
                AdministrateurAdmin::getInstance()->add(
                    $request->request->get("nom"),
                    $request->request->get("prenom"),
                    $request->request->get("identifiant"),
                    $request->request->get("password"), 
                    $request->request->get("verifyPassword"),
                    $request->request->get("lang"),
                    $request->request->get("profil")
                );
                break;
            case "modifier_password":
                AdministrateurAdmin::getInstance($request->request->get("id"))->modifyPassword(
                    $request->request->get("password"),
                    $request->request->get("verifyPassword")
                );
                break;
            case "change_droits_admin":
                AdministrateurAdmin::getInstance($request->request->get("administrateur"))->changePermissions(
                    $request->request->get("profil"),
                    $request->request->get("droits_g"),
                    $request->request->get("droits_m")
                );
                break;
                
        }
    }
}
