<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminPromo extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return ActionsAdminPromo
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminPromo();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get('action'))
        {   
           case 'add':
                PromoAdmin::getInstance()->add(
                    $request->request->get('code'),
                    $request->request->get('type'),
                    $request->request->get('valeur'),
                    $request->request->get('mini'),
                    $request->request->get('actif'),
                    $request->request->get('limite'),
                    $request->request->get('nombre_limite'),
                    $request->request->get('expiration'),
                    $request->request->get('date_expi')
                );
                break;
            case 'edit':
                PromoAdmin::getInstance()->edit(
                    $request->request->get('id'),
                    $request->request->get('type'),
                    $request->request->get('valeur'),
                    $request->request->get('mini'),
                    $request->request->get('actif'),
                    $request->request->get('limite'),
                    $request->request->get('nombre_limite'),
                    $request->request->get('expiration'),
                    $request->request->get('date_expi')
                );
                break;
            case 'delete':
                PromoAdmin::getInstance($request->query->get('id'))->delete();
                break;
        }
    }
}