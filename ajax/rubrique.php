<?php
require_once __DIR__ . '/../auth.php';


if(! est_autorise("acces_catalogue")) exit; 
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}


switch($request->query->get('action')){
   case 'changeDisplay' :  
       CategoryAdmin::getInstance($request->query->get('category_id'))->display($request->query->get('display'));
       break;
   
   
}

?>
