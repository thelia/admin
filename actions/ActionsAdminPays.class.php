<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminPays extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return ActionsAdminPays
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminPays();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "changeTva":
                PaysAdmin::getInstance($request->query->get("id"))->changeTva();
                break;
            case "changeDefault":
                PaysAdmin::getInstance($request->query->get("id"))->changeDefault();
                break;
            case "changeBoutique":
                PaysAdmin::getInstance($request->query->get("id"))->changeBoutique();
                break;
            case "deleteCountry":
                PaysAdmin::getInstance($request->query->get("id"))->delete();
                break;
            case "editCountry":
                PaysAdmin::getInstance($request->request->get("id"))->modify(
                    $request->request->get("isocode"),
                    $request->request->get("isoalpha2"),
                    $request->request->get("isoalpha3"),
                    $request->request->get("tva"),
                    $request->request->get("zone"),
                    $this->getDesc($request)
                );
                break;
            case "addCountry":
                PaysAdmin::getInstance()->add(
                    $request->request->get("titre"),
                    $request->request->get("isocode"),
                    $request->request->get("isoalpha2"),
                    $request->request->get("isoalpha3"),
                    $request->request->get("tva"),
                    $request->request->get("zone")
                );
                break;
        }
    }
    
    public function getDesc(Request $request)
    {
        $langs = $request->request->get("lang");
        $return = array();
        foreach($langs as $id => $value)
        {
            $return[$id] = array(
                "id" => $id,
                "titre"=> $request->request->get("titre[".$id."]", null, true),
                "chapo"=> $request->request->get("chapo[".$id."]", null, true),
                "description"=> $request->request->get("description[".$id."]", null, true),
            );
        }
        
        return $return;
    }
}
?>
