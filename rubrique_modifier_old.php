<?php
require_once("pre.php");
require_once("auth.php");

//error_reporting(E_ALL);

if(! est_autorise("acces_catalogue"))
    exit;

use Symfony\Component\HttpFoundation\Request;
$request = Request::createFromGlobals();

$rubrique = new Rubrique();
if(!$id || !$rubrique->charger($id))
    redirige('parcourir.php');

$currentLang = new Lang($request->get('lang', ActionsLang::instance()->get_id_langue_courante()));

$errorCode = 0;
$errorMessage = '';

try {
    ActionsAdminCategory::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
}

switch(!empty($tab)?$tab:"")
{
    case 'sectionInformationTab':
    case 'generalDescriptionTab':
    case 'associationTab':
    case 'attachementTab':
        break;
    default:
        $tab = 'sectionInformationTab';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>

<body>
<?php
$menu = "catalogue";
$breadcrumbs = Breadcrumb::getInstance()->getCategoryList($rubrique->id, false);
require_once("entete.php");
?>
        <div class="row-fluid">
        
            <div class="span12">
                
                <ul class="nav nav-tabs">
                    <li class="<?php if($tab=='sectionInformationTab'){ ?>active<?php } ?>"><a href="#sectionInformationTab" data-toggle="tab"><?php echo trad('INFO_RUBRIQUE', 'admin'); ?></a></li>
                    
                    <li class="<?php if($tab=='generalDescriptionTab'){ ?>active<?php } ?>"><a href="#generalDescriptionTab" data-toggle="tab"><?php echo trad('DESCRIPTION_G_RUBRIQUE', 'admin'); ?></a></li>
                    
                    <li class="<?php if($tab=='associationTab'){ ?>active<?php } ?>"><a href="#associationTab" data-toggle="tab"><?php echo trad('ASSOCIATION', 'admin'); ?></a></li>
                    
                    <li class="<?php if($tab=='attachementTab'){ ?>active<?php } ?>"><a href="#attachementTab" data-toggle="tab"><?php echo trad('ATTACHEMENT', 'admin'); ?></a></li>
                        
                    <li class="<?php if($tab=='moduleTab'){ ?>active<?php } ?>"><a href="#moduleTab" data-toggle="tab"><?php echo strtoupper(trad('Modules', 'admin')); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane<?php if($tab=='sectionInformationTab'){ ?> active<?php } ?>" id="sectionInformationTab">
                        
                        <p>
                            <button class="btn btn-large btn-block btn-primary js-submit-change" form-to-submit="changeInformationForm" type="button"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                        </p>
                        
                        <form method="POST" action="rubrique_modifier.php" id="changeInformationForm">
                            <input type="hidden" name="id" value="<?php echo $rubrique->id; ?>" />
                            <input type="hidden" name="action" value="changeInformation" />
                        
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td>ID</td>
                                    <td>
                                        <?php echo $rubrique->id ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('En_ligne', 'admin'); ?></td>
                                    <td>
                                        <input type="checkbox" name="ligne" class="js-editing" <?php if($rubrique->ligne==1) { ?>checked<?php } ?> />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Appartenance', 'admin'); ?></td>
                                    <td>
                                        <select name="parent" class="js-editing">
                                            <option value="0">-- <?php echo trad('Racine', 'admin'); ?> --</option>
<?php
echo arbreOptionRub(0, 1, $id, 0, 1);
?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Champs_libre', 'admin'); ?></td>
                                    <td>
                                        <input type="text" class="span12 js-editing" name="lien" value="<?php echo $rubrique->lien ?>" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                            
                        </form>
                        
                    </div>
                    <div class="tab-pane<?php if($tab=='generalDescriptionTab'){ ?> active<?php } ?>" id="generalDescriptionTab">
                        
                        <p>
                                    <?php echo trad('Changer_langue', 'admin'); ?>
                                        <ul class="nav nav-pills" id="generalDescriptionLangChoice">

<?php
foreach(LangAdmin::getInstance()->getList() as $theLang)
{
?>
                                            <li class="<?php if($theLang->id == $currentLang->id){ ?>active<?php } ?>">
                                                <a href="#generalDescriptionLang<?php echo $theLang->id; ?>Block" data-toggle="pill">
                                                    <img src="gfx/lang<?php echo $theLang->id; ?>.gif" />
                                                </a>
                                            </li>
<?php
}
?>
                                        </ul>
                        </p>
                        
                        <div class="pill-content">
<?php
foreach(LangAdmin::getInstance()->getList() as $theLang)
{
    $rubriquedesc = new Rubriquedesc($rubrique->id, $theLang->id);
?>
                            <div class="pill-pane <?php if($theLang->id == $currentLang->id){ ?>active<?php } ?>" id="generalDescriptionLang<?php echo $theLang->id; ?>Block">
                                
                                <p>
                                    <button class="btn btn-large btn-block btn-primary js-submit-change" form-to-submit="changeDescriptionLang<?php echo $theLang->id; ?>Form" type="button"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                                </p>
                                
                                <form method="POST" action="rubrique_modifier.php" id="changeDescriptionLang<?php echo $theLang->id; ?>Form">
                                    <input type="hidden" name="id" value="<?php echo $rubrique->id; ?>" />
                                    <input type="hidden" name="action" value="changeDescription" />
                                    <input type="hidden" name="lang" value="<?php echo $theLang->id; ?>" />
                                
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td class="span4"><?php echo trad('Titre', 'admin'); ?></td>
                                            <td class="span8">
                                                <input type="text" class="span12 js-editing" name="titre" value="<?php echo $rubriquedesc->titre; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo trad('Chapo', 'admin'); ?></td>
                                            <td>
                                                <textarea rows="3" class="span12 js-editing" name="chapo"><?php echo $rubriquedesc->chapo; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo trad('Description', 'admin'); ?></td>
                                            <td>
                                                <textarea rows="20" class="span12 js-editing" name="description"><?php echo $rubriquedesc->description; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo trad('PS', 'admin'); ?></td>
                                            <td>
                                                <textarea rows="3" class="span12 js-editing" name="postscriptum"><?php echo $rubriquedesc->postscriptum; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo trad('URL_reecrite', 'admin'); ?></td>
                                            <td>
                                                <input type="text" class="span12 js-editing" name="url" value="<?php echo htmlspecialchars(rewrite_rub($rubrique->id, $theLang->id)); ?>" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                </form>
                                
                                <p>
                                    <button class="btn btn-large btn-block btn-primary js-submit-change" form-to-submit="changeDescriptionLang<?php echo $theLang->id; ?>Form" type="button"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                                </p>
                                
                            </div>
<?php
}
?>
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
                                        <select class="span12" id="associatedContent_dossier">
<?php
echo arbreOption_dos(0, 1, 0, 0, 1);
?>
                                        </select>
                                    </td>
                                    <td class="span5">
                                        <select class="span12" id="select_prodcont"></select>
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
                                        <select class="span12" id="associatedFeatureList">
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
                                        <select class="span12" id="associatedVariantList">
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
                    
                    <div class="tab-pane<?php if($tab=='attachementTab'){ ?> active<?php } ?>" id="attachementTab">
                        
                        <div class="row-fluid">
                            
                            
                            <div class="span12">
                                
                                <ul class="nav nav-tabs">
                                    <li class="span5 <?php if(!$request->get('tabAttachement') || $request->get('tabAttachement')=='pictureAttachementTab'){ ?>active<?php } ?>">
                                        <a href="#pictureAttachementTab" data-toggle="tab">
                                            <h4><?php echo trad('GESTION_PHOTOS', 'admin'); ?></h4>
                                        </a>
                                    </li>
                                    <li class="span5 <?php if($request->get('tabAttachement')=='documentAttachementTab'){ ?>active<?php } ?>">
                                        <a href="#documentAttachementTab" data-toggle="tab">
                                            <h4><?php echo trad('GESTION_DOCUMENTS', 'admin'); ?></h4>
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane<?php if(!$request->get('tabAttachement') || $request->get('tabAttachement')=='pictureAttachementTab'){ ?> active<?php } ?>" id="pictureAttachementTab">
                                        <div class="row-fluid">
                                            <div class="span12">

                                                <h4><?php echo trad('Transferer_images', 'admin'); ?></h4>
                                                <p>
                                                <form action="" method="POST" enctype="multipart/form-data" id="addPictureForm">
                                                    <input type="hidden" name="action" value="addPicture" />
                                                    <input type="hidden" name="id" value="<?php echo $rubrique->id; ?>" />
                <?php
                for($i=0; $i<ProductAdmin::getInstance()->getImageFile()->getNumberUpload(); $i++)
                {
                ?>
                                                    <input class="span12" type="file" name="photo<?php echo $i+1; ?>" />
                <?php
                }
                ?>
                                                </form>
                                                </p>

                                                <p>
                                                    <button class="btn btn-large btn-block btn-primary js-submit-add" form-to-submit="addPictureForm" type="button"><?php echo trad('Ajouter', 'admin'); ?></button>
                                                </p>
                                            </div>
                                        </div>
<?php
                if(CategoryAdmin::getInstance($rubrique->id)->getNumberOfImages() > 0)
                {
?>
                                        <div class="row-fluid">
                                            <div class="span12">
                                                
                                                <h4 id="editPicturesAnchor"><?php echo trad('Editer_images', 'admin'); ?></h4>

                                                <p>
                                                    <?php echo trad('Changer_langue', 'admin'); ?>
                                                    <ul class="nav nav-pills" id="pictureLangChoice">

                <?php
                    foreach(LangAdmin::getInstance()->getList() as $theLang)
                    {
                ?>
                                                        <li class="<?php if($theLang->id == $currentLang->id){ ?>active<?php } ?>">
                                                            <a href="#pictureLang<?php echo $theLang->id; ?>Block" data-toggle="pill">
                                                                <img src="gfx/lang<?php echo $theLang->id; ?>.gif" />
                                                            </a>
                                                        </li>
                <?php
                    }
                ?>
                                                    </ul>
                                                </p>

                                                <div class="pill-content">
                <?php
                    foreach(LangAdmin::getInstance()->getList() as $theLang)
                    {
                ?>
                                                    <div class="pill-pane <?php if($theLang->id == $currentLang->id){ ?>active<?php } ?>" id="pictureLang<?php echo $theLang->id; ?>Block">

                                                        <p>
                                                            <button class="btn btn-large btn-block btn-primary js-submit-change" form-to-submit="editPictureLang<?php echo $theLang->id; ?>Form" type="button"><?php echo trad('Editer', 'admin'); ?></button>
                                                        </p>

                                                        <form action="" method="POST" id="editPictureLang<?php echo $theLang->id; ?>Form">

                                                            <input type="hidden" name="action" value="editPicture" />
                                                            <input type="hidden" name="id" value="<?php echo $rubrique->id; ?>" />
                                                            <input type="hidden" name="lang" value="<?php echo $theLang->id; ?>" />
                <?php
                        $Plist = CategoryAdmin::getInstance($rubrique->id)->getImageList($theLang->id);
                        for($i=0; $i<count($Plist); $i++)
                        {
                ?>
                                                            <div class="row-fluid">
                                                                <div class="span3">

                                                                    <div>
                                                                        <img src="<?php echo $Plist[$i]['fichier']; ?>" alt="">
                                                                    </div>

                                                                    <a style="position:relative; margin-top:-45px; float:right" class="btn btn-large js-delete-picture" href="#deletePictureModal" data-toggle="modal" picture-url="<?php echo $Plist[$i]['fichier'] ?>" picture-id="<?php echo $Plist[$i]['id'] ?>"><i class="icon-trash"></i></a>

                                                                </div>
                                                                <div class="span8">
                                                                    <table class="table table-striped">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="span12">
                                                                                    <?php echo trad('Titre', 'admin'); ?>
                                                                                    <input type="text" name="photo_titre_<?php echo $Plist[$i]['id']; ?>" class="span12 js-editing" value="<?php echo $Plist[$i]['titre']; ?>" />
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <?php echo trad('Chapo', 'admin'); ?>
                                                                                    <textarea name="photo_chapo_<?php echo $Plist[$i]['id']; ?>" class="span12 js-editing"><?php echo $Plist[$i]['chapo']; ?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <?php echo trad('Description', 'admin'); ?>
                                                                                    <textarea name="photo_description_<?php echo $Plist[$i]['id']; ?>" class="span12 js-editing"><?php echo $Plist[$i]['description']; ?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="span1">
                                                                    <a href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyPictureClassement&will=M&picture=<?php echo $Plist[$i]['id']; ?>">
                                                                        <i class="icon-arrow-up"></i>
                                                                    </a>
                                                                    <span class="picture_classement_editable" picture-id="<?php echo $Plist[$i]['id']; ?>"><?php echo $Plist[$i]['classement']; ?></span>
                                                                    <a href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyPictureClassement&will=D&picture=<?php echo $Plist[$i]['id']; ?>">
                                                                        <i class="icon-arrow-down"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                <?php
                        }
                ?>
                                                            
                                                        </form>

                                                        <p>
                                                            <button class="btn btn-large btn-block btn-primary js-submit-change" form-to-submit="editPictureLang<?php echo $theLang->id; ?>Form" type="button"><?php echo trad('Editer', 'admin'); ?></button>
                                                        </p>
                                                    </div>
                <?php
                    }
                ?>
                                                </div>

                                            </div>
                                        </div>
<?php
                }
?>
                                    </div>
                                    <div class="tab-pane<?php if($request->get('tabAttachement')=='documentAttachementTab'){ ?> active<?php } ?>" id="documentAttachementTab">
                                        
                                        <div class="row-fluid">
                                            <div class="span12">

                                                <h4><?php echo trad('Transferer_documents', 'admin'); ?></h4>
                                                <p>
                                                <form action="" method="POST" enctype="multipart/form-data" id="addDocumentForm">
                                                    <input type="hidden" name="action" value="addDocument" />
                                                    <input type="hidden" name="id" value="<?php echo $rubrique->id; ?>" />
                <?php
                for($i=0; $i<ProductAdmin::getInstance()->getDocumentFile()->getNumberUpload(); $i++)
                {
                ?>
                                                    <input class="span12" type="file" name="doc<?php echo $i+1; ?>" />
                <?php
                }
                ?>
                                                </form>
                                                </p>

                                                <p>
                                                    <button class="btn btn-large btn-block btn-primary js-submit-add" form-to-submit="addDocumentForm" type="button"><?php echo trad('Ajouter', 'admin'); ?></button>
                                                </p>
                                            </div>
                                        </div>
<?php
                if(CategoryAdmin::getInstance($rubrique->id)->getNumberOfDocuments() > 0)
                {
?>
                                        <div class="row-fluid">
                                            <div class="span12">
                                                
                                                <h4 id="editDocumentsAnchor"><?php echo trad('Editer_documents', 'admin'); ?></h4>

                                                <p>
                                                    <?php echo trad('Changer_langue', 'admin'); ?>
                                                    <ul class="nav nav-pills" id="documentLangChoice">

                <?php
                    foreach(LangAdmin::getInstance()->getList() as $theLang)
                    {
                ?>
                                                        <li class="<?php if($theLang->id == $currentLang->id){ ?>active<?php } ?>">
                                                            <a href="#documentLang<?php echo $theLang->id; ?>Block" data-toggle="pill">
                                                                <img src="gfx/lang<?php echo $theLang->id; ?>.gif" />
                                                            </a>
                                                        </li>
                <?php
                    }
                ?>
                                                    </ul>
                                                </p>

                                                <div class="pill-content">
                <?php
                    foreach(LangAdmin::getInstance()->getList() as $theLang)
                    {
                ?>
                                                    <div class="pill-pane <?php if($theLang->id == $currentLang->id){ ?>active<?php } ?>" id="documentLang<?php echo $theLang->id; ?>Block">

                                                        <p>
                                                            <button class="btn btn-large btn-block btn-primary js-submit-change" form-to-submit="editDocumentLang<?php echo $theLang->id; ?>Form" type="button"><?php echo trad('Editer', 'admin'); ?></button>
                                                        </p>

                                                        <form action="" method="POST" id="editDocumentLang<?php echo $theLang->id; ?>Form">

                                                            <input type="hidden" name="action" value="editDocument" />
                                                            <input type="hidden" name="id" value="<?php echo $rubrique->id; ?>" />
                                                            <input type="hidden" name="lang" value="<?php echo $theLang->id; ?>" />
                <?php
                        $Dlist = CategoryAdmin::getInstance($rubrique->id)->getDocumentList($theLang->id);
                        for($i=0; $i<count($Dlist); $i++)
                        {
                ?>
                                                            <div class="row-fluid">
                                                                <div class="span3">

                                                                    <p><a target="_blank" href="<?php echo $Dlist[$i]['fichier'] ?>"><?php echo $Dlist[$i]['nomFichier'] ?></a></p>
                                                                    <p class="offset4"><a style="position:relative; margin-top:-45px; float:right" class="btn btn-large js-delete-document" href="#deleteDocumentModal" data-toggle="modal" document-file="<?php echo $Dlist[$i]['nomFichier'] ?>" document-id="<?php echo $Dlist[$i]['id'] ?>"><i class="icon-trash"></i></a></p>

                                                                </div>
                                                                <div class="span8">
                                                                    <table class="table table-striped">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="span12">
                                                                                    <?php echo trad('Titre', 'admin'); ?>
                                                                                    <input type="text" name="document_titre_<?php echo $Dlist[$i]['id']; ?>" class="span12 js-editing" value="<?php echo $Dlist[$i]['titre']; ?>" />
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <?php echo trad('Chapo', 'admin'); ?>
                                                                                    <textarea name="document_chapo_<?php echo $Dlist[$i]['id']; ?>" class="span12 js-editing"><?php echo $Dlist[$i]['chapo']; ?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <?php echo trad('Description', 'admin'); ?>
                                                                                    <textarea name="document_description_<?php echo $Dlist[$i]['id']; ?>" class="span12 js-editing"><?php echo $Dlist[$i]['description']; ?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="span1">
                                                                    <a href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyDocumentClassement&will=M&document=<?php echo $Dlist[$i]['id']; ?>">
                                                                        <i class="icon-arrow-up"></i>
                                                                    </a>
                                                                    <span class="document_classement_editable" document-id="<?php echo $Dlist[$i]['id']; ?>"><?php echo $Dlist[$i]['classement']; ?></span>
                                                                    <a href="rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyDocumentClassement&will=D&document=<?php echo $Dlist[$i]['id']; ?>">
                                                                        <i class="icon-arrow-down"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                <?php
                        }
                ?>
                                                            
                                                        </form>

                                                        <p>
                                                            <button class="btn btn-large btn-block btn-primary js-submit-change" form-to-submit="editDocumentLang<?php echo $theLang->id; ?>Form" type="button"><?php echo trad('Editer', 'admin'); ?></button>
                                                        </p>
                                                    </div>
                <?php
                    }
                ?>
                                                </div>

                                            </div>
                                        </div>
<?php
                }
?>
                                        
                                    </div>
                                </div>
                            
                            </div>
                        </div>
                        
                    </div>
                    
                    <div id="moduleTab" class="tab-pane <?php if($tab == "moduleTab"){ ?>active<?php } ?>">
                        <div class="row-fluid">
                            <div class="span12">
                                <?php ActionsAdminModules::instance()->inclure_module_admin("rubriquemodifier"); ?>
                            </div>
                        </div>
                    </div>
                    
                </div>
                                
            </div>
            
        </div>
        
        <div class="row-fluid">
            <div class="span12">

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
                
                <!-- picture delation -->
                <div class="modal hide" id="deletePictureModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3><?php echo trad('Cautious', 'admin'); ?></h3>
                    </div>
                    <div class="modal-body">
                        <p><?php echo trad('DeletePictureWarning', 'admin'); ?></p>
                        <img id="pictureDelationUrl" class="span11" />
                    </div>
                    <div class="modal-footer">
                        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                        <a class="btn btn-primary" id="pictureDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
                    </div>
                </div>
                
                <!-- document delation -->
                <div class="modal hide" id="deleteDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3><?php echo trad('Cautious', 'admin'); ?></h3>
                    </div>
                    <div class="modal-body">
                        <p><?php echo trad('DeleteDocumentWarning', 'admin'); ?></p>
                        <p id="documentDelationUrl"></p>
                    </div>
                    <div class="modal-footer">
                        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                        <a class="btn btn-primary" id="documentDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
                    </div>
                </div>
                
                <!-- switch -->
                <div class="modal hide" id="switchModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close switchModalButton" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3><?php echo trad('Cautious', 'admin'); ?></h3>
                    </div>
                    <div class="modal-body">
                        <p><?php echo trad('SwitchWarning', 'admin'); ?></p>
                        <input type="hidden" id="switchAction" value="deny" />
                    </div>
                    <div class="modal-footer">
                        <a class="btn switchModalButton" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                        <a class="btn btn-primary switchModalButton" id="switchLinkOK" data-dismiss="modal" aria-hidden="true"><?php echo trad('Oui', 'admin'); ?></a>
                    </div>
                </div>
                
            </div>
        </div>
        
<?php require_once("pied.php");?>
<script type="text/javascript" src="js/jeditable.min.js"></script>
<script type="text/javascript">

jQuery(function($){
    
    /*event*/
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
       
    /*editable object (ranking)*/
    $('.picture_classement_editable').editable(function(value, settings){},{
        onblur : function(value){
            window.location = 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyPictureClassement&picture=' + $(this).attr('picture-id') + '&will=' + value;
        }
    });
    
    $('.document_classement_editable').editable(function(value, settings){},{
        onblur : function(value){
            window.location = 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=modifyDocumentClassement&document=' + $(this).attr('document-id') + '&will=' + value;
        }
    });
    
    /* editon form commit */
    $('.js-submit-change').click(function()
    {
        $('#' + $(this).attr('form-to-submit')).submit();
    });
    
    /* prevent leaving when unsaved change */
    /** click **/
    var clickedA = null;
    var aEvent = null;
    $('a').on('click', function (e)
    {
        if(editing && !$(this).is('.switchModalButton'))
        {
            aEvent = 'click';
            
            e.preventDefault();
            
            clickedA = $(this);
            
            $('#switchAction').val('deny');
            $('#switchModal').modal('toggle');
        }
    });
    /** button add **/
    $('.js-submit-add').click(function(e)
    {
        if(editing && !$(this).is('.switchModalButton'))
        {
            aEvent = 'submitAdd';
            e.preventDefault();
            
            clickedA = $(this);
            
            $('#switchAction').val('deny');
            $('#switchModal').modal('toggle');
        }
        else
        {
            $('#' + $(this).attr('form-to-submit')).submit();
        }
    });
    /** tab change **/
    $('.nav').on('show', function (e)
    {
        if(editing)
        {
            aEvent = 'showTab';
            e.preventDefault();
        }
    });
    /** modal pop **/
    $('.modal').on('show', function (e)
    {
        if(editing && !$(this).is('#switchModal'))
        {
            aEvent = 'showTab';
            e.preventDefault();
        }
    });
    
    /* traitement reponse */
    $('#switchModal').on('hidden', function(e)
    {
        if($('#switchAction').val() == 'confirm')
        {
            editing = false;
            if(aEvent && clickedA)
            {
                switch(aEvent)
                {
                    case 'click':
                        window.location = clickedA.attr("href");
                        break;
                    case 'showTab':
                        clickedA.trigger('click');
                        break;
                    case 'submitAdd':
                        $('#' + clickedA.attr('form-to-submit')).submit();
                        break;
                }
            }
        }
    })
    
    $('#switchLinkOK').click(function()
    {
        $('#switchAction').val('confirm');
    });
    /****/
    
    /*dectect unsaved change*/
    var editing = false;
    $('.js-editing').change(function()
    {
        editing = true;
    });
    
    /*loading*/
    $('#associatedContent_dossier').trigger('change');
    $('#associatedFeatureList').trigger('change');
    $('#associatedVariantList').trigger('change');
    
    /*modal*/
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
    
    $('.js-delete-picture').click(function()
    {
        $('#pictureDelationUrl').attr('src', $(this).attr('picture-url'));
        $('#pictureDelationLink').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=deletePicture&picture=' + $(this).attr('picture-id'));
    });
    
    $('.js-delete-document').click(function()
    {
        $('#documentDelationUrl').html($(this).attr('document-file'));
        $('#documentDelationLink').attr('href', 'rubrique_modifier.php?id=<?php echo $rubrique->id; ?>&action=deleteDocument&document=' + $(this).attr('document-id'));
    });
    
});

</script>
    
</script>
</body>
</html>
