<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminSMTP extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return \ActionsAdminVariable
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminSMTP();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "delete":
                SMTPAdmin::getInstance($request->query->get("id"))->delete();
                break;
            case "edit":
                SMTPAdmin::getInstance($request->request->get("id"))->edit($request);
                break;
            case "add":
                SMTPAdmin::getInstance()->add($request->request->get("nom"), $request->request->get("valeur"));
                break;
        }
    }
    
}