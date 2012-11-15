<?php

class FolderAdmin extends Dossier
{
    protected $imageFile;
    protected $documentFile;
    
    public function __construct($id = 0)
    {
        parent::__construct($id);
        
        $this->setImageFile(new ImageFile('dossier', $this->id));
        $this->setDocumentFile(new DocumentFile('dossier', $this->id));
    }

    /**
     * 
     * @return ActionsAdminFolder
     */
    public static function getInstance($id = 0){
        return new FolderAdmin($id);        
    }
    
    public function setImageFile(FichierAdminBase $imageFile)
    {
        $this->setAttachement('image', $imageFile);
    }
    
    public function setDocumentFile(FichierAdminBase $documentFile)
    {
        $this->setAttachement('document', $documentFile);
    }
    
    protected function setAttachement($attachement, $file)
    {
        $property = $this->getProperty($attachement);
        $this->$property = $file;
    }
    
    protected function getProperty($attachement)
    {
        return $attachement."File";
    }
    
    /**
     * 
     * @return ImageFile
     */
    public function getImageFile()
    {
        return $this->getAttachement("image");
    }
    
    /**
     * 
     * @return DocumentFile
     */
    public function getDocumentFile()
    {
        return $this->getAttachement("document");
    }
    
    protected function getAttachement($attachement)
    {
        $property = $this->getProperty($attachement);
        if(property_exists($this, $property) !== true)
        {
            throw new TheliaAdminException("Attachement file does not Exist",  TheliaAdminException::ATTACHEMENT_NOT_FOUND);
        }
        
        return $this->$property;
    }
    
    public function setLang($lang)
    {
        $this->imageFile->setLang($lang);
        $this->documentFile->setLang($lang);
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
    
    public function getImageList($lang = false)
    {
        return $this->getAttachementList('image', $lang);
    }
    
    public function getDocumentList($lang = false)
    {
        return $this->getAttachementList('document', $lang);
    }
    
    protected function getAttachementList($attachement, $lang = false)
    {
        return $this->getAttachement($attachement)->getList($lang);
    }
    
    public function getBreadcrumbList($parent){
        $tab = array_reverse(chemin_dos($parent));
        
        if($tab[0]->id == '') return array();
        
        return $tab;
    }
    
    public function getListAssociatedFeature()
    {
        $return = array();

        if(!$this->id)
            return $return;
        
        $associatedFeature = new Rubcaracteristique();
	$qList = "SELECT * FROM " . Rubcaracteristique::TABLE . " WHERE dossier='$this->id'";
	$rList = $associatedFeature->query($qList);
	while($rList && $theAssociatedFeature = $associatedFeature->fetch_object($rList))
        {
            $featureDescription = new Caracteristiquedesc($theAssociatedFeature->caracteristique);
	
            $return[] = array("id" => $theAssociatedFeature->id, "feature" => $featureDescription->titre);
	}

        return $return;
    }
    
    public function addPicture()
    {
        $this->addAttachement('image', 'photo', array("jpg", "gif", "png", "jpeg"), "uploadimage");
        
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab');
    }
    
    public function addDocument()
    {
        $this->addAttachement('document', 'doc', array(), "uploaddocument");
        
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab&tabAttachement=documentAttachementTab');
    }
    
    protected function addAttachement($attachement, $nom_arg, $extensions_valides = array(), $point_d_entree= null)
    {
        $this->getAttachement($attachement)->ajouter($nom_arg, $extensions_valides, $point_d_entree);
        
        ActionsModules::instance()->appel_module("modrub", $this);
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
    
    public function getNumberOfImages()
    {
        return $this->getNumberOfAttachements('image');
    }
    
    public function getNumberOfDocuments()
    {
        return $this->getNumberOfAttachements('document');
    }
    
    protected function getNumberOfAttachements($attachement)
    {
        return $this->getAttachement($attachement)->compter();
    }
    
    public function updateImage(array $images, $lang)
    {
        $this->updateAttachement('image', $images, $lang);
        
         redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab&lang=' . $lang . '#editPicturesAnchor');
    }
    
    public function updateDocument(array $documents, $lang)
    {
        $this->updateAttachement('document', $documents, $lang);

        redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab&tabAttachement=documentAttachementTab&lang=' . $lang . '#editDocumentsAnchor');
    }
    
    protected function updateAttachement($attachement, $files, $lang)
    {
        if($this->id == '')
        {
            throw new TheliaAdminException("Folder not found", TheliaAdminException::FOLDER_NOT_FOUND);
        }
        
        $this->setLang($lang);
        
        foreach($files as $index => $file)
        {
            $this->getAttachement($attachement)->modifier($index, $file["titre"], $file["chapo"], $file["description"]);
        }
        ActionsModules::instance()->appel_module('modrub', $this);
    }
    
    public function deleteImage($id, $lang)
    {
        $this->deleteAttachement("image", $id);
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab&lang=' . $lang . '#editPicturesAnchor');
    }
    
    public function deleteDocument($id, $lang)
    {
        $this->deleteAttachement("document", $id);
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab&tabAttachement=documentAttachementTab&lang=' . $lang . '#editDocumentsAnchor');
    }
    
    public function deleteAttachement($attachement, $id)
    {
        $this->getAttachement($attachement)->supprimer($id);
        ActionsModules::instance()->appel_module('modrub', $this);
    }
    
    public function modifyImageOrder($id, $will, $lang)
    {
        $this->modifyAttachementOrder("image", $id, $will);
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab&lang=' . $lang . '#editPicturesAnchor');
    }
    
    public function modifyDocumentOrder($id, $will, $lang)
    {
        $this->modifyAttachementOrder("document", $id, $will);
        redirige('dossier_modifier.php?id=' . $this->id . '&tab=attachementTab&tabAttachement=documentAttachementTab&lang=' . $lang . '#editDocumentsAnchor');
    }
    
    public function modifyAttachementOrder($attachement, $id, $will)
    {
        $this->getAttachement($attachement)->modclassement($id, $will);
        ActionsModules::instance()->appel_module('modrub', $this);
    }
}

?>
