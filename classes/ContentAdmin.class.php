<?php

class ContentAdmin extends Contenu {
     
    protected $extends = array();
    
    protected $oldFolder = 0;
    
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
        
        if($id > 0){
            $this->charger_id($id);
        }
        
        $this->extends[] = new AttachementAdmin();
        
        $this->setAttachement("image", new ImageFile('contenu', $this->id));
        $this->setAttachement("document", new DocumentFile('contenu', $this->id));
    }
    
    
    /**
     * 
     * @param string $id
     * @return \ContentAdmin
     */
    public static function getInstance($id = 0){
        return new ContentAdmin($id);
    }

    public function changeColumn($column, $value){
        $this->$column = $value;
        $this->maj();
    }
    
    public function delete()
    {
        if($this->id > 0)
        {
            $contenu = new Contenu($this->id);
            $contenu->delete();

            ActionsModules::instance()->appel_module("supcont", $contenu);
        }
        else
        {
            throw new TheliaAdminException("Content does not Exist",  TheliaAdminException::CONTENT_NOT_FOUND);
        }
        
        redirige("listdos.php?parent=" . $this->dossier);
    }
    
    public function changeOrder($newClassement, $parent){
        $this->modifier_classement($this->id, $newClassement);
        
        redirige('listdos.php?parent='.$parent);
    }
    
    public function modifyOrder($type, $parent){
        $this->changer_classement($this->id, $type);
        
        redirige('listdos.php?parent='.$parent);
    }
    
    public function add($title, $folder)
    {
        $contentdesc = new Contenudesc();
        $contentdesc->titre = $title;
        
        if($contentdesc->titre !== '')
        {
            $this->datemodif = date('Y-m-d H:i:s');
            $this->dossier = $folder;
            $this->ligne = 1;
            $this->classement = $this->getMaxRanking($folder) + 1;
            $this->id = parent::add();
            
            $contentdesc->lang = ActionsLang::instance()->get_id_langue_courante();
            $contentdesc->contenu = $this->id;
            $contentdesc->id = $contentdesc->add();
            
            $contentdesc->reecrire();

            ActionsModules::instance()->appel_module("ajoutcont", new Contenu($this->id));
            
            redirige('contenu_modifier.php?id='.$this->id.'&dossier='.$this->dossier);
        }
        else
        {
            throw new TheliaAdminException("impossible to add new content", TheliaAdminException::CONTENT_ADD_ERROR, null, $contentdesc);
        }
        
    }
    
    public function getMaxRanking($parent)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE dossier='$parent'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
    
    public function modify($lang, $folder, $online, $title, $chapo, $description, $postscriptum, $urlsuiv, $rewriteurl, $images, $documents, $tab)
    {
        if($this->id == '')
        {
            throw new TheliaAdminException("Content not found", TheliaAdminException::CONTENT_NOT_FOUND);
        }
        
        $contenudesc = new Contenudesc($this->id, $lang);
        
        if($contenudesc->id == '')
        {
            CacheBase::getCache()->reset_cache();
            
            $contenudesc->contenu = $this->id;
            $contenudesc->lang = $lang;
            $contenudesc->id = $contenudesc->add();
        }
        
        $this->oldFolder = $this->dossier;
        
        $this->datemodif = date('Y-m-d H:i:s');
        
        
        if($this->dossier != $folder){
            $this->checkRewrite($folder);
            $this->checkOrder($folder);
        }
        
        $this->ligne = ($online == 'on')?1:0;

        
        $contenudesc->chapo = str_replace("\n", "<br />", $chapo);
        $contenudesc->titre = $title;
        $contenudesc->postscriptum = $postscriptum;
        $contenudesc->description = $description;
        
        $this->maj();
        $contenudesc->maj();
        
        $contenudesc->reecrire($rewriteurl);
        $this->setLang($lang);
        $this->updateImage($images);
        $this->getImageFile()->ajouter("photo", array("jpg", "gif", "png", "jpeg"), "uploadimage");
        $this->updateDocuments($documents);
        $this->getDocumentFile()->ajouter("document_", array(), "uploaddocument");
        
        ActionsModules::instance()->appel_module("modcont", new Contenu($this->id));
        
        if ($urlsuiv)
        {
            redirige('listdos.php?parent='.$this->dossier);
        } else {
            redirige('contenu_modifier.php?id='.$this->id.'&dossier='.$this->dossier.'&tab='.$tab.'&lang='.$lang);
        }
        
        
    }
    
    /**
     * 
     * if folder change, order must be change in old and new folder
     * 
     * @param int $folder
     */
    public function checkOrder($folder)
    {
        //in old folder
        $this->modifier_classement($this->id, $this->getMaxRanking($this->oldFolder) + 1);
        $this->dossier = $folder;
        //in new folder
        $this->classement = $this->getMaxRanking($folder) + 1;        
    }
    
    /**
     * 
     * if folder change, the rewriting must be check
     * 
     * @param int $folder
     */
    public function checkRewrite($folder){
        if($this->dossier != $folder) {
            
            $param_old = Contenudesc::calculer_clef_url_reecrite($this->id, $this->oldFolder);
            $param_new = Contenudesc::calculer_clef_url_reecrite($this->id, $folder);

            //$query_reec = "select * from ".Reecriture::TABLE." where param='&$param_old' and lang=$lang and actif=1";
            /* @author etienne
             * We need to edit params for all rewriting rules since :
             *  - inactive rules are redirected to new url based on these params
             *  - params are the same no matter the lang
             */
            $query_reec = "select * from ".Reecriture::TABLE." where param='&$param_old'";

            $resul_reec = $this->query($query_reec);
            while($resul_reec && $row_reec = $this->fetch_object($resul_reec)) {

                $tmpreec = new Reecriture();
                $tmpreec->charger_id($row_reec->id);
                $tmpreec->param = "&$param_new";
                $tmpreec->maj();
            }
            
            $this->dossier = $folder;
        }
    }
    
    
    /**
     * 
     * Return an array of content for the current folder
     * 
     * @param string $dossier id of the current folder
     * @param string $critere order by clause
     * @param string $order ASC or DESC
     * @param string $alpha if order is alpha pu "alpha"
     * @return Array
     */
    public function getList($dossier, $critere, $order, $alpha) {
        
        $return = array();

	if($alpha == "alpha"){
		$query = "select c.id, c.dossier, c.ligne, c.classement from ".Contenu::TABLE." p LEFT JOIN ".Contenudesc::TABLE." pd ON cd.contenu = c.id and lang="  . ActionsLang::instance()->get_id_langue_courante() . " where c.dossier=\"$dossier\" order by cd.$critere $order";
	}else{
		$query = "select id, dossier, ligne, classement from ".Contenu::TABLE." where dossier=\"$dossier\" order by $critere $order";
	}
                
	$resul = $this->query($query);
	$i=0;
	while($resul && $row = $this->fetch_object($resul)){


		$contenudesc = new Contenudesc();
		$contenudesc->charger($row->id);
                
		if (! $contenudesc->affichage_back_office_permis()) continue;


		$image = new Image();
		$query_image = "select * from ".Image::TABLE." where contenu=\"" . $row->id . "\" order by classement limit 0,1";
		$resul_image = $image->query($query_image);
		$row_image = $image->fetch_object($resul_image, 'image');
                
                $return[] = array(
                    "id" => $row->id,
                    "dossier" => $row->dossier,
                    "ligne" => $row->ligne,
                    "classement" => $row->classement,
                    "titre" => $contenudesc->titre,
                    "langue_courante" => $contenudesc->est_langue_courante(),
                    "image" => array(
                        "fichier" => $row_image->fichier
                    )
                );
	}
                
        return $return;
    }
    
    public function getSearchList($searchTerm)
    {   
        $searchTerm = $this->escape_string(trim($searchTerm));
        
        $return = array();
        
        if($searchTerm==='')
            return $return;
	
        $query = "SELECT c.id, c.dossier, c.ligne, cd.titre
            FROM " . Contenu::TABLE . " c
                LEFT JOIN " . Contenudesc::TABLE . " cd
                    ON c.id=cd.contenu  AND cd.lang='" . ActionsLang::instance()->get_id_langue_courante() . "'
            WHERE cd.titre LIKE '%$searchTerm%'
                OR cd.description LIKE '%$searchTerm%'";
                
	$resul = $this->query($query);
	while($resul && $row = $this->fetch_object($resul))
        {
            $return[] = array(
                "id" => $row->id,
                "dossier" => $row->dossier,
                "ligne" => $row->ligne,
                "titre" => $row->titre
                );
	}
                
        return $return;
    }
}
