<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
try
{
    ActionsAdminMessage::getInstance()->action($request);
} catch(TheliaAdminException $e) {
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
	ActionsAdminModules::instance()->inclure_module_admin("message_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_messages', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('LISTE_MESSAGES', 'admin'); ?>
            <div class="btn-group">
                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#messageAddModal" data-toggle="modal">
                    <i class="icon-plus-sign icon-white"></i>
                </a>
            </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("message");
?>
            <div class="bigtable">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo trad('Nom_message', 'admin'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach(MessageAdmin::getInstance()->getList() as $message): ?>
                    <tr>
                        <td><?php echo $message["intitule"]?:$message["nom"]; ?></td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini" href="message_modifier.php?id=<?php echo $message["id"]; ?>"><i class="icon-edit"></i></a>
                                <a class="btn btn-mini js-delete-message" href="#deleteMessage" message-id="<?php echo $message["id"]; ?>" message-intitule="<?php echo $message["intitule"]?:$message["nom"]; ?>"><i class="icon-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="modal hide fade in" id="deleteMessage">
        <div class="modal-header"> <a class="close" data-dismiss="modal">×</a>
            <h3><?php echo trad('SUPPRESSION_MESSAGE', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <p><?php echo trad('DeleteMessageWarning', 'admin'); ?></p>
            <p id="messageDelationInfo"></p>
        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
            <a class="btn btn-primary" id="messageDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
        </div>
    </div>
    
    <div class="modal hide fade in" id="messageAddModal">
        <form method="post" action="message.php">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('CREATION_MESSAGE', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <?php if($errorCode == TheliaAdminException::MESSAGE_NAME_EMPTY || $errorCode == TheliaAdminException::MESSAGE_ALREADY_EXISTS){ ?>
                    <div class="alert alert-block alert-error fade in">
                        <h4 class="alert-heading"><?php echo trad('message_error_create','admin') ?></h4>
                        <p><?php echo trad('message_'.$errorCode,'admin'); ?></p>
                    </div>
            <?php } ?>
            
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><?php echo trad('Nom_message', 'admin'); ?></td>
                        <td><input type="text" name="nom"></td>
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
	ActionsAdminModules::instance()->inclure_module_admin("message_bottom");
?>
<?php require_once("pied.php"); ?> 
<script type="text/javascript">
    $(document).ready(function(){
        $(".js-delete-message").click(function(){
            $("#messageDelationInfo").html($(this).attr("message-intitule"));
            $("#messageDelationLink").attr("href","message.php?action=supprimer&id="+$(this).attr("message-id"));
            $("#deleteMessage").modal("show");
        })
    });
    <?php if($errorCode == TheliaAdminException::MESSAGE_NAME_EMPTY || $errorCode == TheliaAdminException::MESSAGE_ALREADY_EXISTS){ ?>
        $('#messageAddModal').modal('show');
        $('#messageAddModal').on("show", function(){
            $(this).find(".alert").remove();
        })
    <?php } ?>
</script>
</body>
</html>