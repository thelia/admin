<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

if (false == $lang = $request->get("lang", false))
    $lang = ActionsLang::instance()->get_id_langue_courante();

$autorisation = new Autorisation();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_droit', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('Gestion_droit', 'admin'); ?></h3>
                <select class="input-xlarge" onchange="location='droits.php?id=' + this.value" id="changeAdmin">
                    <option value=""><?php echo trad('Select_administrateur', 'admin'); ?></option>
                    <?php
                            $administrateur = new Administrateur();
                            $query = "select * from $administrateur->table where profil<>1";
                            $resul = mysql_query($query, $administrateur->link);
                            while($row = mysql_fetch_object($resul)){
                    ?>
                            <option value="<?php echo $row->id; ?>" <?php if(isset($_GET['id']) && $_GET['id'] == $row->id) { ?> selected="selected" <?php } ?>><?php echo $row->identifiant; ?></option>
                    <?php
                            }
                    ?>
		</select>
        </div>
    </div>
    <?php if(false !== $id = $request->get("id", false)): ?>
    <div class="row-fluid">
        <div class="span12">
            <div class="bigtable">
                <table class="table table-striped">
                    <thead>
                        <caption>
                            <h3><?php echo trad('Droits_generaux', 'admin'); ?></h3>
                        </caption>
                    <tr>
                        <th><?php echo trad('Autorisation', 'admin'); ?></th>
                        <th><?php echo trad('Description', 'admin'); ?></th>
                        <th><?php echo trad('Acces', 'admin'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach($autorisation->query_liste("SELECT * FROM ".$autorisation->table) as $row){
                                $autorisationdesc = new Autorisationdesc();
                                $autorisationdesc->charger($row->id, $lang);

                                $autorisation_administrateur = new Autorisation_administrateur();
                                $autorisation_administrateur->charger($row->id, $id);
                        ?>
                            <tr>
                                <td><?php echo $autorisationdesc->titre; ?></td>
                                <td><?php echo $autorisationdesc->description; ?></td>
                                <td><input type="checkbox" class="js-admin-modif" droit-id="<?php echo $row->id; ?>" <?php if($autorisation_administrateur->lecture) { ?> checked="checked" <?php } ?> ></td>
                            </tr>
                        <?php   
                            }
                        ?>
                       
                    </tbody>
                </table>
            </div>
            <div class="bigtable">
                <table class="table table-striped">
                    <thead>
                        <caption>
                            <h3><?php echo trad('Droits_modules', 'admin'); ?></h3>
                        </caption>
                        <tr>
                            <th><?php echo trad('Module', 'admin'); ?></th>
                            <th><?php echo trad('Description', 'admin'); ?></th>
                            <th><?php echo trad('Acces', 'admin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $liste = ActionsAdminModules::instance()->lister(false, true);

                            foreach($liste as $module)
                            {
                                $autorisation_modules = new Autorisation_modules();
                                $autorisation_modules->charger($module->id, $id);

                                if (ActionsAdminModules::instance()->est_administrable($module->nom))
                                {
                                    $modulesdesc = new Modulesdesc();
                                    $modulesdesc->charger($module->id);

                                ?>
                                    <tr>
                                        <td><?php echo ActionsAdminModules::instance()->lire_titre_module($module); ?></td>
                                        <td><?php echo $modulesdesc->description; ?></td>
                                        <td><input type="checkbox" class="js-module-admin" module-id="<?php echo $module->id; ?>" <?php if($autorisation_modules->autorise) { ?> checked="checked" <?php } ?> ></td>
                                    </tr>
                                <?php
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php require_once("pied.php"); ?> 
    <script type="text/javascript">
        $(document).ready(function(){
            
            $(".js-admin-modif").click(function(){
                var valeur = $(this).is(':checked');
                if(valeur == true){
                    valeur = 1;
                } else {
                    valeur = 0;
                }
                
                $.ajax({type:'GET', url:'ajax/droits.php', data:'type_droit=1&autorisation='+$(this).attr("droit-id")+'&administrateur=<?php echo $request->get("id") ?>' + '&valeur=' + valeur})
            });
            
            $(".js-module-admin").click(function(){
                var valeur = $(this).is(':checked');
                if(valeur == true){
                    valeur = 1;
                } else {
                    valeur = 0;
                }
                
                $.ajax({type:'GET', url:'ajax/droits.php', data:'type_droit=2&module='+$(this).attr("module-id")+'&administrateur=<?php echo $request->get("id") ?>' + '&valeur=' + valeur})
            });
            
            $("#changeAdmin").change(function(){
               document.location = "droits.php?id="+$(this).val();
            });
        });
    </script>
</body>
</html>