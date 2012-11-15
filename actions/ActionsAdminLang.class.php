<?php
/**
 * Administration des langues depuis le back office
 *
 * Ce singleton permet de gérer la manipulation des langues depuis l'admin Thelia.
 *
 * @author Franck Allimant <franck@cqfdev.fr>
 * @version $Id$
 */

class ActionsAdminLang extends ActionsLang {

	private static $instance = false;

	private function __construct() {
		parent::__construct();
	}

	/**
	 * Cette classe est un singleton
	 * @return ActionsAdminLang une instance de ActionsAdminLang
	 */
	public static function instance() {
		if (self::$instance === false) self::$instance = new ActionsAdminLang();

		return self::$instance;
	}

	public function maj_parametres($un_domaine_par_langue, $action_si_trad_absente, $urlsite) {

		Variable::ecrire(self::VAR_UN_DOMAINE_PAR_LANGUE, intval($un_domaine_par_langue));

		Variable::ecrire(self::VAR_ACTION_SI_TRAD_ABSENTE, intval($action_si_trad_absente));

		if ($un_domaine_par_langue == 0) {

			Variable::ecrire('urlsite', rtrim($urlsite, "/"));
		}
	}

	/**
	 * Modifier une langue existante
	 *
	 */
	public function modifier($id, $description, $code, $url, $defaut) {

		$lang = new Lang();

		if ($lang->charger_id($id)) {

			$lang->description = trim($description);
			$lang->code = strtolower(trim($code));
			$lang->defaut = $defaut;

			if ($this->get_un_domaine_par_langue() == 1) {

				$lang->url = rtrim($url, "/");

				// Compatibilité ascendante: urlsite contient l'url du site par defaut.
				if ($defaut) Variable::ecrire('urlsite', $lang->url);
			}

			$lang->maj();

			ActionsModules::instance()->appel_module("modlangue", $lang);
		}
	}

	/**
	 * Ajouter une nouvelle langue
	 *
	 *
	 */
	public function ajouter($description, $code, $url) {
		$lang = new Lang();

		$lang->description = trim($description);
		$lang->code = strtolower(trim($code));
		$lang->defaut = 0;

		if ($this->get_un_domaine_par_langue() == 1) {
			$lang->url = rtrim($url, "/");
		}

		$lang->add();

		ActionsModules::instance()->appel_module("ajoutlangue", $langue);
	}

	/**
	 * Supprimer une langue existante
	 */
	public function supprimer($id) {
		$langue = new Lang();

		if ($langue->charger_id($id)) {
			$langue->delete();

			ActionsModules::instance()->appel_module("supplangue", $langue);
		}
	}

	/**
	 * Retourne une liste des langues
	 */
	public function lister() {

		$langue = new Lang();

		return $langue->query_liste("select * from $langue->table", "Lang");
	}
}
?>