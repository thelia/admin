<?php
require_once(__DIR__ . "/../auth.php");

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}

ActionsAdminModules::instance()->inclure_module_admin("promomodifier");

?>