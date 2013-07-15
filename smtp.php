<?php
require_once("pre.php");
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

$smtp = new SMTPAdmin();

try
{
    if($request->request->get("action") == 'edit')
        $smtp->edit($request);
} catch(TheliaAdminException $e) {
    $errorCode = $e->getCode();
    switch ($errorCode)
    {
        case TheliaAdminException::SMTP_EDIT_ERROR:
            $editError = 1;
            $errorData = $e->getData();
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("smtp_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_mail', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('LISTE_VARIABLES', 'admin'); ?></h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("smtp");
?>
            <form method="POST" action="smtp.php">
                <input type="hidden" name="action" value="edit" />
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="span3"><?php echo trad('Nom2', 'admin'); ?></th>
                        <th class="span7"><?php echo trad('Valeur', 'admin'); ?></th>
                        <th class="span2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo trad('Serveur', 'admin'); ?></td>
                        <td>
                            <input class="span12 js-edit" type="text" name="serveur" valeur-original="<?php echo htmlentities($smtp->serveur, ENT_QUOTES, 'UTF-8'); ?>" ligne-id="1" id="js_edit_1" value="<?php echo htmlentities($smtp->serveur, ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-cancel-edit" title="<?php echo trad('undo_changes', 'admin'); ?>" ligne-id="1" id="js_cancel_edit_1"><i class="icon-remove"></i></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Port', 'admin'); ?></td>
                        <td>
                            <input class="span12 js-edit" type="text" name="port" valeur-original="<?php echo htmlentities($smtp->port, ENT_QUOTES, 'UTF-8'); ?>" ligne-id="2" id="js_edit_2" value="<?php echo htmlentities($smtp->port, ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-cancel-edit" title="<?php echo trad('undo_changes', 'admin'); ?>" ligne-id="2" id="js_cancel_edit_2"><i class="icon-remove"></i></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Nom_utilisateur', 'admin'); ?></td>
                        <td>
                            <input class="span12 js-edit" type="text" name="username" valeur-original="<?php echo htmlentities($smtp->username, ENT_QUOTES, 'UTF-8'); ?>" ligne-id="3" id="js_edit_3" value="<?php echo htmlentities($smtp->username, ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-cancel-edit" title="<?php echo trad('undo_changes', 'admin'); ?>" ligne-id="3" id="js_cancel_edit_3"><i class="icon-remove"></i></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Mdp', 'admin'); ?></td>
                        <td>
                            <input class="span12 js-edit" type="password" name="password" valeur-original="<?php echo htmlentities($smtp->password, ENT_QUOTES, 'UTF-8'); ?>" ligne-id="4" id="js_edit_4" value="<?php echo htmlentities($smtp->password, ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-cancel-edit" title="<?php echo trad('undo_changes', 'admin'); ?>" ligne-id="4" id="js_cancel_edit_4"><i class="icon-remove"></i></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Protocole_securise', 'admin'); ?></td>
                        <td>
                            <input class="span12 js-edit" type="text" name="secure" valeur-original="<?php echo htmlentities($smtp->secure, ENT_QUOTES, 'UTF-8'); ?>" ligne-id="5" id="js_edit_5" value="<?php echo htmlentities($smtp->secure, ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-cancel-edit" title="<?php echo trad('undo_changes', 'admin'); ?>" ligne-id="5" id="js_cancel_edit_5"><i class="icon-remove"></i></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Actif', 'admin'); ?></td>
                        <td>
                            <input class="span12 js-edit" type="text" name="active" valeur-original="<?php echo htmlentities($smtp->active, ENT_QUOTES, 'UTF-8'); ?>" ligne-id="6" id="js_edit_6" value="<?php echo htmlentities($smtp->active, ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-cancel-edit" title="<?php echo trad('undo_changes', 'admin'); ?>" ligne-id="6" id="js_cancel_edit_6"><i class="icon-remove"></i></a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p>
            </form> 
        </div>
    </div>
 
 <?php
	ActionsAdminModules::instance()->inclure_module_admin("smtp_bottom");
?>   
    
<?php require_once("pied.php"); ?> 
<script type="text/javascript">
jQuery(function($)
{
    /*cancel edit*/
    $('.js-cancel-edit')
        .addClass('disabled')
        .click(function()
        {
            if(!$(this).is('.disabled'))
            {
                $(this).addClass('disabled')
            
                $('#js_edit_' + $(this).attr('ligne-id')).val($('#js_edit_' + $(this).attr('ligne-id')).attr('valeur-original'));
            }
        });
    $('.js-edit').keyup(function(){
        $('#js_cancel_edit_' + $(this).attr('ligne-id')).removeClass('disabled');
    });
});
</script>
</body>
</html>