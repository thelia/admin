<?php
require_once("pre.php");
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
require __DIR__ . '/liste/plugins.php';

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

try
{
    ActionsAdminModules::instance()->action($request);
} catch (TheliaException $e) {
    Tlog::error($e);
}

// Mise a jour de la base suivant le contenu du repertoire plugins
ActionsAdminModules::instance()->mettre_a_jour();

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
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#pluginsAddModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>
                
            <?php echo afficher_plugins(); ?>
        </div>
    </div>
    <div class="modal hide fade in" id="pluginsAddModal">
        <form method="post" action="plugins.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="ajouter" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('AJOUTER_PLUGIN', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <input type="file" name="plugin" >
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
            <button type="submit" class="btn btn-primary"><?php echo trad('Ajouter', 'admin'); ?></button>
        </div>
        </form>
    </div>
<?php require_once("pied.php"); ?> 
</body>
</html>