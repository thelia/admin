<?php
    require_once(__DIR__ . "/../auth.php");
    require_once(__DIR__ . "/../../fonctions/divers.php");

    if(! est_autorise("acces_configuration")) exit;
    
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    if ( $request->isXmlHttpRequest() === false )
    {
        redirige("../accueil.php");
    }

    header('Content-Type: text/html; charset=utf-8');

    if($request->query->get('action') == "supprimer" && $request->query->get('zone', "") != ""){
            $transzone = new Transzone();
            $transzone->charger_id($request->query->get('zone'));
            $transzone->delete();
            echo 1;

    } else if($request->query->get('action') == "ajouter" && $request->query->get('id',"") != "" && $request->query->get('zone',"") != ""){
            $transzone = new Transzone();
            $transzone->zone = $request->query->get('zone');
            $transzone->transport = $request->query->get('id');
            $id = $transzone->add();
            echo json_encode(array("res" => 1, "id" => $id));
    }
?>