<?php
require_once __DIR__ . '/../auth.php';

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}


if(est_autorise("acces_catalogue"))
{
    switch($request->query->get('action')){
       case 'changeDisplay' :  
           ProductAdmin::getInstance($request->query->get('product_id'))->changeColumn("ligne", ($request->query->get('display') == 'true')?1:0);
           break;
       case 'changePromo':
           ProductAdmin::getInstance($request->query->get('product_id'))->changeColumn("promo", ($request->query->get('display') == 'true')?1:0);
           break;
       case 'changeNew':
           ProductAdmin::getInstance($request->query->get('product_id'))->changeColumn("nouveaute", ($request->query->get('display') == 'true')?1:0);
           break;
    }
}

if(est_autorise("acces_commande"))
{
    switch($request->request->get('action')){
       case 'match':
           die(ProductAdmin::getInstance()->match($request->request->get('ref'), $request->request->get('max_accepted')));
           break;
    }
}

?>