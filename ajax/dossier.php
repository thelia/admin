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
       FolderAdmin::getInstance($request->query->get('folder_id'))->display($request->query->get('display'));
       break;
   
   
}

?>
