<?php
require_once("pre.php");
require_once("auth.php");

require_once("../fonctions/divers.php");
if (!est_autorise("acces_catalogue"))
    exit;

use Symfony\Component\HttpFoundation\Request;
$request = Request::createFromGlobals();

try {
    ActionsAdminPromo::getInstance()->action($request);
} catch (TheliaAdminException $e) {
    $errorCode = $e->getCode();
    switch ($errorCode)
    {
        default:
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once("title.php"); ?>
</head>

<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("recherche_top");
$menu = "";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Resultats_recherche', 'admin'));
require_once("entete.php");
?>

<!--CLIENT-->
<?php
$clientResult = ClientAdmin::getInstance()->getSearchList($request->query->get('motcle'));
?>
<div class="row-fluid">
    <div class="span12">
        <h3><?php echo strtoupper(trad((count($clientResult) > 0?'RESULTATS_CLIENTS':'AUCUN_RESULTATS_CLIENTS'), 'admin')); ?></h3>
    </div>
</div>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("recherche");
?>

<div class="row-fluid">
    <div class="span12">
<?php
$clientFoundIdList = array();
if(count($clientResult) > 0)
{
?>
        <div class="bigtable">
        <table class="table table-striped" >
            <thead>
                <tr>
                    
                    <th><?php echo trad('Num_client', 'admin'); ?></th>
                    <th><?php echo trad('Societe', 'admin'); ?></th>
                    <th><?php echo trad('Nom', 'admin'); ?></th>
                    <th><?php echo trad('Prenom', 'admin'); ?></th>
                    <th><?php echo trad('Ville', 'admin'); ?></th>
                    <th><?php echo trad('Email', 'admin'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
<?php
    foreach($clientResult as $client)
    {
        $clientFoundIdList[] = $client->id;
?>
                    <tr>
                        <td>
                            <?php echo $client->ref ; ?>
                        </td>
                        <td>
                            <?php echo $client->entreprise; ?>
                        </td>
                        <td>
                            <?php echo $client->nom; ?>
                        </td>
                        <td>
                            <?php echo $client->prenom; ?>
                        </td>
                        <td>
                            <?php echo $client->ville; ?>
                        </td>
                        <td>
                            <?php echo $client->email; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="client_visualiser.php?ref=<?php echo $client->ref; ?>"><i class="icon-edit"></i></a>
                                <a class="btn btn-mini js-delete-client" title="<?php echo trad('supprimer', 'admin'); ?>" href="#deleteClientModal" data-toggle="modal" client-info="<?php echo $client->nom ?> <?php echo $client->prenom ?>" client-ref="<?php echo $client->ref ?>"><i class="icon-trash"></i></a>
                            </div>
                        </td>
                    </tr>
<?php
    }
?>
            </tbody>   
        </table>
        </div>
<?php
}
?>
    </div>
</div>

<!--COMMANDES-->
<?php
$orderResult = OrderAdmin::getInstance()->getSearchList($request->query->get('motcle'), $clientFoundIdList);
?>
<div class="row-fluid">
    <div class="span12">
        <h3><?php echo strtoupper(trad((count($orderResult) > 0?'RESULTATS_COMMANDE':'AUCUN_RESULTATS_COMMANDES'), 'admin')); ?></h3>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
<?php
if(count($orderResult) > 0)
{
?>
        <div class="bigtable">
        <table class="table table-striped" >
            <thead>
                <tr>
                    
                    <th class="span2"><?php echo trad('Num_commande', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Num_facture', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Num_transaction', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Date_Heure', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Nom', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Montant', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Statut', 'admin'); ?></th>
                    <th class="span1"></th>
                </tr>
            </thead>
            <tbody>
<?php
    foreach($orderResult as $commande)
    {
        switch($commande['statut'])
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
                        <td>
                            <?php echo $commande['ref'] ; ?>
                        </td>
                        <td>
                            <?php echo $commande['facture']?:''; ?>
                        </td>
                        <td>
                            <?php echo $commande['transaction']?:''; ?>
                        </td>
                        <td>
                            <?php echo $commande['date']; ?>
                        </td>
                        <td>
                            <?php echo $commande['client']['prenom']; ?>
                            <?php echo $commande['client']['nom']; ?>
                        </td>
                        <td>
                            <?php echo $commande['total']; ?>
                            <?php echo $commande['devise']; ?>
                        </td>
                        <td>
                            <?php echo $commande['titre']; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="commande_details.php?ref=<?php echo $commande['ref']; ?>"><i class="icon-edit"></i></a>
                                <?php if($commande['statut'] != Commande::ANNULE): ?>
                                <a class="btn btn-mini js-delete-order" title="<?php echo trad('Annuler', 'admin'); ?>" data-toggle="modal" href="#deleteOrderModal" order-ref="<?php echo $commande["ref"]; ?>" ><i class="icon-remove-sign"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
<?php
    }
?>
            </tbody>   
        </table>
        </div>
<?php
}
?>
    </div>
</div>

<!--PRODUITS-->
<?php
$productResult = ProductAdmin::getInstance()->getSearchList($request->query->get('motcle'), $clientFoundIdList);
?>
<div class="row-fluid">
    <div class="span12">
        <h3><?php echo strtoupper(trad((count($productResult) > 0?'RESULTATS_PRODUITS':'AUCUN_RESULTATS_PRODUITS'), 'admin')); ?></h3>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
<?php
if(count($productResult) > 0)
{
?>
        <div class="bigtable">
        <table class="table table-striped" >
            <thead>
                <tr>
                    
                    <th class="span3"><?php echo trad('Reference', 'admin'); ?></th>
                    <th class="span3"><?php echo trad('Titre', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Prix', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('En_promotion', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Nouveaute', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('En_ligne', 'admin'); ?></th>
                    <th class="span1"></th>
                </tr>
            </thead>
            <tbody>
<?php
    foreach($productResult as $produit)
    {
?>
                    <tr>
                        <td>
                            <?php echo $produit['ref'] ; ?>
                        </td>
                        <td>
                            <?php echo $produit['titre']; ?>
                        </td>
                        <td>
                            <?php echo $produit['prix']; ?>
                        </td>
                        <td>
                            <input type="checkbox" product-id="<?php echo $produit["id"]; ?>" product-action="changePromo" class="js-change-product" <?php if($produit["promo"]) echo 'checked="checked"' ?> />
                        </td>
                        <td>
                            <input type="checkbox" product-id="<?php echo $produit["id"]; ?>" product-action="changeNew" class="js-change-product" <?php if($produit["nouveaute"]) echo 'checked="checked"' ?> />
                        </td>
                        <td>
                            <input type="checkbox" product-id="<?php echo $produit["id"]; ?>" product-action="changeDisplay" class="js-change-product" <?php if($produit["ligne"]) echo 'checked="checked"' ?> />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="produit_modifier.php?ref=<?php echo $produit['ref']; ?>&rubrique=<?php echo $produit['rubrique']; ?>"><i class="icon-edit"></i></a>
                            </div>
                        </td>
                    </tr>
<?php
    }
?>
            </tbody>   
        </table>
        </div>
<?php
}
?>
    </div>
</div>

<!--CONTENUS-->
<?php
$contentResult = ContentAdmin::getInstance()->getSearchList($request->query->get('motcle'), $clientFoundIdList);
?>
<div class="row-fluid">
    <div class="span12">
        <h3><?php echo strtoupper(trad((count($contentResult) > 0?'RESULTATS_CONTENUS':'AUCUN_RESULTATS_CONTENUS'), 'admin')); ?></h3>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
<?php
if(count($contentResult) > 0)
{
?>
        <div class="bigtable">
        <table class="table table-striped" >
            <thead>
                <tr>
                    
                    <th class="span9"><?php echo trad('Titre', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('En_ligne', 'admin'); ?></th>
                    <th class="span1"></th>
                </tr>
            </thead>
            <tbody>
<?php
    foreach($contentResult as $contenu)
    {
?>
                    <tr>
                        <td>
                            <?php echo $contenu['titre']; ?>
                        </td>
                        <td>
                            <input type="checkbox" content-id="<?php echo $contenu["id"]; ?>" content-action="changeDisplay" class="js-change-content" <?php if($contenu["ligne"]) echo 'checked="checked"' ?> />
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="contenu_modifier.php?id=<?php echo $contenu['id']; ?>&dossier=<?php echo $contenu['dossier']; ?>"><i class="icon-edit"></i></a>
                            </div>
                        </td>
                    </tr>
<?php
    }
?>
            </tbody>   
        </table>
        </div>
<?php
}
?>
    </div>
</div>

<div class="row-fluid">
    <div class="span12">

        <!-- client delation -->
        <div class="modal hide" id="deleteClientModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3><?php echo trad('Cautious', 'admin'); ?></h3>
            </div>
            <div class="modal-body">
                <p><?php echo trad('DeleteClientWarning', 'admin'); ?></p>
                <p id="clientDelationInfo"></p>
            </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Non', 'admin'); ?></a>
                <a class="btn btn-primary" id="clientDelationLink"><?php echo trad('Oui', 'admin'); ?></a>
            </div>
        </div>
        
        <!-- order cancellation -->
        <div class="modal hide" id="deleteOrderModal">
            <div class="modal-header"> <a class="close" data-dismiss="modal">x</a>
                <h3><?php echo trad('Cautious', 'admin'); ?></h3>
            </div>
            <div class="modal-body">
                <p><?php echo trad('CancelOrderWarning', 'admin'); ?></p>
                <p id="orderCancellationInfo"></p>
            </div>
            <div class="modal-footer">
                <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('non', 'admin'); ?></a>
                <a class="btn btn-primary" id="orderCancellationLink"><?php echo trad('Oui', 'admin'); ?></a>
            </div>
        </div>

    </div>
</div>

<div class="row-fluid">
    <div class="span12">
        <?php ActionsAdminModules::instance()->inclure_module_admin("recherche"); ?>
    </div>
</div>

<?php
	ActionsAdminModules::instance()->inclure_module_admin("recherche_bottom");
?>
    
<?php require_once("pied.php"); ?>
<link type="text/css" href="js/jquery-ui-1.9.1/css/ui-lightness/jquery-ui-1.9.1.custom.min.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-ui-1.9.1/js/jquery-ui-1.9.1.custom.min.js"></script>
<script type="text/javascript">

jQuery(function($)
{
    /*modal*/
    $('.js-delete-client').click(function()
    {
        $('#clientDelationInfo').html($(this).attr('client-ref') + ' - ' + $(this).attr('client-info'));
        $('#clientDelationLink').attr('href', 'client.php?action=delete&ref=' + $(this).attr('client-ref'));
    });
    
    $('.js-delete-order').click(function(){
       $('#orderCancellationInfo').html('Ref. ' + $(this).attr('order-ref'));
       $('#orderCancellationLink').attr('href', 'commande.php?action=supprcmd&ref=' + $(this).attr('order-ref') + '&statut=*&page=1');
    });
    
    $(".js-change-product").click(function(){
        $.ajax({
            url : 'ajax/produit.php',
            data : {
                product_id : $(this).attr('product-id'),
                action : $(this).attr('product-action'),
                display : $(this).is(':checked')
            }
        });
    });
    
    $(".js-change-content").click(function(){
        $.ajax({
            url : 'ajax/contenu.php',
            data : {
                content_id : $(this).attr('content-id'),
                action : $(this).attr('content-action'),
                display : $(this).is(':checked')
            }
        });
    });
});

</script>
</body>
</html>