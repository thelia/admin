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
            case "createOrder":
                OrderAdmin::getInstance()->createOrder(
                    $request->request->get("facturation_raison"),
                    $request->request->get("facturation_entreprise"), 
                    $request->request->get("facturation_nom"), 
                    $request->request->get("facturation_prenom"), 
                    $request->request->get("facturation_adresse1"), 
                    $request->request->get("facturation_adresse2"), 
                    $request->request->get("facturation_adresse3"), 
                    $request->request->get("facturation_cpostal"), 
                    $request->request->get("facturation_ville"), 
                    $request->request->get("facturation_tel"), 
                    $request->request->get("facturation_pays"),
                    $request->request->get("livraison_raison"),
                    $request->request->get("livraison_entreprise"), 
                    $request->request->get("livraison_nom"), 
                    $request->request->get("livraison_prenom"), 
                    $request->request->get("livraison_adresse1"), 
                    $request->request->get("livraison_adresse2"), 
                    $request->request->get("livraison_adresse3"), 
                    $request->request->get("livraison_cpostal"), 
                    $request->request->get("livraison_ville"), 
                    $request->request->get("livraison_tel"), 
                    $request->request->get("livraison_pays"),
                    $request->request->get("type_paiement"),
                    $request->request->get("type_transport"),
                    $request->request->get("fraisport"),
                    $request->request->get("remise"),
                    $request->request->get("client_selected"),
                    $request->request->get("ref"),
                    $request->request->get("email"),
                    new Panier()
                );
                break;
        }
    }
}
