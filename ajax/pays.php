<?php
require_once __DIR__ . '/../auth.php';


if(! est_autorise("acces_configuration")) exit; 

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}

ActionsAdminPays::getInstance()->action($request);
