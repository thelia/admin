<?php
require_once __DIR__ . '/../auth.php';

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}


if(! est_autorise("acces_catalogue")) exit; 


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

?>