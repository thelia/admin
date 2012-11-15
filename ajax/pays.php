<?php

require_once __DIR__ . '/../pre.php';
require_once __DIR__ . '/../auth.php';


if(! est_autorise("acces_configuration")) exit; 

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

ActionsAdminPays::getInstance()->action($request);
