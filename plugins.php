<?php
require_once("pre.php");
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
require __DIR__ . '/liste/plugins.php';

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_plugins', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('Gestion_plugins', 'admin'); ?>
            <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#clientAddModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>
                
            <?php echo afficher_plugins(); ?>
        </div>
    </div>
<?php require_once("pied.php"); ?> 
</body>
</html>