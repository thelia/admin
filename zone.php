<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

ActionsAdminZone::getInstance()->action($request);

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("zone_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_zones_livraison', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('LISTE_ZONES', 'admin'); ?>
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#adminAddZone" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("zone");
?>
            <div class="bigtable">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo trad("Description", "admin"); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(ZoneAdmin::getInstance()->getList() as $zone): ?>
                        <tr>
                            <td><?php echo $zone->nom; ?></td>
                            <td>
                                <div class="btn-group">
                                    <a class="btn btn-mini" href="zone.php?id=<?php echo $zone->id; ?>&action=showZone#zone"><i class="icon-edit"></i></a>
                                    <a href="zone.php?action=supprimer&id=<?php echo $zone->id; ?>" class="btn btn-mini"><i class="icon-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php if(false !== $request->query->get("id", false) && $request->query->get("action") == "showZone"): 
        $zone = new Zone($request->query->get("id"));
    
?>
    <div class="row-fluid">
        <div class="span12">
            <a name="zone">&nbsp;</a>
            <h3><?php echo trad('MODIFICATION_ZONE', 'admin')." ".$zone->nom; ?></h3>
            <table class="table table-striped">
                <tbody id="listZone">
                    <tr>
                        <td>
                            <select class="form_select" id="pays">
                                <?php foreach($zone->query_liste("SELECT p.id, ps.titre FROM ".Pays::TABLE." p left join ".Paysdesc::TABLE." ps on p.id=ps.pays and ps.lang=".ActionsAdminLang::instance()->get_id_langue_courante()." where p.zone='-1'") as $pays): ?>
                                    <option value="<?php echo $pays->id; ?>"><?php echo $pays->titre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <a href="#" class="btn" id="addCountry"><?php echo trad('AJOUTER_PAYS', 'admin'); ?></a>
                        </td>
                    </tr>
                    <?php foreach($zone->query_liste("SELECT p.id, ps.titre FROM ".Pays::TABLE." p LEFT JOIN ".Paysdesc::TABLE." ps ON p.id=ps.pays and ps.lang=".ActionsAdminLang::instance()->get_id_langue_courante()." WHERE p.zone=".$id) as $pays): ?>
                    <tr id="pays<?php echo $pays->id; ?>">
                        <td><?php echo $pays->titre; ?></td>
                        <td><a href="#" class="btn btn-mini js-delete-country" country-id="<?php echo $pays->id; ?>" country-name="<?php echo $pays->titre; ?>"><i class="icon-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><?php echo trad('Forfait transport: ', 'admin'); ?></td>
                        <td><input type="text" class="form_inputtext" id="forfait" value="<?php echo htmlspecialchars($zone->unite); ?>" /></td>
                        <td><a href="#" class="btn" id="port"><?php echo trad('VALIDER', 'admin'); ?></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <div class="modal hide fade in" id="adminAddZone">
        <form method="post" action="zone.php">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('AJOUTER_ZONE', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <tr>
                    <td><?php echo trad("Nom", "admin"); ?></td>
                    <td><input type="text" name="nom"></td>
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
	ActionsAdminModules::instance()->inclure_module_admin("zone_bottom");
?>
    
<?php require_once("pied.php"); ?> 
    <?php if(false !== $request->query->get("id", false) && $request->query->get("action") == "showZone"): ?>
    <script type="text/javascript">
        $(document).ready(function(){
           $("#addCountry").click(function(e){
               e.preventDefault();
               var pays = $("#pays"), paysId = pays.val(), paysOption = pays.find(":selected");
               if(pays.val() != ""){
                   $.ajax({
                      url : "ajax/zone.php",
                      data : {
                          action : "ajouter",
                          pays : paysId,
                          id : "<?php echo $request->query->get("id"); ?>"
                      },
                      success : function(response){
                          if(response == 1){
                              $("#listZone").append("<tr id=\"pays"+paysId+"\"><td>"+paysOption.text()+"</td><td><a href=\"#\" class=\"btn btn-mini js-delete-country\" country-id=\""+paysId+"\" country-name=\""+paysOption.text()+"\"><i class=\"icon-trash\"></i></a></td></tr>");
                              paysOption.remove();
                          }
                      }
                   });
               }
           });
           
           $(".js-delete-country").live("click", function(e){
                e.preventDefault();
                var $this = $(this);
                $.ajax({
                   url : "ajax/zone.php",
                   data : {
                       action : "supprimer",
                       pays : $this.attr("country-id")
                   },
                   success : function(response){
                       if (response == 1) {
                           $("#pays").append("<option value=\""+$this.attr("country-id")+"\">"+$this.attr("country-name")+"</option>")
                           $("#pays"+$this.attr("country-id")).remove();
                       }
                   }
                });
           });
           
           $("#port").click(function(e){
               e.preventDefault();
               $.ajax({
                   url : "ajax/zone.php",
                   data : {
                       action : "forfait",
                       valeur : $("#forfait").val(),
                       id : "<?php echo $request->query->get("id"); ?>"
                   },
                   success : function(response){
                       if(response == 1){
                           $("#forfait").css("border","1px solid #0AFF43");
                           $("#forfait").css("box-shadow", "0 0 8px #23FF8A");
                           setInterval(function(){
                               $("#forfait").removeAttr("style");
                           }, 1000);
                       }
                   }
               })
           });
        });
    </script>
    <?php endif; ?>
</body>
</html>