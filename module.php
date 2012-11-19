<?php
require_once("auth.php");
if (!est_autorise("acces_modules"))
    exit;
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "plugins";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_plugins', 'admin'));
require_once("entete.php");

try {
        require_once(ActionsAdminModules::instance()->trouver_fichier_admin($nom));
} catch (Exception $e) {
        die($e->getMessage());
}
require_once("pied.php");
?>
    
</div>
</div>
</body>
</html>
