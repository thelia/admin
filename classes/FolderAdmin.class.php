<?php

class FolderAdmin extends Dossier
{
    protected $extends = array();
    
    public function __call($name, $arguments) {
        $find = false;
        foreach($this->extends as $extend)
        {
            if(method_exists($extend, $name))
            {
                $find = true;
                return call_user_func_array(array($extend, $name), $arguments);
            }
        }
        
        if($find === false)
        {
            return parent::__callStatic($name, $arguments);
            throw new BadMethodCallException("Method ".$name." not found in " . __CLASS__);
        }
    }
    
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id){
            $this->charger_id($id);
        }
        
        $this->extends[] = new AttachementAdmin();
        
        $this->setAttachement("image", new ImageFile('dossier', $this->id));
        $this->setAttachement("document", new DocumentFile('dossier', $this->id));
    }

    /**
     * 
     * @return ActionsAdminFolder
     */
    public static function getInstance($id = 0){
        return new FolderAdmin($id);        
    }

    public function delete()
    {
        if($this->id > 0)
        {
            parent::delete();
        }
        else
        {
            throw new TheliaAdminException("Folder does not Exist",  TheliaAdminException::FOLDER_NOT_FOUND);
        }
        
        redirige("listdos.php?parent=" . $this->parent);
    }
    
    public function display($display){
        $this->ligne = ($display == 'true')?1:0;        
        $this->maj();
    }
    
    public function add($title, $parent)
    {
        $dossierdesc = new Dossierdesc();
        $dossierdesc->titre = $title;
        
        if($dossierdesc->titre !== '')
        {
            if(!is_numeric($parent) && $parent<1)
                $parent = 0;
            
            $this->parent = $parent;
            $this->ligne = 0;
            $this->classement = $this->getMaxRanking($parent) + 1;
            $this->id = parent::add();
            
            $dossierdesc->dossier = $this->id;
            $dossierdesc->lang = ActionsLang::instance()->get_id_langue_courante();
            $dossierdesc->chapo = '';
            $dossierdesc->description = '';
            $dossierdesc->postscriptum = '';
            $dossierdesc->id = $dossierdesc->add();
            
            $dossierdesc->reecrire();

            ActionsModules::instance()->appel_module("ajoutdos", $dossierdesc);
            
            redirige("dossier_modifier.php?id=" . $this->id);
        }
        else
        {
            throw new TheliaAdminException("impossible to add new folder", TheliaAdminException::FOLDER_ADD_ERROR, null, $dossierdesc);
        }
    }
    
    public function getMaxRanking($parent)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE parent='$parent'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
    
    public function modifyOrder($type, $parent){
        $this->changer_classement($this->id, $type);
        
        redirige('listdos.php?parent='.$parent);
    }
    
    public function changeOrder($newClassement, $parent){
        $this->modifier_classement($this->id, $newClassement);
        
        redirige('listdos.php?parent='.$parent);
    }

    public function getList($parent, $critere, $order, $alpha)
    {
        $return = array();
        
	$dossierdesc = new Dossierdesc();

	if($alpha == "alpha"){
		$query = "select r.id, r.ligne, r.classement from ".Dossier::TABLE." r LEFT JOIN ".Dossierdesc::TABLE." rd ON rd.dossier=r.id and lang=" . ActionsLang::instance()->get_id_langue_courante() . " where r.parent=\"$parent\" order by rd.$critere $order";
	}else{
		$query = "select id, ligne, classement from ".Dossier::TABLE." where parent=\"$parent\" order by $critere $order";
	}

	$resul = $this->query($query);

	$i=0;

	while($resul && $row = $this->fetch_object($resul)){
		$dossierdesc = new Dossierdesc();
		$dossierdesc->charger($row->id);

		if (! $dossierdesc->affichage_back_office_permis()) continue;

		$return[] = array(
                        "id" => $row->id,
                        "ligne" => $row->ligne,
                        "classement" => $row->classement,
                        "titre" => $dossierdesc->titre,
                        "langue_courante" => $dossierdesc->est_langue_courante(),
                        "parent" => $parent
                    );

	}
        
        return $return;
    }
    
    public function getBreadcrumbList($parent){
        $tab = array_reverse(chemin_dos($parent));
        
        if($tab[0]->id == '') return array();
        
        return $tab;
    }
    
    public function editInformation($ligne, $parent, $lien)
    {
        if(!$this->id)
            return;
        
        if($parent != $this->parent)
        {
            $folderIsMovedInItselfOrASubfolder = 0;
            $test = chemin_rub($parent);
            for($i = 0; $i < count($test); $i++)
            {
                if($test[$i]->dossier == $this->id)
                {
                    $folderIsMovedInItselfOrASubfolder = 1;
                    break;
                }
            }
            
            if(!$folderIsMovedInItselfOrASubfolder)
            {
                $qUpdateClassement = "SELECT * FROM " . Dossier::TABLE . " WHERE parent='$this->parent' AND id<>'$this->id' ORDER BY classement ASC";
                $rUpdateClassement = $this->query($qUpdateClassement);

                $newClassement = 1;
                while($rUpdateClassement && $theFolder = $this->fetch_object($rUpdateClassement, 'Dossier'))
                {
                    $theFolder->classement = $newClassement;
                    $theFolder->maj();
                    $newClassement++;
                }
            
                $this->classement = $this->getMaxRanking($parent) + 1;
                $this->parent = $parent;
            }
        }
        
        $this->lien = $lien;
        $this->ligne = ($ligne=='on')?1:0;

        parent::maj();
        
        ActionsModules::instance()->appel_module("modrub", $this);
        
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=sectionInformationTab');
    }
    
    public function editDescription($langId, $titre, $chapo, $description, $postscriptum, $url)
    {
        $lang = new Lang($langId);
        
        if(!$this->id || !$lang->id)
            return;
        
        $dossierdesc = new Dossierdesc();
        if(!$dossierdesc->charger($this->id, $lang->id))
        {
            CacheBase::getCache()->reset_cache();
            $dossierdesc->dossier = $this->id;
            $dossierdesc->lang = $lang->id;
            $dossierdesc->id = $dossierdesc->add();
        }
        
        $dossierdesc->titre = $titre;
        $dossierdesc->chapo = $chapo;
        $dossierdesc->description = $description;
        $dossierdesc->postscriptum = $postscriptum;
        $dossierdesc->maj();
        $dossierdesc->reecrire(($url)?:$lang->code . "-" . $dossierdesc->dossier . "-" . $dossierdesc->titre . ".html");
        
        ActionsModules::instance()->appel_module("modrub", $this);
        
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=generalDescriptionTab&lang=' . $lang->id);
    }

}

?>
