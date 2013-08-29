<?php
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_catalogue"))
    exit;
require_once __DIR__ . '/liste/accessoire.php';
require_once __DIR__ . '/liste/contenu_associe.php';
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();


if (!isset($action))
    $action = "";
if (!isset($lang))
    $lang = ActionsLang::instance()->get_id_langue_courante();
if (!isset($id))
    $id = "";
if (!isset($ref))
    $ref = "";
if (!isset($tab))
    $tab = "generalDescriptionTab";

$errorCode = 0;
$errorMessage = '';
$errorDuplicate = 0;

try {
    ActionsAdminProduct::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    switch($e->getCode()) {
        case TheliaAdminException::REF_ALREADY_EXISTS:
        case TheliaAdminException::REF_EMPTY:
            $errorDuplicate = $e->getCode();
            $duplicate = $request->request->get('duplicate');
            break;
        default :
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            break;
    }
}

$produit = new Produit($request->get('ref'));
$produitdesc = new Produitdesc($produit->id, $lang);
$produitdesc->chapo = str_replace("<br />", "\n", $produitdesc->chapo);
$produitdesc->postscriptum = str_replace("<br />", "\n", $produitdesc->postscriptum);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>

    <body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("produit_modifier_top");
$menu = "catalogue";
$breadcrumbs = Breadcrumb::getInstance()->getProductList($request->get('rubrique'), $produitdesc->titre);
require_once("entete.php");
?>
            <div class="row-fluid">
                <div class="span12">
                    <h3><?php echo trad('Reference', 'admin'); ?> : <?php echo $produit->ref; ?></h3>
                </div>
            </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("produit_modifier");
?>
            <div class="row-fluid">
                <div class="span4 offset5">
                    <div class="row-fluid">
                        <div class="span4">
                            <ul class="nav nav-pills">
                                <?php foreach (LangAdmin::getInstance()->getList() as $displayLang): ?>
                                    <li class="<?php if ($displayLang->id == $lang) { ?>active<?php } ?>"><a href="produit_modifier.php?ref=<?php echo $produit->ref; ?>&rubrique=<?php echo $produit->rubrique ?>&lang=<?php echo $displayLang->id; ?>" class="change-page change-lang"><img src="gfx/lang<?php echo $displayLang->id; ?>.gif" /></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="span4 offset4" style="text-align: right">
                            <ul class="nav nav-pills">
                                <li class="">
                                    <?php
                                    $previous = ToolBoxAdmin::getPrevious($produit, 'rubrique');
                                    if($previous !== false)
                                    {
                                    ?>
                                    <a href="produit_modifier.php?ref=<?php echo $previous->ref; ?>&rubrique=<?php echo $previous->rubrique; ?>" title="<?php echo trad('previous', 'admin'); ?>" class="change-page">
                                        <i class="icon-backward"></i>
                                    </a>
                                    <?php
                                    }
                                    ?>
                                </li>
                                <li class="">
                                    <a href="#duplicateModal" target="_blank" title="<?php echo trad('duplicate', 'admin'); ?>" class="change-page" data-toggle="modal">
                                        <i class="icon-folder-close"></i>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="<?php echo urlfond("produit", "id_produit=$produit->id&id_rubrique=$produit->rubrique", true); ?>" target="_blank" title="<?php echo trad('preview', 'admin'); ?>" class="change-page">
                                        <i class="icon-eye-open"></i>
                                    </a>
                                </li>
                                <li class="">
                                    <?php
                                    $next = ToolBoxAdmin::getNext($produit, 'rubrique');
                                    if($next !== false)
                                    {
                                    ?>
                                    <a href="produit_modifier.php?ref=<?php echo $next->ref; ?>&rubrique=<?php echo $next->rubrique; ?>" title="<?php echo trad('next', 'admin'); ?>" class="change-page">
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
                        <li class="<?php if ($tab == 'caracteristiqueTab') echo "active"; ?>"><a href="#caracteristiqueTab" data-toggle="tab"><?php echo trad('INFO_SUP', 'admin'); ?></a></li>
                        <li class="<?php if ($tab == 'imageTab') echo "active"; ?>"><a href="#imageTab" data-toggle="tab"><?php echo trad('GESTION_PHOTOS', 'admin'); ?></a></li>
                        <li class="<?php if ($tab == 'documentTab') echo "active"; ?>"><a href="#documentTab" data-toggle="tab"><?php echo trad('GESTION_DOCUMENTS', 'admin'); ?></a></li>
                        <li class="<?php if ($tab == 'moduleTab') echo "active"; ?>"><a href="#moduleTab" data-toggle="tab"><?php echo strtoupper(trad('Modules', 'admin')); ?></a></li>
                    </ul>
                    <form method="post" action="produit_modifier.php" enctype="multipart/form-data" id="formulaire">
                        <p>
                            <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
                        </p>    
                        <input type="hidden" name="ref" value="<?php echo $produit->ref ?>">
                        <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                        <input type="hidden" name="action" value="modifier" >
                        <input type="hidden" name="tab" value="<?php echo $tab; ?>" >
                        <div class="tab-content">
                            <div class="tab-pane <?php if ($tab == 'generalDescriptionTab') echo "active"; ?>" id="generalDescriptionTab">
                                <div class="row-fluid">
                                    <div class="span12">
                                        <table class="table table-striped">
                                            <tbody>
                                                <tr>
                                                    <td class="span4"><?php echo trad('Titre', 'admin'); ?></td> 
                                                    <td class="span8"><input class="span12" type="text" name="titre" value="<?php echo htmlspecialchars($produitdesc->titre); ?>"></td>
                                                </tr>
                                                <tr>
                                                    <td class="span4">
                                                        <p><?php echo trad('Chapo', 'admin'); ?></p>
                                                        <p><small><?php echo trad('courte_descript_intro', 'admin'); ?></small></p>
                                                    </td>
                                                    <td class="span8"><textarea class="span12 text_editor" name="chapo"><?php echo $produitdesc->chapo; ?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td class="span4">
                                                        <p><?php echo trad('Description', 'admin'); ?></p>
                                                        <p><small><?php echo trad('description_complete', 'admin'); ?></small></p>
                                                    </td>
                                                    <td class="span8">
                                                        <textarea class="span12 text_editor" rows="10" name="description"><?php echo $produitdesc->description; ?></textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="span4">
                                                        <p><?php echo trad('PS', 'admin'); ?></p>
                                                        <p><small><?php echo trad('champs_info_complementaire', 'admin'); ?></small></p>
                                                    </td>
                                                    <td class="span8">
                                                        <textarea class="span12 text_editor" name="postscriptum"><?php echo $produitdesc->postscriptum; ?></textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="span4"><?php echo trad('URL_reecrite', 'admin'); ?></td>
                                                    <td class="span8"><input type="text" class="span12" name="urlreecrite" value="<?php echo htmlspecialchars(rewrite_prod("$produit->ref", $lang)); ?>"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane <?php if ($tab == 'sectionInformationTab') echo "active"; ?>" id="sectionInformationTab">
                                <div class="row-fluid">
                                    <div class="span6">
                                        <table class="table table-striped">
                                            <tbody>
                                                <tr>
                                                    <td><?php echo trad('Prix_TTC', 'admin'); ?></td>
                                                    <td><input type="text" name="prix" value="<?php echo $produit->prix; ?>" ></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('Prix_promo_TTC', 'admin'); ?></td>
                                                    <td><input type="text" name="prix2" value="<?php echo $produit->prix2; ?>" ></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('TVA', 'admin'); ?></td>
                                                    <td><input type="text" name="tva" value="<?php echo $produit->tva; ?>" ></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('Poids', 'admin'); ?></td>
                                                    <td><input type="text" name="poids" value="<?php echo $produit->poids; ?>" ></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('Ecotaxe', 'admin'); ?></td>
                                                    <td><input type="text" name="ecotaxe" value="<?php echo $produit->ecotaxe; ?>"</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="span6">
                                        <table class="table table-striped">
                                            <tbody>
                                                <tr>
                                                    <td><?php echo trad('Nouveaute', 'admin'); ?></td>
                                                    <td><input type="checkbox" name="nouveaute" <?php if ($produit->nouveaute): ?>checked="checked" <?php endif; ?> ></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('En_promotion', 'admin'); ?></td>
                                                    <td><input type="checkbox" name="promo" <?php if ($produit->promo): ?>checked="checked" <?php endif; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('En_ligne', 'admin'); ?></td>
                                                    <td><input type="checkbox" name="ligne" <?php if ($produit->ligne): ?>checked="checked" <?php endif; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('Stock', 'admin'); ?></td>
                                                    <td><input type="text" name="stock" value="<?php echo $produit->stock; ?>"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row-fluid">
                                    <div class="span12">
                                        <div class="littletable">
                                        <table class="table table-striped table-condensed">
                                            <tbody>
                                                <tr>
                                                    <td>ID</td>
                                                    <td><?php echo $produit->id; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('Appartenance', 'admin'); ?></td>
                                                    <td>
                                                        <select name="rubrique">
                                                            <option value="0"><?php echo trad('A la racine', 'admin'); ?></option>
                                                            <?php 
                                                                  echo arbreOption(0, 1, $produit->rubrique, 0);
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo trad('Derniere_modif', 'admin'); ?></td>
                                                    <td><?php echo strftime("%d/%m/%Y %H:%M:%S", strtotime($produit->datemodif)); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane <?php if ($tab == 'caracteristiqueTab') echo "active"; ?>" id="caracteristiqueTab">
                                <div class="row-fluid">
                                    <div class="span6">
                                        <table class="table table-striped">
                                            <caption>
                                                <h3><?php echo trad('CARACTERISTIQUES_AJOUTEES', 'admin'); ?></h3>
                                            </caption>
                                            <tbody>
                                            <?php
                                            $caracteristiquedesc = new Caracteristiquedesc();
                                            $caracdispdesc = new Caracdispdesc();

                                            $query = "select rc.caracteristique from ".  Rubcaracteristique::TABLE." rc left join ".Caracteristique::TABLE." c on rc.caracteristique=c.id where rc.rubrique=".$produit->rubrique." order by c.classement";
                                            $resul = $produit->query($query);

                                            $caracval = new Caracval();

                                            while($resul && $row = $produit->fetch_object($resul)){
                                                $caracval = new Caracval();
                                                $caracteristiquedesc->charger($row->caracteristique);
                                                $caracval->charger($produit->id, $row->caracteristique);

                                                $query2 = "select c.* from ".Caracdisp::TABLE." c left join $caracdispdesc->table cd on cd.caracdisp = c.id and cd.lang = $lang where c.caracteristique='$row->caracteristique' order by cd.classement";
                                                $resul2 = mysql_query($query2);
                                                $nbres = mysql_num_rows($resul2);
                                                if(! $nbres) { ?>
                                                <tr>
                                                    <td><?php echo $caracteristiquedesc->titre; ?></td>
                                                    <td>
                                                        <input type="text" class="span12" name="caract<?php echo($row->caracteristique); ?>" value="<?php echo(htmlspecialchars($caracval->valeur)); ?>" >
                                                    </td>
                                                </tr>
                                                <?php } else {?>

                                                <tr>
                                                    <td><?php echo $caracteristiquedesc->titre; ?></td>
                                                    <td>
                                                            <select name="caract<?php echo($row->caracteristique); ?>[]" multiple="multiple" class="span12">
                                                            <?php while($row2 = mysql_fetch_object($resul2)){
                                                                $caracdispdesc->charger_caracdisp($row2->id);
                                                                $caracval->charger_caracdisp($produit->id, $row2->caracteristique, $caracdispdesc->caracdisp);
                                                                        if( $caracdispdesc->caracdisp == $caracval->caracdisp) $selected="selected=\"selected\""; else $selected="";?>
                                                                <option value="<?php echo($caracdispdesc->caracdisp); ?>" <?php echo($selected); ?>><?php echo($caracdispdesc->titre); ?></option>
                                                            <?php } ?>
                                                            </select>
                                                    </td>
                                                </tr>
                                                <?php }  
                                            } ?>    
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="span6">
                                        <table class="table table-striped">
                                            <caption>
                                                <h3><?php echo trad('GESTION_DECLINAISONS', 'admin'); ?></h3>
                                            </caption>
                                        </table>
                                        <?php
                                        $declinaisondesc = new Declinaisondesc();
                                        $declidispdesc = new Declidispdesc();

                                        $query = "select rd.declinaison from " . Rubdeclinaison::TABLE . " rd left join " . Declinaison::TABLE . " d on rd.declinaison=d.id where rd.rubrique=" . $produit->rubrique . " order by d.classement";
                                        foreach ($produit->query_liste($query) as $row) {

                                            $declinaisondesc->charger($row->declinaison);
                                            $query2 = "select d.* from " . Declidisp::TABLE . " d left join ". Declidispdesc::TABLE . " dd on dd.declidisp=d.id where dd.lang=" . $lang . " and declinaison=" . $row->declinaison . " order by dd.classement";
                                            $resul2 = $produit->query($query2);
                                            ?>
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo($declinaisondesc->titre); ?></th>
                                                        <th><?php echo trad('Stock', 'admin'); ?></th>
                                                        <th><?php echo trad('Surplus', 'admin'); ?></th>
                                                        <th><?php echo trad('Active', 'admin'); ?></th>
                                                    </tr>
                                                </thead>                                    
                                                <tbody>
                                                    <?php
                                                    while ($row2 = mysql_fetch_object($resul2)) {
                                                        $declidispdesc->charger_declidisp($row2->id);

                                                        $stock = new Stock();
                                                        $stock->charger($row2->id, $produit->id);

                                                        $exdecprod = new Exdecprod();
                                                        $res = $exdecprod->charger($produit->id, $row2->id);
                                                        ?>
                                                        <tr>
                                                            <td><?php echo($declidispdesc->titre); ?></td>
                                                            <td><input class="input-small" type="text" name="stock<?php echo($row2->id); ?>" value="<?php echo($stock->valeur); ?>" ></td>
                                                            <td><input class="input-small" type="text" name="surplus<?php echo($row2->id); ?>" value="<?php echo($stock->surplus); ?>" ></td>
                                                            <td><input type="checkbox" <?php echo $res ? '' : 'checked="checked"' ?> name="exdecprod<?php echo($declidispdesc->declidisp); ?>" ></td>
                                                        </tr>
    <?php }
}
?>  
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                                <div class="row-fluid">
                                    <div class="span6">
                                        <table class="table table-striped">
                                            <caption>
                                                <h3><?php echo trad('GESTION_ACCESSOIRES', 'admin'); ?></h3>
                                            </caption>
                                            <tbody id="accessory-table">
                                                <tr>
                                                    <td>
                                                        <select id="accessoire_rubrique">
                                                            <option value="">&nbsp;</option>
                                                            <?php echo arbreOption(0, 1, 0, 0); ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="select_prodacc">
                                                            <option value="">&nbsp;</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <a href="#" id="add-accessory" class="btn btn-mini" title="<?php echo trad('AJOUTER', 'admin'); ?>"><i class="icon-plus-sign"></i></a>
                                                    </td>
                                                </tr>
                                                <?php lister_accessoires($produit->ref); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="span6">
                                        <table class="table table-striped">
                                            <caption>
                                                <h3><?php echo trad('GESTION_CONTENUS_ASSOCIES', 'admin'); ?></h3>
                                            </caption>
                                            <tbody id="content-table">
                                                <tr>
                                                    <td>
                                                        <select id="associatedContent_dossier">
                                                            <option value=""></option>
                                                            <?php echo arbreOption_dos(0, 1, 0, 0, 1); ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="select_prodcont"></select>
                                                    </td>
                                                    <td>
                                                        <a href="#" id="add-content" class="btn btn-mini" title="<?php echo trad('AJOUTER', 'admin'); ?>"><i class="icon-plus-sign"></i></a>
                                                    </td>
                                                </tr>
                                                <?php lister_contenuassoc(1, $produit->ref); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane <?php if ($tab == 'imageTab') echo "active"; ?>" id="imageTab">
                                <div class="row-fluid">
                                <div class="span6 offset4">
                                    <?php for($i = 1; $i <= ProductAdmin::getInstance()->getImageFile()->getNumberUpload(); $i++): ?>
                                        <input type="file" name="photo<?php echo $i; ?>" class="input-large">
                                        <br >
                                    <?php endfor; ?>
                                </div>
                                </div>
                                <?php foreach (ProductAdmin::getInstanceByRef($produit->ref)->getImageList($lang) as $image): ?>
                                <div class="row-fluid js-bloc-image" js-image-id="<?php echo $image['id'] ?>">
                                    <div class="span3" style="position: relative;">
                                        <img class="js-image" src="../fonctions/redimlive.php?nomorig=<?php echo
                                        $image["nomFichier"] ?>&type=produit&width=250&height=250&exact=1">
                                        <img style="display: none; position: absolute;" class="js-image-delation" src="gfx/interdit-150x150.png" />
                                        <input type="hidden" class="js-delete-input" name="image_to_delete_<?php echo $image['id'] ?>" value="0" />
                                        <input type="hidden" class="js-rank-input" name="rank_<?php echo $image['id'] ?>" value="<?php echo $image['classement'] ?>" />
                                        <a style="position:relative; margin-top:-45px; float:right" class="btn btn-large js-delete-picture" href="#"><i class="icon-trash"></i></a>
                                    </div>
                                    <br />
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
                                        <?php for($i=1; $i <= ProductAdmin::getInstance()->getDocumentFile()->getNumberUpload(); $i++): ?>
                                            <input type="file" name="document_<?php echo $i ?>" class="input-large">
                                            <br >
                                        <?php endfor;?>
                                    </div>
                                </div>
                                <?php foreach(ProductAdmin::getInstanceByRef($produit->ref)->getDocumentList($lang) as $document): ?>
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
                                        <?php ActionsAdminModules::instance()->inclure_module_admin("produitmodifier"); ?>
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
            <?php if($errorCode > 0): ?>
            <div class="modal hide fade" id="product_error">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-error">
                        <h4 class="alert-heading"><?php echo trad('product_error','admin') ?></h4>
                        <p><?php echo trad('product_'.$errorCode,'admin'); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
                        
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

            <!-- duplicate product -->
            <div class="modal hide fade" id="duplicateModal">
                <form method="POST" action="produit_modifier.php">
                    <input type="hidden" name="action" value="duplicateProduct" />
                    <input type="hidden" name="ref" value="<?php echo $produit->ref; ?>" />
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3><?php echo trad('Duplicate_intro', 'admin'); ?></h3>
                    </div>
                    <div class="modal-body">

                        <?php if($errorDuplicate){ ?>
                            <div class="alert alert-block alert-error fade in">
                                <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                                <p><?php echo trad('check_information', 'admin'); ?></p>
                                <?php if($errorDuplicate == TheliaAdminException::REF_ALREADY_EXISTS || $errorDuplicate == TheliaAdminException::REF_EMPTY) { ?>
                                    <p><?php echo trad('duplicate_error_ref', 'admin'); ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <h4><?php echo trad('duplicate_ask','admin') ?></h4>
                        <div>
                            <p><input type="checkbox" name="duplicate[description]" checked="checked"> <?php echo trad('duplicate_desciption_g', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[info]" checked="checked"> <?php echo trad('duplicate_info', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[features]"> <?php echo trad('duplicate_features', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[variants]"> <?php echo trad('duplicate_variants', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[accessories]"> <?php echo trad('duplicate_accessories', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[accessories_auto]"> <?php echo trad('duplicate_accessories_auto', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[associated_contents]"> <?php echo trad('duplicate_associated_contents', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[pictures]"> <?php echo trad('duplicate_pictures', 'admin'); ?></p>
                            <p><input type="checkbox" name="duplicate[documents]"> <?php echo trad('duplicate_documents', 'admin'); ?></p>
                        </div>
                        <h4><?php echo trad('duplicate_new_ref','admin') ?></h4>
                        <div class="<?php if($errorDuplicate && ($errorDuplicate == TheliaAdminException::REF_ALREADY_EXISTS || $errorDuplicate == TheliaAdminException::REF_EMPTY)){ ?>control-group error<?php } ?>">
                            <input type="text" name="duplicate[ref]" placeholder="<?php echo $produit->ref ?>" value="<?php echo $duplicate['ref'] ?>"> <i class=""></i>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo trad('Duplicate', 'admin'); ?></button>
                    </div>
                </form>
            </div>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("produit_modifier_bottom");
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
        
    $('#accessoire_rubrique').change(function(){
        $('#select_prodacc').load(
            'ajax/accessoire.php',
            'action=produit&ref=<?php echo $produit->ref; ?>&id_rubrique=' + $(this).val()
        );
    });
    
    $('#associatedContent_dossier').change(function(){
        $('#select_prodcont').load(
            'ajax/contenu_associe.php',
            'action=contenu_assoc&type=1&objet=<?php echo $produit->ref; ?>&id_dossier=' + $(this).val()
        );
    });
    
    $('#add-content').click(function(e){
       var id = $('#select_prodcont').val();
       if(id > 0)
       {
           $.ajax({
              url : 'ajax/contenu_associe.php',
              data : {
                  action : "ajouter",
                  type : 1,
                  objet : "<?php echo $produit->ref; ?>",
                  id : id
              },
              success : function(html){
                $('.content_liste').remove();
                $('#content-table').append(html);
                $('#associatedContent_dossier').trigger('change');
              }
           });
       }
       e.preventDefault();
    });
    
    $('#add-accessory').click(function(e){
        var id = $('#select_prodacc').val();
        if(id > 0)
        {
            $.ajax({
               url : 'ajax/accessoire.php',
               data : {
                   action : 'ajouter',
                   ref : '<?php echo $produit->ref; ?>',
                   id : $('#select_prodacc').val()
               },
               success : function(html){
                   $('.accessory_liste').remove();
                   $('#accessory-table').append(html);
                   $('#accessoire_rubrique').trigger('change');
               }
            });
        }
        e.preventDefault();
    });
    
    $(".content-delete").live("click",function(e){
        $.ajax({
            url : "ajax/contenu_associe.php",
            data : {
                action : "supprimer",
                type : 1,
                objet : "<?php echo $produit->ref; ?>",
                id : $(this).attr('data-content')
            },
            success : function(html){
                $('.content_liste').remove();
                $('#content-table').append(html);
                $('#associatedContent_dossier').trigger('change');
            }
        });        
        e.preventDefault();
    });
    
    $(".accessory-delete").live("click",function(e){
        $.ajax({
            url : "ajax/accessoire.php",
            data : {
                action : "supprimer",
                ref : "<?php echo $produit->ref; ?>",
                id : $(this).attr('data-accessory')
            },
            success : function(html){
                $('.accessory_liste').remove();
                $('#accessory-table').append(html);
                $('#accessoire_rubrique').trigger('change');
            }
        });        
        e.preventDefault();
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
    
    <?php if($errorCode > 0): ?>
        $('#product_error').modal('show');
    <?php endif; ?>

    <?php if($errorDuplicate > 0): ?>
        $('#duplicateModal').modal('show');
    <?php endif; ?>


});
</script>
</body>
</html>

