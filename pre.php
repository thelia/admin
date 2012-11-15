<?php

    if(strstr( $_SERVER['PHP_SELF'], "/admin/")){
            header("Location: changerep.php"); exit;
    }
    
    define('THELIA_MAGIC_QUOTE_ENABLED', get_magic_quotes_gpc());

    require_once(__DIR__ . "/../fonctions/error_reporting.php");

    require_once __DIR__ . "/autoload.php";

    require_once(__DIR__ . "/../lib/TheliaPurifier.php");

    function _sanitize_param($value, $config = null) {
        if (is_array($value)) {
            foreach($value as $key => $item) {
                $value[$key] = _sanitize_param($item, $config);
            }
            return $value;
        }
        else {
            if(THELIA_MAGIC_QUOTE_ENABLED){
                $value = stripcslashes($value);
            }
            return TheliaPurifier::instance()->purifier($value);
        }
    }

    ActionsLang::instance()->set_mode_backoffice(true);

    Tlog::mode_back_office(true);
    require_once(__DIR__ . "/../fonctions/divers.php");


    // Put sanitize_admin value to 0 into variable table if you don't want to sanitize (escaping) $_REQUEST parameters

    TheliaPurifier::instance()->set_admin_mode();

    foreach ($_REQUEST as $key => $value) $$key = Variable::lire('sanitize_admin',1) ? _sanitize_param($value) : $value;
    
    setlocale(LC_ALL, 'fr_FR');

?>
