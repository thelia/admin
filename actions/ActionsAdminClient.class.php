<?php
use Symfony\Component\HttpFoundation\Request;

class ActionsAdminClient extends ActionsAdminBase
{
    
    private static $instance = false;
    
    protected function __construct() { }
    
    /**
     * 
     * @return \ActionsAdminClient
     */
    public static function getInstance()
    {
        if(self::$instance === false) self::$instance = new ActionsAdminClient();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "deleteOrder":
                ClientAdmin::getInstanceByRef($request->query->get("ref"))->deleteOrder($request->query->get("id"));
                break;
            case 'deleteAddress':
                ClientAdmin::getInstanceByRef($request->query->get("ref"))->deleteAddress($request->query->get("id"));
                break;
            case 'editCustomer':
                ClientAdmin::getInstanceByRef($request->request->get("ref"))->edit(
                    $request->request->get("pourcentage"),
                    $request->request->get("raison"),
                    $request->request->get("entreprise"),
                    $request->request->get("siret"),
                    $request->request->get("intracom"),
                    $request->request->get("nom"),
                    $request->request->get("prenom"),
                    $request->request->get("adresse1"),
                    $request->request->get("adresse2"),
                    $request->request->get("adresse3"),
                    $request->request->get("cpostal"),
                    $request->request->get("ville"),
                    $request->request->get("pays"),
                    $request->request->get("telfixe"),
                    $request->request->get("telport"),
                    $request->request->get("email"),
                    $request->request->get("type")
                );
                break;
            case "editAddress":
                ClientAdmin::getInstanceByRef($request->request->get("ref"))->editAddress(
                    $request->request->get("id"), 
                    $request->request->get("libelle"), 
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
            case "addAddress":
                ClientAdmin::getInstanceByRef($request->request->get("ref"))->addAddress(
                    $request->request->get("libelle"),
                    $request->request->get("raison"),
                    $request->request->get("nom"), 
                    $request->request->get("prenom"),
                    $request->request->get("entreprise"),
                    $request->request->get("adresse1"),
                    $request->request->get("adresse2"),
                    $request->request->get("adresse3"),
                    $request->request->get("cpostal"),
                    $request->request->get("ville"),
                    $request->request->get("pays"),
                    $request->request->get("tel")
                );
                break;
        }
    }
}
