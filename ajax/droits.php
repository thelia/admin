<?php
    require_once(__DIR__ . "/../auth.php");

    require_once(__DIR__ . "/../../fonctions/divers.php");
    
    header('Content-Type: text/html; charset=utf-8');
	
  if(! est_autorise("acces_configuration")) exit; 
  
  $request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

    if ( $request->isXmlHttpRequest() === false )
    {
        redirige("../accueil.php");
    }

  if($request->query->get('type_droit') == "1"){
	$autorisation = new Autorisation();
	$autorisation->charger_id($request->query->get('autorisation'));
	$autorisation_administrateur = new Autorisation_administrateur();
	$autorisation_administrateur->charger($autorisation->id, $request->query->get('administrateur'));

	if($autorisation->type == "1"){

		if($request->query->get('valeur') == 1){
			$autorisation_administrateur->lecture = 1;
			$autorisation_administrateur->ecriture = 1;
		} else {
			$autorisation_administrateur->lecture = 0;
			$autorisation_administrateur->ecriture = 0;
		}
		
	} else if($autorisation->type == "2"){
		
		
	}


	if($autorisation_administrateur->id)
		$autorisation_administrateur->maj();
	else {
		$autorisation_administrateur->autorisation = $autorisation->id;
		$autorisation_administrateur->administrateur = $request->query->get('administrateur');
		$autorisation_administrateur->add();
	}
 } else if($request->query->get('type_droit') == "2"){
	
		$autorisation_modules = new Autorisation_modules();
		$autorisation_modules->charger($request->query->get('module'), $request->query->get('administrateur'));
		
		if($request->query->get('valeur') == 1)
			$autorisation_modules->autorise = 1;
	 	else 
			$autorisation_modules->autorise = 0;

		if($autorisation_modules->id)
			$autorisation_modules->maj();
		else {
			$autorisation_modules->module = $request->query->get('module');
			$autorisation_modules->administrateur = $request->query->get('administrateur');
			$autorisation_modules->add();
		}	
 }
?>