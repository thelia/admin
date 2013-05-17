<?php
require_once("pre.php");
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
try
{
    ActionsAdminVariable::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    $errorCode = $e->getCode();
    switch ($errorCode)
    {
        case TheliaAdminException::VARIABLE_ADD_ERROR:
            $addError = 1;
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
	ActionsAdminModules::instance()->inclure_module_admin("variable_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_variables', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('LISTE_VARIABLES', 'admin'); ?>
            <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#addVariableModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("variable");
?>

            <form method="POST" action="variable.php">
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
                    <?php foreach(VariableAdmin::getInstance()->getList() as $variable){ ?>
                    <tr>
                        <td><?php echo $variable["nom"]; ?></td>
                        <td>
                            <input class="span11 js-edit" type="text" name="valeur_<?php echo $variable["id"]; ?>" variable-original="<?php echo htmlentities($variable["valeur"], ENT_QUOTES, "UTF-8"); ?>" variable-id="<?php echo $variable["id"]; ?>" id="js_edit_<?php echo $variable["id"]; ?>" value="<?php echo htmlentities($variable["valeur"], ENT_QUOTES, "UTF-8"); ?>" />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini js-cancel-edit" title="<?php echo trad('undo_changes', 'admin'); ?>" variable-id="<?php echo $variable["id"]; ?>" id="js_cancel_edit_<?php echo $variable["id"]; ?>"><i class="icon-remove"></i></a>
                            <?php if($variable["protege"] == 0) { ?>
                                <a class="btn btn-mini js-delete-variable" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deleteVariableModal" data-toggle="modal" variable-id="<?php echo $variable["id"]; ?>" variable-nom="<?php echo $variable["nom"]; ?>"><i class="icon-trash"></i></a>
                            <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p>
            </form> 
        </div>
    </div>
    
    <div class="modal hide fade in" id="deleteVariableModal">
        <div class="modal-header"> <a class="close" data-dismiss="modal">×</a>
            <h3><?php echo trad('SUPPRESSION_VARIABLE', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <p><?php echo trad('DeleteVariableWarning', 'admin'); ?></p>
            <p id="variableDelationInfo"></p>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
            <a class="btn btn-primary" id="variableDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
    
    <div class="modal hide fade in" id="addVariableModal">
        <form method="post" action="variable.php">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('CREATION_VARIABLE', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            
<?php if($addError){ ?>
                    <div class="alert alert-block alert-error fade in">
                        <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                    <p><?php echo trad('check_information', 'admin'); ?></p>
                    </div>
<?php } ?>
            
            <table class="table table-striped">
                <tbody>
                    <tr class="<?php if($addError && ($errorData->nom==='' || VariableAdmin::testVariableExists($errorData->nom))){ ?>error<?php } ?>">
                        <td>
                            <?php echo trad('Nom2', 'admin'); ?> *
                            <?php if($addError && VariableAdmin::testVariableExists($errorData->nom)){ ?>
                            <br /><?php echo trad('variable_already_exists', 'admin'); ?>
                            <?php } ?>
                        </td>
                        <td><input type="text" name="nom" value="<?php echo ($addError)?$errorData->nom:''; ?>"></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Valeur', 'admin'); ?></td>
                        <td><input type="text" name="valeur" value="<?php echo ($addError)?$errorData->valeur:''; ?>"></td>
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
	ActionsAdminModules::instance()->inclure_module_admin("variable_bottom");
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
            
                $('#js_edit_' + $(this).attr('variable-id')).val($('#js_edit_' + $(this).attr('variable-id')).attr('variable-original'));
            }
        });
    $('.js-edit').keyup(function(){
        $('#js_cancel_edit_' + $(this).attr('variable-id')).removeClass('disabled');
    });
    
    /*modal*/
    $(document).ready(function(){
        $(".js-delete-variable").click(function(){
            $("#variableDelationInfo").html($(this).attr("variable-nom"));
            $("#variableDelationLink").attr("href","variable.php?action=delete&id=" + $(this).attr("variable-id"));
        })
    });
<?php if($addError){ ?>
    $('#addVariableModal').modal();
<?php } ?>
});
</script>
</body>
</html>