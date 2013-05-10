<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$errorCode = 0;
$errorMessage = "";
$errorMultiple = false;
$errorMultipleArray = array();

if (false == $langue = $request->get("lang", false))
    $langue = ActionsLang::instance()->get_id_langue_courante();

try
{
    ActionsAdminAdministrateur::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    Tlog::error($e->getMessage());
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
    if($e->getCode() == TheliaAdminException::ADMIN_MULTIPLE_ERRORS)
    {
        $errorMultiple = true;
        $errorMultipleArray = $e->getData();
    }
}
$administrateur = new Administrateur();
$langs = LangAdmin::getInstance()->getList();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("gestadmin_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_administrateurs', 'admin'));
require_once("entete.php");
?>
    <form method="post" action="gestadm.php">
        <input type="hidden" name="action" value="modifier">
    
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('LISTE_ADMINISTRATEURS', 'admin'); ?>
                <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#adminAddModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("gestadm");
?>
            <div class="bigtable">
                
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo trad('Nom', 'admin'); ?></th>
                            <th><?php echo trad('Prenom', 'admin'); ?></th>
                            <th><?php echo trad('Identifiant', 'admin'); ?></th>
                            <th><?php echo trad('Langue', 'admin'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(AdministrateurAdmin::getInstance()->getList() as $admin): ?>
                        <tr <?php if($errorMultiple && array_key_exists($admin->id, $errorMultipleArray)): ?> class="error" <?php endif; ?>>
                                <td><input type="text" name="nom[<?php echo $admin->id; ?>]" value="<?php echo $admin->nom; ?>"></td>
                                <td><input type="text" name="prenom[<?php echo $admin->id; ?>]" value="<?php echo $admin->prenom; ?>"></td>
                                <td><input type="text" name="identifiant[<?php echo $admin->id; ?>]" value="<?php echo $admin->identifiant; ?>"></td>
                                <td>
                                    <select name="lang[<?php echo $admin->id; ?>]">
                                        <?php foreach($langs as $lang): ?>
                                            <option value="<?php echo $lang->id; ?>" <?php if($admin->lang == $lang->id) echo 'selected="selected"' ?>><?php echo $lang->description; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" title="<?php echo trad("Change_password", "admin"); ?>" class="btn btn-mini js-edit-admin" admin-id="<?php echo $admin->id; ?>">
                                            <i class="icon-lock"></i>
                                        </button>
                                    <?php
                                    if($admin->id != $_SESSION['util']->id) {
                                    ?>
                                        <a type="button" title="<?php echo trad("Edit_permissions", "admin"); ?>" href="gestadm_droits.php?administrateur=<?php echo $admin->id; ?>" class="btn btn-mini">
                                            <i class="icon-edit"></i>
                                        </a>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if($admin->id != $_SESSION['util']->id) {
                                    ?>
                                        <a class="btn btn-mini js-delete-admin" title="<?php echo trad("supprimer", "admin"); ?>" href="#deleteModal" data-toggle="modal" admin-info="<?php echo $admin->nom ?> <?php echo $admin->prenom ?>" admin-id="<?php echo $admin->id ?>">
                                            <i class="icon-trash"></i>
                                        </a>
                                    <?php
                                    }
                                    ?>
                                    </div>
                                </td>
                                
                        </tr>
                        <?php if($errorMultiple && array_key_exists($admin->id, $errorMultipleArray)): ?>
                        <tr class="error">
                            <td colspan="4">
                                <ul>
                                <?php $errors = $errorMultipleArray[$admin->id]; ?>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo trad("admin_error_".$error, "admin"); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            </div>
        </div>
    </div>
    
    <div class="row-fluid">
        <div class="span12">
            <?php
                ActionsAdminModules::instance()->inclure_module_admin("gestadm");
            ?>
        </div>
    </div>
    
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p>
    </form>
    
    <div class="modal hide fade in" id="adminAddModal">
        <form method="post" action="gestadm.php">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('AJOUTER_ADMINISTRATEUR', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <?php if($request->get("action") == "ajouter" && $errorCode > 0): ?>
                    <div class="alert alert-block alert-error fade in" id="warningAddAdmin">
                        <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                    <p><?php echo trad('admin_error_'.$errorCode, 'admin'); ?></p>
                    </div>
            <?php endif; ?>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><?php echo trad('Nom', 'admin'); ?></td>
                        <td><input type="text" name="nom" value="<?php if($request->get("action") == "ajouter" && $errorCode > 0) echo $request->request->get("nom"); ?>" ></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Prenom', 'admin'); ?></td>
                        <td><input type="text" name="prenom" value="<?php if($request->get("action") == "ajouter" && $errorCode > 0) echo $request->request->get("prenom"); ?>" ></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Identifiant', 'admin'); ?></td>
                        <td><input type="text" name="identifiant" value="<?php if($request->get("action") == "ajouter" && $errorCode > 0) echo $request->request->get("identifiant"); ?>" ></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Mdp', 'admin'); ?></td>
                        <td><input type="password" name="password" ></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Confirmation', 'admin'); ?></td>
                        <td><input type="password" name="verifyPassword" ></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Langue', 'admin'); ?></td>
                        <td>
                            <select name="lang">
                                <?php foreach($langs as $lang): ?>
                                    <option value="<?php echo $lang->id; ?>" <?php if($lang->defaut): ?> selected="selected" <?php endif; ?>><?php echo $lang->description; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Profil', 'admin'); ?></td>
                        <td>
                            <select name="profil">
                                <?php foreach($administrateur->query_liste("SELECT profil, titre FROM ".Profildesc::TABLE." WHERE lang=".$langue) as $profildesc): ?>
                                    <option value="<?php echo $profildesc->profil; ?>" <?php if($request->get("action") == "ajouter" && $errorCode > 0 && $request->request->get("profil") == $profildesc->profil ) echo 'selected="selected"';  ?>><?php echo $profildesc->titre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
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
    
    <div class="modal hide fade in" id="editAdmin">
        <form method="post" action="gestadm.php">
        <input type="hidden" name="action" value="modifier_password">
        <input type="hidden" name="id" id="admin_id" value="<?php if($request->get("action") == "modifier_password" && $errorCode > 0) echo $request->request->get("id"); ?>">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('MODIF_PASSWORD', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <?php if($request->get("action") == "modifier_password" && $errorCode > 0): ?>
                    <div class="alert alert-block alert-error fade in" id="warningEditAdmin">
                        <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                    <p><?php echo trad('admin_error_'.$errorCode, 'admin'); ?></p>
                    </div>
            <?php endif; ?>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><?php echo trad('Mdp', 'admin'); ?></td>
                        <td><input type="password" name="password"></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Confirmation', 'admin'); ?></td>
                        <td><input type="password" name="verifyPassword"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
            <button type="submit" class="btn btn-primary"><?php echo trad('Modifier', 'admin'); ?></button>
        </div>
        </form>
    </div>
    
    <!-- admin delation -->
    <div class="modal hide" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('Cautious', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <p><?php echo trad('DeleteAdminWarning', 'admin'); ?></p>
            <p id="adminDelationInfo"></p>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
            <a class="btn btn-primary" id="adminDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("gestadm_bottom");
?>    
<?php require_once("pied.php"); ?> 
</body>
<script type="text/javascript">
    $(".js-edit-admin").click(function(){
        $("#admin_id").attr("value",$(this).attr("admin-id"));
        $("#editAdmin").modal("show");
    });
    
    $('.js-delete-admin').click(function()
    {
        $('#adminDelationInfo').html($(this).attr('admin-info'));
        $('#adminDelationLink').attr('href', 'gestadm.php?action=delete&administrateur=' + $(this).attr('admin-id'));
    });
    
    <?php if($request->get("action") == "ajouter" && $errorCode > 0): ?>
        $("#adminAddModal").modal("show");
        $("#adminAddModal").on("hidden",function(){
           $("#warningAddAdmin").remove(); 
        });
    <?php endif; ?>
    <?php if($request->get("action") == "modifier_password" && $errorCode > 0): ?>
        $("#editAdmin").modal("show");
        $("#editAdmin").on("hidden",function(){
           $("#warningEditAdmin").remove(); 
        });
    <?php endif; ?>
</script>
</html>