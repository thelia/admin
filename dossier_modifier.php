<?php

require_once("auth.php");

if(! est_autorise("acces_contenu")) exit;

require_once __DIR__ . "/../fonctions/divers.php";

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if (false == $lang = $request->get("lang", false))
    $lang = ActionsLang::instance()->get_id_langue_courante();
if (false == $tab = $request->get("tab", false))
    $tab = "generalDescriptionTab";

$errorCode = 0;
$errorMessage = '';

try {
    ActionsAdminFolder::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
}

$dossier = new Dossier($request->query->get("id"));
$dossierdesc = new Dossierdesc($dossier->id, $lang);
$dossierdesc->chapo = str_replace('<br />', "\n", $dossierdesc->chapo);
$dossierdesc->postscriptum = str_replace('<br />', "\n", $dossierdesc->postscriptum);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>

    <body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("dossier_modifier_top");
$menu = "contenu";
$breadcrumbs = Breadcrumb::getInstance()->getFolderList($dossier->id, false);
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('DESCRIPTION_G_DOSSIER', 'admin'); ?></h3>
        </div>
    </div>        
<?php
	ActionsAdminModules::instance()->inclure_module_admin("dossier_modifier");
?>
    <div class="row-fluid">
        <div class="span4 offset5">
            <div class="row-fluid">
                <div class="span4">
                    <ul class="nav nav-pills">
                        <?php foreach (LangAdmin::getInstance()->getList() as $displayLang): ?>
                            <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>"><a href="dossier_modifier.php?id=<?php echo $dossier->id; ?>&lang=<?php echo $displayLang->id; ?>" class="change-page change-lang"><img src="gfx/lang<?php echo $displayLang->id; ?>.gif" /></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="span4 offset4" style="text-align: right">
                    <ul class="nav nav-pills">
                        <li class="">
                            <?php
                            $previous = ToolBoxAdmin::getPrevious($dossier);
                            if($previous !== false)
                            {
                            ?>
                            <a href="dossier_modifier.php?id=<?php echo $previous->id; ?>" title="<?php echo trad('previous', 'admin'); ?>" class="change-page">
                                <i class="icon-backward"></i>
                            </a>
                            <?php
                            }
                            ?>
                        </li>
                        <li class="">
                            <a href="<?php echo urlfond("dossier", "id_dossier=$dossier->id", true); ?>" target="_blank" title="<?php echo trad('preview', 'admin'); ?>" class="change-page">
                                <i class="icon-eye-open"></i>
                            </a>
                        </li>
                        <li class="">
                            <?php
                            $next = ToolBoxAdmin::getNext($dossier);
                            if($next !== false)
                            {
                            ?>
                            <a href="dossier_modifier.php?id=<?php echo $next->id; ?>" title="<?php echo trad('next', 'admin'); ?>" class="change-page">
                                <i class="icon-forward"></i>
                            </a>
                            <?php
                            }
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div> 
    <div class="row-fluid">
        <div class="span12">
            <ul id="mainTabs" class="nav nav-tabs">
                <li class="<?php if ($tab == 'generalDescriptionTab') echo "active"; ?>"><a href="#generalDescriptionTab" data-toggle="tab"><?php echo trad('DESCRIPTION_G_RUBRIQUE', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'sectionInformationTab') echo "active"; ?>"><a href="#sectionInformationTab" data-toggle="tab"><?php echo trad('INFO_RUBRIQUE', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'imageTab') echo "active"; ?>"><a href="#imageTab" data-toggle="tab"><?php echo trad('GESTION_PHOTOS', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'documentTab') echo "active"; ?>"><a href="#documentTab" data-toggle="tab"><?php echo trad('GESTION_DOCUMENTS', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'moduleTab') echo "active"; ?>"><a href="#moduleTab" data-toggle="tab"><?php echo strtoupper(trad('Modules', 'admin')); ?></a></li>
            </ul>
            <form method="post" action="dossier_modifier.php" enctype="multipart/form-data" id="formulaire">
                <input type="hidden" name="id" value="<?php echo $dossier->id; ?>">
                <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="tab" value="<?php echo $tab; ?>">
                <p>
                    <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                </p>
                <div class="tab-content">
                    <div class="tab-pane <?php if ($tab == 'generalDescriptionTab') echo "active"; ?>" id="generalDescriptionTab">
                        <div class="row-fluid">
                            <div class="span12">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td class="span4"><?php echo trad('Titre', 'admin'); ?></td>
                                            <td class="span8"><input type="text" class="span12" name="titre" value="<?php echo htmlspecialchars($dossierdesc->titre); ?>"></td>    
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('Chapo', 'admin'); ?></p>
                                                <p><small><?php echo trad('courte_descript_intro', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8"><textarea class="span12 text_editor" name="chapo"><?php echo $dossierdesc->chapo; ?></textarea></td>
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('Description', 'admin'); ?></p>
                                                <p><small><?php echo trad('description_complete', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8">
                                                <textarea class="span12 text_editor" rows="10" name="description"><?php echo $dossierdesc->description; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('PS', 'admin'); ?></p>
                                                <p><small><?php echo trad('champs_info_complementaire', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8">
                                                <textarea class="span12 text_editor" name="postscriptum"><?php echo $dossierdesc->postscriptum; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span4"><?php echo trad('URL_reecrite', 'admin'); ?></td>
                                            <td class="span8"><input type="text" class="span12" name="urlreecrite" value="<?php echo htmlspecialchars(rewrite_dos("$dossier->id", $lang)); ?>"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane <?php if ($tab == 'sectionInformationTab') echo "active"; ?>" id="sectionInformationTab">
                        <div class="row-fluid">
                            <div class="span12">
                                <table class="table table-striped">
                                    <tr>
                                        <td>ID</td>
                                        <td><?php echo $dossier->id; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('Appartenance', 'admin'); ?></td>
                                        <td>
                                            <select name="dossier">
                                                <option value="0"><?php echo trad('A la racine', 'admin'); ?></option>
                                                <?php echo arbreOption_dos(0, 1, $dossier->parent, $request->query->get("id"), 1); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('En_ligne', 'admin'); ?></td>
                                        <td><input type="checkbox" name="ligne" <?php if ($dossier->ligne): ?>checked="checked" <?php endif; ?>></td>
                                    </tr>
                                </table>
                            </div>    
                        </div>
                    </div>
                    <div class="tab-pane <?php if ($tab == 'imageTab') echo "active"; ?>" id="imageTab">
                        <div class="row-fluid">
                        <div class="span6 offset4">
                            <?php for($i = 1; $i <= FolderAdmin::getInstance()->getImageFile()->getNumberUpload(); $i++): ?>
                                <input type="file" name="photo<?php echo $i; ?>" class="input-large">
                                <br >
                            <?php endfor; ?>
                        </div>
                        </div>
                        <?php foreach (FolderAdmin::getInstance($dossier->id)->getImageList($lang) as $image): ?>
                        <div class="row-fluid js-bloc-image" js-image-id="<?php echo $image['id'] ?>">
                            <div class="span3" style="position: relative;">
                                <img class="js-image" src="../fonctions/redimlive.php?nomorig=<?php echo
                                $image["nomFichier"] ?>&type=dossier&width=250&height=250&exact=1">
                                <img style="display: none; position: absolute;" class="js-image-delation" src="gfx/interdit-150x150.png" />
                                <input type="hidden" class="js-delete-input" name="image_to_delete_<?php echo $image['id'] ?>" value="0" />
                                <input type="hidden" class="js-rank-input" name="rank_<?php echo $image['id'] ?>" value="<?php echo $image['classement'] ?>" />
                                <a style="position:relative; margin-top:-45px; float:right" class="btn btn-large js-delete-picture" href="#"><i class="icon-trash"></i></a>
                            </div>
                            <div class="span8">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td class="span12">
                                                <?php echo trad('Titre', 'admin'); ?>
                                                <input type="text" name="photo_titre_<?php echo $image["id"]; ?>" class="span12" value="<?php echo $image["titre"]; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span12">
                                                <?php echo trad('Chapo', 'admin'); ?>
                                                <textarea name="photo_chapo_<?php echo $image["id"]; ?>" class="span12"><?php echo $image['chapo']; ?></textarea>
                                            </td >
                                        </tr>
                                        <tr>
                                            <td class="span12">
                                                <?php echo trad('Description', 'admin'); ?>
                                                <textarea name="photo_description_<?php echo $image["id"]; ?>" class="span12"><?php echo $image['description']; ?></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="span1">
                                <a class="change-image-rank" js-sens="up" href="#">
                                    <i class="icon-arrow-up"></i>
                                </a>
                                <a class="change-image-rank" js-sens="down" href="#">
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class=" tab-pane <?php if($tab == 'documentTab') echo "active" ?>" id="documentTab">
                        <div class="row-fluid">
                            <div class="span6 offset4">
                                <?php for($i=1; $i <= FolderAdmin::getInstance()->getDocumentFile()->getNumberUpload(); $i++): ?>
                                    <input type="file" name="document_<?php echo $i ?>" class="input-large">
                                    <br >
                                <?php endfor;?>
                            </div>
                        </div>
                        <?php foreach(FolderAdmin::getInstance($dossier->id)->getDocumentList($lang) as $document): ?>
                        <div class="row-fluid js-bloc-document" js-document-id="<?php echo $document['id'] ?>">
                            <div class="span3" style="position: relative;">
                                <p class="js-document">
                                    <a target="_blank" href="<?php echo $document["fichier"]; ?>"><?php echo $document["nomFichier"]; ?></a>
                                </p>
                                <img style="display: none; position: absolute;" class="js-document-delation" src="gfx/interdit-150x150.png" />
                                <input type="hidden" class="js-delete-input" name="document_to_delete_<?php echo $document['id'] ?>" value="0" />
                                <input type="hidden" class="js-rank-input" name="rank_<?php echo $document['id'] ?>" value="<?php echo $document['classement'] ?>" />
                                <a class="btn btn-large js-delete-document" href="#">
                                    <i class="icon-trash"></i>
                                </a>
                                
                            </div>
                            <div class="span8">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td class="span12">
                                                <?php echo trad('Titre', 'admin'); ?>
                                                <input type="text" name="document_titre_<?php echo $document["id"]; ?>" class="span12" value="<?php echo $document["titre"]; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span12">
                                                <?php echo trad('Chapo', 'admin'); ?>
                                                <textarea name="document_chapo_<?php echo $document["id"]; ?>" class="span12"><?php echo $document['chapo']; ?></textarea>
                                            </td >
                                        </tr>
                                        <tr>
                                            <td class="span12">
                                                <?php echo trad('Description', 'admin'); ?>
                                                <textarea name="document_description_<?php echo $document["id"]; ?>" class="span12"><?php echo $document['description']; ?></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="span1">
                                <a class="change-document-rank" js-sens="up" href="#">
                                    <i class="icon-arrow-up"></i>
                                </a>
                                <a class="change-document-rank" js-sens="down" href="#">
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="moduleTab" class="tab-pane <?php if($tab == "moduleTab") echo "active" ?>">
                        <div class="row-fluid">
                            <div class="span12">
                                <?php ActionsAdminModules::instance()->inclure_module_admin("dossiermodifier"); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <p>
                    <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                </p>
            </form>
        </div>
    </div>
<!-- form not saved -->
<div class="modal hide fade" id="changeLangModal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3><?php echo trad('Cautious', 'admin'); ?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo trad('formulaire_not_saved','admin') ?></p>
    </div>
    <div class="modal-footer">
        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
        <a class="btn btn-primary" id="changeLangLink"><?php echo trad('Oui', 'admin'); ?></a>
    </div>
</div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("dossier_modifier_bottom");
?>
<?php require_once("pied.php"); ?> 
<script type="text/javascript">
$(document).ready(function(){
    var form = 0;

    $("#formulaire").change(function(){
        form=1;
    });

    $(".change-page").click(function(e){
        if(form == 1){            
            e.preventDefault();
            $("#changeLangLink").attr("href",$(this).attr('href') + '&tab=' + $("ul#mainTabs li.active a").attr('href').substr(1));
            $("#changeLangModal").modal("show");
        }
    });
    
    $('.change-lang').click(function()
    {
        $(this).attr('href', $(this).attr('href') + '&tab=' + $("ul#mainTabs li.active a").attr('href').substr(1));
    });
    
    $('a[data-toggle="tab"]').on('show',function(e){
        $("input[name=tab]").val($(e.target).attr('href').substring(1));
    });
    
    /*picture delation*/
    $(".js-delete-picture").click(function(e){
        e.preventDefault();
        
        form=1;
        
        if($(this).parent().children('.js-image-delation').is(':hidden'))
        {
            $(this).parent().children('.js-image-delation')
                .css('top', ($(this).parent().children('.js-image').height() - 150) / 2)
                .css('left', ($(this).parent().width() - 150) / 2)
                .show();
            $(this).parent().children('.js-delete-input').val(1);
        }
        else
        {
            $(this).parent().children('.js-image-delation').hide();
            $(this).parent().children('.js-delete-input').val(0);
        }
    });
    
    /*picture ranking*/
    $('.change-image-rank').click(function(e)
    {
        e.preventDefault();
        
        form=1;
        
        if($(this).attr('js-sens') == 'up')
        {
            if($(this).parent().parent().prev().is('.js-bloc-image'))
            {
                $(this).parent().parent().insertBefore(
                    $(this).parent().parent().prev()
                );
            }
        }
        else if($(this).attr('js-sens') == 'down')
        {
            if($(this).parent().parent().next().is('.js-bloc-image'))
            {
                $(this).parent().parent().insertAfter(
                    $(this).parent().parent().next()
                );
            }
        }
        
        $('.js-bloc-image').each(function(k, v){
            $(v).children().children('.js-rank-input').val(
                parseInt(k) + 1
            );
        });
    });
    
    /*document delation*/
    $(".js-delete-document").click(function(e){
        e.preventDefault();
        
        form=1;
        
        if($(this).parent().children('.js-document-delation').is(':hidden'))
        {
            $(this).parent().children('.js-document-delation')
                .css('top', ($(this).parent().children('.js-document').height() - 150) / 2)
                .css('left', ($(this).parent().width() - 150) / 2)
                .show();
            $(this).parent().children('.js-delete-input').val(1);
        }
        else
        {
            $(this).parent().children('.js-document-delation').hide();
            $(this).parent().children('.js-delete-input').val(0);
        }
    });
    
    /*document ranking*/
    $('.change-document-rank').click(function(e)
    {
        e.preventDefault();
        
        form=1;
        
        if($(this).attr('js-sens') == 'up')
        {
            if($(this).parent().parent().prev().is('.js-bloc-document'))
            {
                $(this).parent().parent().insertBefore(
                    $(this).parent().parent().prev()
                );
            }
        }
        else if($(this).attr('js-sens') == 'down')
        {
            if($(this).parent().parent().next().is('.js-bloc-document'))
            {
                $(this).parent().parent().insertAfter(
                    $(this).parent().parent().next()
                );
            }
        }
        
        $('.js-bloc-document').each(function(k, v){
            $(v).children().children('.js-rank-input').val(
                parseInt(k) + 1
            );
        });
    });
});
</script>
</body>
</html>
