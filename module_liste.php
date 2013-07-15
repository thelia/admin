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
	ActionsAdminModules::instance()->inclure_module_admin("module_liste_top");
$menu = "plugins";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_plugins', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('LISTE_MODULES', 'admin'); ?></h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("module_liste");
?>          
            <table class="table table-striped">
                <tbody>
<?php
//print_r(ActionsAdminModules::instance()->lister(false, true));
foreach(ActionsAdminModules::instance()->lister(false, true) as $module)
{
    if(! $module->est_autorise()) continue;
    
    try
    {
        ActionsAdminModules::instance()->trouver_fichier_admin($module->nom);
?>
                    <tr>
                        <td class="span11"><?php echo ActionsAdminModules::instance()->lire_titre_module($module); ?></td>
                        <td class="span1">
                            <div class="btn-group">
                                <a class="btn btn-mini" href="module.php?nom=<?php echo $module->nom; ?>" title="<?php echo trad('editer', 'admin'); ?>">
                                    <i class="icon-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
            <?php
    } catch (Exception $ex) {
        //echo $ex;
    }
}
?>
                </tbody>
            </table>
            
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("module_liste_bottom");
?>  
<?php require_once("pied.php"); ?> 
</body>
</html>