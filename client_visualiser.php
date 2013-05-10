<?php
require_once("auth.php");
require_once("../fonctions/divers.php");

if(! est_autorise("acces_clients"))
    exit;

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$client = new Client();
if(!$request->get("ref") || !$client->charger_ref($request->get("ref")))
    redirige('client.php');

$errorCode = 0;

try
{
    ActionsAdminClient::getInstance()->action($request);
}
catch(TheliaAdminException $e)
{
    $errorCode = $e->getCode();    
    switch ($errorCode)
    {
        case TheliaAdminException::CLIENT_ADD_ADRESS:
            $addError = 1;
            break;
        case TheliaAdminException::CLIENT_ADRESS_EDIT_ERROR:
            $editAddressError[$request->get("id")] = 1;
            break;
    }
}
    

$raisondesc = new Raisondesc($client->raison);

if($client->parrain){
    $parrain = new Client();
    $parrain->charger_id($client->parrain);
}

$paysdesc = new Paysdesc();
$paysdesc->charger($client->pays);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>

<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("client_visualiser");
$menu = "client";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_clients', 'admin'), "client.php");
require_once("entete.php");
?>
    <div class="row-fluid">
        
        <div class="span6">
            
            <div class="row-fluid">
                
                <div class="span12">
                    
                    <h3>
                        <?php echo trad('INFO_CLIENT', 'admin'); ?>
                        <div class="btn-group">
                            <a class="btn btn-large" title="<?php echo trad('editer', 'admin'); ?>" href="#clientEditionModal" data-toggle="modal">
                                <i class="icon-edit icon-white"></i>
                            </a>
                            <a class="btn btn-large" title="<?php echo trad('send_email', 'admin'); ?>" href="mailto:<?php echo($client->email); ?>">
                                <i class="icon-envelope icon-white"></i>
                            </a>
                        </div>
                    </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("client_visualiser");
?>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td><?php echo trad('Societe', 'admin'); ?></td>
                                <td><?php echo $client->entreprise ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Siret', 'admin'); ?></td>
                                <td><?php echo $client->siret ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Numintracom', 'admin'); ?></td>
                                <td><?php echo $client->intracom ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Civilite', 'admin'); ?></td>
                                <td><?php echo $raisondesc->long ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Nom', 'admin'); ?></td>
                                <td><?php echo $client->nom ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Prenom', 'admin'); ?></td>
                                <td><?php echo $client->prenom ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adresse', 'admin'); ?></td>
                                <td><?php echo $client->adresse1 ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td><?php echo $client->adresse2 ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td><?php echo $client->adresse3 ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('CP', 'admin'); ?></td>
                                <td><?php echo $client->cpostal ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Ville', 'admin'); ?></td>
                                <td><?php echo $client->ville ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Pays', 'admin'); ?></td>
                                <td><?php echo $paysdesc->titre ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telfixe', 'admin'); ?></td>
                                <td><?php echo $client->telfixe ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telport', 'admin'); ?></td>
                                <td><?php echo $client->telport ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Email', 'admin'); ?></td>
                                <td><?php echo $client->email ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Remise', 'admin'); ?></td>
                                <td><?php echo $client->pourcentage ?> %</td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Revendeur', 'admin'); ?></td>
                                <td>
                                    <input type="checkbox" disabled="disabled" <?php if($client->type == 1) { ?>checked="checked"<?php } ?> />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Parrain', 'admin'); ?></td>
                                <td>
    <?php
    if(isset($parrain))
    {
    ?>
                                    <a href="client_visualiser.php?ref=<?php echo $parrain->ref ?>"><?php echo $parrain->prenom . " " . $parrain->nom; ?> </a>
    <?php
    }
    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
            </div>
            
            <div class="row-fluid">
                
                <div class="span12">
            
                    <h3><?php echo trad('LISTE_COMMANDES_CLIENT', 'admin'); ?></h3>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo trad('NUM_COMMANDE', 'admin'); ?></th>
                                <th><?php echo trad('DATE_HEURE', 'admin'); ?></th>
                                <th><?php echo trad('MONTANT_euro', 'admin'); ?></th>
                                <th><?php echo trad('STATUT', 'admin'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
<?php
$commande = new Commande();
$query = "SELECT * FROM " . Commande::TABLE . " WHERE client='" . $client->id . "' ORDER BY date DESC";
$resul = $commande->query($query);
while($resul && $cmd = $commande->fetch_object($resul, 'Commande'))
{
    $statutdesc = new Statutdesc();
    $statutdesc->charger($cmd->statut);
    
    switch($cmd->statut)
    {
        case '1':
            $trClass = 'warning';
            break;
        case '4':
            $trClass = 'success';
            break;
        case '5':
            $trClass = 'error';
            break;
        default:
            $trClass = 'info';
            break;
    }
?>
                            <tr class="<?php echo $trClass; ?>">
                                <td><?php echo $cmd->ref; ?></td>
                                <td><?php echo date("d/m/Y H:i:s", strtotime($cmd->date)); ?></td>
                                <td><?php echo formatter_somme($cmd->total(true, true)); ?></td>
                                <td><?php echo $statutdesc->titre; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="commande_details.php?ref=<?php echo $cmd->ref; ?>"><i class="icon-edit"></i></a>
<?php
    if($cmd->statut != 5)
    {
?>
                                <a class="btn btn-mini js-delete-order" title="<?php echo trad('supprimer', 'admin'); ?>" href="#cancelModal" data-toggle="modal" order-ref="<?php echo $cmd->ref; ?>" order-id="<?php echo $cmd->id; ?>"><i class="icon-remove-sign"></i></a>
<?php
    }
?>
                                    </div>
                                </td>
                            </tr>
<?php
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <h3><?php echo trad('LISTE_FILLEULS_CLIENT', 'admin'); ?></h3>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo trad('NOM', 'admin'); ?></th>
                                <th><?php echo trad('PRENOM', 'admin'); ?></th>
                                <th><?php echo trad('EMAIL', 'admin'); ?></th>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
<?php
$listepar = new Client();
$query = "SELECT * FROM " . Client::TABLE . " WHERE parrain='" . $client->id . "' ORDER BY id ASC";
$resul = $listepar->query($query);
while($resul && $filleul = $listepar->fetch_object($resul, 'Client'))
{
?>
                            <tr>
                                <td><?php echo $filleul->nom; ?></td>
                                <td><?php echo $filleul->prenom; ?></td>
                                <td><?php echo $filleul->email; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="client_visualiser.php?ref=<?php echo $filleul->ref; ?>"><i class="icon-edit"></i></a>
                                        <a class="btn btn-mini" title="<?php echo trad('send_email', 'admin'); ?>" href="mailto:<?php echo($filleul->email); ?>"><i class="icon-envelope"></i></a>
                                    </div>
                                </td>
                            </tr>
<?php
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="row-fluid">
                <div class="span12">
                    <?php
                            ActionsAdminModules::instance()->inclure_module_admin("clientvisualisergauche");
                    ?>
                </div>
            </div>
            
        </div>
        
        <div class="span6">
            
<?php
$previous = ToolBoxAdmin::getPrevious($client, false, 'nom');
$next = ToolBoxAdmin::getNext($client, false, 'nom');
if($next!==false || $previous!==false)
{
?>
            
            <div class="row-fluid">
                <span class="12">
                    <div class="littletable">
                        <ul class="nav nav-pills">
                            <li class="">
                                <?php
                                if($previous !== false)
                                {
                                ?>
                                <a href="client_visualiser.php?ref=<?php echo $previous->ref; ?>" title="<?php echo trad('previous', 'admin'); ?>" class="change-page">
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
                                <a href="client_visualiser.php?ref=<?php echo $next->ref; ?>" title="<?php echo trad('next', 'admin'); ?>" class="change-page">
                                    <i class="icon-forward"></i>
                                </a>
                                <?php
                                }
                                ?>
                            </li>
                        </ul>
                    </div>
                </span>
            </div>
<?php
}
?>
            
            <div class="row-fluid">

                <span class="12">
                    <h3>
                        <?php echo trad('CLIENT_ADDRESSES', 'admin'); ?>

                        <div class="btn-group">
                            <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="#addressAddModal" data-toggle="modal">
                                <i class="icon-plus-sign icon-white"></i>
                            </a>
                        </div>
                    </h3>

<?php
$address = new Adresse();
$qListAddresses = "SELECT * FROM " . Adresse::TABLE . " WHERE client='$client->id'";
$rListAddresses = $address->query($qListAddresses);
while($rListAddresses && $theAddress = $address->fetch_object($rListAddresses, 'Adresse'))
{
    $addressRaisondesc = new Raisondesc($theAddress->raison);
    $addressPaysdesc = new Paysdesc($theAddress->pays);
?>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th colspan="2">
                                    <?php echo $theAddress->libelle; ?>
                                    <div class="btn-group">
                                        <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="#address<?php echo $theAddress->id; ?>EditionModal" data-toggle="modal">
                                            <i class="icon-edit"></i>
                                        </a>
                                        <a class="btn btn-mini js-delete-address" title="<?php echo trad('supprimer', 'admin'); ?>" href="#addressDelationModal" data-toggle="modal" address-formulation="<?php echo $theAddress->libelle; ?>" address-id="<?php echo $theAddress->id; ?>">
                                            <i class="icon-trash"></i>
                                        </a>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo trad('Societe', 'admin'); ?></td>
                                <td><?php echo $theAddress->entreprise ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Civilite', 'admin'); ?></td>
                                <td><?php echo $addressRaisondesc->long ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Nom', 'admin'); ?></td>
                                <td><?php echo $theAddress->nom ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Prenom', 'admin'); ?></td>
                                <td><?php echo $theAddress->prenom ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adresse', 'admin'); ?></td>
                                <td><?php echo $theAddress->adresse1 ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td><?php echo $theAddress->adresse2 ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td><?php echo $theAddress->adresse3 ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('CP', 'admin'); ?></td>
                                <td><?php echo $theAddress->cpostal ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Ville', 'admin'); ?></td>
                                <td><?php echo $theAddress->ville ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Pays', 'admin'); ?></td>
                                <td><?php echo $addressPaysdesc->titre ?></td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telephone', 'admin'); ?></td>
                                <td><?php echo $theAddress->tel ?></td>
                            </tr>
                        </tbody>
                    </table>
<?php
}
?>
                </span>
            </div>
            
            <div class="row-fluid">
                <div class="span12">
                    <?php
                            ActionsAdminModules::instance()->inclure_module_admin("clientvisualiserdroite");
                    ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <div class="row-fluid">
        <div class="span12">
            <?php
                    ActionsAdminModules::instance()->inclure_module_admin("clientvisualiser");
            ?>
        </div>
    </div>
    
    <div class="row-fluid">
        <div class="span12">
            
            <!-- order cancellation -->
            <div class="modal hide" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 ><?php echo trad('Cautious', 'admin'); ?></h3>
                </div>
                <div class="modal-body">
                    <p><?php echo trad('CancelOrderWarning', 'admin'); ?></p>
                    <p id="orderCancellationInfo"></p>
                </div>
                <div class="modal-footer">
                    <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                    <a class="btn btn-primary" id="orderCancellationLink"><?php echo trad('Oui', 'admin'); ?></a>
                </div>
            </div>
            
            <!-- address delation -->
            <div class="modal hide" id="addressDelationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3><?php echo trad('Cautious', 'admin'); ?></h3>
                </div>
                <div class="modal-body">
                    <p><?php echo trad('AddressDelationWarning', 'admin'); ?></p>
                    <p id="addressDelationInfo"></p>
                </div>
                <div class="modal-footer">
                    <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                    <a class="btn btn-primary" id="addressDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
                </div>
            </div>
            
            <!-- client edition -->
            <div class="modal hide" id="clientEditionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <form method="POST" action="client_visualiser.php">
                <input type="hidden" name="action" value="editCustomer" />
                <input type="hidden" name="ref" value="<?php echo $client->ref; ?>" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 ><?php echo trad('INFO_CLIENT', 'admin'); ?></h3>
                </div>
                <div class="modal-body">
                    
<?php if($errorCode == TheliaAdminException::CLIENT_EDIT_ERROR){ ?>
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
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($request->request->get("entreprise")):htmlspecialchars($client->entreprise) ?>" name="entreprise"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Siret', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($request->request->get("siret")):htmlspecialchars($client->siret) ?>" name="siret"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Numintracom', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($request->request->get("intracom")):htmlspecialchars($client->intracom) ?>" name="intracom"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Civilite', 'admin'); ?></td>
                                <td>
                                    <select name="raison" >
<?php

$qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListTitles = $raisondesc->query($qListTitles);
while($rListTitles && $theTitle = $raisondesc->fetch_object($rListTitles, 'Raisondesc'))
{
?>
                                        <option value="<?php echo $theTitle->raison; ?>" <?php if($theTitle->raison==($errorCode?$request->request->get("raison"):$raisondesc->raison)){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theTitle->long; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="<?php if($errorCode && empty($nom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Nom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($nom):htmlspecialchars($client->nom) ?>" name="nom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($errorCode && empty($prenom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Prenom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($prenom):htmlspecialchars($client->prenom) ?>" name="prenom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($errorCode && empty($adresse1)){ ?>error<?php } ?>">
                                <td><?php echo trad('Adresse', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($adresse1):htmlspecialchars($client->adresse1) ?>" name="adresse1"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($adresse2):htmlspecialchars($client->adresse2) ?>" name="adresse2"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($adresse3):htmlspecialchars($client->adresse3) ?>" name="adresse3"  />
                                </td>
                            </tr>
                            <tr class="<?php if($errorCode && empty($cpostal)){ ?>error<?php } ?>">
                                <td><?php echo trad('CP', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?$cpostal:$client->cpostal ?>" name="cpostal"  />
                                </td>
                            </tr>
                            <tr  class="<?php if($errorCode && empty($ville)){ ?>error<?php } ?>">
                                <td><?php echo trad('Ville', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($ville):htmlspecialchars($client->ville) ?>" name="ville"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Pays', 'admin'); ?></td>
                                <td>
                                    <select name="pays" >
<?php

$qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListCountries = $paysdesc->query($qListCountries);
while($rListCountries && $theCountry = $paysdesc->fetch_object($rListCountries, 'Paysdesc'))
{
?>
                                        <option value="<?php echo $theCountry->pays; ?>" <?php if($theCountry->pays==($errorCode?$pays:$paysdesc->pays)){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theCountry->titre; ?>
                                        </option>
<?php
}
?>
                                    </select>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telfixe', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($telfixe):htmlspecialchars($client->telfixe) ?>" name="telfixe"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telport', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($telport):htmlspecialchars($client->telport) ?>" name="telport"  />
                                </td>
                            </tr>
                            <tr class="<?php if($errorCode && (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || ($email!=$client->email && $client->existe($email)))){ ?>error<?php } ?>">
                                <td>
                                    <?php echo trad('Email', 'admin'); ?> *
                                    <?php if($errorCode && !filter_var($email, FILTER_VALIDATE_EMAIL)){ ?><br /><?php echo trad('email_bad_format', 'admin'); ?><?php }
                                    elseif($errorCode && $client->existe($email)){ ?><br /><?php echo trad('email_already_exists', 'admin'); ?><?php } ?>
                                </td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($email):htmlspecialchars($client->email) ?>" name="email"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Remise', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $errorCode?htmlspecialchars($pourcentage):htmlspecialchars($client->pourcentage) ?>" name="pourcentage"  />
                                 %</td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Revendeur', 'admin'); ?></td>
                                <td>
                                    <input type="checkbox" name="type" <?php if(($errorCode?$type:$client->type) == ($errorCode?'on':1)) { ?>checked="checked"<?php } ?> />
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

            <!-- address edition -->
<?php
$address = new Adresse();
$qListAddresses = "SELECT * FROM " . Adresse::TABLE . " WHERE client='$client->id'";
$rListAddresses = $address->query($qListAddresses);
while($rListAddresses && $theAddress = $address->fetch_object($rListAddresses, 'Adresse'))
{
    $addressRaisondesc = new Raisondesc($theAddress->raison);
    $addressPaysdesc = new Paysdesc($theAddress->pays);
?>
            <div class="modal hide" id="address<?php echo $theAddress->id; ?>EditionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <form method="POST" action="client_visualiser.php">
                <input type="hidden" name="action" value="editAddress" />
                <input type="hidden" name="id" value="<?php echo $theAddress->id; ?>" />
                <input type="hidden" name="ref" value="<?php echo $client->ref; ?>" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <div class="<?php if($editAddressError[$theAddress->id] && empty($libelle)){ ?>control-group error<?php } ?>">
                        <h3>
                            <?php echo trad('Formulation', 'admin'); ?> : <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$libelle:$theAddress->libelle ?>" name="libelle" />
                        </h3>
                    </div>
                </div>
                <div class="modal-body">
                    
<?php if($editAddressError[$theAddress->id]){ ?>
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
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$entreprise:$theAddress->entreprise ?>" name="entreprise"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Civilite', 'admin'); ?></td>
                                <td>
                                    <select name="raison" >
<?php

$qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListTitles = $raisondesc->query($qListTitles);
while($rListTitles && $theTitle = $raisondesc->fetch_object($rListTitles, 'Raisondesc'))
{
?>
                                        <option value="<?php echo $theTitle->raison; ?>" <?php if(($editAddressError[$theAddress->id]?$raison:$addressRaisondesc->raison)==$theTitle->raison){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theTitle->long; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="<?php if($editAddressError[$theAddress->id] && empty($nom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Nom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$nom:$theAddress->nom ?>" name="nom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($editAddressError[$theAddress->id] && empty($prenom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Prenom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$prenom:$theAddress->prenom ?>" name="prenom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($editAddressError[$theAddress->id] && empty($adresse1)){ ?>error<?php } ?>">
                                <td><?php echo trad('Adresse', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$adresse1:$theAddress->adresse1 ?>" name="adresse1"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$adresse2:$theAddress->adresse2 ?>" name="adresse2"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$adresse3:$theAddress->adresse3 ?>" name="adresse3"  />
                                </td>
                            </tr>
                            <tr class="<?php if($editAddressError[$theAddress->id] && empty($cpostal)){ ?>error<?php } ?>">
                                <td><?php echo trad('CP', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$cpostal:$theAddress->cpostal ?>" name="cpostal"  />
                                </td>
                            </tr>
                            <tr class="<?php if($editAddressError[$theAddress->id] && empty($ville)){ ?>error<?php } ?>">
                                <td><?php echo trad('Ville', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$ville:$theAddress->ville ?>" name="ville"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Pays', 'admin'); ?></td>
                                <td>
                                    <select name="pays" >
<?php

$qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListCountries = $paysdesc->query($qListCountries);
while($rListCountries && $theCountry = $paysdesc->fetch_object($rListCountries, 'Paysdesc'))
{
?>
                                        <option value="<?php echo $theCountry->pays; ?>" <?php if(($editAddressError[$theAddress->id]?$pays:$addressPaysdesc->pays)==$theCountry->pays){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theCountry->titre; ?>
                                        </option>
<?php
}
?>
                                    </select>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telephone', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $editAddressError[$theAddress->id]?$tel:$theAddress->tel ?>" name="tel"  />
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
<?php
}
?>
            <!-- address add -->
            <div class="modal hide" id="addressAddModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <form method="POST" action="client_visualiser.php">
                <input type="hidden" name="action" value="addAddress" />
                <input type="hidden" name="ref" value="<?php echo $client->ref; ?>" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3>
                        <div class="<?php if($addError && empty($libelle)){ ?>control-group error<?php } ?>">
                            <?php echo trad('Formulation', 'admin'); ?> : <input type="text" value="<?php echo $addError?$libelle:''; ?>" name="libelle" />
                        </div>
                    </h3>
                </div>
                <div class="modal-body">
                    
<?php if($addError){ ?>
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
                                    <input type="text" value="<?php echo $addError?$entreprise:''; ?>" name="entreprise"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Civilite', 'admin'); ?></td>
                                <td>
                                    <select name="raison" >
<?php

$qListTitles = "SELECT * FROM " . Raisondesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListTitles = $raisondesc->query($qListTitles);
while($rListTitles && $theTitle = $raisondesc->fetch_object($rListTitles, 'Raisondesc'))
{
?>
                                        <option value="<?php echo $theTitle->raison; ?>" <?php if($addError && $raison==$theTitle->raison){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theTitle->long; ?>
                                        </option>
<?php
}
?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="<?php if($addError && empty($nom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Nom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$nom:''; ?>" name="nom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($addError && empty($prenom)){ ?>error<?php } ?>">
                                <td><?php echo trad('Prenom', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$prenom:''; ?>" name="prenom"  />
                                </td>
                            </tr>
                            <tr class="<?php if($addError && empty($adresse1)){ ?>error<?php } ?>">
                                <td><?php echo trad('Adresse', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$adresse1:''; ?>" name="adresse1"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$adresse2:''; ?>" name="adresse2"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Adressesuite', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$adresse3:''; ?>" name="adresse3"  />
                                </td>
                            </tr>
                            <tr class="<?php if($addError && empty($cpostal)){ ?>error<?php } ?>">
                                <td><?php echo trad('CP', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$cpostal:''; ?>" name="cpostal"  />
                                </td>
                            </tr>
                            <tr class="<?php if($addError && empty($ville)){ ?>error<?php } ?>">
                                <td><?php echo trad('Ville', 'admin'); ?> *</td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$ville:''; ?>" name="ville"  />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo trad('Pays', 'admin'); ?></td>
                                <td>
                                    <select name="pays" >
<?php

$qListCountries = "SELECT * FROM " . Paysdesc::TABLE . " WHERE lang=" . ActionsLang::instance()->get_id_langue_courante();
$rListCountries = $paysdesc->query($qListCountries);
while($rListCountries && $theCountry = $paysdesc->fetch_object($rListCountries, 'Paysdesc'))
{
?>
                                        <option value="<?php echo $theCountry->pays; ?>" <?php if($addError && $pays==$theCountry->pays){ ?>selected="selected"<?php } ?>>
                                            <?php echo $theCountry->titre; ?>
                                        </option>
<?php
}
?>
                                    </select>
                            </tr>
                            <tr>
                                <td><?php echo trad('Telephone', 'admin'); ?></td>
                                <td>
                                    <input type="text" value="<?php echo $addError?$tel:''; ?>" name="tel"  />
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
            
        </div>
    </div>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("client_visualiser");
?>
    
<?php require_once("pied.php");?>

    
<script type="text/javascript">

jQuery(function($)
{
    $('.js-delete-order').click(function()
    {
        $('#orderCancellationInfo').html('Ref. ' + $(this).attr('order-ref'));
        $('#orderCancellationLink').attr('href', 'client_visualiser.php?action=deleteOrder&id=' + $(this).attr('order-id') + '&ref=<?php echo $client->ref; ?>');
    });
    
    $('.js-delete-address').click(function()
    {
        $('#addressDelationInfo').html($(this).attr('address-formulation'));
        $('#addressDelationLink').attr('href', 'client_visualiser.php?action=deleteAddress&id=' + $(this).attr('address-id') + '&ref=<?php echo $client->ref; ?>');
    });
    
<?php if($addError){ ?>
    $('#addressAddModal').modal();
<?php } ?>
    
<?php if($errorCode == TheliaAdminException::CLIENT_EDIT_ERROR){ ?>
    $('#clientEditionModal').modal();
<?php } ?>
    
<?php
if(isset($editAddressError))
{
    foreach($editAddressError as $key => $idModal){ ?>
    $('#address<?php echo $key ?>EditionModal').modal();
    <?php }
} ?>

});

</script>
    
</body>
</html>
