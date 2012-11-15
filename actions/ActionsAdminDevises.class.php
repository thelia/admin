<?php
/**
 * Administration des devises depuis le back office
 *
 * Ce singleton permet de gérer la manipulation des devises depuis l'admin Thelia.
 *
 * @author Franck Allimant <franck@cqfdev.fr>
 * @version $Id$
 */
use Symfony\Component\HttpFoundation\Request;

class ActionsAdminDevises extends ActionsAdminBase
{

    private static $instance = false;

    private function __construct() {
    }

    /**
     * Cette classe est un singleton
     * @return ActionsAdminDevises une instance de ActionsAdminDevises
     */
    public static function getInstance() {
            if (self::$instance === false) self::$instance = new ActionsAdminDevises();

            return self::$instance;
    }

    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "refresh":
                $this->refresh();
                break;
            case "modifier":
                $this->modifier($request);
                break;
            case "ajouter":
                DeviseAdmin::getInstance()->ajouter(
                    $request->request->get("nom"), 
                    $request->request->get("taux"), 
                    $request->request->get("symbole"), 
                    $request->request->get("code")
                );
                break;
            case "supprimer":
                DeviseAdmin::getInstance($request->query->get("id"))->supprimer();
                break;
        }
    }
    
    public function modifier(Request $request)
    {
        $names = $request->request->get("nom");
        foreach($names as $id => $name)
        {
            
            DeviseAdmin::getInstance($id)->modifier(
                    $name,
                    $request->request->get("taux[".$id."]", null, true),
                    $request->request->get("symbole[".$id."]", null, true),
                    $request->request->get("code[".$id."]", null, true),
                    $request->request->get("defaut") == $id ? 1 : 0
           );
        }
        
        redirige("devise.php");
    }

    /**
     * Mettre à jour les taux de conversions par rapport à l'Euro
     */
    public function refresh(){
        $file_contents = file_get_contents('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');

        $devise = new Devise();

        if ($file_contents && $sxe = new SimpleXMLElement($file_contents)) {

            foreach ($sxe->Cube[0]->Cube[0]->Cube as $last)
            {
                $devise->query("UPDATE $devise->table SET  taux='".$devise->escape_string($last["rate"])."' WHERE code='".$devise->escape_string($last["currency"])."'");
            }
        }
        
        redirige("devise.php");
    }
}
?>