<?php
require_once(__DIR__ . "/../auth.php");

require_once(__DIR__ . "/../../fonctions/divers.php");

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}

if(! est_autorise("acces_configuration")) exit; 


        header('Content-Type: text/html; charset=utf-8');

if($request->query->get('action') == "forfait" && $request->query->get('valeur', "") != ""){
        $zone = new Zone();
        $zone->charger($request->query->get('id'));
        $zone->unite = $request->query->get('valeur');
        $zone->maj();
        echo 1;
}

else if($request->query->get('action') == "ajouter" && $request->query->get('pays', "") != ""){
        $pays = new Pays();
        $query = "update $pays->table set zone='" . $request->query->get('id') . "' where id=\"" . $request->query->get('pays') . "\"";
        $resul = $pays->query($query);
        echo 1;
}

else if($request->query->get('action') == "supprimer" && $request->query->get('pays', "") != ""){
        $pays = new Pays();
        $query = "update $pays->table set zone='-1' where id=\"" . $request->query->get('pays') . "\"";
        $resul = $pays->query($query);
        echo 1;
}


?>