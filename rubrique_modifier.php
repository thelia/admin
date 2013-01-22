<?php

require_once("auth.php");

if(! est_autorise("acces_catalogue")) exit;

require_once __DIR__ . "/../fonctions/divers.php";

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

if (false == $lang = $request->get("lang", false))
    $lang = ActionsLang::instance()->get_id_langue_courante();
if (false == $tab = $request->get("tab", false))
    $tab = "generalDescriptionTab";

$errorCode = 0;
$errorMessage = '';

try {
    ActionsAdminCategory::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
}

$rubrique = new Rubrique($request->query->get("id"));
$rubriquedesc = new Rubriquedesc($rubrique->id, $lang);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>

    <body>
<?php
$menu = "catalogue";
$breadcrumbs = Breadcrumb::getInstance()->getCategoryList($rubrique->id, false);
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('DESCRIPTION_G_RUBRIQUE', 'admin'); ?></h3>
        </div>
    </div>        
    <div class="row-fluid">
        <div class="span4 offset5">
            <ul class="nav nav-pills">
                <?php foreach (LangAdmin::getInstance()->getList() as $displayLang): ?>
                    <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>">
                        <a href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&lang=<?php echo $displayLang->id; ?>" class="change-page change-lang">
                            <img src="gfx/lang<?php echo $displayLang->id; ?>.gif" />
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div> 
    <div class="row-fluid">
        <div class="span12">
            <ul id="mainTabs" class="nav nav-tabs">
                <li class="<?php if ($tab == 'generalDescriptionTab') echo "active"; ?>"><a href="#generalDescriptionTab" data-toggle="tab"><?php echo trad('DESCRIPTION_G_RUBRIQUE', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'sectionInformationTab') echo "active"; ?>"><a href="#sectionInformationTab" data-toggle="tab"><?php echo trad('INFO_RUBRIQUE', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'associationTab') echo "active"; ?>"><a href="#associationTab" data-toggle="tab"><?php echo trad('ASSOCIATION', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'imageTab') echo "active"; ?>"><a href="#imageTab" data-toggle="tab"><?php echo trad('GESTION_PHOTOS', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'documentTab') echo "active"; ?>"><a href="#documentTab" data-toggle="tab"><?php echo trad('GESTION_DOCUMENTS', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'moduleTab') echo "active"; ?>"><a href="#moduleTab" data-toggle="tab"><?php echo strtoupper(trad('Modules', 'admin')); ?></a></li>
            </ul>
            <form method="post" action="rubrique_modifier.php" enctype="multipart/form-data" id="formulaire">
                <input type="hidden" name="id" value="<?php echo $rubrique->id; ?>">
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
                                            <td class="span8"><input type="text" class="span12" name="titre" value="<?php echo $rubriquedesc->titre; ?>"></td>    
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('Chapo', 'admin'); ?></p>
                                                <p><small><?php echo trad('courte_descript_intro', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8"><textarea class="span12 text_editor" name="chapo"><?php echo $rubriquedesc->chapo; ?></textarea></td>
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('Description', 'admin'); ?></p>
                                                <p><small><?php echo trad('description_complete', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8">
                                                <textarea class="span12 text_editor" rows="10" name="description"><?php echo $rubriquedesc->description; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('PS', 'admin'); ?></p>
                                                <p><small><?php echo trad('champs_info_complementaire', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8">
                                                <textarea class="span12 text_editor" name="postscriptum"><?php echo $rubriquedesc->postscriptum; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span4"><?php echo trad('URL_reecrite', 'admin'); ?></td>
                                            <td class="span8"><input type="text" class="span12" name="urlreecrite" value="<?php echo htmlspecialchars(rewrite_rub("$rubrique->id", $lang)); ?>"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane<?php if($tab=='associationTab'){ ?> active<?php } ?>" id="associationTab">
                        
                        <h3 id="associatedContentAnchor">
                            <?php echo trad('GESTION_CONTENUS_ASSOCIES', 'admin'); ?>
                        </h3>
                        
                        <table class="table table-striped">
                            <tbody>
                                <tr class="info">
                                    <td class="span5">
                                        <select class="span12 not-change-page" id="associatedContent_dossier">
<?php
echo arbreOption_dos(0, 1, 0, 0, 1);
?>
                                        </select>
                                    </td>
                                    <td class="span5">
                                        <select class="span12 not-change-page" id="select_prodcont"></select>
                                    </td>
                                    <td class="span1 offset1">
                                        <div class="btn-group">
                                            <a class="btn btn-mini" id="link_prodcont" title="<?php echo trad('ajouter', 'admin'); ?>" href="">
                                                <i class="icon-plus-sign"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
<?php
$AClist = AssociatedContentAdmin::getInstance()->getList(0, $rubrique->id);
for($i=0; $i<count($AClist); $i++)
{
?>
                                <tr>
                                    <td>
                                        <?php echo $AClist[$i]['folder']; ?>
                                    </td>
                                    <td>
                                        <?php echo $AClist[$i]['content']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a class="btn btn-mini js-delete-associatedContent" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deletContenuassoceModal" data-toggle="modal" associated-content-info="<?php echo $AClist[$i]['folder'] ?> > <?php echo $AClist[$i]['content'] ?>" associated-content-id="<?php echo $AClist[$i]['id'] ?>">
                                                <i class="icon-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
<?php
}
?>
                            </tbody>
                        </table>
                        
                        <h3 id="associatedFeatureAnchor">
                            <?php echo trad('GESTION_CARACTERISTIQUES_ASSOCIEES', 'admin'); ?>
                        </h3>

                        <table class="table table-striped">
                            <tbody>
                                <tr class="info">
                                    <td class="span10">
                                        <select class="span12 not-change-page" id="associatedFeatureList">
<?php
$Flist = AssociatedFeatureAdmin::getInstance()->getListAvailableFeature($rubrique->id);
for($i=0; $i<count($Flist); $i++)
{
?>
                                            <option value="<?php echo $Flist[$i]['id']; ?>"><?php echo $Flist[$i]['titre']; ?></option>
<?php
}
?>
                                        </select>
                                    </td>
                                    <td class="span1 offset1">
                                        <div class="btn-group">
                                            <a class="btn btn-mini" id="link_addAssociatedFeature" title="<?php echo trad('ajouter', 'admin'); ?>" href="">
                                                <i class="icon-plus-sign"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
<?php
$AFlist = AssociatedFeatureAdmin::getInstance()->getList($rubrique->id);
for($i=0; $i<count($AFlist); $i++)
{
?>
                                <tr>
                                    <td>
                                        <?php echo $AFlist[$i]['feature']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a class="btn btn-mini js-delete-associatedFeature" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deletAssociatedFeatureModal" data-toggle="modal" associated-feature-info="<?php echo $AFlist[$i]['feature'] ?>" associated-feature-id="<?php echo $AFlist[$i]['id'] ?>">
                                                <i class="icon-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
<?php
}
?>
                            </tbody>
                        </table>
                        
                        <h3  id="associatedVariantAnchor">
                            <?php echo trad('GESTION_DECLINAISONS_ASSOCIEES', 'admin'); ?>
                        </h3>

                        <table class="table table-striped">
                            <tbody>
                                <tr class="info">
                                    <td class="span10">
                                        <select class="span12 not-change-page" id="associatedVariantList">
<?php
$Vlist = AssociatedVariantAdmin::getInstance()->getListAvailableVariant($rubrique->id);
for($i=0; $i<count($Vlist); $i++)
{
?>
                                            <option value="<?php echo $Vlist[$i]['id']; ?>"><?php echo $Vlist[$i]['titre']; ?></option>
<?php
}
?>
                                        </select>
                                    </td>
                                    <td class="span1 offset1">
                                        <div class="btn-group">
                                            <a class="btn btn-mini" id="link_addAssociatedVariant" title="<?php echo trad('ajouter', 'admin'); ?>" href="">
                                                <i class="icon-plus-sign"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
<?php
$AVlist = AssociatedVariantAdmin::getInstance()->getList($rubrique->id);
for($i=0; $i<count($AVlist); $i++)
{
?>
                                <tr>
                                    <td>
                                        <?php echo $AVlist[$i]['variant']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a class="btn btn-mini js-delete-associatedVariant" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deletAssociatedVariantModal" data-toggle="modal" associated-variant-info="<?php echo $AVlist[$i]['variant'] ?>" associated-variant-id="<?php echo $AVlist[$i]['id'] ?>">
                                                <i class="icon-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
<?php
}
?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane <?php if ($tab == 'sectionInformationTab') echo "active"; ?>" id="sectionInformationTab">
                        <div class="row-fluid">
                            <div class="span12">
                                <table class="table table-striped">
                                    <tr>
                                        <td>ID</td>
                                        <td><?php echo $rubrique->id; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('Appartenance', 'admin'); ?></td>
                                        <td>
                                            <select name="parent">
                                                <option value="0"><?php echo trad('A la racine', 'admin'); ?></option>
                                                <?php 
                                                      echo arbreOptionRub(0, 1, $rubrique->id, 0, 1); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('En_ligne', 'admin'); ?></td>
                                        <td><input type="checkbox" name="ligne" <?php if ($rubrique->ligne): ?>checked="checked" <?php endif; ?>></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('Champs_libre', 'admin'); ?></td>
                                        <td>
                                            <input type="text" class="span12 js-editing" name="lien" value="<?php echo $rubrique->lien ?>" />
                                        </td>
                                    </tr>
                                </table>
                            </div>    
                        </div>
                    </div>
                    <div class="tab-pane <?php if ($tab == 'imageTab') echo "active"; ?>" id="imageTab">
                        <div class="row-fluid">
                            <div class="span6 offset4">
                                <?php for($i = 1; $i <= CategoryAdmin::getInstance()->getImageFile()->getNumberUpload(); $i++): ?>
                                    <input type="file" name="photo<?php echo $i; ?>" class="input-large">
                                    <br >
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php foreach (CategoryAdmin::getInstance($rubrique->id)->getImageList($lang) as $image): ?>
                        <div class="row-fluid">
                            <div class="span3" style="position: relative;">
                                <img class="js-image" src="<?php echo  $image["fichier"] ?>">
                                <img style="display: none; position: absolute;" class="js-image-delation" src="gfx/interdit-150x150.png" />
                                <input type="hidden" class="js-delete-input" name="image_to_delete_<?php echo $image['id'] ?>" value="0" />
                                <a style="position: absolute; bottom: 0px; right: 0px;" class="btn btn-large js-delete-picture" href="#">
                                    <i class="icon-trash"></i>
                                </a>
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
                                <a class="change-page" href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyAttachementPosition&direction=M&attachement=image&attachement_id=<?php echo $image['id']; ?>&lang=<?php echo $lang; ?>&tab=imageTab">
                                    <i class="icon-arrow-up"></i>
                                </a>

                                <a class="change-page" href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyAttachementPosition&direction=D&attachement=image&attachement_id=<?php echo $image['id']; ?>&lang=<?php echo $lang; ?>&tab=imageTab">
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class=" tab-pane <?php if($tab == 'documentTab') echo "active" ?>" id="documentTab">
                        <div class="row-fluid">
                            <div class="span6 offset4">
                                <?php for($i=1; $i <= CategoryAdmin::getInstance()->getDocumentFile()->getNumberUpload(); $i++): ?>
                                    <input type="file" name="document_<?php echo $i ?>" class="input-large">
                                    <br >
                                <?php endfor;?>
                            </div>
                        </div>
                        <?php foreach(CategoryAdmin::getInstance($rubrique->id)->getDocumentList($lang) as $document): ?>
                        <div class="row-fluid">
                            <div class="span3" style="position: relative;">
                                <p class="js-document">
                                    <a target="_blank" href="<?php echo $document["fichier"]; ?>"><?php echo $document["nomFichier"]; ?></a>
                                </p>
                                
                                <img style="display: none; position: absolute;" class="js-document-delation" src="gfx/interdit-150x150.png" />
                                <input type="hidden" class="js-delete-input" name="document_to_delete_<?php echo $document['id'] ?>" value="0" />
                                
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
                                <a class="change-page" href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyAttachementPosition&direction=M&attachement=document&attachement_id=<?php echo $document['id']; ?>&lang=<?php echo $lang; ?>&tab=documentTab">
                                    <i class="icon-arrow-up"></i>
                                </a>

                                <a class="change-page" href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyAttachementPosition&direction=D&attachement=document&attachement_id=<?php echo $document['id']; ?>&lang=<?php echo $lang; ?>&tab=documentTab">
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="moduleTab" class="tab-pane <?php if($tab == "moduleTab") echo "active" ?>">
                        <div class="row-fluid">
                            <div class="span12">
                                <?php ActionsAdminModules::instance()->inclure_module_admin("rubriquemodifier"); ?>
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

<!-- associatedContent delation -->
<div class="modal hide" id="deletContenuassoceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3><?php echo trad('Cautious', 'admin'); ?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo trad('DeleteAssociatedContentWarning', 'admin'); ?></p>
        <p id="associatedContentDelationInfo"></p>
    </div>
    <div class="modal-footer">
        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
        <a class="btn btn-primary" id="associatedContentDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
    </div>
</div>

<!-- associatedFeature delation -->
<div class="modal hide" id="deletAssociatedFeatureModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3><?php echo trad('Cautious', 'admin'); ?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo trad('DeleteAssociatedFeatureWarning', 'admin'); ?></p>
        <p id="associatedFeatureDelationInfo"></p>
    </div>
    <div class="modal-footer">
        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
        <a class="btn btn-primary" id="associatedFeatureDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
    </div>
</div>

<!-- associatedVariant delation -->
<div class="modal hide" id="deletAssociatedVariantModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3><?php echo trad('Cautious', 'admin'); ?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo trad('DeleteAssociatedVariantWarning', 'admin'); ?></p>
        <p id="associatedVariantDelationInfo"></p>
    </div>
    <div class="modal-footer">
        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
        <a class="btn btn-primary" id="associatedVariantDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
    </div>
</div>

<?php require_once("pied.php"); ?> 
<script type="text/javascript">
$(document).ready(function(){
    var form = 0;

    $("#formulaire").change(function(e){
        if(!$(e.target).is('.not-change-page'))
            form=1;
    });

    $(".change-page").click(function(e){
        if(form == 1){            
            e.preventDefault();
            $("#changeLangLink").attr("href",$(this).attr('href') + '&tab=' + $("ul#mainTabs li.active a").attr('href').substr(1));
            $("#changeLangModal").modal("show");
        }
    });
    
    /*keep current tab when changing language*/
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
    
    /*associated features event*/
    $('#associatedContent_dossier').change(function(){
        $('#select_prodcont').load(
            'ajax/contenu_associe.php',
            'action=contenu_assoc&type=0&objet=<?php echo $rubrique->id; ?>&id_dossier=' + $(this).val(),
            function()
            {
                $('#select_prodcont').trigger('change');
            }
        );
    });
    
    $('#select_prodcont').change(function()
    {
        if($(this).val() != null)
        {
            $('#link_prodcont').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=addAssociatedContent&contenu=' + $(this).val());
            $('#link_prodcont').removeClass('disabled');
        }
        else
        {
            $('#link_prodcont').removeAttr('href');
            $('#link_prodcont').addClass('disabled');
        }
    });
    
    $('#associatedFeatureList').change(function()
    {
        if($(this).val() != null)
        {
            $('#link_addAssociatedFeature').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=addAssociatedFeature&feature=' + $(this).val());
            $('#link_addAssociatedFeature').removeClass('disabled');
        }
        else
        {
            $('#link_addAssociatedFeature').removeAttr('href');
            $('#link_addAssociatedFeature').addClass('disabled');
        }
    });
    
    $('#associatedVariantList').change(function()
    {
        if($(this).val() != null)
        {
            $('#link_addAssociatedVariant').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=addAssociatedVariant&variant=' + $(this).val());
            $('#link_addAssociatedVariant').removeClass('disabled');
        }
        else
        {
            $('#link_addAssociatedVariant').removeAttr('href');
            $('#link_addAssociatedVariant').addClass('disabled');
        }
    });
    
    /*associated features loading*/
    
    $('#associatedContent_dossier').trigger('change');
    $('#associatedFeatureList').trigger('change');
    $('#associatedVariantList').trigger('change');
    
    /*associated features modals*/
    $('.js-delete-associatedContent').click(function()
    {
        $('#associatedContentDelationInfo').html($(this).attr('associated-content-info'));
        $('#associatedContentDelationLink').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=deleteAssociatedContent&associatedContent=' + $(this).attr('associated-content-id'));
    });
    
    $('.js-delete-associatedFeature').click(function()
    {
        $('#associatedFeatureDelationInfo').html($(this).attr('associated-feature-info'));
        $('#associatedFeatureDelationLink').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=deleteAssociatedFeature&associatedFeature=' + $(this).attr('associated-feature-id'));
    });
    
    $('.js-delete-associatedVariant').click(function()
    {
        $('#associatedVariantDelationInfo').html($(this).attr('associated-variant-info'));
        $('#associatedVariantDelationLink').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=deleteAssociatedVariant&associatedVariant=' + $(this).attr('associated-variant-id'));
    });
});
</script>
</body>
</html>