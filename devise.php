<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
try
{
    ActionsAdminDevises::getInstance()->action($request);
} catch (TheliaAdminException $e) {
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
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_devises', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('LISTE_DEVISES','admin'); ?>
                <div class="btn-group">
                    <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#deviseAddModal" data-toggle="modal">
                        <i class="icon-plus-sign icon-white"></i>
                    </a>
                </div>
            </h3>
            <div class="bigtable">
                <form method="post" action="devise.php">
                <input type="hidden" name="action" value="modifier">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo trad('ID', 'admin'); ?></th>
                            <th><?php echo trad('Designation', 'admin'); ?></th>
                            <th><a title="<?php echo trad('Voir la liste complète des codes ISO 4217', 'admin'); ?>" href="http://fr.wikipedia.org/wiki/ISO_4217" target="_blank"><?php echo trad('Code ISO 4217', 'admin'); ?></a></th>
                            <th><?php echo trad('Symbole', 'admin'); ?></th>
                            <th><?php echo trad('Taux_actuels', 'admin'); ?></th>
                            <th><?php echo trad('Défaut', 'admin'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(DeviseAdmin::getInstance()->getList() as $devise): ?>
                        <tr>
                            <td><?php echo $devise->id; ?></td>
                            <td><input type="text" class="input-small" name="nom[<?php echo $devise->id; ?>]" value="<?php echo $devise->nom ?>"></td>
                            <td><input type="text" class="input-small" name="code[<?php echo $devise->id; ?>]" value="<?php echo $devise->code ?>"></td>
                            <td><input type="text" class="input-small" name="symbole[<?php echo $devise->id; ?>]" value="<?php echo $devise->symbole; ?>"></td>
                            <td><input type="text" class="input-small" name="taux[<?php echo $devise->id; ?>]" value="<?php echo $devise->taux; ?>"></td>
                            <td><input type="radio" name="defaut" value="<?php echo $devise->id; ?>" <?php if($devise->defaut): ?> checked="checked" <?php endif; ?> ></td>
                            <td>
                                <div class="btn-group">
                                    <a class="btn btn-mini js-delete-devise" href="#deleteDevise" devise-id="<?php echo $devise->id; ?>" devise-nom="<?php echo $devise->nom; ?>"><i class="icon-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="7">
                                <p class="span4 offset1">
                                    <a class="btn btn-large btn-block btn-primary" href="devise.php?action=refresh"><?php echo trad('Mettre les taux de change à jour', 'admin'); ?></a>
                                </p> 
                                <p class="span4 offset2">
                                    <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                                </p> 
                        </tr>
                    </tbody>
                </table>
                </form>
            </div>
        </div>
    </div>
    <div class="modal hide fade in" id="deleteDevise">
        <div class="modal-header">
            <div class="modal-header"> <a class="close" data-dismiss="modal">×</a>
                <h3><?php echo trad('SUPPRIMER_DEVISE', 'admin'); ?></h3>
            </div>
            <div class="modal-body">
                <p><?php echo trad('DeleteDeviseWarning', 'admin'); ?></p>
                <p id="deviseDelationInfo"></p>
            </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                <a class="btn btn-primary" id="deviseDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
            </div>
        </div>
    </div>
    <div class="modal hide fade in" id="deviseAddModal">
        <form method="post" action="devise.php">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">×</a>
            <h3><?php echo trad("AJOUT_DEVISE","admin"); ?></h3>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <tr>
                    <td><?php echo trad('Designation', 'admin'); ?></td>
                    <td><input type="text" name="nom"></td>
                </tr>
                <tr>
                    <td><?php echo trad('Code ISO 4217', 'admin'); ?></td>
                    <td><input type="text" name="code"></td>
                </tr>
                <tr>
                    <td><?php echo trad('Symbole', 'admin'); ?></td>
                    <td><input type="text" name="symbole"></td>
                </tr>
                <tr>
                    <td><?php echo trad('Taux_actuels', 'admin'); ?></td>
                    <td><input type="text" name="taux"></td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
            <button type="submit" class="btn btn-primary"><?php echo trad('Ajouter', 'admin'); ?></button>
        </div>
        </form>
    </div>
<?php require_once("pied.php"); ?> 
<script type="text/javascript">
    $(document).ready(function(){
        $(".js-delete-devise").click(function(e){
            $("#deviseDelationInfo").html($(this).attr("devise-nom"));
            $("#deviseDelationLink").attr("href","devise.php?action=supprimer&id="+$(this).attr("devise-id"));
            $("#deleteDevise").modal("show");
        });
    });
</script>
</body>
</html>