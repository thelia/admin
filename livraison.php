<?php
	require_once("auth.php");

	if(! est_autorise("acces_commandes")) exit;

	$commande = new Commande();
	$commande->charger_ref($ref);

	if (file_exists(__DIR__.'/../client/pdf/modeles/facture.php'))
	{

            $commande = new Commande();
            $commande->charger_ref($ref);

            $client = new Client();
            $client->charger_id($commande->client);

            $pays = new Pays();
            $pays->charger($client->pays);

            $zone = new Zone();
            $zone->charger($pays->zone);

            require_once("../client/pdf/modeles/livraison.php");

            $livraison = new Livraison();
            $livraison->creer($ref);

            exit();
	}

	$nom_fichier_pdf = $commande->livraison . '.pdf';

	// Le moteur ne sortira pas le contenu de $res
	$sortie = false;

	// Le fond est le template de livraison.
	$reptpl = __DIR__ . "/../client/pdf/template/";
	$fond = "livraison.html";

	$lang = $commande->lang;

	// Compatibilité avec le moteur.
	$_REQUEST['commande'] = $ref;

	require_once(__DIR__ . "/../fonctions/moteur.php");

	Pdf::instance()->generer($res, $nom_fichier_pdf);
?>
