<?php
require_once("pre.php");
require_once("auth.php");

if(! est_autorise("acces_contenu")) exit;

require_once __DIR__ . "/../fonctions/divers.php";

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if (false == $lang = $request->get("lang", false))
    $lang = ActionsLang::instance()->get_id_langue_courante();
if (false == $tab = $request->get("tab", false))
    $tab = "generalDescriptionTab";

$contenu = new Contenu($request->query->get("id"));
$contenudesc = new Contenudesc($contenu->id, $lang);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>

    <body>
<?php
$menu = "contenu";
$breadcrumbs = Breadcrumb::getInstance()->getContentListe($request->get('dossier'), $contenudesc->titre);
require_once("entete.php");
?>
<div class="row-fluid">
    <div class="span12">

    </div>
</div>
<?php require_once("pied.php"); ?> 
</body>
</html>