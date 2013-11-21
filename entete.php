<?php
function activemenu($menu,$verif){
    if($menu==$verif){
        print 'active';
    }
}

$statAdmin = new StatAdmin();
?>
<?php
ActionsAdminModules::instance()->inclure_module_admin("entete_top");
ActionsAdminModules::instance()->inclure_module_admin("entete_brandbar");
?>
    <div class="brandbar">
        <div class="container">
            <a class="brand" href="accueil.php">
                v. <?php echo rtrim(preg_replace("/(.)/", "$1.", Variable::lire('version')), "."); ?>
            </a>

            <div class="pull-right call-to-action">
                <?php	if(est_autorise("acces_rechercher")){ ?>
                    <form class="form-search" method="GET" action="recherche.php">
                        <div class="pull-right">
                            <div class="control-group">
                                <div class="input-append">
                                    <input type="text" class="input-medium search-query" id="motcle" name="motcle" />
                                    <button class="btn">
                                        <i class="icon-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php } ?>

                <dl class="Blocmoncompte">
                    <dt><?php echo($_SESSION["util"]->prenom); ?> <?php echo($_SESSION["util"]->nom); ?></dt>
                    <dt class="deconnexion"><a href="index.php?action=deconnexion" ><?php echo trad('Deconnexion', 'admin'); ?></a></dt>
                </dl>
            </div>

        </div>
    </div>
<?php
ActionsAdminModules::instance()->inclure_module_admin("entete_navbar");
?>
    <div class="navbar">
        <div class="navbar-inner">
            <div class="container">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="nav-collapse">
                    <ul class="nav">
                        <li class="<?php activemenu($menu, "accueil");?>" id="menuaccueil"><a href="accueil.php"><?php echo trad('Accueil', 'admin'); ?></a></li>
                        <?php	if(est_autorise("acces_clients")){ ?>
                            <li class="<?php activemenu($menu, "client");?>" id="menuclient"><a href="client.php"><?php echo trad('Clients', 'admin'); ?></a></li>
                        <?php } ?>
                        <?php	if(est_autorise("acces_commandes")){ ?>
                            <li class="dropdown <?php activemenu($menu, "commande");?>" id="menucommande" data-toggle="dropdown">
                                <a href="#" data-toggle="dropdown" ><?php echo trad('Commandes', 'admin'); ?><span class="caret"></span></a>
                                <ul class="dropdown-menu config_menu" role="menu">
                                    <li role="menuitem"><a data-target="commande.php" href="commande.php"><?php echo trad('All_orders', 'admin'); ?> <span class="badge badge-important"><?php echo $statAdmin->getNbCommand(); ?></span></a></li>
                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::NONPAYE; ?>" href="commande.php?statut=<?php echo Commande::NONPAYE; ?>">Non Pay&eacute;es <span class="badge badge-important"><?php echo $statAdmin->getNbCommandToPaid(); ?></span></a></li>
                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::PAYE; ?>" href="commande.php?statut=<?php echo Commande::PAYE; ?>">Pay&eacute;es <span class="badge badge-important"><?php echo $statAdmin->getNbCommandPaid(); ?></span></a></li>
                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::TRAITEMENT; ?>" href="commande.php?statut=<?php echo Commande::TRAITEMENT; ?>">En cours de traitement <span class="badge badge-warning"><?php echo $statAdmin->getNbCommandProcessed(); ?></span></a></li>
                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::EXPEDIE; ?>" href="commande.php?statut=<?php echo Commande::EXPEDIE; ?>">Exp&eacute;di&eacute;es <span class="badge badge-warning"><?php echo $statAdmin->getNbCommandSend(); ?></span></a></li>
                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::ANNULE; ?>" href="commande.php?statut=<?php echo Commande::ANNULE; ?>">Annul&eacute;es <span class="badge badge-warning"><?php echo $statAdmin->getNbCommandCanceled(); ?></span></a></li>
                                    <li role="menuitem"><a data-target="commande_creer.php" href="commande_creer.php"><?php echo trad('Create_order', 'admin'); ?></a></li>
                                </ul>
                            </li>
                        <?php } ?>
                        <?php	if(est_autorise("acces_catalogue")){ ?>
                            <li class="<?php activemenu($menu, "catalogue");?>" id="menucatalogue" >
                                <a href="parcourir.php" ><?php echo trad('Catalogue', 'admin'); ?></a>
                            </li>
                        <?php } ?>
                        <?php	if(est_autorise("acces_contenu")){ ?>
                            <li class="<?php activemenu($menu, "contenu");?>" id="menucontenu"><a href="listdos.php"><?php echo trad('Contenu', 'admin'); ?></a></li>
                        <?php } ?>
                        <?php	if(est_autorise("acces_codespromos")){ ?>
                            <li class="<?php activemenu($menu, "paiement");?>" id="menupaiement"><a href="promo.php"><?php echo trad('Codes_promos', 'admin'); ?></a></li>
                        <?php } ?>
                        <?php	if(est_autorise("acces_configuration")){ ?>
                            <li class="<?php activemenu($menu, "configuration");?>"><a href="configuration.php"><?php echo trad('Configuration', 'admin'); ?></a></li>
                        <?php } ?>
                        <?php	if(est_autorise("acces_modules")){ ?>
                            <li class="<?php activemenu($menu, "plugins");?>" id="menuplugins"><a href="module_liste.php"><?php echo trad('Modules', 'admin'); ?></a></li>
                        <?php } ?>
                    </ul>

                </div><!--/.nav-collapse -->
            </div>

        </div>
    </div>
<div class="bg-image">
<?php
$cataloguePage = array(
    "produit_modifier",
    "rubrique_modifier",
    "contenu_modifier",
    "dossier_modifier"
)
?>
    <br/>
    <div class="container">
        <?php if(isset($breadcrumbs) && is_array($breadcrumbs)): ?>
            <ul class="breadcrumb">
                <?php foreach($breadcrumbs as $breadcrumb): ?>
                    <?php if($breadcrumb["url"] == ""): ?>
                        <li class="active">
                            <?php echo $breadcrumb["display"]; ?>
                            <?php if($breadcrumb["edit"] != ""): ?>
                                (<a href="<?php echo $breadcrumb["edit"]; ?>"><?php echo trad('editer', 'admin'); ?></a>)
                            <?php endif; ?>
                            <?php if($breadcrumb["browse"] != ""): ?>
                                (<a href="<?php echo $breadcrumb["browse"]; ?>"><?php echo trad('parcourir', 'admin'); ?></a>)
                            <?php endif; ?>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo $breadcrumb["url"] ?>"><?php echo $breadcrumb["display"]; ?></a><span class="divider">/</span></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div id="wrapper" class="container"> <!--div id="subwrapper"> -->
<div class="<?php if(preg_match("`([^\/]*).php`", $_SERVER['PHP_SELF'], $page) && in_array($page[1], $cataloguePage)) echo "catalogue"; ?>">

<?php if(ActionsAdminModules::instance()->inclure_module_admin("entete_bottom")){ ?>
    <div class="row-fluid">
        <div class="span12">
            <?php
            ActionsAdminModules::instance()->inclure_module_admin("entete_bottom");
            ?>
        </div>
    </div>
<?php } ?>