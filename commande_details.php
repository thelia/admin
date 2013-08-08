<?php
require_once("auth.php");
        
if(! est_autorise("acces_commandes")) exit; 

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

require_once("../fonctions/divers.php");
require __DIR__ . '/liste/commande_details.php';

if(!isset($action)) $action="";
if(!isset($statutch)) $statutch="";

$commande = new Commande();
$commande->charger_ref($ref);

$modules = new Modules();
$modules->charger_id($commande->paiement);

$devise = new Devise();
$devise->charger($commande->devise);

if($statutch)
{
        $commande->setStatutAndSave($statutch);
}

if (isset($colis) && $colis != "") {
        $commande->colis = $colis;
        $commande->maj();
        ActionsModules::instance()->appel_module("statut", $commande, $commande->statut);
}

$client = new Client();
$client->charger_id($commande->client);

$statutdesc = new Statutdesc();
$statutdesc->charger($commande->statut);

$date = new DateTime($commande->date);

$moduletransport = new Modules();
$moduletransport->charger_id($commande->transport);

$moduletransportdesc = new Modulesdesc();
$moduletransportdesc->charger($moduletransport->nom);

$promoutil = new Promoutil();
$promoutil->charger_commande($commande->id);


$adrFacturation = new Venteadr($commande->adrfact);
$adrLivraison = new Venteadr($commande->adrlivr);

$paysFacturation = new Paysdesc($adrFacturation->pays);
$paysLivraison = new Paysdesc($adrLivraison->pays);

$raisonFacturation = new Raisondesc($adrFacturation->raison);
$raisonLivraison = new Raisondesc($adrLivraison->raison);

$statusArray = $commande->query_liste('SELECT * FROM '.Statutdesc::TABLE.' WHERE lang='.ActionsLang::instance()->get_id_langue_courante());

try
{
    ActionsAdminOrder::getInstance()->action($request);
}
catch(TheliaAdminException $e)
{
    $errorCode = $e->getCode();    
    switch ($errorCode)
    {
        case TheliaAdminException::ORDER_VENTEADR_EDIT_ERROR:
            if( $id == $adrFacturation->id )
                $facturationError = 1;
            elseif( $id == $adrLivraison->id )
                $deliveryError = 1;
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
	ActionsAdminModules::instance()->inclure_module_admin("commande_details_top");
        $menu = "commande";
        $breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_commandes', 'admin'), "commande.php");
        require_once("entete.php");
    ?>
        <div class="row-fluid">
        <?php
                ActionsAdminModules::instance()->inclure_module_admin("commande_details");
        ?>
            <div class="span8">
                <table class="table table-striped">
                    <caption>
                       <h4><?php echo trad('INFO_COMMANDE', 'admin'); ?> <?php echo $commande->ref ?></h4>
                    </caption>
                    <thead>
                        <tr>
                            <th><?php echo trad('Designation', 'admin'); ?></th>
                            <th><?php echo trad('Prix_unitaire', 'admin'); ?></th>
                            <th><?php echo trad('Qte', 'admin'); ?></th>
                            <th><?php echo trad('Total', 'admin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php 
                            $total = 0;
                            foreach(liste_venteprod($commande) as $venteprods){
                                foreach ($venteprods as $index => $venteprod){
                                    if($index > 0){
                                        $venteprod["title"] .= "↳";
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $venteprod["ref"]." // ".$venteprod["title"]; ?></td>
                                    <td><?php echo formatter_somme($venteprod["price"])." ".$devise->symbole; ?></td>
                                    <td><?php echo $venteprod["qtity"]; ?></td>
                                    <td><?php echo formatter_somme($venteprod["total"])." ".$devise->symbole; ?></td>
                                </tr>
                                <?php
                                }
                            } ?>
                            <tr class="info">
                                <td colspan="3"><b><?php echo trad('Total', 'admin'); ?></b></td>
                                <td><?php echo formatter_somme($commande->total())." ".$devise->symbole; ?></td>
                            </tr>
                    </tbody>
                </table>
                
                <table class="table table-striped">
                    <caption>
                        <h4><?php echo trad('INFO_FACTURE', 'admin'); ?></h4>
                    </caption>
                    <thead>
                        <tr>
                            <th><?php echo trad('Num_Fact', 'admin'); ?></th>
                            <th><?php echo trad('Societe', 'admin'); ?></th>
                            <th><?php echo trad('Nom', 'admin'); ?> &amp; <?php echo trad('Prenom', 'admin'); ?></th>
                            <th><?php echo trad('Date_Heure', 'admin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo($commande->facture); ?></td>
                            <td><?php echo($client->entreprise); ?></td>
                            <td>
                                <a href="client_visualiser.php?ref=<?php echo $client->ref; ?>">
                                    <?php echo $client->nom." ".$client->prenom; ?>
                                </a>
                            </td>
                            <td><?php echo $date->format('d/m/Y H:i:s'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <table class="table table-striped">
                    <caption>
                        <h4><?php echo trad('INFO_TRANSPORT', 'admin'); ?></h4>
                    </caption>
                    <tbody>
                        <tr>
                            <td><strong><?php echo trad('Mode_transport', 'admin'); ?></strong></td>
                            <td><?php echo $moduletransportdesc->titre; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Description', 'admin'); ?></strong></td>
                            <td><?php echo $moduletransportdesc->description; ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <table class="table table-striped">
                    <caption>
                        <h4><?php echo trad('INFO_REGLEMENT', 'admin'); ?></h4>
                    </caption>
                    <tbody>
                        <tr>
                            <td><strong><?php echo trad('Type_paiement', 'admin'); ?></strong></td>
                            <td>
                            <?php
                                try {
                                        $tmpobj = ActionsAdminModules::instance()->instancier($modules->nom);
                                        echo $tmpobj->getTitre();
                                } catch (Exception $ex) {
                                        echo trad('Inconnu', 'admin');
                                }
                            ?>    
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Ref_transaction', 'admin'); ?></strong></td>
                            <td><?php echo $commande->transaction; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Total_commande_avant_remise', 'admin'); ?></strong></td>
                            <td><?php echo $commande->total()." ".$devise->symbole; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Remise', 'admin'); ?></strong></td>
                            <td><?php echo round($commande->remise, 2)." ".$devise->symbole; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Code_promo', 'admin'); ?></strong></td>
                            <td><?php if($promoutil->id){ echo $promoutil->code; ?> (<?php echo $promoutil->valeur; echo($promoutil->type==Promo::TYPE_SOMME)?'€':'%'; ?>) <?php } ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Total_avec_remise', 'admin'); ?></strong></td>
                            <td><?php echo $commande->total(false, true)." ".$devise->symbole; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Frais_transport', 'admin'); ?></strong></td>
                            <td><?php echo ($commande->port < 0) ? 0 : $commande->port; ?> <?php echo $devise->symbole; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Total', 'admin'); ?></strong></td>
                            <td><?php echo $commande->total(true, true)." ".$devise->symbole; ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <table class="table table-striped">
                    <caption>
                        <h4>
                            <?php echo trad('ADRESSE_FACTURATION', 'admin'); ?>
                            <div class="btn-group">
                                <a class="btn btn-large" title="<?php echo trad('editer', 'admin'); ?>" href="#facturationModal" data-toggle="modal">
                                    <i class="icon-edit icon-white"></i>
                                </a>
                            </div>
                            <div style="clear: both;"></div>
                        </h4>
                        
                            
                        
                    </caption>
                    <tbody>
                        <tr>
                            <td><strong><?php echo trad('Civilite', 'admin'); ?></strong></td>
                            <td><?php echo $raisonFacturation->long; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Societe', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->entreprise; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Prenom', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->prenom; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Nom', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->nom; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Adresse', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->adresse1; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Adressesuite', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->adresse2; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Complement_adresse', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->adresse3; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('CP', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->cpostal; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Ville', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->ville; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Pays', 'admin'); ?></strong></td>
                            <td><?php echo $paysFacturation->titre; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Telephone', 'admin'); ?></strong></td>
                            <td><?php echo $adrFacturation->tel; ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <table class="table table-striped">
                    <caption>
                        <h4>
                            <?php echo trad('ADRESSE_LIVRAISON', 'admin'); ?>
                            <div class="btn-group">
                                <a class="btn btn-large" title="<?php echo trad('editer', 'admin'); ?>" href="#deliveryModal" data-toggle="modal">
                                    <i class="icon-edit icon-white"></i>
                                </a>
                            </div>
                            <div style="clear: both;"></div>
                        </h4>
                    </caption>
                    <tbody>
                        <tr>
                            <td><strong><?php echo trad('Civilite', 'admin'); ?></strong></td>
                            <td><?php echo $raisonLivraison->long; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Societe', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->entreprise; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Prenom', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->prenom; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Nom', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->nom; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Adresse', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->adresse1; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Adressesuite', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->adresse2; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Complement_adresse', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->adresse3; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('CP', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->cpostal; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Ville', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->ville; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Pays', 'admin'); ?></strong></td>
                            <td><?php echo $paysLivraison->titre; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo trad('Telephone', 'admin'); ?></strong></td>
                            <td><?php echo $adrLivraison->tel; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="span4">
                
<?php
$previous = ToolBoxAdmin::getPrevious($commande, false, 'id');
$next = ToolBoxAdmin::getNext($commande, false, 'id');
if($next!==false || $previous!==false)
{
?>
                <div class="row-fluid">
                    <div class="span12">
                        <div class="littletable">
                            <ul class="nav nav-pills">
                                <li class="">
                                    <?php
                                    if($previous !== false)
                                    {
                                    ?>
                                    <a href="commande_details.php?ref=<?php echo $previous->ref; ?>" title="<?php echo trad('previous', 'admin'); ?>" class="change-page">
                                        <i class="icon-backward"></i>
                                    </a>
                                    <?php
                                    }
                                    ?>
                                </li>
                                <li class="">
                                    <?php
                                    if($next !== false)
                                    {
                                    ?>
                                    <a href="commande_details.php?ref=<?php echo $next->ref; ?>" title="<?php echo trad('next', 'admin'); ?>" class="change-page">
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
<?php
}
?>
                
                <div class="row-fluid">
                    <div class="span12">
                        <div class="littletable">
                        <table class="table table-striped">
                            <caption>
                                <h4><?php echo trad('INFO_COMPLEMENTAIRE', 'admin'); ?></h4>
                            </caption>
                            <tbody>
                                <tr>
                                    <td><strong><?php echo trad('STATUT_REGLEMENT', 'admin'); ?></strong></td>
                                    <td>
                                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formStatus" method="post" class="form-inline">
                                            <input type="hidden" name="ref" value="<?php echo $commande->ref; ?>">
                                            <select name="statutch" id="statutch" class="input-medium">
                                            <?php foreach($statusArray as $statusDesc): ?>
                                                <option value="<?php echo $statusDesc->statut; ?>" <?php if($statusDesc->statut == $commande->statut) echo 'selected="selected"'; ?>><?php echo $statusDesc->titre; ?></option>
                                            <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo trad('SUIVI_COLIS', 'admin'); ?></strong></td>
                                    <td>
                                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                            <input type="hidden" name="ref" value="<?php echo $commande->ref; ?>">
                                            <div class="input-append">
                                            <input type="text" class="input-small" name="colis" value="<?php echo htmlspecialchars($commande->colis); ?>">
                                            <button class="btn" type="submit"><?php echo trad('Valider','admin'); ?></button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo trad('Facture', 'admin'); ?></strong></td>
                                    <td><a href="../client/pdf/facture.php?ref=<?php echo($commande->ref); ?>" target="_blank"><?php echo trad('Visualiser_format_PDF', 'admin'); ?></a></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo trad('Bon_livraison', 'admin'); ?></strong></td>
                                    <td><a href="livraison.php?ref=<?php echo($commande->ref); ?>" target="_blank"><?php echo trad('Visualiser_format_PDF', 'admin'); ?></a></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <?php
                        ActionsAdminModules::instance()->inclure_module_admin("commandedetails_aside");
                    ?>
                </div>
            </div>
        </div>
        
        <div class="row-fluid">
            <div class="span12">
                <?php
                        ActionsAdminModules::instance()->inclure_module_admin("commandedetails");
                ?>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <!-- facturation modal -->
                <div class="modal hide" id="facturationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <form method="POST" action="commande_details.php">
                    <input type="hidden" name="action" value="editVenteadr" />
                    <input type="hidden" name="ref" value="<?php echo $commande->ref; ?>" />
                    <input type="hidden" name="id" value="<?php echo $adrFacturation->id; ?>" />
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <div>
                            <h3>
                                <?php echo trad('ADRESSE_FACTURATION', 'admin'); ?>
                            </h3>
                        </div>
                    </div>
                    <div class="modal-body">

    <?php if($facturationError){ ?>
                        <div class="alert alert-block alert-error fade in">
                            <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                        <p><?php echo trad('check_information', 'admin'); ?></p>
                        </div>
    <?php } ?>

                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><?php echo trad('Societe', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$entreprise:$adrFacturation->entreprise ?>" name="entreprise"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Civilite', 'admin'); ?></td>
                                    <td>
                                        <select name="raison" >
    <?php

    $qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
    $rListTitles = $raisonFacturation->query($qListTitles);
    while($rListTitles && $theTitle = $raisonFacturation->fetch_object($rListTitles, 'Raisondesc'))
    {
    ?>
                                            <option value="<?php echo $theTitle->raison; ?>" <?php if(($facturationError?$raison:$raisonFacturation->raison)==$theTitle->raison){ ?>selected="selected"<?php } ?>>
                                                <?php echo $theTitle->long; ?>
                                            </option>
    <?php
    }
    ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="<?php if($facturationError && empty($nom)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Nom', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$nom:$adrFacturation->nom ?>" name="nom"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($facturationError && empty($prenom)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Prenom', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$prenom:$adrFacturation->prenom ?>" name="prenom"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($facturationError && empty($adresse1)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Adresse', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$adresse1:$adrFacturation->adresse1 ?>" name="adresse1"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$adresse2:$adrFacturation->adresse2 ?>" name="adresse2"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$adresse3:$adrFacturation->adresse3 ?>" name="adresse3"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($facturationError && empty($cpostal)){ ?>error<?php } ?>">
                                    <td><?php echo trad('CP', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$cpostal:$adrFacturation->cpostal ?>" name="cpostal"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($facturationError && empty($ville)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Ville', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$ville:$adrFacturation->ville ?>" name="ville"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Pays', 'admin'); ?></td>
                                    <td>
                                        <select name="pays" >
    <?php

    $qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
    $rListCountries = $paysFacturation->query($qListCountries);
    while($rListCountries && $theCountry = $paysFacturation->fetch_object($rListCountries, 'Paysdesc'))
    {
    ?>
                                            <option value="<?php echo $theCountry->pays; ?>" <?php if(($facturationError?$pays:$paysFacturation->pays)==$theCountry->pays){ ?>selected="selected"<?php } ?>>
                                                <?php echo $theCountry->titre; ?>
                                            </option>
    <?php
    }
    ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Telephone', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $facturationError?$tel:$adrFacturation->tel ?>" name="tel"  />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="modal-footer">
                        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo trad('Modifier', 'admin'); ?></button>
                    </div>
                </form>
                </div>
                
                <!-- delivery modal -->
                <div class="modal hide" id="deliveryModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <form method="POST" action="commande_details.php">
                    <input type="hidden" name="action" value="editVenteadr" />
                    <input type="hidden" name="ref" value="<?php echo $commande->ref; ?>" />
                    <input type="hidden" name="id" value="<?php echo $adrLivraison->id; ?>" />
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <div>
                            <h3>
                                <?php echo trad('ADRESSE_LIVRAISON', 'admin'); ?>
                            </h3>
                        </div>
                    </div>
                    <div class="modal-body">

    <?php if($deliveryError){ ?>
                        <div class="alert alert-block alert-error fade in">
                            <h4 class="alert-heading"><?php echo trad('Cautious', 'admin'); ?></h4>
                        <p><?php echo trad('check_information', 'admin'); ?></p>
                        </div>
    <?php } ?>

                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><?php echo trad('Societe', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$entreprise:$adrLivraison->entreprise ?>" name="entreprise"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Civilite', 'admin'); ?></td>
                                    <td>
                                        <select name="raison" >
    <?php

    $qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
    $rListTitles = $raisonLivraison->query($qListTitles);
    while($rListTitles && $theTitle = $raisonLivraison->fetch_object($rListTitles, 'Raisondesc'))
    {
    ?>
                                            <option value="<?php echo $theTitle->raison; ?>" <?php if(($deliveryError?$raison:$raisonLivraison->raison)==$theTitle->raison){ ?>selected="selected"<?php } ?>>
                                                <?php echo $theTitle->long; ?>
                                            </option>
    <?php
    }
    ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="<?php if($deliveryError && empty($nom)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Nom', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$nom:$adrLivraison->nom ?>" name="nom"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($deliveryError && empty($prenom)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Prenom', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$prenom:$adrLivraison->prenom ?>" name="prenom"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($deliveryError && empty($adresse1)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Adresse', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$adresse1:$adrLivraison->adresse1 ?>" name="adresse1"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$adresse2:$adrLivraison->adresse2 ?>" name="adresse2"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$adresse3:$adrLivraison->adresse3 ?>" name="adresse3"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($deliveryError && empty($cpostal)){ ?>error<?php } ?>">
                                    <td><?php echo trad('CP', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$cpostal:$adrLivraison->cpostal ?>" name="cpostal"  />
                                    </td>
                                </tr>
                                <tr class="<?php if($deliveryError && empty($ville)){ ?>error<?php } ?>">
                                    <td><?php echo trad('Ville', 'admin'); ?> *</td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$ville:$adrLivraison->ville ?>" name="ville"  />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Pays', 'admin'); ?></td>
                                    <td>
                                        <select name="pays" >
    <?php

    $qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
    $rListCountries = $paysLivraison->query($qListCountries);
    while($rListCountries && $theCountry = $paysLivraison->fetch_object($rListCountries, 'Paysdesc'))
    {
    ?>
                                            <option value="<?php echo $theCountry->pays; ?>" <?php if(($deliveryError?$pays:$paysLivraison->pays)==$theCountry->pays){ ?>selected="selected"<?php } ?>>
                                                <?php echo $theCountry->titre; ?>
                                            </option>
    <?php
    }
    ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo trad('Telephone', 'admin'); ?></td>
                                    <td>
                                        <input type="text" value="<?php echo $deliveryError?$tel:$adrLivraison->tel ?>" name="tel"  />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="modal-footer">
                        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Cancel', 'admin'); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo trad('Modifier', 'admin'); ?></button>
                    </div>
                </form>
                </div>
            </div>
        </div>
<?php
        ActionsAdminModules::instance()->inclure_module_admin("commande_details_bottom");
?>
<?php require_once("pied.php");?> 
<script type="text/javascript">
$(document).ready(function(){
    $('#statutch').change(function(){
        $('#formStatus').submit();
    });
    
<?php if($facturationError){ ?>
    $('#facturationModal').modal();
<?php } ?>
    
<?php if($deliveryError){ ?>
    $('#deliveryModal').modal();
<?php } ?>
    
});
</script>
</body>
</html>
