<?php
	require_once(__DIR__ . "/pre.php");

	session_start();
	header("Content-type: text/html; charset=utf-8");

	if(!isset($_SESSION["util"])) $_SESSION["util"]=new Administrateur();

	if(isset($_POST['identifiant']) && isset($_POST['motdepasse'])){
		$identifiant = str_replace(" ", "", $_POST['identifiant']);
		$motdepasse = str_replace(" ", "", $_POST['motdepasse']);
	}

        if($_POST['action'] == "identifier") {
               $admin = new Administrateur();
                if(! $admin->charger($identifiant, $motdepasse)) {redirige("index.php");exit;}
                else{
                     $_SESSION["util"] = new Administrateur();
                     $_SESSION["util"] = $admin;

               }
        }

	if( ! isset($_SESSION["util"]->id) ) {redirige("index.php");exit;}

	require_once(__DIR__ . "/../fonctions/traduction.php");

	// chargement du fichier de langue
	if(! isset($_SESSION["util"]->lang) || ! $_SESSION["util"]->lang)
         $_SESSION["util"]->lang = 1;

        require_once(__DIR__ . "/lang/" . $_SESSION["util"]->lang . ".php");

        ActionsAdminModules::instance()->inclure_lang_admin($_SESSION["util"]->lang);
        ActionsAdminModules::instance()->inclure_module_admin("pre");

?>