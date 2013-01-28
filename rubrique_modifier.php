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
                    <div class="tab-pane <?php if($tab=='associationTab'){ ?> active<?php } ?>" id="associationTab">
                        
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
                            <tbody id="listeAssociatedFeature">
                                <tr class="info">
                                    <td class="span10">
<?php
$Flist = AssociatedFeatureAdmin::getInstance()->getListAvailableFeature($rubrique->id);
$AFlist = AssociatedFeatureAdmin::getInstance()->getList($rubrique->id);
for($i=0; $i<count($Flist); $i++)
{
?>
                                        <input type="hidden" id="alive_associated_feature_<?php echo $Flist[$i]['id']; ?>" name="alive_associated_feature_<?php echo $Flist[$i]['id']; ?>" value="0" />
<?php
}
for($i=0; $i<count($AFlist); $i++)
{
?>
                                        <input type="hidden" id="alive_associated_feature_<?php echo $AFlist[$i]['feature_id']; ?>" name="alive_associated_feature_<?php echo $AFlist[$i]['feature_id']; ?>" value="1" />
<?php
}
?>
                                        <select class="span12" id="associatedFeatureList">
<?php
for($i=0; $i<count($Flist); $i++)
{
?>
                                            <option value="<?php echo $Flist[$i]['id']; ?>" js-titre="<?php echo $Flist[$i]['titre']; ?>"><?php echo $Flist[$i]['titre']; ?></option>
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
for($i=0; $i<count($AFlist); $i++)
{
?>
                                <tr>
                                    <td>
                                        <?php echo $AFlist[$i]['feature_titre']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a class="btn btn-mini js-delete-associatedFeature" title="<?php echo trad('supprimer', 'admin'); ?>" href="#" js-feature-id="<?php echo $AFlist[$i]['feature_id'] ?>" js-feature-title="<?php echo $AFlist[$i]['feature_titre'] ?>">
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
                            <tbody id="listeAssociatedVariant">
                                <tr class="info">
                                    <td class="span10">
<?php
$Vlist = AssociatedVariantAdmin::getInstance()->getListAvailableVariant($rubrique->id);
$AVlist = AssociatedVariantAdmin::getInstance()->getList($rubrique->id);
for($i=0; $i<count($Vlist); $i++)
{
?>
                                        <input type="hidden" id="alive_associated_variant_<?php echo $Vlist[$i]['id']; ?>" name="alive_associated_variant_<?php echo $Vlist[$i]['id']; ?>" value="0" />
<?php
}
for($i=0; $i<count($AVlist); $i++)
{
?>
                                        <input type="hidden" id="alive_associated_variant_<?php echo $AVlist[$i]['variant_id']; ?>" name="alive_associated_variant_<?php echo $AVlist[$i]['variant_id']; ?>" value="1" />
<?php
}
?>
                                        <select class="span12" id="associatedVariantList">
<?php
for($i=0; $i<count($Vlist); $i++)
{
?>
                                            <option value="<?php echo $Vlist[$i]['id']; ?>" js-titre="<?php echo $Vlist[$i]['titre']; ?>"><?php echo $Vlist[$i]['titre']; ?></option>
<?php
}
?>
                                        </select>
                                    </td>
                                    <td class="span1 offset1">
                                        <div class="btn-group">
                                            <a class="btn btn-mini" id="link_addAssociatedVariant" title="<?php echo trad('ajouter', 'admin'); ?>" href="#">
                                                <i class="icon-plus-sign"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
<?php
for($i=0; $i<count($AVlist); $i++)
{
?>
                                <tr>
                                    <td>
                                        <?php echo $AVlist[$i]['variant_titre']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a class="btn btn-mini js-delete-associatedVariant" title="<?php echo trad('supprimer', 'admin'); ?>" href="#" js-variant-id="<?php echo $AVlist[$i]['variant_id'] ?>" js-variant-title="<?php echo $AVlist[$i]['variant_titre'] ?>">
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
                        <div class="row-fluid js-bloc-image" js-image-id="<?php echo $image['id'] ?>">
                            <div class="span3" style="position: relative;">
                                <img class="js-image" src="<?php echo  $image["fichier"] ?>">
                                <img style="display: none; position: absolute;" class="js-image-delation" src="gfx/interdit-150x150.png" />
                                <input type="hidden" class="js-delete-input" name="image_to_delete_<?php echo $image['id'] ?>" value="0" />
                                <input type="hidden" class="js-rank-input" name="rank_<?php echo $image['id'] ?>" value="<?php echo $image['classement'] ?>" />
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
                                <?php for($i=1; $i <= CategoryAdmin::getInstance()->getDocumentFile()->getNumberUpload(); $i++): ?>
                                    <input type="file" name="document_<?php echo $i ?>" class="input-large">
                                    <br >
                                <?php endfor;?>
                            </div>
                        </div>
                        <?php foreach(CategoryAdmin::getInstance($rubrique->id)->getDocumentList($lang) as $document): ?>
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

<?php require_once("pied.php"); ?> 
<script type="text/javascript">
/*html elements for dynamic display*/
var htmlElements = {
    tr_list: {
        variant: function(variant_id, variant_title)
        {
            if(!variant_id && !variant_title)
                return;

            return $('<tr />').append(
                $('<td />').html(variant_title),
                $('<td />').append(
                    $('<div />').addClass('btn-groupn').append(
                        $('<a />').addClass('btn').addClass('btn-mini').addClass('js-delete-associatedVariant').attr('title', '<?php echo trad('supprimer', 'admin'); ?>').attr('href', '#').attr('js-variant-id', variant_id).attr('js-variant-title', variant_title).append(
                            $('<i />').addClass('icon-trash')
                        )
                    )
                )
            )
        },
        feature: function(feature_id, feature_title)
        {
            if(!feature_id && !feature_title)
                return;

            return $('<tr />').append(
                $('<td />').html(feature_title),
                $('<td />').append(
                    $('<div />').addClass('btn-groupn').append(
                        $('<a />').addClass('btn').addClass('btn-mini').addClass('js-delete-associatedFeature').attr('title', '<?php echo trad('supprimer', 'admin'); ?>').attr('href', '#').attr('js-feature-id', feature_id).attr('js-feature-title', feature_title).append(
                            $('<i />').addClass('icon-trash')
                        )
                    )
                )
            )
        }
    },
    opt: {
        variant: function(variant_id, variant_title)
        {
            if(!variant_id && !variant_title)
                return;
            
            return $('<option />').val(variant_id).attr('js-titre', variant_title).html(variant_title)
        },
        feature: function(feature_id, feature_title)
        {
            if(!feature_id && !feature_title)
                return;
            
            return $('<option />').val(feature_id).attr('js-titre', feature_title).html(feature_title)
        }
    }
}
    
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
    
    /*features*/
    $('#associatedFeatureList').change(function()
    {
        if($(this).val() != null)
            $('#link_addAssociatedFeature').removeClass('disabled');
        else
            $('#link_addAssociatedFeature').addClass('disabled');
    });
    
    $('#link_addAssociatedFeature').click(function(e)
    {
        e.preventDefault();
        
        /*add line*/
        $('#listeAssociatedFeature').append(
            htmlElements.tr_list.feature(
                $('#associatedFeatureList').val(),
                $('#associatedFeatureList').children(":selected").attr("js-titre")
            )
        );
            
        /*mark for add*/
        $('#alive_associated_feature_' + $('#associatedFeatureList').val()).val(1);
            
        /*delete from select*/
        $('#associatedFeatureList').children(":selected").unbind().remove();
        
        /**/
        $('#associatedFeatureList').trigger('change');
    });
    
    $('.js-delete-associatedFeature').live('click', function(e)
    {
        e.preventDefault();
        
        console.log(
            
        );
        
        /*delete ligne*/
        $(this).parent().parent().parent().unbind().remove();
        
        /*mark for delation*/
        $('#alive_associated_feature_' + $(this).attr('js-feature-id')).val(0);
        
        /*add in select*/
        $('#associatedFeatureList').append(
            htmlElements.opt.feature(
                $(this).attr('js-feature-id'),
                $(this).attr('js-feature-title')
            )
        );
            
        /**/
        $('#associatedFeatureList').trigger('change');
    });
    
    
    /*variants*/
    $('#associatedVariantList').change(function()
    {
        if($(this).val() != null)
            $('#link_addAssociatedVariant').removeClass('disabled');
        else
            $('#link_addAssociatedVariant').addClass('disabled');
    });
    
    $('#link_addAssociatedVariant').click(function(e)
    {
        e.preventDefault();
        
        /*add line*/
        $('#listeAssociatedVariant').append(
            htmlElements.tr_list.variant(
                $('#associatedVariantList').val(),
                $('#associatedVariantList').children(":selected").attr("js-titre")
            )
        );
        
        /*mark for add*/
        $('#alive_associated_variant_' + $('#associatedVariantList').val()).val(1);
        
        /*delete from select*/
        $('#associatedVariantList').children(":selected").unbind().remove();
        
        /**/
        $('#associatedVariantList').trigger('change');
    });
    
    $('.js-delete-associatedVariant').live('click', function(e)
    {
        e.preventDefault();
        
        /*delete ligne*/
        $(this).parent().parent().parent().unbind().remove();
        
        /*mark for delation*/
        $('#alive_associated_variant_' + $(this).attr('js-variant-id')).val(0);
        
        /*add in select*/
        $('#associatedVariantList').append(
            htmlElements.opt.variant(
                $(this).attr('js-variant-id'),
                $(this).attr('js-variant-title')
            )
        );
        
        /**/
        $('#associatedVariantList').trigger('change');
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
});
</script>
</body>
</html>