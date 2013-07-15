<?php

class FolderAdmin extends Dossier
{
    protected $extends = array();
    protected $oldParent;
    
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
            $this->ligne = 1;
            $this->classement = $this->getMaxRanking($parent) + 1;
            $this->id = parent::add();
            
            $dossierdesc->dossier = $this->id;
            $dossierdesc->lang = ActionsLang::instance()->get_id_langue_courante();
            $dossierdesc->chapo = '';
            $dossierdesc->description = '';
            $dossierdesc->postscriptum = '';
            $dossierdesc->id = $dossierdesc->add();
            
            $dossierdesc->reecrire();

            ActionsModules::instance()->appel_module("ajoutdos", new Dossier($this->id));
            
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
    
    public function modify($lang, $parent, $online, $title, $chapo, $description, $postscriptum, $urlsuiv, $rewriteurl, $images, $documents, $tab)
    {
        
        if($this->id == '')
        {
            throw new TheliaAdminException("Folder not found", TheliaAdminException::FOLDER_NOT_FOUND);
        }
        
        $dossierdesc = new Dossierdesc($this->id, $lang);
        
        if($dossierdesc->id == '')
        {
            CacheBase::getCache()->reset_cache();
            
            $dossierdesc->dossier = $this->id;
            $dossierdesc->lang = $lang;
            $dossierdesc->id = $dossierdesc->add();
        }

        $this->oldParent = $this->parent;
                
        
        if($this->parent != $parent){
            $this->checkOrder($parent);
        }
        $this->parent = $parent;
        
        $this->ligne = ($online == 'on')?1:0;

        
        $dossierdesc->chapo = str_replace("\n", "<br />", $chapo);
        $dossierdesc->titre = $title;
        $dossierdesc->postscriptum = $postscriptum;
        $dossierdesc->description = $description;
        
        $this->maj();
        $dossierdesc->maj();
        
        $dossierdesc->reecrire($rewriteurl);
        $this->setLang($lang);
        $this->updateImage($images);
        $this->getImageFile()->ajouter("photo", array("jpg", "gif", "png", "jpeg"), "uploadimage");
        $this->updateDocuments($documents);
        $this->getDocumentFile()->ajouter("document_", array(), "uploaddocument");
        
        ActionsModules::instance()->appel_module("moddos", $this);
        
        if ($urlsuiv)
        {
            redirige('listdos.php?parent='.$this->dossier);
        } else {
            redirige('dossier_modifier.php?id='.$this->id.'&tab='.$tab.'&lang='.$lang);
        }
        
        
    }
    
    /**
     * 
     * if folder change, order must be change in old and new folder
     * 
     * @param int $folder
     */
    public function checkOrder($parent)
    {
        //in old folder
        $this->modifier_classement($this->id, $this->getMaxRanking($this->oldParent) + 1);

        //in new folder
        $this->classement = $this->getMaxRanking($parent) + 1;        
    }
}

?>
