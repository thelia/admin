<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminVariable extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return \ActionsAdminVariable
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminVariable();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "delete":
                VariableAdmin::getInstance($request->query->get("id"))->delete();
                break;
            case "edit":
                VariableAdmin::getInstance($request->request->get("id"))->edit($request);
                break;
            case "add":
                VariableAdmin::getInstance()->add($request->request->get("nom"), $request->request->get("valeur"));
                break;
        }
    }
    
}