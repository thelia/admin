<?php

    require_once __DIR__ . '/../classes/Autoload.class.php';
    
    $basedir = __DIR__ . "/../";

    $autoload = Autoload::getInstance();

    $autoload->addDirectories(array(
        $basedir . "/classes/",
        $basedir . "/classes/actions/",
        $basedir . "/classes/filtres/",
        $basedir . "/classes/parseur/",
        $basedir . "/classes/tlog/",
        $basedir . "/classes/tlog/destinations",
        __DIR__ . "/classes/",
        __DIR__ . "/actions/",
      ));

    $autoload->register();
    
    require __DIR__ . '/vendor/autoload.php';
?>
