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
                    
                    <table class="table table-striped">
                        <caption>
                            <h4>
                                <?php echo trad('Client', 'admin'); ?>
                            </h4>
                        </caption>
                        <tbody>
                            
                            <tr>
                                <td><strong><?php echo trad('E-mail', 'admin'); ?></strong></td>
                                <td>
                                    <input class="span12" type="text" id="email" name="email" value="<?php echo $createError?$email:''; ?>">
                                </td>
                            </tr>
                            
                            <tr>
                                <td></td>
                                <td>
                                    <ul id="client_matched" style="display:none">
                                    </ul>
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
                
                <span class="12">
                
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
            
        </div>
        
    </div>
    
    <p>
        <button class="btn btn-large btn-block btn-primary" type="submit"><?php echo trad('VALIDER_LES_MODIFICATIONS', 'admin'); ?></button>
    </p>
    
    </form>
    
<?php require_once("pied.php");?>

    
<script type="text/javascript">

jQuery(function($)
{
    $('#copy_facturation_to_delivery').click(function(e)
    {
        e.preventDefault();
        
        console.log($('select[name="facturation_raison"]').val());
        
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
    
    var matching = false;
    $('#email').keyup(function($e)
    {
        
        if(matching)
            matching.abort();

        matching = $.post(
            'ajax/client.php',
            {
                action:         "match",
                email:          $(this).val(),
                nom:          $(this).val(),
                max_accepted:   10
            },
            function(retour)
            {
                if(retour != 'KO')
                {
                    if(retour.substr(0,8) == 'TOO_MUCH')
                    {   
                        $('#client_matched').empty();
                        $('#client_matched').show();
                        $('#client_matched').prepend(
                            $('<li />').html(
                                '<?php echo htmlentities(trad('too_much_email', 'admin'), ENT_QUOTES, 'UTF-8'); ?> : ' + retour.substr(9) + ' <?php echo htmlentities(trad('results', 'admin'), ENT_QUOTES, 'UTF-8'); ?>'
                            )
                        );
                    }
                    else
                    {
                        $('#client_matched').empty();
                        $('#client_matched').show();

                        var resultat = $.parseJSON(retour);

                        $(resultat).each(function(k, v)
                        {
                            $('#client_matched').prepend(
                                $('<li />').append(
                                    $('<span />').html(v.email + ' : '),
                                    $('<a />').attr('href', '#').html('utiliser ce client').click(function(e)
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
                                        
                                        $('#email').val(v.email);
                                        
                                        $('#client_matched').hide();
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

</script>
    
</body>
</html>
