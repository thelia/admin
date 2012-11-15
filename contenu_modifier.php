<?php
require_once("pre.php");
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
    ActionsAdminContent::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
}


$contenu = new Contenu($request->query->get("id"));
$contenudesc = new Contenudesc($contenu->id, $lang);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>

    <body>
<?php
$menu = "contenu";
$breadcrumbs = Breadcrumb::getInstance()->getContentListe($request->get('dossier'), $contenudesc->titre);
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3><?php echo trad('DESCRIPTION_G', 'admin'); ?></h3>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span4 offset5">
            <ul class="nav nav-pills">
                <?php foreach (LangAdmin::getInstance()->getList() as $displayLang): ?>
                    <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>"><a href="contenu_modifier.php?id=<?php echo $contenu->id; ?>&dossier=<?php echo $contenu->dossier ?>&lang=<?php echo $displayLang->id; ?>" class="change-page"><img src="gfx/lang<?php echo $displayLang->id; ?>.gif" /></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <ul class="nav nav-tabs">
                <li class="<?php if ($tab == 'generalDescriptionTab') echo "active"; ?>"><a href="#generalDescriptionTab" data-toggle="tab"><?php echo trad('DESCRIPTION_G_RUBRIQUE', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'sectionInformationTab') echo "active"; ?>"><a href="#sectionInformationTab" data-toggle="tab"><?php echo trad('INFO_RUBRIQUE', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'imageTab') echo "active"; ?>"><a href="#imageTab" data-toggle="tab"><?php echo trad('GESTION_PHOTOS', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'documentTab') echo "active"; ?>"><a href="#documentTab" data-toggle="tab"><?php echo trad('GESTION_DOCUMENTS', 'admin'); ?></a></li>
                <li class="<?php if ($tab == 'moduleTab') echo "active"; ?>"><a href="#moduleTab" data-toggle="tab"><?php echo strtoupper(trad('Modules', 'admin')); ?></a></li>
            </ul>
            <form method="post" action="contenu_modifier.php" enctype="multipart/form-data" id="formulaire">
                <input type="hidden" name="id" value="<?php echo $contenu->id; ?>">
                <input type="hidden" name="dossier" value="<?php echo $contenu->dossier; ?>">
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
                                            <td class="span8"><input type="text" class="span12" name="titre" value="<?php echo $contenudesc->titre; ?>"></td>    
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('Chapo', 'admin'); ?></p>
                                                <p><small><?php echo trad('courte_descript_intro', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8"><textarea class="span12 text_editor" name="chapo"><?php echo $contenudesc->chapo; ?></textarea></td>
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('Description', 'admin'); ?></p>
                                                <p><small><?php echo trad('description_complete', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8">
                                                <textarea class="span12 text_editor" rows="10" name="description"><?php echo $contenudesc->description; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span4">
                                                <p><?php echo trad('PS', 'admin'); ?></p>
                                                <p><small><?php echo trad('champs_info_complementaire', 'admin'); ?></small></p>
                                            </td>
                                            <td class="span8">
                                                <textarea class="span12 text_editor" name="postscriptum"><?php echo $contenudesc->postscriptum; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="span4"><?php echo trad('URL_reecrite', 'admin'); ?></td>
                                            <td class="span8"><input type="text" class="span12" name="urlreecrite" value="<?php echo htmlspecialchars(rewrite_cont("$contenu->id", $lang)); ?>"></td>
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
                                        <td><?php echo $contenu->id; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('Appartenance', 'admin'); ?></td>
                                        <td>
                                            <select name="dossier">
                                                <option value="0"><?php echo trad('A la racine', 'admin'); ?></option>
                                                <?php echo arbreOption_dos(0, 1, $contenu->dossier, $_GET['dossier'], -1); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('En_ligne', 'admin'); ?></td>
                                        <td><input type="checkbox" name="ligne" <?php if ($contenu->ligne): ?>checked="checked" <?php endif; ?>></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo trad('Derniere_modif', 'admin'); ?></td>
                                        <td><?php echo strftime("%d/%m/%Y %H:%M:%S", strtotime($contenu->datemodif)); ?></td>
                                    </tr>
                                </table>
                            </div>    
                        </div>
                    </div>
                    <div class="tab-pane <?php if ($tab == 'imageTab') echo "active"; ?>" id="imageTab">
                        <div class="row-fluid">
                        <div class="span6 offset4">
                            <?php for($i = 1; $i <= ContentAdmin::getInstance()->getImageFile()->getNumberUpload(); $i++): ?>
                                <input type="file" name="photo<?php echo $i; ?>" class="input-large"> <br >
                            <?php endfor; ?>
                        </div>
                        </div>
                        <?php foreach (ContentAdmin::getInstance($contenu->id)->getImageList($lang) as $image): ?>
                        <hr>
                        <div class="row-fluid">
                            <div class="span3">
                                <img src="<?php echo  $image["fichier"] ?>">
                                <a style="position:relative; margin-top:-45px; float:right" class="btn btn-large js-delete-picture" href="#deletePictureModal" data-toggle="modal" picture-file="<?php echo $image["fichier"]; ?>" picture-id="<?php echo $image['id'] ?>"><i class="icon-trash"></i></a>
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
                                <a class="change-page" href="contenu_modifier.php?id=<?php echo $contenu->id; ?>&action=modifyAttachementPosition&direction=M&attachement=image&attachement_id=<?php echo $image['id']; ?>&lang=<?php echo $lang; ?>&tab=imageTab">
                                    <i class="icon-arrow-up"></i>
                                </a>

                                <a class="change-page" href="contenu_modifier.php?id=<?php echo $contenu->id; ?>&action=modifyAttachementPosition&direction=D&attachement=image&attachement_id=<?php echo $image['id']; ?>&lang=<?php echo $lang; ?>&tab=imageTab">
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class=" tab-pane <?php if($tab == 'documentTab') echo "active" ?>" id="documentTab">
                        <div class="row-fluid">
                            <div class="span6 offset4">
                                <?php for($i=1; $i <= ContentAdmin::getInstance()->getDocumentFile()->getNumberUpload(); $i++): ?>
                                    <input type="file" name="document_<?php echo $i ?>" class="input-large">
                                <?php endfor;?>
                            </div>
                        </div>
                        <?php foreach(ContentAdmin::getInstance($contenu->id)->getDocumentList($lang) as $document): ?>
                        <hr>
                        <div class="row-fluid">
                            <div class="span3">
                                <p><a target="_blank" href="<?php echo $document["fichier"]; ?>"><?php echo $document["nomFichier"]; ?></a></p>
                                <p class="offset4"><a class="btn btn-large js-delete-document" href="#deleteDocumentModal" data-toggle="modal" document-file="<?php echo $document["nomFichier"]; ?>" document-id="<?php echo $document['id'] ?>"><i class="icon-trash"></i></a></p>
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
                                <a class="change-page" href="contenu_modifier.php?id=<?php echo $contenu->id; ?>&action=modifyAttachementPosition&direction=M&attachement=document&attachement_id=<?php echo $document['id']; ?>&lang=<?php echo $lang; ?>&tab=documentTab">
                                    <i class="icon-arrow-up"></i>
                                </a>

                                <a class="change-page" href="contenu_modifier.php?id=<?php echo $contenu->id; ?>&action=modifyAttachementPosition&direction=D&attachement=document&attachement_id=<?php echo $document['id']; ?>&lang=<?php echo $lang; ?>&tab=documentTab">
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="moduleTab" class="tab-pane <?php if($tab == "moduleTab") echo "active" ?>">
                        <div class="row-fluid">
                            <div class="span12">
                                <?php ActionsAdminModules::instance()->inclure_module_admin("contenumodifier"); ?>
                            </div>
                        </div>
                    </div>
                </div>
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
            $("#changeLangLink").attr("href",$(this).attr('href'));
            $("#changeLangModal").modal("show");
        }
    });
    $('a[data-toggle="tab"]').on('show',function(e){
        $("input[name=tab]").val($(e.target).attr('href').substring(1));
    });
});
</script>
</body>
</html>