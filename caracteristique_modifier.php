<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

if(false !== $lang = $request->get("lang"))
    $lang = ActionsAdminLang::instance()->get_id_langue_courante();

$caract = new Caracteristique($request->get("id"));

$caractDisp = new Caracteristiquedesc($caract->id, $lang);

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getCaracList($caractDisp->titre);
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">

        </div>
    </div>
<?php require_once("pied.php"); ?> 
</body>
</html>