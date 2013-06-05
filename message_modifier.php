<?php
require_once __DIR__ . "/auth.php";

if(! est_autorise("acces_configuration")) exit;
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$errorCode = 0;
try
{
    ActionsAdminMessage::getInstance()->action($request);
} catch(TheliaAdminException $e) {
    Tlog::error($e->getCode());
    $errorCode = $e->getCode();
}
if (!isset($lang))
    $lang = ActionsLang::instance()->get_id_langue_courante();

$message = new Message();
$message->charger_id($request->query->get("id"));

$messagedesc = new Messagedesc($message->id, $lang);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("message_modifier_top");
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getConfigurationList(trad('Gestion_messages', 'admin'), 'message.php', trad('modifier', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('MODIFICATION_MESSAGE', 'admin'); ?></h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("message_modifier");
?>
            <form method="post" action="message_modifier.php">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id" value="<?php echo $message->id; ?>">
            <input type="hidden" name="lang" value="<?php echo $lang; ?>">
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p> 
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td class="span4"><?php echo trad('Changer_langue', 'admin'); ?></td>
                        <td class="span8">
                            <ul class="nav nav-pills">
                                <?php foreach (LangAdmin::getInstance()->getList() as $displayLang): ?>
                                    <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>"><a href="message_modifier.php?id=<?php echo $message->id; ?>&lang=<?php echo $displayLang->id; ?>" class="change-page"><img src="gfx/lang<?php echo $displayLang->id; ?>.gif" /></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Nom_message', 'admin'); ?></td>
                        <td><?php echo $message->nom; ?></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Intitule_message', 'admin'); ?></td>
                        <td><input type="text" name="intitule" value="<?php echo htmlspecialchars($messagedesc->intitule); ?>" class="span12"></td>
                    </tr>
                    <tr>
                        <td><?php echo trad('Titre_message', 'admin'); ?></td>
                        <td><input type="text" name="titre" value="<?php echo htmlspecialchars($messagedesc->titre); ?>" class="span12"></td>
                    </tr>
                    <tr>
                        <td><p><?php echo trad('Chapo', 'admin'); ?></p><p><small><?php echo trad('courte_descript_format_texte', 'admin'); ?></small></p></td>
                        <td><textarea name="chapo" class="span12"><?php echo $messagedesc->chapo; ?></textarea></td>
                    </tr>
                    <tr>
                        <td><p><?php echo trad('Description', 'admin'); ?></p><p><small><?php echo trad('format_html', 'admin'); ?></small></p></td>
                        <td><textarea name="description" rows="15" class="span12"><?php echo htmlspecialchars($messagedesc->description) ?></textarea> </td>
                    </tr>
                    <tr>
                        <td><p><?php echo trad('Description', 'admin'); ?></p><p><small><?php echo trad('format_text', 'admin'); ?></small></p></td>
                        <td><textarea name="descriptiontext" rows="15" class="span12"><?php echo $messagedesc->descriptiontext; ?></textarea> </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
            </p> 
            </form>
        </div>
    </div>
    <?php if($errorCode > 0): ?>
    <div class="modal hide fade in" id="messageError">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3><?php echo trad('Cautious', 'admin'); ?></h3>
        </div>
        <div class="modal-body">
            <div class="alert alert-error">
                <h4 class="alert-heading"><?php echo trad('message_error','admin') ?></h4>
                <p><?php echo trad('message_'.$errorCode,'admin'); ?></p>
            </div>
        </div>
        <div class="modal-footer">
            <a class="btn" href="message.php"><?php echo trad('Valider', 'admin'); ?></a>
        </div>
        </div>
    </div>
    <?php endif; ?>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("message_modifier_bottom");
?>
<?php require_once("pied.php"); ?> 
<script type="text/javascript">
    $(document).ready(function(){
       <?php if($errorCode > 0): ?>
            $("#messageError").modal("show");
       <?php endif; ?>
    });
</script>
</body>
</html>

