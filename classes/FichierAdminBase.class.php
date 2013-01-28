<?php
abstract class FichierAdminBase {

	protected $typeobjet;
	protected $idobjet;
	protected $class;
	protected $classdesc;
	protected $lang = false;
	protected $nompageadmin;


	protected $nombre_champs_upload;

	public function __construct($class, $nombre_champs_upload, $typeobjet, $idobjet) {

            $this->typeobjet = $typeobjet;
            $this->idobjet = $idobjet;

            $this->class = ucfirst(strtolower($class));
            $this->classdesc = ucfirst(strtolower($class)."desc");

            $this->nombre_champs_upload = $nombre_champs_upload;
        }
        
        public function setLang($lang){
            $this->lang = $lang;
        }
        
        public function getNumberUpload()
        {
            return $this->nombre_champs_upload;
        }


	protected abstract function chemin_objet($fichier);
        
        public function getList($lang = false)
        {
            if($lang !== false) $this->lang = $lang;
            $return = array();
            
            $obj = new $this->class();
            
            $query = "SELECT o.id, o.classement, o.fichier from ".$obj->table.' o where o.'.$this->typeobjet.' = '.$this->idobjet." order by o.classement";
          
            
            foreach($obj->query_liste($query) as $row)
            {
                $objdesc = new $this->classdesc($row->id, $this->lang);
                
                $return[] = array(
                    "id" => $row->id,
                    "classement" => $row->classement,
                    "titre" => $objdesc->titre,
                    "chapo" => $objdesc->chapo,
                    "description" => $objdesc->description,
                    "fichier" => $this->chemin_objet($row->fichier),
                    "nomFichier" => $row->fichier
                );
            }
            
            return $return;
        }

        public function modclassement($id, $type){
		$obj = new $this->class();
                
		if ($obj->charger($id))
                {
                    if(is_numeric($type))
                        $obj->modifier_classement($id, $type);
                    else
                        $obj->changer_classement($id, $type);
                }
	}

	public function supprimer($id)
        {
		$obj = new $this->class();

		if ($obj->charger($id))
                {

			if(file_exists($this->chemin_objet($obj->fichier)))
				unlink($this->chemin_objet($obj->fichier));

			$obj->delete();
		}
	}

	public function modifier($id, $titre, $chapo, $description, $rank)
        {
            $obj = new $this->class();
            $obj->charger($id);
            $obj->classement = $rank;
            $obj->maj();
            
            $objdesc = new $this->classdesc();

            $colonne = strtolower($this->class);
            $objdesc->$colonne = $id;

            $objdesc->lang = $this->lang;

            $objdesc->charger($id,$this->lang);

            $objdesc->titre = $titre;
            $objdesc->chapo = $chapo;
            $objdesc->description = $description;
            
            $objdesc->lang = $this->lang;
            $objdesc->$colonne = $id;

            if(!$objdesc->id)
                $objdesc->add();
            else
                $objdesc->maj();

	}
        
        public function cleanRanking()
        {
            $obj = new $this->class();
            $qCleanRanking = "SELECT * FROM $obj->table WHERE $this->typeobjet='$this->idobjet' ORDER BY classement ASC";
            foreach($obj->query_liste($qCleanRanking, $this->class) as $key => $aCleanRanking)
            {
                $aCleanRanking->classement = $key+1;
                $aCleanRanking->maj();
            }
        }
        
        public function compter()
        {
            $obj = new $this->class();
            $qCount = "SELECT count(id) AS count FROM " . $obj->table . " WHERE $this->typeobjet='$this->idobjet'";
            
            return $obj->get_result($obj->query($qCount), 0, 'count');
        }
        
        public function ajouter($nom_arg, $extensions_valides = array(), $point_d_entree)
        {
		for($i = 1; $i <= $this->nombre_champs_upload; $i++)
                {
                    $fichier = $_FILES[$nom_arg . $i]['tmp_name'];
                    $nom = $_FILES[$nom_arg . $i]['name'];

                    if ($fichier != "") {

                        $dot = strrpos($nom, '.');

                        if ($dot !== false) {

                               $fich = substr($nom, 0, $dot);
                               $extension = substr($nom, $dot+1);

                               if ($fich != "" && $extension != "" && (empty($extensions) || (in_array($extension, $extensions_valides))) ) {

                                       $obj = new $this->class();

                                       $colonne = $this->typeobjet;
                                       $obj->$colonne = $this->idobjet;

                                       $query = "select max(classement) as maxClassement from $obj->table where $this->typeobjet='" . $this->idobjet . "'";

                                       $resul = $obj->query($query);
                                       $maxClassement = $obj->get_result($resul, 0, "maxClassement");

                                       $obj->classement = $maxClassement + 1;

                                       $lastid = $obj->add();

                                       $obj->charger($lastid);
                                       $obj->fichier = eregfic(sprintf("%s_%s", $fich, $lastid)) . "." . $extension;
                                       $obj->maj();

                                       copy($fichier, $this->chemin_objet($obj->fichier));

                                       ActionsModules::instance()->appel_module($point_d_entree, $obj);
                               }
                        }
                    }
		}

	}
}
?>