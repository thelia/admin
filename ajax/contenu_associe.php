<?php
require_once (__DIR__ . "/../auth.php");

require_once (__DIR__ . "/../../fonctions/divers.php");


if (!est_autorise("acces_catalogue"))    exit;

require_once (__DIR__ . "/../liste/contenu_associe.php");

header('Content-Type: text/html; charset=utf-8');

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if ( $request->isXmlHttpRequest() === false )
{
    redirige("../accueil.php");
}

switch ($request->query->get('action')) {
    case 'contenu_assoc':
        contenuassoc_contenu($request);
        break;
    case 'ajouter':
        contenuassoc_ajouter($request);
        break;
    case 'supprimer':
        contenuassoc_supprimer($request);
        break;
}

function contenuassoc_contenu($request) {
    if ($request->query->get('type') == 1) {
        $objet = new Produit();
        $objet->charger($request->query->get('objet'));
    } else {
        $objet = new Rubrique();
        $objet->charger($request->query->get('objet'));
    }

    $contenu = new Contenu();

    $query = "select * from $contenu->table where dossier=\"" . $request->query->get('id_dossier') . "\"";
    $resul = $contenu->query($query);

    while ($resul && $row = $contenu->fetch_object($resul)) {

        $contenuassoc = new Contenuassoc();
        if (
                (
                    !in_array($row->id, explode('-', $request->query->get('force_show_content')))
                    && $contenuassoc->existe($objet->id, $request->query->get('type'), $row->id)
                )
                || in_array($row->id, explode('-', $request->query->get('force_hide_content')))
        )
                continue;

        $contenudesc = new Contenudesc();
        $contenudesc->charger($row->id);

        ?>
<option value="<?php echo $row->id; ?>"><?php echo $contenudesc->titre; ?></option>
        <?php
    }
}

function contenuassoc_ajouter($request) {
	if ($request->query->get('type') == 1) {
		$objet = new Produit();
		$objet->charger($request->query->get('objet'));
	} else {
		$objet = new Rubrique();
		$objet->charger($request->query->get('objet'));
	}

	$contenuassoc = new Contenuassoc();

	$query = "select max(classement) as maxClassement from $contenuassoc->table where objet=\"" . $objet->id . "\" and type=\"" . $request->query->get('type') . "\"";
	$resul = $contenuassoc->query($query);
	$classement = $contenuassoc->get_result($resul, 0, "maxClassement") + 1;

	$contenuassoc = new Contenuassoc();
	$contenuassoc->objet = $objet->id;
	$contenuassoc->type = $request->query->get('type');
	$contenuassoc->contenu = $request->query->get('id');
	$contenuassoc->classement = $classement;
	$contenuassoc->add();

	lister_contenuassoc($request->query->get('type'), $request->query->get('objet'));

	if ($contenuassoc->type == 1)
		ActionsModules::instance()->appel_module("modprod", $objet);
	else
		ActionsModules::instance()->appel_module("modrub", $objet);

}

function contenuassoc_supprimer($request) {
	$contenuassoc = new Contenuassoc();
	$contenuassoc->charger($request->query->get('id'));
	$contenuassoc->delete();

	if ($contenuassoc->type == 1)
		$objet = new Produit();
	else
		$objet = new Rubrique();

	$objet->charger($contenuassoc->objet);

	if ($contenuassoc->type == 1)
		ActionsModules::instance()->appel_module("modprod", $objet);
	else
		ActionsModules::instance()->appel_module("modrub", $objet);

	lister_contenuassoc($request->query->get('type'), $request->query->get('objet'));
}
?>