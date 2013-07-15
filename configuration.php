<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;


?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("configuration_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Configuration', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('Configuration', 'admin'); ?></h3>
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("configuration");
?>
    <div class="row-fluid">
        <div class="span4">
            <div class="littletable">
            <table class="table table-striped">
                    <caption><?php echo trad('GESTION_CATALOGUE_PRODUIT', 'admin'); ?></caption>
                <tr>
                    <td><?php echo trad('Gestion_caracteristiques', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="caracteristique.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_declinaison', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="declinaison.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_messages', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="message.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_devises', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="devise.php"><i class="icon-edit"></i></a></td>
                </tr>
            </table>
            </div>
        </div>
        <div class="span4">
            <div class="littletable">
            <table class="table table-striped">
                    <caption><?php echo trad('GESTION_TRANSPORTS_LIVRAISONS', 'admin'); ?></caption>
                <tr>
                    <td><?php echo trad('Gestion des pays', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="pays.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_transport', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="transport.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_zones_livraison', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="zone.php"><i class="icon-edit"></i></a></td>
                </tr>
            </table>
            </div>
        </div>
        <div class="span4">
            <div class="littletable">
            <table class="table table-striped">
                    <caption><?php echo trad('PARAMETRES_SYSTEME', 'admin'); ?></caption>
                <tr>
                    <td><?php echo trad('Activation_plugins', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="plugins.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_variables', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="variable.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_administrateurs', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="gestadm.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_cache', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="cache.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_log', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="logs.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_droit', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="droits.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_mail', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="smtp.php"><i class="icon-edit"></i></a></td>
                </tr>
                <tr>
                    <td><?php echo trad('Gestion_langue', 'admin'); ?></td>
                    <td><a class="btn btn-mini" href="langue.php"><i class="icon-edit"></i></a></td>
                </tr>
            </table>
            </div>
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("configuration_bottom");
?>
<?php require_once("pied.php"); ?> 
</body>
</html>