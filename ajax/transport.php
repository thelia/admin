<?php
    require_once(__DIR__ . "/../auth.php");
    require_once(__DIR__ . "/../../fonctions/divers.php");

    if(! est_autorise("acces_configuration")) exit;

    header('Content-Type: text/html; charset=utf-8');

    if($_GET['action'] == "supprimer" && $_GET['zone'] != ""){
            $transzone = new Transzone();
            $transzone->charger_id($_GET['zone']);
            $transzone->delete();
            echo 1;

    } else if($_GET['action'] == "ajouter" && $_GET['id'] != "" && $_GET['zone'] != ""){
            $transzone = new Transzone();
            $transzone->zone = $_GET['zone'];
            $transzone->transport = $_GET['id'];
            $id = $transzone->add();
            echo json_encode(array("res" => 1, "id" => $id));
    }
?>