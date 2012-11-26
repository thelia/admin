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
        $path = ActionsAdminModules::instance()->trouver_fichier_admin($nom);
        
        Tlog::debug($path);
    
        include($path);
} catch (Exception $e) {
        Tlog::error($e->getMessage());
}
require_once("pied.php");
?>
    
</div>
</div>
</body>
</html>
