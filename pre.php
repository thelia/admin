<?php

    if(strstr( $_SERVER['PHP_SELF'], "/admin/")){
            header("Location: changerep.php"); exit;
    }
    
    require_once(__DIR__ . "/../fonctions/error_reporting.php");

    require_once __DIR__ . "/autoload.php";

    ActionsLang::instance()->set_mode_backoffice(true);

    Tlog::mode_back_office(true);
    require_once(__DIR__ . "/../fonctions/divers.php");



    foreach ($_REQUEST as $key => $value) $$key = $value;
    
    if(!ini_get("date.timezone"))
    {
        date_default_timezone_set("Europe/Paris");
        Tlog::error("Timezone not set, set to Europe/Paris for no errors");
    }
    
    setlocale(LC_TIME,      'fr_FR.utf8', 'fr_FR');
    setlocale(LC_NUMERIC,   'en_US.utf8', 'en_US');

?>
