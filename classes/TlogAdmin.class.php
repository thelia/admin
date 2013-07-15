<?php

class TlogAdmin extends Tlog
{
    public $niveau;

    function __construct()
    {
    	parent::init();
    }

    private function maj_variable($nom, $valeur) {

        $variable = new Variable();

        if ($variable->charger($nom)) {
            $variable->valeur = $valeur;
            $variable->maj();
        }
        else {
            $variable->nom = $nom;
            $variable->valeur = $valeur;
            $variable->protege = 1;
            $variable->cache = 1;

            $variable->add();
        }
    }

    public function update_config()
    {
    	if (! empty($_REQUEST['fichier'])) {
            $_REQUEST[Tlog::VAR_FILES] = ltrim($_REQUEST[Tlog::VAR_FILES] . ";" . trim($_REQUEST['fichier']), ";");
        }

        foreach($_REQUEST as $var => $value)
        {
            if (! preg_match('/^tlog_/', $var)) continue;

            $this->maj_variable($var, $value);
        }

        // Mise à jour des destinations
        $actives = "";

        foreach($_REQUEST['destinations'] as $classname) {

            if (isset($_REQUEST["${classname}_actif"])) {

                $actives .= $classname . ";";

                foreach($_REQUEST as $var => $valeur) {
                    if (strpos($var, "${classname}_") !== false) {
                        $nom = str_replace("${classname}_", "", $var);

                        if ($nom == 'actif') continue;

                        $this->maj_variable($nom, $valeur);
                    }
                }
            }
        }

        $this->maj_variable(self::VAR_DESTINATIONS, rtrim($actives, ";"));

        redirige("logs.php");
    }

    public function prepare_page() {
            $this->niveau = Variable::lire(Tlog::VAR_NIVEAU, Tlog::DEFAUT_NIVEAU);
    }

    public function liste_destinations() {

        $destinations = array();

        // Charger (et instancier) toutes les destinations.
        $this->charger_classes_destinations($destinations);

        //valeurs bidons à remplacer

        return $destinations;
    }

    public function liste_destinations_actives() {
    	return explode(";", Variable::lire(self::VAR_DESTINATIONS, self::DEFAUT_DESTINATIONS));
    }
}