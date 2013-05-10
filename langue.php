<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

if (false == $action = $request->get("action", false))
    $action = "";

switch ($action) {
    case "modifier":

        // Mettre à jour les paramètres
        ActionsAdminLang::instance()->maj_parametres(
            $request->get('un_domaine_par_langue'),
            $request->get('action_si_trad_absente'),
            $request->get('urlsite')
        );

        // Appliquer les modifications
        foreach($request->get('description') as $id => $description) {
            ActionsAdminLang::instance()->modifier(
                $id,
                $description,
                $request->get("code[".$id."]", null, true),
                $request->get("url[".$id."]", null, true),
                ($id == $request->get('defaut')) ? 1 : 0
            );
        }
        redirige("langue.php");
    break;
    case "supprimer":
        ActionsAdminLang::instance()->supprimer($request->query->get("id"));
        break;
    case "ajouter":
        ActionsAdminLang::instance()->ajouter(
                $request->get('ajout_description'),
                $request->get('ajout_code'),
                $request->get('ajout_url')
            );
        break;
}

$langs = LangAdmin::getInstance()->getList();

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("langue_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_langue', 'admin'));
require_once("entete.php");
?>
    <form method="post" action="langue.php">
    <input type="hidden" name="action" value="modifier">
<div class="row-fluid">
    <div class="span6">
        <div class="row-fluid">
            <div class="span12">
            <h3>
                <?php echo trad('GERER LES LANGUES', 'admin'); ?>
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#langAddModal" data-toggle="modal">
                    <i class="icon-plus-sign"></i>
                </a>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("langue");
?>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?php echo trad('Nom de la langue', 'admin'); ?></th>
                    <th><a title="<?php echo trad('Voir la liste complète des codes ISO 639-1', 'admin'); ?>" style="color: #2F3D46; font-weight: normal" href="http://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1" target="_blank"><?php echo trad('Code ISO 639', 'admin'); ?></a></th>
                    <th><?php echo trad('Par défaut', 'admin'); ?></th>
                    <th><?php echo trad('Supprimer', 'admin'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($langs as $lang): ?>
                    <tr>
                        <td><input type="text" name="description[<?php echo($lang->id); ?>]" value="<?php echo(htmlspecialchars($lang->description)); ?>" ></td>
                        <td><input type="text" class="input-mini" name="code[<?php echo($lang->id); ?>]" value="<?php echo(htmlspecialchars($lang->code)); ?>" ></td>
                        <td><input type="radio" name="defaut" value="<?php echo($lang->id); ?>" <?php if ($lang->defaut) echo 'checked="checked"'; ?>></td>
                        <td>
                            <div class="btn-group">
                                <a href="#" class="btn btn-mini js-lang-delete" lang-id="<?php echo $lang->id; ?>"><i class="icon-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="span6">
        <div class="row-fluid">
            <div class="span12">
                <h3><?php echo trad('PARAMETRES', 'admin'); ?></h3>
                <table class="table table-striped">
                    <tr>
                        <td><?php echo trad('Si une traduction est absente ou incomplète :', 'admin'); ?></td>
                        <td>
                            <select name="action_si_trad_absente" id="action_si_trad_absente">
                                    <option value="<?php echo ActionsLang::UTILISER_LANGUE_PAR_DEFAUT ?>" <?php if(ActionsAdminLang::instance()->get_action_si_trad_absente() == ActionsLang::UTILISER_LANGUE_PAR_DEFAUT) echo 'selected="selected"' ?>><?php echo trad('Remplacer par la langue par défaut', 'admin'); ?></option>
                                    <option value="<?php echo ActionsLang::UTILISER_LANGUE_INDIQUEE ?>" <?php if(ActionsAdminLang::instance()->get_action_si_trad_absente() == ActionsLang::UTILISER_LANGUE_INDIQUEE) echo 'selected="selected"' ?>><?php echo trad('Utiliser strictement la langue demandée', 'admin'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <h3><?php echo trad('ASSOCIATION LANGUE - URL', 'admin'); ?></h3>
                <table class="table table-striped">
                    <tr>
                        <td colspan="2"><label class="radio inline"><?php echo trad('Utiliser le même domaine pour toutes les langues', 'admin'); ?><input type="radio" class="js-change-method" lang-method="0" name="un_domaine_par_langue" <?php if (ActionsAdminLang::instance()->get_un_domaine_par_langue() == 0) echo 'checked="checked"' ?> > </label></td> 
                    </tr>
                    <tr>
                        <td><?php echo trad('URL du site', 'admin'); ?></td>
                        <td><input name="urlsite" class="input-xlarge urlsite" type="text" value="<?php echo  Variable::lire('urlsite'); ?>" <?php if(ActionsAdminLang::instance()->get_un_domaine_par_langue() == 1) echo 'disabled="disabled"'; ?> ></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label class="radio inline"><?php echo trad('Utiliser un domaine ou sous-domaine pour chaque langue', 'admin'); ?><input type="radio" class="js-change-method" lang-method="1"  name="un_domaine_par_langue" value="1" <?php if (ActionsAdminLang::instance()->get_un_domaine_par_langue() == 1) echo 'checked="checked"' ?> ></label>
                        </td>
                    </tr>
                    <?php
                        foreach($langs as $lang) {
                    ?>
                        <tr>
                            <td><?php echo($lang->description); ?></td>
                            <td><input class="input-xlarge urllangue" type="text" name="url[<?php echo($lang->id); ?>]" value="<?php echo  ($lang->url); ?>" <?php if(ActionsAdminLang::instance()->get_un_domaine_par_langue() == 0) echo 'disabled="disabled"'; ?>></td>
                        </tr>
                    <?php
                        }
                     ?>
                </table>
            </div>
        </div>
    </div>
</div>
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p> 
    </form>
    <div class="modal hide fade in" id="deleteLang">
        <div class="modal-body">
            <?php echo trad('Supprimer définitivement cette langue ?', 'admin'); ?>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('non', 'admin'); ?></a>
            <a class="btn btn-primary" id="deleteLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
    <div class="modal hide fade in" id="langAddModal">
        <form mthod="post" action="langue.php">
        <input type="hidden" name="action" value="ajouter">    
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 ><?php echo trad('Ajouter une langue', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><?php echo trad('Nom de la langue', 'admin'); ?></td>
                        <td><input type="text" name="ajout_description"></td>
                    </tr>
                    <tr>
                        <td><a title="<?php echo trad('Voir la liste complète des codes ISO 639-1', 'admin'); ?>" style="color: #2F3D46; font-weight: normal" href="http://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1" target="_blank"><?php echo trad('Code ISO 639', 'admin'); ?></a></td>
                        <td><input type="text" name="ajout_code"></td>
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
	ActionsAdminModules::instance()->inclure_module_admin("langue_bottom");
?>
<?php require_once("pied.php"); ?> 
    <script type="text/javascript"> 
        $(document).ready(function() {
            $(".js-change-method").click(function(){
                var $this = $(this), method = $this.attr("lang-method");
                if(method == 1){
                    $('.urlsite').attr("disabled", "disabled");
                    $('.urllangue').removeAttr("disabled"); 
                    $('.urllangue')[0].select();
                    
                } else {
                    $('.urllangue').attr("disabled", "disabled");
                    $('.urlsite').removeAttr("disabled");
                    $('.urlsite').select();
                }
            });
            
            $(".js-lang-delete").click(function(){
               $("#deleteLink").attr("href","langue.php?action=supprimer&id="+$(this).attr("lang-id"));
               $("#deleteLang").modal("show");
            });

	});
    
    </script>
</body>
</html>