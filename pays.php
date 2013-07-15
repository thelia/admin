<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$errorCode = 0;
try
{
    ActionsAdminPays::getInstance()->action($request);
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
	ActionsAdminModules::instance()->inclure_module_admin("pays_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion des pays', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('Gestion des pays', 'admin'); ?>
                <div class="btn-group">
                    <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#countryAddModal" data-toggle="modal">
                        <i class="icon-plus-sign icon-white"></i>
                    </a>
                </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("pays");
?>   
            <div class="bigtable">
            <table class="table table-striped" id="table-pays">
                <thead>
                    <th><?php echo trad('ID', 'admin'); ?></th>
                    <th><?php echo trad('Nom', 'admin'); ?></th>
                    <th><?php echo trad('TVA', 'admin'); ?></th>
                    <th><?php echo trad('Défaut', 'admin'); ?></th>
                    <th><?php echo trad('Boutique', 'admin'); ?></th>
                    <th><?php echo trad('N° ISO', 'admin'); ?></th>
                    <th><?php echo trad('Codes ISO', 'admin'); ?></th>
                    <th>&nbsp;</th>
                </thead>
                <tbody>
                    <?php foreach(PaysAdmin::getInstance()->getList() as $pays): ?>
                    <tr>
                        <td><?php echo $pays->id; ?></td>
                        <td><?php echo $pays->titre; ?></td>
                        <td><input type="checkbox" name="tva" value="<?php echo $pays->id; ?>" <?php if($pays->tva): ?> checked="checked" <?php endif; ?>></td>
                        <td><input type="radio" name="defaut" value="<?php echo $pays->id; ?>" <?php if($pays->defaut): ?> checked="checked" <?php endif; ?>></td>
                        <td><input type="radio" name="boutique" value="<?php echo $pays->id; ?>" <?php if
                            ($pays->boutique): ?> checked="checked" <?php endif; ?>></td>
                        <td><?php echo $pays->isocode; ?></td>
                        <td><?php echo $pays->isoalpha2 ?>/<?php echo $pays->isoalpha3; ?></td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini" data-toggle="modal" href="pays_edit.php?id=<?php echo $pays->id; ?>" data-target="#editPays"><i class="icon-edit"></i></a>
                                <a class="btn btn-mini js-delete-pays" href="#deleteCountry" country-id="<?php echo $pays->id; ?>" country-name="<?php echo $pays->titre ?>"><i class="icon-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="modal hide fade in" id="deleteCountry">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('SUPPRIMER_PAYS', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <p><?php echo trad("DeleteCountryWarning") ?></p>
            <p id="countryDelationInfo"></p>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
            <a class="btn btn-primary" id="countryDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
    <div class="modal hide fade in" id="editPays">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('EDITION', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            
        </div>
    </div>
    <div class="modal hide fade in" id="countryAddModal">
        <form method="post" action="pays.php">
        <input type="hidden" name="action" value="addCountry">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('AJOUTER_PAYS', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <?php if($errorCode > 0): ?>
                <div class="alert alert-block alert-error" id="countryError">
                    <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                    <p><?php echo trad('country_error_'.$errorCode, 'admin'); ?></p>
                </div>
            <?php endif; ?>
            <table class="table table-striped">
                <tr class="<?php if($errorCode == TheliaAdminException::COUNTRY_TITLE_EMPTY) echo "error"; ?>">
                    <td><?php echo trad('Nom', 'admin'); ?></td>
                    <td><input type="text" name="titre" value="<?php if($errorCode > 0) echo $request->request->get("titre"); ?>"></td>
                </tr>
                <tr>
                    <td><?php echo trad('ISO-3166', 'admin') ?></td>
                    <td><input type="text" name="isocode" value="<?php if($errorCode > 0) echo $request->request->get("isocode"); ?>"></td>
                </tr>
                <tr>
                    <td><?php echo trad('alpha-2', 'admin') ?></td>
                    <td><input type="text" name="isoalpha2" value="<?php if($errorCode > 0) echo $request->request->get("isoalpha2"); ?>"></td>
                </tr>
                <tr>
                    <td><?php echo trad('alpha-3', 'admin') ?></td>
                    <td><input type="text" name="isoalpha3" value="<?php if($errorCode > 0) echo $request->request->get("isoalpha3"); ?>"></td>
                </tr>
                <tr>
                    <td><?php echo trad('TVA_francaise', 'admin') ?></td>
                    <td>
                        <label class="radio inline"><?php echo trad('Oui', 'admin'); ?><input type="radio" name="tva" value="1" <?php if($errorCode > 0 && $request->request->get("tva") == 1){ echo 'checked="checked"'; } else if($errorCode == 0) { echo 'checked="checked"'; }?>></label>
                        <label class="radio inline"><?php echo trad('Non', 'admin'); ?><input type="radio" name="tva" value="0" <?php if($errorCode > 0 && $request->request->get("tva") == 0) echo 'checked="checked"'; ?> ></label>
                    </td>
                </tr>
                <tr>
                    <td><?php echo trad('Zone_transport', 'admin'); ?></td>
                    <td>
                        <select name="zone">
                            <option value="0"><?php echo trad('Aucune', 'admin'); ?></option>
                            <?php 
                                $pays = new Pays();
                                foreach($pays->query_liste("SELECT * FROM ".Zone::TABLE." ORDER BY nom") as $zone):
                            ?>
                            <option value="<?php echo $zone->id; ?>" <?php if($errorCode > 0 && $request->request->get("zone") == $zone->id): ?>selected="selected"<?php endif; ?>><?php echo $zone->nom; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
            <button type="submit" class="btn btn-primary"><?php echo trad('Ajouter', 'admin'); ?></button>
        </div>
        </form>    
    </div>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("pays_bottom");
?>

<?php require_once("pied.php"); ?> 
<script type="text/javascript">
    $(document).ready(function(){
        $(".js-delete-pays").click(function(){
            var $this = $(this);
            $("#countryDelationInfo").html($this.attr("country-name"));
            $("#countryDelationLink").attr("href","pays.php?action=deleteCountry&id="+$this.attr('country-id'));
            $("#deleteCountry").modal("show");
        });
        
        $("#table-pays input[name=defaut]").click(function(){
            $.ajax({
               url : "ajax/pays.php",
               data : {
                   action : "changeDefault",
                   id : $(this).attr('value')
               }
            });
        });

        $("#table-pays input[name=boutique]").click(function(){
            $.ajax({
                url : "ajax/pays.php",
                data : {
                    action : "changeBoutique",
                    id : $(this).attr('value')
                }
            });
        });
        
        $("#table-pays input[name=tva]").click(function(){
           $.ajax({
               url : "ajax/pays.php",
               data : {
                   action : "changeTva",
                   id : $(this).attr('value')
               }
           }); 
        });
        
        <?php if($errorCode): ?>
            $("#countryAddModal").modal("show");
            $("#countryAddModal").on("hidden", function(){
                $("#countryError").remove();
                $("#countryAddModal tr[class=error]").each(function(){
                    $(this).removeClass("error");
                })
            });
        <?php endif; ?>
    });
</script>
</body>
</html>