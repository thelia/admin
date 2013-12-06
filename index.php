<?php
	@ini_set('default_socket_timeout', 5);
        
	require_once("pre.php");
        
	require_once("../classes/Administrateur.class.php");
	session_start();
	header("Content-type: text/html; charset=utf-8");
	if(isset($action))
		if($action == "deconnexion") unset($_SESSION["util"]);
	require_once("../lib/simplepie.inc");
	require_once("../classes/Variable.class.php");

	function couperTexte($texte, $nbcar){
		if (strlen($texte) < $nbcar) return $texte;
        $res = "";
        $mots = explode(" ", $texte);
        foreach($mots as $mot) {
        	$tmp = "$res $mot";
        	if (strlen($tmp) > $nbcar) break;
        	$res = $tmp;
        }
        return $res . "...";
	}

        
        function lire_feed($url, $nb=3) {

		$feed = new SimplePie($url, '../client/cache/flux');
		$feed->init();
		$feed->handle_content_type();

		$tab = $feed->get_items();

		return (count($tab) > 0) ? array_slice($tab, 0, 3) : false;
	}

	function afficher_feed($url, $picto, $nb = 3) {

		$items = lire_feed($url, $nb);

		if ($items !== false) {
                    foreach($items as $item){
			$link = $item->get_permalink();

			$title = strip_tags($item->get_title());
			$author = strip_tags($item->get_author());
			$description = $item->get_description();
			$date = $item->get_date('d/m/Y');

			?>
                        <div class="span4">
                            <h2><?php echo($date); ?> - <?php echo $title; ?></h2>
                            <p><?php echo couperTexte($description, 250); ?></p>
                            <p><a class="btn" href="<?php echo($link); ?>" target="_blank">Lire la suite &raquo;</a></p>
                        </div>
			<?php
                    }
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
  ActionsAdminModules::instance()->inclure_module_admin("index_top");
  ?>
  <div class="loginpage">
      <div class="brandbar">
          <div class="container">
              <a class="brand" href="accueil.php">
                  v. <?php echo rtrim(preg_replace("/(.)/", "$1.", Variable::lire('version')), "."); ?>
              </a>
          </div>
      </div>
      <div id="wrapper" class="container">
          <?php
          ActionsAdminModules::instance()->inclure_module_admin("index");
          ?>
          <!-- Main hero unit for a primary marketing message or call to action -->
          <div class="hero-unit">
              <h1>Administration Thelia</h1>
              <form action="accueil.php" method="post" class="well form-inline">
                  <input type="text" class="input" placeholder="Nom d'utilisateur" name="identifiant" />
                  <input type="password" class="input" placeholder="Mot de passe" name="motdepasse" />
                  <input name="action" type="hidden" value="identifier" />
                  <button type="submit" class="btn btn-primary">Connexion &raquo;</button>
              </form>
          </div>

          <!-- Example row of columns -->
          <div class="row-fluid">
              <?php afficher_feed("http://thelia.net/Flux-rss.html?id_rubrique=8", "picto-formation.gif"); ?>
          </div>
      </div>
      <?php
      ActionsAdminModules::instance()->inclure_module_admin("index_bottom");
      ?>
      <?php require_once("pied.php");?>
  </body>
</html>