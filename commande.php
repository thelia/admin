<?php
    require_once("auth.php");
    
    if(! est_autorise("acces_commandes"))
        exit;
    
    if(!isset($action)) $action="";
    if(!isset($client)) $client="";
    if(!isset($page)) $page=0;
    if(!isset($classement)) $classement="";
    
    
    
    if (isset($statut)) {
            if ($statut == '*')
                    $search="";
            else if($statut != "")
                    $search="and statut=" . $statut;
    }
    
    switch($action){
        case 'supprcmd':
            $commande = new Commande();
            if($commande->charger_ref($ref)){
                $commande->annuler();
                redirige('commande.php?page='.$page.'&statut='.$statut);
            }
    }


    if($client != "") $search .= " and client=\"$client\"";
    $commande = new Commande();
    if($page=="") $page=1;

    $query = OrderAdmin::getInstance()->getRequest('count', $search);
    
    $pagination = new PaginationAdmin($query, $page);


    if($classement == "client") {
            $critere = "client";
            $order = "asc";
    }
    else if($classement == "statut") {
            $critere = "statut";
            $order = "asc";
    }
    else {
            $critere = "date";
            $order = "desc";
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once("title.php");?>
</head>

<body>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("commande_top");
$menu = "commande";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Gestion_commandes', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="span12">
            <h3>
                <?php echo trad('LISTE_COMMANDES', 'admin'); ?>
                
                <div class="btn-group">
                    <a class="btn btn-large" title="<?php echo trad('ajouter', 'admin'); ?>" href="commande_creer.php">
                        <i class="icon-plus-sign icon-white"></i>
                    </a>
                </div>
            </h3>
<?php
	ActionsAdminModules::instance()->inclure_module_admin("commande");
?>
            <div class="bigtable">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo trad('Num_commande', 'admin'); ?></th>
                        <th><?php echo trad('Date_Heure', 'admin'); ?></th>
                        <th><?php echo trad('Societe', 'admin'); ?></th>
                        <th><?php echo trad('Nom', 'admin'); ?></th>
                        <th><?php echo trad('Montant', 'admin'); ?></th>
                        <th><?php echo trad('Statut', 'admin'); ?></th>
                        <th><?php echo trad('Annuler', 'admin'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach(OrderAdmin::getInstance()->getList($critere, $order, $pagination->getStarted(), $pagination->getViewPerPage(), $search) as $commande): ?>
                    <tr>
                        <td><a href="commande_details.php?ref=<?php echo $commande['ref']; ?>"><?php echo $commande['ref']; ?></a></td>
                        <td><?php echo $commande['date']; ?></td>
                        <td><?php echo $commande['client']['entreprise']; ?></td>
                        <td><a href="client_visualiser.php?ref=<?php echo $commande['client']['ref'] ?>"><?php echo $commande['client']['nom'].' '.$commande['client']['prenom']; ?></a></td>
                        <td><?php echo $commande['total']; ?></td>
                        <td><?php echo $commande['titre']; ?></td>
                        <td>
                            <div class="btn-group">
                            <a class="btn btn-mini" title="<?php echo trad('editer', 'admin'); ?>" href="commande_details.php?ref=<?php echo $commande['ref']; ?>"><i class="icon-edit"></i></a>
                            <?php if($commande['statut'] != Commande::ANNULE): ?>
                                <a class="btn btn-mini js-delete-order" title="<?php echo trad('Annuler', 'admin'); ?>" data-toggle="modal" href="#delOrder" order-ref="<?php echo $commande["ref"]; ?>" ><i class="icon-remove-sign"></i></a>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <?php if($pagination->getTotalPages() > 1): ?>
    <div class="row-fluid">
        <div class="span12 spacetop18">
            <div class="pagination pagination-centered">
                <ul>
                    <?php if($pagination->getCurrentPage() == 1 ): ?>
                        <li class="disabled">
                            <a>Prev</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="commande.php?page=<?php echo $pagination->getPreviousPage(); ?>&statut=<?php echo $statut; ?>">Prev</a>
                        </li>   
                    <?php endif; ?>
                        
                    <?php if($pagination->getTotalPages() > $pagination->getMaxPagesDisplayed() && $pagination->getCurrentPage() > 1): ?>
                        <li>
                            <a href="commande.php?page=1&statut=<?php echo $statut; ?>">...</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for($i = $pagination->getStartedPagination(); $i <= $pagination->getEndPagination(); $i ++ ): ?>
                        <?php if($pagination->getCurrentPage() == $i): ?>
                            <li class="active"><a><?php echo $i; ?></a></li>
                        <?php else: ?>
                            <li><a href="commande.php?page=<?php echo $i; ?>&statut=<?php echo $statut; ?>"><?php echo $i; ?></a></li>
                        <?php endif; ?>
                    
                    <?php endfor; ?>
                        
                    <?php if($pagination->getTotalPages() > $pagination->getMaxPagesDisplayed() && $pagination->getCurrentPage() < $pagination->getTotalPages()): ?>
                        <li>
                            <a href="commande.php?page=<?php echo $pagination->getTotalPages(); ?>&statut=<?php echo $statut; ?>">...</a>
                        </li>
                    <?php endif; ?>
                        
                    <?php if($pagination->getCurrentPage() == $pagination->getTotalPages()): ?>
                        <li class="disabled">
                            <a>Next</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="commande.php?page=<?php echo $pagination->getNextPage(); ?>&statut=<?php echo $statut; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="modal hide" id="delOrder">
        <div class="modal-header"> <a class="close" data-dismiss="modal">x</a>
            <h3>Plus d'informations</h3>
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
<?php
	ActionsAdminModules::instance()->inclure_module_admin("commande_bottom");
?>
<?php require_once("pied.php");?>
<script type="text/javascript">

    $(document).ready(function(){
        $('.js-delete-order').click(function(){
           $('#orderCancellationInfo').html('Ref. ' + $(this).attr('order-ref'));
           $('#orderCancellationLink').attr('href', 'commande.php?action=supprcmd&ref=' + $(this).attr('order-ref') + '&statut=<?php echo $statut; ?>&page=<?php echo $pagination->getCurrentPage(); ?>');
        });
    })
</script>
</body>
</html>
