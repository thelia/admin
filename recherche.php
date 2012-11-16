<?php
require_once("pre.php");
require_once("auth.php");
require_once("liste/commande.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_catalogue"))
    exit;

use Symfony\Component\HttpFoundation\Request;
$request = Request::createFromGlobals();

error_reporting(E_ALL);

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
$menu = "";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Resultats_recherche', 'admin'));
require_once("entete.php");
?>

<!--CLIENT-->
<div class="row-fluid">
    <div class="span12">
        <h3><?php echo strtoupper(trad('RESULTATS_CLIENTS', 'admin')); ?></h3>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
        <div class="bigtable">
        <table class="table table-striped" >
            <thead>
                <tr>
                    
                    <th class="span1"><?php echo trad('Num_client', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Societe', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Nom', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Prenom', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Ville', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Email', 'admin'); ?></th>
                    <th class="span1"></th>
                </tr>
            </thead>
            <tbody>
<?php
$clientFoundList = array();
foreach(ClientAdmin::getInstance()->getSearchList($request->query->get('motcle')) as $client)
{
    $clientFoundList[] = $client->id;
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
    </div>
</div>

<!--COMMANDES-->
<div class="row-fluid">
    <div class="span12">
        <h3><?php echo strtoupper(trad('RESULTATS_COMMANDE', 'admin')); ?></h3>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
        <div class="bigtable">
        <table class="table table-striped" >
            <thead>
                <tr>
                    
                    <th class="span1"><?php echo trad('Num_commande', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Date_Heure', 'admin'); ?></th>
                    <th class="span1"><?php echo trad('Nom', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Montant', 'admin'); ?></th>
                    <th class="span2"><?php echo trad('Statut', 'admin'); ?></th>
                    <th class="span1"></th>
                </tr>
            </thead>
            <tbody>
<?php
foreach(OrderAdmin::getInstance()->getSearchList($request->query->get('motcle'), $clientFoundList) as $commande)
{
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

    </div>
</div>
        
    
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
});

</script>
</body>
</html>