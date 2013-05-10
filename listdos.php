<?php
require_once("auth.php");
        
if(! est_autorise("acces_contenu"))
    exit; 

use Symfony\Component\HttpFoundation\Request;
$request = Request::createFromGlobals();

if(!isset($parent)) $parent="";
if(!isset($id)) $id="";
if(!isset($classement)) $classement="";
if(!isset($action)) $action = "";

$addContentError = false;
$addFolderError = false;

$errorCode = 0;
$addError = 0;

$editError = array();

try {
    ActionsAdminFolder::getInstance()->action($request);
    ActionsAdminContent::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    $errorCode = $e->getCode();
    switch ($errorCode)
    {
        case TheliaAdminException::FOLDER_ADD_ERROR:
            $addFolderError = 1;
            $errorData = $e->getData();
            break;
        case TheliaAdminException::CONTENT_ADD_ERROR:
            $addContentError = 1;
            $errorData = $e->getData();
            break;
    }
}

/*
switch($action){
    default:break;
    //folder
    case 'modClassementFolder':
        FolderAdmin::getInstance($folder_id)->modifyOrder($type, $parent);
        break;
    case 'changeClassementFolder':
        FolderAdmin::getInstance($folder_id)->changeOrder($newClassement, $parent);
        break;
    
    //Content
    case 'modClassementContent':
        ContentAdmin::getInstance($content_id)->modifyOrder($type, $parent);
        break;
    case 'changeClassementContent':
        ContentAdmin::getInstance($content_id)->changeOrder($newClassement, $parent);
        break;
}
 */


?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>

<body>
    <?php
	ActionsAdminModules::instance()->inclure_module_admin("listdos_top");
        $menu = "contenu";
        $breadcrumbs = Breadcrumb::getInstance()->getFolderList($parent);
        require_once("entete.php");
    ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="bigtable">
                <table class="table table-striped">
                    <caption>
                        <h3>
                            <?php echo trad('LISTE_DOSSIERS_CONTENU', 'admin'); ?>
                        
                            <div class="btn-group">
                                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#folderAddModal" data-toggle="modal">
                                    <i class="icon-plus-sign icon-white"></i>
                                </a>
                            </div>
                        </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("listdos");
?>                        
                    </caption>
                    <thead>
                        <tr>
                            <th><?php echo trad('Titre_dossier', 'admin'); ?></th>
                            <th><?php echo trad('En_ligne', 'admin'); ?></th>
                            <th><?php echo trad('Classement', 'admin'); ?></th>
                            <th></th>
                            <th><?php echo trad('Suppr', 'admin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(FolderAdmin::getInstance()->getList($parent, 'classement', 'ASC', '') as $dossier): ?>
                            <tr>
                                <td><?php echo $dossier["titre"]; ?></td>
                                <td><input type="checkbox" folder-id="<?php echo $dossier["id"]; ?>" class="folderDisplay" <?php if($dossier["ligne"]) echo 'checked="checked"' ?>></td>
                                <td>
                                    <a href="listdos.php?parent=<?php echo $parent; ?>&folder_id=<?php echo $dossier["id"]; ?>&type=M&action=modClassementFolder"><i class="icon-arrow-up"></i></a>
                                    <span class="object_classement_editable" object-action="changeClassementFolder" object-name="folder_id" object-id="<?php echo $dossier["id"]; ?>"><?php echo $dossier["classement"]; ?></span>
                                    <a href="listdos.php?parent=<?php echo $parent; ?>&folder_id=<?php echo $dossier["id"]; ?>&type=D&action=modClassementFolder"><i class="icon-arrow-down"></i></a>
                                </td>
                                <td>
                                    <a href="listdos.php?parent=<?php echo $dossier["id"] ?>"><?php echo trad('parcourir','admin'); ?></a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="dossier_modifier.php?id=<?php echo($dossier["id"]); ?>"><i class="icon-edit"></i></a>
                                        <a class="btn btn-mini js-folder-delete" title="<?php echo trad('supprimer', 'admin'); ?>" data-toggle="modal" href="#delObject" folder-id="<?php echo $dossier["id"]; ?>" ><i class="icon-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        
        <div class="row-fluid">
            <div class="span12">
                <div class="bigtable">
                <table class="table table-striped">
                    <caption>
                        <h3><?php echo trad('LISTE_CONTENUS', 'admin'); ?>
                            <div class="btn-group">
                                <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#contentAddModal" data-toggle="modal">
                                    <i class="icon-plus-sign icon-white"></i>
                                </a>
                            </div>
                        </h3> 
                    </caption>
                    <thead>
                        <tr>
                            <th><?php echo trad('Titre_contenu', 'admin'); ?></th>
                            <th><?php echo trad('En_ligne', 'admin'); ?></th>
                            <th><?php echo trad('Classement', 'admin'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(ContentAdmin::getInstance()->getList($parent, 'classement', 'ASC', '') as $contenu): ?>
                            <tr>
                                <td><?php echo $contenu["titre"]; ?></td>
                                
                                <td><input type="checkbox" content-id="<?php echo $contenu["id"]; ?>" content-action="changeDisplay" class="contentCheckbox" <?php if($contenu["ligne"]) echo 'checked="checked"' ?>></td>
                                <td>
                                    <a href="listdos.php?parent=<?php echo $parent; ?>&content_id=<?php echo $contenu["id"]; ?>&type=M&action=modClassementContent"><i class="icon-arrow-up"></i></a>
                                    <span class="object_classement_editable" object-action="changeClassementContent" object-name="content_id" object-id="<?php echo $contenu["id"]; ?>"><?php echo $contenu["classement"]; ?></span>
                                    <a href="listdos.php?parent=<?php echo $parent; ?>&content_id=<?php echo $contenu["id"]; ?>&type=D&action=modClassementContent"><i class="icon-arrow-down"></i></a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="contenu_modifier.php?id=<?php echo($contenu["id"]); ?>&dossier=<?php echo $contenu["dossier"]; ?>"><i class="icon-edit"></i></a>
                                        <a class="btn btn-mini js-content-delete" title="<?php echo trad('supprimer', 'admin'); ?>" data-toggle="modal" href="#delObject" content-id="<?php echo $contenu["id"]; ?>" ><i class="icon-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        
        <div class="modal hide fade" id="delObject">
            <div class="modal-header"> <a class="close" data-dismiss="modal">x</a>
                <h3>Plus d'informations</h3>
            </div>
            <div class="modal-body">
                <p id="explainText"></p>
            </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('non', 'admin'); ?></a>
                <a class="btn btn-primary" id="deleteLink"><?php echo trad('Oui', 'admin'); ?></a>
            </div>
        </div>
        
        <!-- folder add -->
        <div class="modal hide fade" id="folderAddModal" tabindex="-1" role="dialog" aria-hidden="true">
        <form method="POST" action="listdos.php">
            <input type="hidden" name="action" value="addFolder" />
            <input type="hidden" name="parent" value="<?php echo $parent; ?>" />
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 ><?php echo trad('AJOUTER_DOSSIER', 'admin'); ?></h3>
            </div>
            <div class="modal-body">

<?php if($addFolderError){ ?>
                <div class="alert alert-block alert-error fade in" id="folderError">
                    <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                <p><?php echo trad('check_information', 'admin'); ?></p>
                </div>
<?php } ?>

                <table class="table table-striped" id="folderCreation">
                    <tbody>
                        <tr class="<?php if($addFolderError && $errorData->titre===''){ ?>error<?php } ?>">
                            <td><?php echo trad('Titre', 'admin'); ?> *</td>
                            <td>
                                <input type="text" value="<?php echo $title ?>" name="title"  />
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
        
        <!-- content add -->
        <div class="modal hide fade" id="contentAddModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel"><?php echo trad('AJOUTER_CONTENU', 'admin'); ?></h3>
            </div>
            <form method="POST" action="listdos.php">
            <div class="modal-body">
            <?php if($addContentError){ ?>
                <div class="alert alert-block alert-error fade in" id="contentError">
                    <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                <p><?php echo trad('check_information', 'admin'); ?></p>
                </div>
            <?php } ?>
            
            <input type="hidden" name="action" value="addContent" />
            <input type="hidden" name="parent" value="<?php echo $parent; ?>" />
                <table class="table table-striped" id="contentCreation">
                    <tbody>
                        <tr class="<?php if($addContentError && $errorData->titre===''){ ?>error<?php } ?>">
                            <td><?php echo trad('Titre', 'admin'); ?> *</td>
                            <td>
                                <input type="text" value="<?php echo $title ?>" name="title"  />
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
<?php
	ActionsAdminModules::instance()->inclure_module_admin("listdos_bottom");
?>
        
<?php require_once("pied.php");?> 
<script type="text/javascript" src="js/Thelia.js"></script>
<script type="text/javascript" src="js/jeditable.min.js"></script>
<script>
$(document).ready(function(){

    //put online/offline folder
    $(".folderDisplay").click(function(){
        $.ajax({
           url : 'ajax/dossier.php',
           data : {
               folder_id : $(this).attr('folder-id'),
               action : 'changeDisplay',
               display : $(this).is(':checked')
           }
        });
    });
    
    $(".contentCheckbox").click(function(){
        $.ajax({
            url : 'ajax/contenu.php',
            data : {
                content_id : $(this).attr('content-id'),
                action : $(this).attr('content-action'),
                display : $(this).is(':checked')
            }
        });
    });

    //delete a folder
    $('.js-folder-delete').click(function()
    {
        $('#explainText').html("<?php echo trad('DeleteFolderWarning','admin'); ?>");
        $('#deleteLink').attr('href','listdos.php?parent=<?php echo $parent; ?>&action=deleteFolder&folder_id='+$(this).attr('folder-id'));
    });
    
    $('.js-content-delete').click(function(){
        $('#explainText').html("<?php echo trad('DeleteContentWarning','admin'); ?>");
        $('#deleteLink').attr('href','listdos.php?parent=<?php echo $parent; ?>&action=deleteContent&content_id='+$(this).attr('content-id'));
    });
    
    $('.object_classement_editable').editable(function(value, settings){        
        var form = Thelia.generateForm({
            action : $(this).attr('object-action'),
            parent : "<?php echo $parent; ?>",
            object_name : $(this).attr('object-name'),
            object_id : $(this).attr('object-id'),
            value : value,
            target : "listdos.php"
        });
        
        $(this).prepend(form);
        form.submit();
    },{
        onblur : 'submit',
        select : true
    });
    
<?php if($addFolderError){ ?>
    $('#folderAddModal').modal();
    $('#folderAddModal').on('hidden',function(){
        $('#folderError').remove();
        $('#folderCreation tr').each(function(){
            $(this).removeClass('error');
            $(this).find('input').removeAttr('value');
        });
    });
<?php } ?>

<?php if($addContentError): ?>
    $('#contentAddModal').modal();
    $('#contentAddModal').on('hidden', function(){
        $('#contentError').remove();
        $('#contentCreation tr').each(function(){
            $(this).removeClass('error');
            $(this).find('input').removeAttr('value');
        });
    });
<?php endif; ?>
});

</script>
</body>
</html>
