<?php
require_once("auth.php");

if(! est_autorise("acces_commandes"))
    exit;

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$commande = new Commande();

$adrFacturation = new Venteadr();
$adrLivraison = new Venteadr();

$paysFacturation = new Paysdesc();
$paysLivraison = new Paysdesc();

$raisonFacturation = new Raisondesc();
$raisonLivraison = new Raisondesc();

try
{
    ActionsAdminOrder::getInstance()->action($request);
}
catch(TheliaAdminException $e)
{
    $errorCode = $e->getCode();    
    switch ($errorCode)
    {
        case TheliaAdminException::ORDER_ADD_ERROR:
            $createError = 1;
            break;
        case TheliaAdminException::EMAIL_FORMAT_ERROR:
            $createError = 1;
            $emailBadFormat = 1;
            break;
        case TheliaAdminException::EMAIL_ALREADY_EXISTS:
            $createError = 1;
            $emailAlreadyExists = 1;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>

<body>
<?php
$menu = "commande";
$breadcrumbs = Breadcrumb::getInstance()->getOrderList(trad('Creation_commande', 'admin'), "client.php");
require_once("entete.php");
?>
    <form method="POST" action="commande_creer.php">
        <input type="hidden" name="action" value="createOrder" />
        
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p>
        
    <div class="row-fluid">
        
        <div class="span6">
            
            <div class="row-fluid">
                
                <div class="span12">
                    
                    <ul id="mainTabs" class="nav nav-tabs">
                        <li class="<?php echo !$createError || $client_selected?'active':''; ?>">
                            <a href="#searchClientTab" data-toggle="tab"><?php echo trad('Search_client', 'admin'); ?></a>
                        </li>
                        <li class="<?php echo $createError && !$client_selected?'active':''; ?>">
                            <a href="#newClientTab" data-toggle="tab"><?php echo trad('creer_client', 'admin'); ?></a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        
                        <div class="tab-pane <?php echo !$createError || $client_selected?'active':''; ?>" id="searchClientTab">
                            <div class="row-fluid">
                                <div class="span12">
                    
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <td><strong><?php echo trad('Reference', 'admin'); ?></strong></td>
                                                <td>
                                                    <input class="span12 clientSearch" type="text" name="ref" id="ref" value="<?php echo $createError?$ref:''; ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong><?php echo trad('Prenom', 'admin'); ?></strong></td>
                                                <td>
                                                    <input class="span12 clientSearch" type="text" id="prenom" name="prenom_search" value="<?php echo $createError && $client_selected?$prenom_search:''; ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong><?php echo trad('Nom', 'admin'); ?></strong></td>
                                                <td>
                                                    <input class="span12 clientSearch" type="text" id="nom" name="nom_search" value="<?php echo $createError && $client_selected?$nom_search:''; ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong><?php echo trad('E-mail', 'admin'); ?></strong></td>
                                                <td>
                                                    <input class="span12 clientSearch" type="text" id="email" name="email_search" value="<?php echo $createError && $client_selected?$email_search:''; ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="span12">
                                                    <input type="hidden" name="client_selected" value="<?php echo $createError && $client_selected?1:0; ?>" />
                                                    <div id="client_matched" style="display:none;"></div>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane <?php echo $createError && !$client_selected?'active':''; ?>" id="newClientTab">
                            <div class="row-fluid">
                                <div class="span12">
                    
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr class="<?php if($createError && !$client_selected && (empty($email) || $emailAlreadyExists || $emailBadFormat) ){ ?>error<?php } ?>">
                                                <td>
                                                    <strong><?php echo trad('E-mail', 'admin'); ?></strong>
<?php if($createError && $emailBadFormat){ ?>
                                                    <br /><?php echo trad('email_bad_format', 'admin'); ?><?php }
elseif($createError && $emailAlreadyExists){ ?>
                                                    <br /><?php echo trad('email_already_exists', 'admin'); ?>
<?php } ?>
                                                </td>
                                                <td>
                                                    <input class="span12" type="text" name="email" value="<?php echo $createError?$email:''; ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <?php echo trad('beg_for_facturation', 'admin'); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                
            </div>
            
            <div class="row-fluid">
                
                <div class="span12">
                    
                    <table class="table table-striped">
                        <caption>
                            <h4>
                                <?php echo trad('ADRESSE_FACTURATION', 'admin'); ?>
                            </h4>
                        </caption>
                        <tbody>
                            <tr class="<?php if($createError && empty($facturation_raison)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Civilite', 'admin'); ?>*</strong></td>
                                <td>
                                    <select class="span12" name="facturation_raison">
<?php

$qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListTitles = $raisonFacturation->query($qListTitles);
while($rListTitles && $theTitle = $raisonFacturation->fetch_object($rListTitles, 'Raisondesc'))
{
?>
                                        <option value="<?php echo $theTitle->raison; ?>" <?php if(($createError?$facturation_raison:'')==$theTitle->raison){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theTitle->long; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Societe', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_entreprise" value="<?php echo $createError?$facturation_entreprise:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($facturation_prenom)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Prenom', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_prenom" value="<?php echo $createError?$facturation_prenom:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($facturation_nom)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Nom', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_nom" value="<?php echo $createError?$facturation_nom:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($facturation_adresse1)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Adresse', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_adresse1" value="<?php echo $createError?$facturation_adresse1:''; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Adressesuite', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_adresse2" value="<?php echo $createError?$facturation_adresse2:''; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Complement_adresse', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_adresse3" value="<?php echo $createError?$facturation_adresse3:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($facturation_cpostal)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('CP', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_cpostal" value="<?php echo $createError?$facturation_cpostal:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($facturation_ville)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Ville', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_ville" value="<?php echo $createError?$facturation_ville:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($facturation_pays)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Pays', 'admin'); ?>*</strong></td>
                                <td>
                                    <select class="span12" name="facturation_pays">
<?php

$qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListCountries = $paysFacturation->query($qListCountries);
while($rListCountries && $theCountry = $paysFacturation->fetch_object($rListCountries, 'Paysdesc'))
{
?>
                                        <option value="<?php echo $theCountry->pays; ?>" <?php if(($createError?$facturation_pays:'')==$theCountry->pays){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theCountry->titre; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Telephone', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="facturation_tel" value="<?php echo $createError?$facturation_tel:''; ?>">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
                
            </div>
            
            <div class="row-fluid">
                
                <div class="span12">
                    
                    <table class="table table-striped">
                        <caption>
                            <h4>
                                <?php echo trad('ADRESSE_LIVRAISON', 'admin'); ?> 
                            </h4>
                            <a href="#" id="copy_facturation_to_delivery">
                                <?php echo trad('Copy_facturation_to_delivery', 'admin'); ?>
                            </a>
                        </caption>
                        <tbody>
                            <tr class="<?php if($createError && empty($livraison_raison)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Civilite', 'admin'); ?>*</strong></td>
                                <td>
                                    <select class="span12" name="livraison_raison">
<?php

$qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListTitles = $raisonLivraison->query($qListTitles);
while($rListTitles && $theTitle = $raisonLivraison->fetch_object($rListTitles, 'Raisondesc'))
{
?>
                                        <option value="<?php echo $theTitle->raison; ?>" <?php if(($createError?$livraison_raison:'')==$theTitle->raison){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theTitle->long; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Societe', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_entreprise" value="<?php echo $createError?$livraison_entreprise:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($livraison_prenom)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Prenom', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_prenom" value="<?php echo $createError?$livraison_prenom:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($livraison_nom)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Nom', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_nom" value="<?php echo $createError?$livraison_nom:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($livraison_adresse1)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Adresse', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_adresse1" value="<?php echo $createError?$livraison_adresse1:''; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Adressesuite', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_adresse2" value="<?php echo $createError?$livraison_adresse2:''; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Complement_adresse', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_adresse3" value="<?php echo $createError?$livraison_adresse3:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($livraison_cpostal)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('CP', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_cpostal" value="<?php echo $createError?$livraison_cpostal:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($livraison_ville)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Ville', 'admin'); ?>*</strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_ville" value="<?php echo $createError?$livraison_ville:''; ?>">
                                </td>
                            </tr>
                            <tr class="<?php if($createError && empty($livraison_pays)){ ?>error<?php } ?>">
                                <td><strong><?php echo trad('Pays', 'admin'); ?>*</strong></td>
                                <td>
                                    <select class="span12" name="livraison_pays">
<?php

$qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListCountries = $paysLivraison->query($qListCountries);
while($rListCountries && $theCountry = $paysLivraison->fetch_object($rListCountries, 'Paysdesc'))
{
?>
                                        <option value="<?php echo $theCountry->pays; ?>" <?php if(($createError?$livraison_pays:'')==$theCountry->pays){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theCountry->titre; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Telephone', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" name="livraison_tel" value="<?php echo $createError?$livraison_tel:''; ?>">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
                
            </div>
            
        </div>
        
        <div class="span6">
            
            <div class="row-fluid">
                
                <span class="span12">
                
                    <table class="table table-striped">
                        <caption>
                            <h4><?php echo trad('INFO_COMPLEMENTAIRE', 'admin'); ?></h4>
                        </caption>
                        <tbody>
                            <tr>
                                <td><strong><?php echo trad('Type_paiement', 'admin'); ?>*</strong></td>
                                <td>
                                    <select class="span12" name="type_paiement" class="<?php if($createError && empty($type_paiement)){ ?>error<?php } ?>">
                                    
<?php 
foreach(OrderAdmin::getInstance()->getPaymentTypesList() as $paymentType)
{
?>
                                        <option value="<?php echo $paymentType->id; ?>">
                                            <?php echo $paymentType->nom; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Type_transport', 'admin'); ?>*</strong></td>
                                <td>
                                    
                                    <select class="span12" name="type_transport" class="<?php if($createError && empty($type_transport)){ ?>error<?php } ?>">
                                    
<?php 
foreach(OrderAdmin::getInstance()->getDeliveryTypesList() as $deliveryType)
{
?>
                                        <option value="<?php echo $deliveryType->id; ?>">
                                            <?php echo $deliveryType->nom; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Montant_frais_port', 'admin'); ?></strong></td>
                                <td>
                                    <div class="input-append">
                                        <input type="text" name="fraisport" value="<?php echo $commande->port; ?>">
                                        <span class="add-on">€ TTC</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo trad('Remise', 'admin'); ?></strong></td>
                                <td>
                                    <div class="input-append">
                                        <input type="text" name="remise" value="<?php echo $commande->remise; ?>">
                                        <span class="add-on">€ TTC</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </span>
                
            </div>
            
            <div class="row-fluid">
                
                <span class="span12">
                
                    <table class="table table-striped">
                        <caption>
                            <h4>
                                <?php echo trad('CART', 'admin'); ?>
                                <div class="btn-group">
                                    <a href="#addProductModal" class="btn btn-large" title="<?php echo trad('add_product', 'admin'); ?>" data-toggle="modal">
                                        <i class="icon-plus-sign icon-white"></i>
                                    </a>
                                </div>
                            </h4>
                        </caption>
                        <tbody>
                            <tr>
                                <td><?php echo trad('cart_is_empty', 'admin'); ?></td>
                            </tr>
                        </tbody>
                    </table>

                </span>
                
            </div>
            
        </div>
        
    </div>
    
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p>
    
    </form>
    
    <div class="modal hide" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <div>
                <h3>
                    <?php echo trad('ADD_PRODUCT', 'admin'); ?>
                </h3>
            </div>
        </div>
        <div class="modal-body">

            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td class="span3"><?php echo trad('search_reference', 'admin'); ?></td>
                        <td class="span9">
                            <input type="text" id="searchProductRef">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="products_matched" style="display:none;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo trad('or_browse', 'admin'); ?>
                        </td>
                        <td>
                            <table class="table-bordered"">
                                <tr>
                                    <td colspan="2" id="fastBrowser_breadcrumb"></td>
                                </tr>
                                <tr>
                                    <th class="span6">
                                        <?php echo trad('categories_list', 'admin'); ?>
                                    </th>
                                    <th class="span6">
                                        <?php echo trad('products_list', 'admin'); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <td id="fastBrowser_categories"></td>
                                    <td id="fastBrowser_products"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="productToAddInformation" style="display: none;">
                        <td><?php echo trad('Reference', 'admin'); ?></td>
                        <td>
                            <input type="text" id="productToAdd_ref" class="input-xlarge" readonly>
                        </td>
                    </tr>
                    <tr class="productToAddInformation" style="display: none;">
                        <td><?php echo trad('titre', 'admin'); ?></td>
                        <td>
                            <input type="text" id="productToAdd_titre" class="input-xlarge" readonly>
                        </td>
                    </tr>
                    <tr class="productToAddInformation" style="display: none;">
                        <td><?php echo trad('variants', 'admin'); ?></td>
                        <td id="productToAdd_variant"></td>
                    </tr>
                    <tr class="productToAddInformation" style="display: none;">
                        <td><?php echo trad('Quantite', 'admin'); ?></td>
                        <td>
                            <div class="input-append">
                                <input type="text" id="productToAdd_quantite" class="input-xlarge">
                                <span class="add-on"><span id="productToAdd_stock"></span> <?php echo trad('in_stock', 'admin'); ?></span>
                            </div>
                        </td>
                    </tr>
                    <tr class="productToAddInformation" style="display: none;">
                        <td><?php echo trad('Prix', 'admin'); ?></td>
                        <td>
                            <div class="input-append">
                                <input type="text" id="productToAdd_prix" class="input-xlarge">
                                <span class="add-on">€ TTC</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="productToAddInformation" style="display: none;">
                        <td><?php echo trad('TVA', 'admin'); ?></td>
                        <td>
                            <div class="input-append">
                                <input type="text" id="productToAdd_tva" class="input-xlarge">
                                <span class="add-on">%</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
        <div class="modal-footer">
            <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
            <button class="btn btn-primary" id="btn_ajout_produit" style="display: none;"><?php echo trad('Ajouter', 'admin'); ?></button>
        </div>
    </div>
    
<?php require_once("pied.php");?>

    
<script type="text/javascript">

jQuery(function($)
{
    loadFastBrowser(0);
    
    $('#copy_facturation_to_delivery').click(function(e)
    {
        e.preventDefault();
        
        $('select[name="livraison_raison"]').val( $('select[name="facturation_raison"]').val() );
        $('input[name="livraison_entreprise"]').val( $('input[name="facturation_entreprise"]').val() );
        $('input[name="livraison_nom"]').val( $('input[name="facturation_nom"]').val() );
        $('input[name="livraison_prenom"]').val( $('input[name="facturation_prenom"]').val() );
        $('input[name="livraison_adresse1"]').val( $('input[name="facturation_adresse1"]').val() );
        $('input[name="livraison_adresse2"]').val( $('input[name="facturation_adresse2"]').val() );
        $('input[name="livraison_adresse3"]').val( $('input[name="facturation_adresse3"]').val() );
        $('input[name="livraison_cpostal"]').val( $('input[name="facturation_cpostal"]').val() );
        $('input[name="livraison_ville"]').val( $('input[name="facturation_ville"]').val() );
        $('select[name="livraison_pays"]').val( $('select[name="facturation_pays"]').val() );
        $('input[name="livraison_tel"]').val( $('input[name="facturation_tel"]').val() );
    });
    
    $('a[data-toggle="tab"]').on('shown', function (e) {
        
        if($(e.target).attr('href') != '#searchClientTab')
        {
            changeClient(null);
        }
    });
    
<?php
if($createError && $client_selected) {
?>
    $('.clientSearch').attr('readonly', true);
    
    displayChangeClientMessage();
    $('#client_matched').show();
<?php
}
?>
    var matching = false; //one ajax call at a time
    $('#searchProductRef').keyup(function($e)
    {
        if(matching)
            matching.abort();

        matching = $.post(
            'ajax/produit.php',
            {
                action:         "match",
                ref:            $(this).val(),
                max_accepted:   10
            },
            function(retour)
            {
                if(retour != 'KO')
                {
                    if(retour.substr(0,8) == 'TOO_MUCH')
                    {   
                        $('#products_matched').unbind().empty().show().prepend(
                            $('<span />').html(
                                '<?php echo htmlentities(trad('too_much_products', 'admin'), ENT_QUOTES, 'UTF-8'); ?> : ' + retour.substr(9) + ' <?php echo htmlentities(trad('results', 'admin'), ENT_QUOTES, 'UTF-8'); ?>'
                            )
                        );
                    }
                    else
                    {
                        $('#products_matched').unbind().empty().show();
                        
                        var resultat = $.parseJSON(retour);

                        $(resultat).each(function(k, v)
                        {
                            $('#products_matched').prepend(
                                $('<li />').append(
                                    $('<span />').html(v.ref + ' - ' + v.titre + ' : '),
                                    $('<a />').attr('href', '#').html('<?php echo htmlentities(trad('use_this_product', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').click(function(e)
                                    {
                                        e.preventDefault();
                                        
                                        $('#productToAdd_ref').val(v.ref);
                                        $('#productToAdd_titre').val(v.titre);
                                        $('#productToAdd_quantite').val(1);
                                        
                                        manageStock(v.stock, v.promo?v.prix2:v.prix);
                                        
                                        $('#productToAdd_tva').val(v.tva);

                                        $('.productToAddInformation').show();
                                        $('#btn_ajout_produit').show();
                                        
                                        loadFastBrowser(v.rubrique);
                                    })
                                )
                            );
                        });
                    }
                }
                else
                {
                    $('#products_matched').hide();
                }
            }
        );
    });
    
    $('.clientSearch').keyup(function(e)
    {
        if($(this).attr('readonly') == 'readonly')
            return;
        
        if(matching)
            matching.abort();

        matching = $.post(
            'ajax/client.php',
            {
                action:         "match",
                email:          $('#email').val(),
                prenom:         $('#prenom').val(),
                nom:            $('#nom').val(),
                ref:            $('#ref').val(),
                max_accepted:   10
            },
            function(retour)
            {
                if(retour != 'KO')
                {
                    if(retour.substr(0,8) == 'TOO_MUCH')
                    {   
                        $('#client_matched').unbind().empty().show().prepend(
                            $('<span />').html(
                                '<?php echo htmlentities(trad('too_much_email', 'admin'), ENT_QUOTES, 'UTF-8'); ?> : ' + retour.substr(9) + ' <?php echo htmlentities(trad('results', 'admin'), ENT_QUOTES, 'UTF-8'); ?>'
                            )
                        );
                    }
                    else
                    {
                        $('#client_matched').unbind().empty().show();

                        var resultat = $.parseJSON(retour);

                        $(resultat).each(function(k, v)
                        {
                            $('#client_matched').prepend(
                                $('<li />').append(
                                    $('<span />').html(v.prenom + ' ' + v.nom + ' - ' + v.email + ' : '),
                                    $('<a />').attr('href', '#').html('<?php echo htmlentities(trad('use_this_client', 'admin'), ENT_QUOTES, 'UTF-8'); ?>').click(function(e)
                                    {
                                        e.preventDefault();
                                        
                                        $('select[name="facturation_raison"]').val(v.raison);
                                        $('input[name="facturation_entreprise"]').val(v.entreprise);
                                        $('input[name="facturation_nom"]').val(v.nom);
                                        $('input[name="facturation_prenom"]').val(v.prenom);
                                        $('input[name="facturation_adresse1"]').val(v.adresse1);
                                        $('input[name="facturation_adresse2"]').val(v.adresse2);
                                        $('input[name="facturation_adresse3"]').val(v.adresse3);
                                        $('input[name="facturation_cpostal"]').val(v.cpostal);
                                        $('input[name="facturation_ville"]').val(v.ville);
                                        $('select[name="facturation_pays"]').val(v.pays);
                                        $('input[name="facturation_tel"]').val(v.tel);
                                        
                                        $('select[name="livraison_raison"]').val(v.raison);
                                        $('input[name="livraison_entreprise"]').val(v.entreprise);
                                        $('input[name="livraison_nom"]').val(v.nom);
                                        $('input[name="livraison_prenom"]').val(v.prenom);
                                        $('input[name="livraison_adresse1"]').val(v.adresse1);
                                        $('input[name="livraison_adresse2"]').val(v.adresse2);
                                        $('input[name="livraison_adresse3"]').val(v.adresse3);
                                        $('input[name="livraison_cpostal"]').val(v.cpostal);
                                        $('input[name="livraison_ville"]').val(v.ville);
                                        $('select[name="livraison_pays"]').val(v.pays);
                                        $('input[name="livraison_tel"]').val(v.tel);
                                        
                                        $('input[name="email"]').val(v.email);
                                        
                                        $('#email').val(v.email);
                                        $('#nom').val(v.nom);
                                        $('#prenom').val(v.prenom);
                                        $('#ref').val(v.ref);
                                        
                                        $('.clientSearch').attr('readonly', true);
                                        
                                        displayChangeClientMessage();
                                    })
                                )
                            );
                        });
                    }
                }
                else
                {
                    $('#client_matched').hide();
                }
            }
        );
    });
});

function displayChangeClientMessage()
{
    $('input[name="client_selected"]').val(1);
    $('#client_matched').unbind().empty().append(
        $('<a />').attr('href', '#').html(
            '<?php echo htmlentities(trad('cancel_choice', 'admin'), ENT_QUOTES, 'UTF-8'); ?>'
        ).click(function(e)
        {
            changeClient(e)
        })
    );
}

function changeClient(e)
{
    if(e !== null)
        e.preventDefault();

    $('.clientSearch').attr('readonly', false);
    $('.clientSearch').val('');

    $('#client_matched').hide();

    $('input[name="client_selected"]').val(0);
    
    emptyAddressesVal();
}

function emptyAddressesVal()
{
    $('select[name="facturation_raison"]').val('');
    $('input[name="facturation_entreprise"]').val('');
    $('input[name="facturation_nom"]').val('');
    $('input[name="facturation_prenom"]').val('');
    $('input[name="facturation_adresse1"]').val('');
    $('input[name="facturation_adresse2"]').val('');
    $('input[name="facturation_adresse3"]').val('');
    $('input[name="facturation_cpostal"]').val('');
    $('input[name="facturation_ville"]').val('');
    $('select[name="facturation_pays"]').val('');
    $('input[name="facturation_tel"]').val('');

    $('select[name="livraison_raison"]').val('');
    $('input[name="livraison_entreprise"]').val('');
    $('input[name="livraison_nom"]').val('');
    $('input[name="livraison_prenom"]').val('');
    $('input[name="livraison_adresse1"]').val('');
    $('input[name="livraison_adresse2"]').val('');
    $('input[name="livraison_adresse3"]').val('');
    $('input[name="livraison_cpostal"]').val('');
    $('input[name="livraison_ville"]').val('');
    $('select[name="livraison_pays"]').val('');
    $('input[name="livraison_tel"]').val('');
}

function loadFastBrowser(root)
{
    $.post(
        'ajax/catalogFastBrowser.php',
        {
            root:           root
        },
        function(retour)
        {   
            var resultat = $.parseJSON(retour);
            
            var breadcrumb = $('<div />');
            $(resultat.breadcrumb).each(function(k, v)
            {
                breadcrumb.append(
                    $('<span />').html(' > '),
                    $('<a />').attr('href', '#').html(v.display).click(function(e)
                    {
                        e.preventDefault();
                        
                        loadFastBrowser(v.url)
                    })
                );
            });
            
            var categories = $('<div />');
            $(resultat.categories).each(function(k, v)
            {
                categories.append(
                    $('<p />').append(
                        $('<a />').attr('href', '#').html(v.titre).click(function(e)
                        {
                            e.preventDefault();

                            loadFastBrowser(v.id)
                        })
                    )
                );
            });
            
            var products = $('<div />');
            $(resultat.products).each(function(k, v)
            {
                products.append(
                    $('<p />').append(
                        $('<a />').attr('href', '#').html(v.titre).click(function(e)
                        {
                            e.preventDefault();

                            $('#productToAdd_ref').val(v.ref);
                            $('#productToAdd_titre').val(v.titre);
                            $('#productToAdd_quantite').val(1);
                            
                            manageStock(v.variants, v.promo?v.prix2:v.prix);
                            
                            $('#productToAdd_tva').val(v.tva);
                            
                            $('.productToAddInformation').show();
                            $('#btn_ajout_produit').show();
                        })
                    )
                );
            });
            
            $('#fastBrowser_breadcrumb').unbind().empty().append(breadcrumb);
            $('#fastBrowser_categories').unbind().empty().append(categories);
            $('#fastBrowser_products').unbind().empty().append(products);
        }
    );
}

function manageStock(stock, basePrice)
{
    if($.isArray(stock) || $.isPlainObject(stock))
    {
        //variants
        $('#productToAdd_variant').unbind().empty();
        $.each(stock, function(k2, v2)
        {
            var variantsList = $('<select />').change(function(e)
            {
                $('#productToAdd_stock').html($(this).children('option:selected').attr('js-stock'));
                $('#productToAdd_prix').val(
                    parseFloat($('#productToAdd_prix').attr('js-prix-orig')) + parseFloat($(this).children('option:selected').attr('js-surplus'))
                );
            });
            $.each(v2.declinaisons, function(k3, v3)
            {
                variantsList.append(
                    $('<option />').val(v3.declidisp_id).html(v3.declidisp_titre).attr('js-stock', v3.declidisp_stock).attr('js-surplus', v3.declidisp_surplus)
                );
            });

            $('#productToAdd_variant').append(
                $('<div />').append(
                    $('<label />').html(v2.titre),
                    variantsList
                )
            );

            $('#productToAdd_prix').attr('js-prix-orig', basePrice);

            variantsList.trigger('change');
        });

        $('#productToAdd_variant').attr('disabled', false);
    }
    else
    {
        //no variants
        $('#productToAdd_stock').html(stock);
        $('#productToAdd_variant').unbind().empty().append(
            $('<span />').attr('selected', true).html('Ø')
        );
        $('#productToAdd_variant').attr('disabled', true);
        $('#productToAdd_prix').val(basePrice).attr('js-prix-orig', basePrice);
    }
}

</script>
    
</body>
</html>
