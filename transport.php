<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("transport_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_transports', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('Gestion_transports', 'admin'); ?></h3>
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("transport");
?>
    <div class="row-fluid">
        <div class="span7">
            <table class="table table-striped">
                <thead>
                    <caption>
                        <h3><?php echo trad('LISTE_TRANSPORTS', 'admin'); ?></h3>
                    </caption>
                </thead>
                <tbody>
                    <?php 
                    $liste = ActionsAdminModules::instance()->lister(Modules::TRANSPORT, true);
                    
                    foreach($liste as $module)
                    {
                    ?>    
                    <tr>
                        <td><?php echo ActionsAdminModules::instance()->lire_titre_module($module); ?></td>
                        <td><a href="transport.php?id=<?php echo $module->id; ?>#lzone"><?php echo trad('editer', 'admin'); ?></a></td>
                    </tr>
                    <?php    
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (false !== $id = $request->query->get("id", false)){ 
        $module = new Modules($id);
    ?>
    <a name="lzone">&nbsp;</a>
    <div class="row-fluid">
        <div class="span7">
            <table class="table table-striped">
                <thead>
                    <caption>
                        <h3><?php echo trad('MODIFICATION_TRANSPORT', 'admin').' '.ActionsAdminModules::instance()->lire_titre_module($module); ?></h3>
                    </caption>
                </thead>
                <tbody id="listZone">
                    <tr>
                        <td>
                            <select class="form_select" id="zone">
                            <?php
                            $query = "SELECT z.id, z.nom FROM ".Zone::TABLE." z LEFT JOIN ".Transzone::TABLE." tz ON z.id=tz.zone AND tz.transport=".$id." where ISNULL(tz.transport)";
                            foreach($module->query_liste($query) as $zone)
                            {
                            ?>
                                <option value="<?php echo $zone->id; ?>" id="zone<?php echo $zone->id; ?>" zone-name="<?php echo $zone->nom; ?>"><?php echo $zone->nom; ?></option>
                            <?php
                            }
                            ?>
                            </select>
                        </td>
                        <td><a href="#" class="btn" id="addZone"><?php echo trad('AJOUTER_ZONE', 'admin'); ?></a></td>
                    </tr>
                    <?php
                    $query = "SELECT z.nom, z.id, tz.id as tzid FROM ".Zone::TABLE." z left join ".Transzone::TABLE." tz on tz.zone=z.id where tz.transport=".$id;
                    foreach($module->query_liste($query) as $zone)
                    {
                    ?>    
                    <tr id="transzone<?php echo $zone->id ?>">
                        <td><?php echo $zone->nom; ?></td>
                        <td><a href="#" class="btn btn-mini js-delete-zone" tz-id="<?php echo $zone->tzid; ?>" zone-id="<?php echo $zone->id; ?>" zone-name="<?php echo $zone->nom; ?>"><i class="icon-trash"></i></a></td>
                    </tr>
                    <?php    
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>
    
<?php
	ActionsAdminModules::instance()->inclure_module_admin("transport_bottom");
?>
    
<?php require_once("pied.php"); ?> 
    <?php if (false !== $id = $request->query->get("id", false)){  ?>
    <script type="text/javascript">
        $(document).ready(function(){
           $("#addZone").live("click", function(e){
              e.preventDefault(); 
              var zoneid = $("#zone").val();
              $.ajax({
                  url : "ajax/transport.php",
                  data : {
                      action : "ajouter",
                      zone : zoneid,
                      id : "<?php echo $id; ?>"
                  },
                  dataType : "json",
                  success : function(response){
                      if(response.res == 1){
                          var zone = $("#zone"+zoneid), name = zone.attr("zone-name");
                          zone.remove();
                          $("#listZone").append("<tr id=\"transzone"+zoneid+"\"><td>"+name+"</td><td><a href=\"#\" class=\"btn btn-mini js-delete-zone\" zone-name=\""+name+"\" tz-id=\""+response.id+"\" zone-id=\""+zoneid+"\"><i class=\"icon-trash\"></i></a></td></tr>");
                      }
                  }
              });
           });
           
           $(".js-delete-zone").live("click", function(e){
               e.preventDefault(); 
               var zone = $(this),
               zoneId = zone.attr("zone-id"),
               zoneName = zone.attr("zone-name"),
               tzId = zone.attr("tz-id");
               
               $.ajax({
                   url : "ajax/transport.php",
                   data : {
                       action : "supprimer",
                       zone : tzId
                   },
                   success : function(res){
                       if(res == 1){
                           $("#transzone"+zoneId).remove();
                           $("#zone").append("<option value=\""+zoneId+"\" id=\"zone"+zoneId+"\" zone-name=\""+zoneName+"\">"+zoneName+"</option>");
                       }
                   }
               })
           })
        });
    </script>
    <?php } ?>
</body>
</html>