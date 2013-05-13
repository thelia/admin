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
ActionsAdminModules::instance()->inclure_module_admin("module_top");

$menu = "plugins";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_plugins', 'admin'));
require_once("entete.php");
ActionsAdminModules::instance()->inclure_module_admin("module");

try {
        $path = ActionsAdminModules::instance()->trouver_fichier_admin($nom);
            
        include($path);
} catch (Exception $e) {
        Tlog::error($e->getMessage());
}

ActionsAdminModules::instance()->inclure_module_admin("module_bottom");
require_once("pied.php");
?>
    
</div>
</div>
</body>
</html>
