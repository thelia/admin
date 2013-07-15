<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$errorCode = 0;

try 
{
    ActionsAdminCaracteristique::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    Tlog::error($e->getMessage());
    $errorCode = $e->getCode();
}


?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
        ActionsAdminModules::instance()->inclure_module_admin("caracteristique_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_caracteristiques', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('LISTE_DES_CARACTERISTIQUES', 'admin'); ?>
                <div class="btn-group">
                    <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#caracteristiqueAddModal" data-toggle="modal">
                        <i class="icon-plus-sign icon-white"></i>
                    </a>
                </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("caracteristique");
?>
            <div class="bigtable">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo trad('Titre_caracteristique', 'admin'); ?></th>
                            <th><?php echo trad('Classement', 'admin'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(CaracteristiqueAdmin::getInstance()->getList() as $carac): ?>
                        <tr>
                            <td><?php echo $carac["titre"]; ?></td>
                            <td>
                                <a href="caracteristique.php?id=<?php echo $carac["id"]; ?>&type=M&action=modClassementCaracteristique"><i class="icon-arrow-up"></i></a>
                                <span class="object_classement_editable" object-action="changeClassementCategory" object-name="category_id" object-id="<?php echo $carac["id"]; ?>"><?php echo $carac["classement"]; ?></span>
                                <a href="caracteristique.php?id=<?php echo $carac["id"]; ?>&type=D&action=modClassementCaracteristique"><i class="icon-arrow-down"></i></a>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="caracteristique_modifier.php?id=<?php echo $carac["id"]; ?>" class="btn btn-mini"><i class="icon-edit"></i></a>
                                    <a href="#" carac-id="<?php echo $carac["id"]; ?>" class="btn btn-mini js-carac-delete"><i class="icon-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal hide fade in" id="caracteristiqueAddModal">
        <form method="post" action="caracteristique.php">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4><?php echo trad('AJOUTER_UNE_NOUVELLE_CARACTERISTIQUE', 'admin'); ?></h4>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <tbody>
                    <tr class="<?php if($errorCode == TheliaAdminException::CARAC_TITLE_EMPTY) echo "error"; ?>">
                        <td><?php echo trad('Titre_caracteristique', 'admin'); ?></td> 
                        <td><input type="text" name="titre"></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Visible', 'admin'); ?></td>
                        <td><label class="checkbox"><small><?php echo trad('permet', 'admin'); ?></small><input name="affiche" type="checkbox" checked="checked"/></label></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Ajoutauto', 'admin'); ?></td>
                        <td><label class="checkbox"><small><?php echo trad('Ajout_carac_toutes_rubriques', 'admin'); ?></small><input type="checkbox" name="ajoutrub" value="1" checked="checked" /></label></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
            <button type="submit" class="btn btn-primary"><?php echo trad('Ajouter', 'admin'); ?></button>
        </div>
        </form>
    </div>
    <div class="modal hide fade in" id="delObject">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('supprime_caracteristique', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('non', 'admin'); ?></a>
            <a class="btn btn-primary" id="deleteLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("caracteristique_bottom");
?>
<?php require_once("pied.php"); ?> 
    <script type="text/javascript">
        $(document).ready(function(){
            $(".js-carac-delete").click(function(e){
               e.preventDefault();
               $("#deleteLink").attr("href","caracteristique.php?action=supprimer&id="+$(this).attr("carac-id"));
               
               $("#delObject").modal("show");
            });
            
            <?php if($errorCode == TheliaAdminException::CARAC_TITLE_EMPTY): ?>
                $("#caracteristiqueAddModal").modal("show");
            <?php endif; ?>
        });
    </script>
</body>
</html>