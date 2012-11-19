<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

ActionsAdminPays::getInstance()->action($request);

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Configuration', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('Gestion des pays', 'admin'); ?>
            </h3>
            <div class="bigtable">
            <table class="table table-striped" id="table-pays">
                <thead>
                    <th><?php echo trad('ID', 'admin'); ?></th>
                    <th><?php echo trad('Nom', 'admin'); ?></th>
                    <th><?php echo trad('TVA', 'admin'); ?></th>
                    <th><?php echo trad('Défaut', 'admin'); ?></th>
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
        
        $("#table-pays input[name=tva]").click(function(){
           $.ajax({
               url : "ajax/pays.php",
               data : {
                   action : "changeTva",
                   id : $(this).attr('value')
               }
           }); 
        });
    });
</script>
</body>
</html>