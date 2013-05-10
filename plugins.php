<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
require __DIR__ . '/liste/plugins.php';

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$errorCode = 0;
$errorMessage = "";
try
{
    ActionsAdminModules::instance()->action($request);
} catch (TheliaException $e) {
    Tlog::error($e->getMessage());
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
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
	ActionsAdminModules::instance()->inclure_module_admin("plugins_top");
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

<?php
	ActionsAdminModules::instance()->inclure_module_admin("plugins");
?>
                
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
    <?php if($errorCode > 0): ?>
    <div class="modal hide fade in" id="error-plugin">
        <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
             <h3><?php echo trad('plugin_error','admin'); ?></h3>
        </div>
        <div class="modal-body">
            <div class="alert alert-block alert-error">
            <?php echo trad('plugin_error_'.$errorCode,'admin', $request->query->get('nom'), $errorMessage); ?>
            </div>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
    <?php endif; ?>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("plugins_bottom");
?>
<?php require_once("pied.php"); ?>
<script type="text/javascript" src="js/Thelia.js"></script>
<script type="text/javascript" src="js/jeditable.min.js"></script>
<script type="text/javascript">

    $('.object_classement_editable').editable(function(value, settings){
        var form = Thelia.generateForm({
            action : $(this).attr('object-action'),
            object_id : $(this).attr('object-id'),
            object_name : "plugin_id",
            value : value,
            target: "plugins.php"
        });

        $(this).prepend(form);
        form.submit();
    },{
        onblur : 'submit',
        select : true
    });


    <?php if($errorCode > 0): ?>
    $("#error-plugin").modal("show");
    <?php endif; ?>
</script>
</body>
</html>