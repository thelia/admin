<?php
use Symfony\Component\HttpFoundation\Request;

class ActionsAdminOrder extends ActionsAdminBase
{
    
    private static $instance = false;
    
    protected function __construct() { }
    
    /**
     * 
     * @return \ActionsAdminOrder
     */
    public static function getInstance()
    {
        if(self::$instance === false) self::$instance = new ActionsAdminOrder();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "editVenteadr":
                OrderAdmin::getInstanceByRef($request->request->get("ref"))->editVenteAdr(
                    $request->request->get("id"), 
                    $request->request->get("raison"),
                    $request->request->get("entreprise"), 
                    $request->request->get("nom"), 
                    $request->request->get("prenom"), 
                    $request->request->get("adresse1"), 
                    $request->request->get("adresse2"), 
                    $request->request->get("adresse3"), 
                    $request->request->get("cpostal"), 
                    $request->request->get("ville"), 
                    $request->request->get("tel"), 
                    $request->request->get("pays")
                );
                break;
        }
    }
}
