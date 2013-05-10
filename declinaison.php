<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

try 
{
    ActionsAdminDeclinaison::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    Tlog::error($e->getMessage());
}


?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("declinaison_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_declinaison', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('LISTE_DECLINAISONS', 'admin'); ?>
            <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#declinaisonAddModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("declinaison");
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
                        <?php foreach(DeclinaisonAdmin::getInstance()->getList() as $decli): ?>
                        <tr>
                            <td><?php echo $decli["titre"]; ?></td>
                            <td>
                                <a href="declinaison.php?id=<?php echo $decli["id"]; ?>&type=M&action=modClassementDeclinaison"><i class="icon-arrow-up"></i></a>
                                <span class="object_classement_editable" object-action="changeClassementCategory" object-name="category_id" object-id="<?php echo $decli["id"]; ?>"><?php echo $decli["classement"]; ?></span>
                                <a href="declinaison.php?id=<?php echo $decli["id"]; ?>&type=D&action=modClassementDeclinaison"><i class="icon-arrow-down"></i></a>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="declinaison_modifier.php?id=<?php echo $decli["id"]; ?>" class="btn btn-mini"><i class="icon-edit"></i></a>
                                    <a href="#" decli-id="<?php echo $decli["id"]; ?>" class="btn btn-mini js-decli-delete"><i class="icon-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal hide fade in" id="delObject">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('supprimer_declinaison', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('non', 'admin'); ?></a>
            <a class="btn btn-primary" id="deleteLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
    <div class="modal hide fade in" id="declinaisonAddModal">
        <form method="post" action="declinaison.php">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('AJOUTER_NOUVELLE_DECLINAISON', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><?php echo trad('Titre_caracteristique', 'admin'); ?></td> 
                        <td><input type="text" name="titre"></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Ajoutauto', 'admin'); ?></td>
                        <td><label class="checkbox"><small><?php echo trad('Ajout_decli_toutes_rubriques', 'admin'); ?></small><input type="checkbox" name="ajoutrub" value="1" checked="checked" /></label></td>
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
<?php
	ActionsAdminModules::instance()->inclure_module_admin("declinaison_bottom");
?>
<?php require_once("pied.php"); ?> 
<script type="text/javascript">
    $(document).ready(function(){
        $(".js-decli-delete").click(function(e){
            e.preventDefault();
            $("#deleteLink").attr("href","declinaison.php?id="+$(this).attr("decli-id")+"&action=supprimer");
            $("#delObject").modal("show");

        });
    });
</script>
</body>
</html>