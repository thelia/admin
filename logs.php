<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$adm = new TlogAdmin();

$command = $request->get("action");

switch($command)
{
    case 'maj_config' :
        $adm->update_config();
    break;
}

$adm->prepare_page();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("logs_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_log', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('Gestion_log', 'admin'); ?></h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("logs");
?>            
                <form method="post" action="logs.php" id="ms_form">
                    <input type="hidden" name="action" value="maj_config">
                <div class="bigtable">
                <table class="table table-striped">
                    <thead>
                        <caption><h3>CONFIGURATION</h3></caption>
                            
                    </thead>    
                    <tbody>
                        <tr>
                            <td >
                                <?php echo trad("message_affiche","admin"); ?>
                                <p>
                                    <small><?php echo trad("message_affiche_desc","admin"); ?></small>
                                </p>
                            </td>
                            <td >
                                <select name="<?php echo Tlog::VAR_NIVEAU ?>">
                                    <option value="<?php echo Tlog::MUET ?>">Désactivé</option>
                                    <option value="<?php echo Tlog::TRACE ?>" <?php if ($adm->niveau == Tlog::TRACE) echo 'selected="selected"'; ?> ><?php echo trad("trace","admin"); ?></option>
                                    <option value="<?php echo Tlog::DEBUG ?>" <?php if ($adm->niveau == Tlog::DEBUG) echo 'selected="selected"'; ?> ><?php echo trad("debug","admin"); ?></option>
                                    <option value="<?php echo Tlog::INFO ?>" <?php if ($adm->niveau == Tlog::INFO) echo 'selected="selected"'; ?> ><?php echo trad("info","admin"); ?></option>
                                    <option value="<?php echo Tlog::WARNING ?>" <?php if ($adm->niveau == Tlog::WARNING) echo 'selected="selected"'; ?> ><?php echo trad("alert","admin"); ?></option>
                                    <option value="<?php echo Tlog::ERROR ?>" <?php if ($adm->niveau == Tlog::ERROR) echo 'selected="selected"'; ?> ><?php echo trad("error","admin"); ?></option>
                                    <option value="<?php echo Tlog::FATAL ?>" <?php if ($adm->niveau == Tlog::FATAL) echo 'selected="selected"'; ?> ><?php echo trad("fatal_error","admin"); ?> fatale</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo trad("entete_logs","admin"); ?>
                                <ul>
                                    <li>#NUM : <?php echo trad("num_ordre","admin"); ?></li>
                                    <li>#NIVEAU : <?php echo trad("message_level","admin"); ?></li>
                                    <li>#FICHIER : <?php echo trad("file_name","admin"); ?></li>
                                    <li>#FONCTION : <?php echo trad("function_name","admin"); ?></li>
                                    <li>#LIGNE : <?php echo trad("line_number","admin"); ?></li>
                                    <li>#DATE : <?php echo trad("date_format","admin"); ?></li>
                                    <li>#HEURE : <?php echo trad("hour_format","admin"); ?></li>
                                </ul>
                            </td>
                            <td>
                                <input type="text" class="input-xxlarge" name="<?php echo Tlog::VAR_PREFIXE ?>" value="<?php echo htmlspecialchars(Variable::lire(Tlog::VAR_PREFIXE, Tlog::DEFAUT_PREFIXE)); ?>" >
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo trad("display_redirect","admin"); ?>
                                <p>
                                    <small><?php echo trad("display_redirect_desc","admin"); ?></small>
                                </p>
                            </td>
                            <td>
                                <label class="radio inline"><?php echo trad("Oui","admin"); ?><input type="radio" name="<?php echo Tlog::VAR_SHOW_REDIRECT ?>" value="1" <?php if (Variable::lire(Tlog::VAR_SHOW_REDIRECT, Tlog::DEFAUT_SHOW_REDIRECT) == 1) echo 'checked="checked"'?> /></label>
                                <label class="radio inline"><?php echo trad("Non","admin"); ?><input type="radio" name="<?php echo Tlog::VAR_SHOW_REDIRECT ?>" value="0" <?php if (Variable::lire(Tlog::VAR_SHOW_REDIRECT, Tlog::DEFAUT_SHOW_REDIRECT) == 0) echo 'checked="checked"'?> /></label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo trad("display_IP","admin"); ?>
                                <p>
                                    <small><?php echo trad("display_IP_desc","admin"); ?></small>
                                </p>
                            </td>
                            <td>
                                <input type="text" name="<?php echo Tlog::VAR_IP ?>" value="<?php echo htmlspecialchars(Variable::lire(Tlog::VAR_IP, Tlog::DEFAUT_IP)); ?>" >
                                <p>
                                    <small><?php echo trad("actual_IP","admin"); ?> <?php echo $request->getClientIp(); ?></small>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo trad("activate_file_logs","admin"); ?>
                                <p>
                                    <small><?php echo trad("activate_file_logs_desc","admin"); ?></small>
                                </p>
                            </td>
                            <td>
                                <input type="hidden" name="<?php echo Tlog::VAR_FILES ?>" value="<?php echo htmlspecialchars(Variable::lire(Tlog::VAR_FILES, Tlog::DEFAUT_FILES)); ?>" >
                                <?php if (Variable::lire(Tlog::VAR_FILES, Tlog::DEFAUT_FILES) != '') { ?>

                                        <ul class="unstyled">
                                        <?php
                                        $files = explode(";", Variable::lire(Tlog::VAR_FILES, Tlog::DEFAUT_FILES));
                                        $idx = 0;
                                        if ($files) foreach($files as $file) {
                                                ?>
                                                <li>
                                                    <a href="#" class="js-delete-file" file-id="<?php echo $idx; ?>"><i class="icon-trash"></i></a>
                                                        <?php echo $file; if ($file == '*') echo " (tous les fichiers)"; ?>
                                                </li>
                                                <?php
                                                $idx++;
                                        }
                                        ?>
                                        </ul>
                                <?php } ?>
                                <?php echo trad('add_file','admin'); ?> : <input type="text" name="fichier" value="" /><input type="submit" value="OK" />
                            </td>
                        </tr>
                    </tbody>
                </table>    
                    
                <p>
                    <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                </p>
            </div>
            <div class="bigtable">
                <table class="table">
                    <thead>
                        <caption>
                            <h3>DESTINATIONS</h3>
                            <p>
                                <?php echo trad("destination_explain","admin"); ?>
                            </p>
                        </caption>
                    </thead> 
                    <tbody>
                        <?php 
                            $actives = $adm->liste_destinations_actives();
                            foreach($adm->liste_destinations() as $nomclasse => $destination)
                            {
                                $titre = $destination->get_titre();
                                $label = $destination->get_description();

                                $active = in_array($nomclasse, $actives);
                        ?>    
                          <tr class="info">
                             <input type="hidden" name="destinations[]" value="<?php echo $nomclasse;?>" > 
                             <td colspan="2">
                                 <label class="checkbox inline"><strong><?php echo $titre; ?></strong> <input type="checkbox" name="<?php echo $nomclasse;?>_actif" class="js-toggle-destination" destination-class="<?php echo $nomclasse; ?>" <?php echo ($active ? 'checked="checked"' : "") ?> > </label>
                                 <p>
                                     <small><?php echo $label; ?></small>
                                 </p>
                             </td>
                          </tr>
                          
                        <?php
                            $disabled = $active ? '' : 'disabled="disabled"';

                            $configs = $destination->get_configs();

                            $idx = 0;
                                foreach($configs as $config) {
                                    ?>
                                    <tr class="<?php echo $nomclasse; ?> warning" <?php if ($disabled) echo 'style="display: none"'; ?>>
                                        <td>
                                            <?php echo $config->titre; ?>:
                                            <p>
                                                <small><?php echo $config->label; ?></small>
                                            </p>
                                        </td>
                                        <td class="valeur-destination" rel="<?php echo $nomclasse; ?>">
                                            <?php
                                                switch($config->type) {
                                                    default:
                                                    case TlogDestinationConfig::TYPE_TEXTFIELD:
                                                        ?>
                                                        <input class="input-xxlarge" type="text" name="<?php echo $nomclasse;?>_<?php echo $config->nom; ?>" value="<?php echo htmlspecialchars($config->valeur); ?>" <?php echo $disabled ?>/>
                                                        <?php
                                                    break;

                                                    case TlogDestinationConfig::TYPE_TEXTAREA:
                                                        ?>
                                                        <textarea class="input-xxlarge" name="<?php echo $nomclasse;?>_<?php echo $config->nom; ?>" <?php echo $disabled ?>><?php echo $config->valeur; ?></textarea>
                                                        <?php
                                                    break;
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                if (count($configs) == 0) {
                                        ?>
                                <tr class="warning <?php echo $nomclasse; ?>" <?php if ($disabled) echo 'style="display: none"'; ?>>
                                                <td colspan="2">
                                                        Cette destination n'offre pas de possibilité de configuration.
                                                </td>
                                        </tr>
                                        <?php
                                }
                            }
                        ?>
                    </tbody>
                </table>
                </form>
                <p>
                    <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                </p>
            </div>
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("logs_bottom");
?>  
<?php require_once("pied.php"); ?> 
    <script type="text/javascript">
        $(document).ready(function(){
            $(".js-delete-file").click(function(e){
                e.preventDefault();
                var files = $('input[name=<?php echo Tlog::VAR_FILES ?>]').val().split(";"), idx = $(this).attr("file-id");

                files.splice(idx, 1);

                $('input[name=<?php echo Tlog::VAR_FILES ?>]').val(files.join(';'));

                $('#ms_form').submit();
            });
            
            $(".js-toggle-destination").click(function(e){
               var $this = $(this), id=$this.attr("destination-class");
               if($this.is(":checked")){
                    $('td[rel='+id+'] input, td[rel='+id+'] textarea').removeAttr('disabled');
                    $('tr.'+id).show();
               } else {
                    $('td[rel='+id+'] input, td[rel='+id+'] textarea').attr('disabled', 'disabled');
                    $('tr.'+id).hide();
               }
            });
        })
    </script>
</body>
</html>