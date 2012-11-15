<?php
require_once("pre.php");
require_once("auth.php");
        
if(! est_autorise("acces_commandes")) exit; 

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

$statusArray = $commande->query_liste('SELECT * FROM '.Statutdesc::TABLE.' WHERE lang='.ActionsLang::instance()->get_id_langue_courante());


?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>

<body>
    <?php
        $menu = "commande";
        $breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_commandes', 'admin'), "commande.php");
        require_once("entete.php");
    ?>
        <div class="row-fluid">
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
                            <td><?php echo $client->nom." ".$client->prenom; ?></td>
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
                            <td><?php if($promoutil->id){ $promoutil->code; ?> (<?php echo $promoutil->valeur; echo($promoutil->type==Promo::TYPE_SOMME)?'€':'%'; ?>) <?php } ?></td>
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
                        <h4><?php echo trad('ADRESSE_FACTURATION', 'admin'); ?></h4>
                    </caption>
                    <tbody>
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
                        <h4><?php echo trad('ADRESSE_LIVRAISON', 'admin'); ?></h4>
                    </caption>
                    <tbody>
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
        </div>
        <div class="row-fluid">
            <div class="span12">
                <?php
                        ActionsAdminModules::instance()->inclure_module_admin("commandedetails");
                ?>
            </div>
        </div>
<?php require_once("pied.php");?> 
<script type="text/javascript">

    $(document).ready(function(){
        $('#statutch').change(function(){
            $('#formStatus').submit();
        });
    });
</script>
</body>
</html>
