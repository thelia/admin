<?php
function activemenu($menu,$verif){
        if($menu==$verif){
                print 'active';
        }
}

$statAdmin = new StatAdmin();
?>

<div class="topbar">
    <div class="container">
  <p>Thelia Version <?php echo rtrim(preg_replace("/(.)/", "$1.", Variable::lire('version')), "."); ?></p>
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
    </div>
</div>
	<div class="brandbar">
            <div class="container">
		<a class="brand" href="accueil.php"><img src="img/logo-thelia-34px.png" alt="THELIA solution e-commerce"/></a>
                <?php if(isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <ul class="breadcrumb">
                    <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <?php if($breadcrumb["url"] == ""): ?>
                            <li class="active">
                                <?php echo $breadcrumb["display"]; ?>
                                <?php if($breadcrumb["edit"] != ""): ?>
                                    (<a href="<?php echo $breadcrumb["edit"]; ?>"><?php echo trad('editer', 'admin'); ?></a>)
                                <?php endif; ?>
                            </li>
                        <?php else: ?>
                            <li><a href="<?php echo $breadcrumb["url"] ?>"><?php echo $breadcrumb["display"]; ?></a><span class="divider">/</span></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
		<dl class="Blocmoncompte">
                    <dt><?php echo($_SESSION["util"]->prenom); ?> <?php echo($_SESSION["util"]->nom); ?></dt>
                    <dt class="deconnexion"><a href="index.php?action=deconnexion" ><?php echo trad('Deconnexion', 'admin'); ?></a></dt>
                </dl>
            </div>
	</div>
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
                                                                    <li role="menuitem"><a data-target="commande.php" href="#">Toutes <span class="badge badge-important"><?php echo $statAdmin->getNbCommand(); ?></span></a></li>
                                                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::NONPAYE; ?>" href="#">Non Pay&eacute;es <span class="badge badge-important"><?php echo $statAdmin->getNbCommandToPaid(); ?></span></a></li>
                                                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::NONPAYE; ?>" href="#">Pay&eacute;es <span class="badge badge-important"><?php echo $statAdmin->getNbCommandPaid(); ?></span></a></li>
                                                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::TRAITEMENT; ?>" href="#">En cours de traitement <span class="badge badge-warning"><?php echo $statAdmin->getNbCommandProcessed(); ?></span></a></li>
                                                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::EXPEDIE; ?>" href="#">Exp&eacute;di&eacute;es <span class="badge badge-warning"><?php echo $statAdmin->getNbCommandSend(); ?></span></a></li>
                                                                    <li role="menuitem"><a data-target="commande.php?statut=<?php echo Commande::EXPEDIE; ?>" href="#">Annul&eacute;es <span class="badge badge-warning"><?php echo $statAdmin->getNbCommandCanceled(); ?></span></a></li>
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
							<li class="dropdown <?php activemenu($menu, "configuration");?>" data-toggle="dropdown" id="menuconfiguration">
                                                            <a href="#" data-toggle="dropdown"><?php echo trad('Configuration', 'admin'); ?><span class="caret"></span></a>
                                                            <ul class="dropdown-menu config_menu" role="menu">
                                                                <li class="dropdown-submenu" role="menuitem">
                                                                    <a href="#"><?php echo trad('GESTION_CATALOGUE_PRODUIT', 'admin'); ?></a>
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_caracteristiques', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_declinaison', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="message.php" href="#"><?php echo trad('Gestion_messages', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="devise.php" href="#"><?php echo trad('Gestion_devises', 'admin'); ?></a></li>
                                                                    </ul>
                                                                </li>
                                                                <li class="dropdown-submenu" role="menuitem">
                                                                    <a href="#"><?php echo trad('GESTION_TRANSPORTS_LIVRAISONS', 'admin'); ?></a>
                                                                    <ul class="dropdown-menu" role="menu" >
                                                                        <li role="menuitem"><a data-target="pays.php" href="#"><?php echo trad('Gestion des pays', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_transport', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_zones_livraison', 'admin'); ?></a></li>
                                                                    </ul>
                                                                </li>
                                                                <li class="dropdown-submenu" role="menuitem">
                                                                    <a href="#"><?php echo trad('PARAMETRES_SYSTEME', 'admin'); ?></a>
                                                                    <ul class="dropdown-menu" role="menu" >
                                                                        <li role="menuitem"><a data-target="plugins.php" href="#"><?php echo trad('Activation_plugins', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="variable.php" href="#"><?php echo trad('Gestion_variables', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_administrateurs', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_cache', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_log', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_droit', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_htmlpurifier', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="smtp.php" href="#"><?php echo trad('Gestion_mail', 'admin'); ?></a></li>
                                                                        <li role="menuitem"><a data-target="#" href="#"><?php echo trad('Gestion_langue', 'admin'); ?></a></li>
                                                                    </ul>
                                                                </li>
                                                            </ul>
                                                        </li>
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
        <div id="wrapper" class="container <?php if(preg_match("`([^\/]*).php`", $_SERVER['PHP_SELF'], $page) && in_array($page[1], $cataloguePage)) echo "catalogue"; ?>"> <!--div id="subwrapper"> -->