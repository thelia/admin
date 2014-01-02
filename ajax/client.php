<?php
require_once(__DIR__ . "/../auth.php");

if(! est_autorise("acces_commandes")) exit;

header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['action'])) $action = $_POST['action'];
else $action = '';

switch($action) {
    case 'match' : checkMatch(); break;
}

function checkMatch()
{
    $email = $_POST['email']?:'';
    $prenom = $_POST['prenom']?:'';
    $nom = $_POST['nom']?:'';
    $ref = $_POST['ref']?:'';
    $max_accepted = $_POST['max_accepted']?:5;
    
    if(strlen($email) == 0 && strlen($prenom) == 0 && strlen($nom) == 0 && strlen($ref) == 0)
        die('KO');
    
    $client = new Client();

    $q = "SELECT * FROM $client->table WHERE
            email LIKE '$email%'
            AND prenom LIKE '$prenom%'
            AND nom LIKE '$nom%'
            AND ref LIKE '$ref%'
        ";
    $r = $client->query($q);
    
    if($client->num_rows($r) == 0)
        die('KO');
    
    if($client->num_rows($r) > $max_accepted)
        die('TOO_MUCH:' . $client->num_rows($r));
    
    $retour = array();
    while($r && $a = $client->fetch_object($r, 'Client')) {
        $retour[] = array(
            "ref"           =>  $a->ref,
            "email"         =>  $a->email,
            "raison"        =>  $a->raison,
            "entreprise"    =>  $a->entreprise,
            "nom"           =>  $a->nom,
            "prenom"        =>  $a->prenom,
            "adresse1"      =>  $a->adresse1,
            "adresse2"      =>  $a->adresse2,
            "adresse3"      =>  $a->adresse3,
            "cpostal"       =>  $a->cpostal,
            "ville"         =>  $a->ville,
            "pays"          =>  $a->pays,
            "tel"           =>  $a->tel,
            "pourcentage"   =>  $a->pourcentage,
        );
    }
    
    die(json_encode($retour));
}
