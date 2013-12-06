<?php
	require_once(__DIR__ . "/../auth.php");

	require_once(__DIR__ . "/../../fonctions/divers.php");

	require_once(__DIR__ . "/../liste/accessoire.php");

?>
<?php if(! est_autorise("acces_catalogue")) exit; ?>
<?php

	header('Content-Type: text/html; charset=utf-8');

  if(isset($_GET['action'])) $action = $_GET['action'];
  else $action = '';

	switch($_GET['action']){
		case 'produit' : accessoire_produit(); break;
		case 'ajouter' : accessoire_ajouter(); break;
		case 'supprimer' : accessoire_supprimer(); break;
	}
?>
<?php
	function accessoire_produit(){
		$produit = new Produit();
		$produit->charger($_GET['ref']);

		$query = "select p.* from $produit->table p LEFT JOIN " . Produitdesc::TABLE . " pd ON p.id=pd.produit AND pd.lang='" . $_SESSION['util']->lang . "' where p.rubrique=\""  . $_GET['id_rubrique'] . "\" ORDER BY pd.titre";
		$resul = $produit->query($query);

		while($resul && $row = $produit->fetch_object($resul)){

			$test = new Accessoire();
			if($test->charger_uni($produit->id, $row->id))
				continue;

			$produitdesc = new Produitdesc();
			$produitdesc->charger($row->id);
?>
			<option value="<?php echo $row->id; ?>"><?php echo $produitdesc->titre; ?></option>
<?php
		}
	}
?>
<?php
	function accessoire_ajouter(){
            $produit = new Produit();
            $produit->charger($_GET['ref']);

            $accessoire = new Accessoire();

            $query = "select max(classement) as maxClassement from $accessoire->table where produit=\"" . $produit->id . "\"";
            $resul = $accessoire->query($query);
            $classement = $accessoire->get_result($resul, 0, "maxClassement") + 1;

            $accessoire = new Accessoire();
            $accessoire->produit = $produit->id;
            $accessoire->accessoire = $_GET['id'];
            $accessoire->classement = $classement;
            $accessoire->add();

            lister_accessoires($produit->ref);
            
            ActionsModules::instance()->appel_module("modprod", $produit);
	}
?>
<?php
	function accessoire_supprimer(){
		$accessoire = new Accessoire();
		$accessoire->charger($_GET['id']);
                $produit = new Produit();
                $produit->charger($accessoire->produit);
		$accessoire->delete();

		lister_accessoires($_GET['ref']);
                
                ActionsModules::instance()->appel_module("modprod", $produit);
	}
?>