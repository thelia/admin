<?php

require_once __DIR__ . '/../auth.php';

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}

if(! est_autorise("acces_contenu")) exit; 


switch($request->query->get('action')){
   case 'changeDisplay' :  
       ContentAdmin::getInstance($request->query->get('content_id'))->changeColumn("ligne", ($request->query->get('display') == 'true')?1:0);
       break;
}

?>