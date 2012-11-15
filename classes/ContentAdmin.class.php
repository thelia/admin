<?php

class ContentAdmin extends Contenu {
     
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
    
    public function delete($parent,$id = 0){
        if($id > 0){
            $this->charger_id($id);
        }

        parent::delete();
        
        redirige('listdos.php?parent='.$parent);
    }
    
    public function modifyOrder($type, $parent){
        //$this->changer_classement($this->ref, $type);
        
        redirige('listdos.php?parent='.$parent);
    }
    
    public function changeOrder($newClassement, $parent){
        $this->modifier_classement($this->id, $newClassement);
        
        redirige('listdos.php?parent='.$parent);
    }
    
    public function changeAttachementPosition($attachement, $id, $type, $lang, $tab)
    {
        $this->getAttachement($attachement)->modclassement($id, $type);
        redirige("contenu_modifier.php?id=".$this->id."&dossier=".$this->dossier."&lang=".$lang."&tab=".$tab);
    }
    
    public function deleteAttachement($attachement, $id, $lang, $tab)
    {
        $this->getAttachement($attachement)->supprimer($id);
        redirige("contenu_modifier.php?id=".$this->id."&dossier=".$this->dossier."&lang=".$lang."&tab=".$tab);
    }
    
    public function add($title, $folder)
    {
        $contentdesc = new Contenudesc();
        $contentdesc->titre = $title;
        $this->datemodif = date('Y-m-d H:i:s');
        $this->dossier = $folder;
        $contentdesc->lang = ActionsLang::instance()->get_id_langue_courante();
        
        if($error === false){
            $this->datemodif = date('Y-m-d H:i:s');
            $this->dossier = $folder;
            $this->id = parent::add();
            
            $productdesc->contenu = $this->id;
            $contentdesc->add();
            
            $contentdesc->reecrire();

            ActionsModules::instance()->appel_module("ajoutcont", $this);
            
            redirige('contenu_modifier.php?id='.$this->id.'&dossier='.$this->dossier);
        }
        else
        {
            throw new TheliaAdminException("impossible to add new folder", TheliaAdminException::CONTENT_ADD_ERROR, null, $this);
        }
        
    }

    
    public function modify($lang, $price, $price2, $ecotaxe, $promo, $folder, $new, $perso, $weight, $stock, $tva, $online, $title, $chapo, $description, $postscriptum, $urlsuiv, $rewriteurl, $caracteristique, $declinaison, $images, $documents, $tab)
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
        
        $this->datemodif = date('Y-m-d H:i:s');
        $this->prix = self::cleanPrice($price);
        $this->prix2 = self::cleanPrice($price2);
        $this->ecotaxe = self::cleanPrice($ecotaxe);
        
        $this->checkRewrite($folder);
        
        
        $this->promo = ($promo == 'on')?1:0;
        $this->nouveaute = ($new == 'on')?1:0;
        $this->ligne = ($online == 'on')?1:0;
        
        $this->perso = $perso;
        $this->poids = $weight;
        $this->checkStock($stock, $declinaison);
        $this->checkCaracteristique($caracteristique);
        $this->tva = self::cleanPrice($tva);
        
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
        
        ActionsModules::instance()->appel_module("modprod", $this);
        
        if ($urlsuiv)
        {
            redirige('listdos.php?parent='.$this->dossier);
        } else {
            redirige('contenu_modifier.php?id='.$this->id.'&dossier='.$this->dossier.'&tab='.$tab.'&lang='.$lang);
        }
        
        
    }

    protected function checkCaracteristique($caracteristique)
    {
        
        
        foreach($caracteristique as $index => $value)
        {
            $this->query("delete from $caracval->table where contenu=" . $this->id . " and caracteristique=" . $index);
            if ( is_array($value) )
            {
                foreach ($value as $caracdisp)
                {
                    $caracval = new Caracval(); 
                    $caracval->contenu = $this->id;
                    $caracval->caracteristique = $index;
                    $caracval->caracdisp = $caracdisp;
                    $caracval->add();
                }
            } else {
                $caracval = new Caracval();
                $caracval->contenu = $this->id;
                $caracval->caracteristique = $index;
                $caracval->valeur = $value;
                $caracval->add();
            }
        }
    }
    
    protected function checkStock($stock, $declinaison){
        $this->stock = $stock;
        
        $nb = 0;
        
        foreach($declinaison as $index => $value)
        {
            $stock = new Stock();
            if(! $stock->charger($index, $this->id))
            {
                $stock->declidisp = $index;
                $stock->contenu = $this->id;
                $nb += $stock->valeur = $value["stock"];
                $stock->surplus = $value["surplus"];
                $stock->add();
            } else {
               $nb +=  $stock->valeur = $value["stock"];
               $stock->surplus = $value["surplus"];
               
               $stock->maj();
            }   
        }
        
        if($nb > 0) $this->stock = $nb;
    }
    
    public function checkRewrite($folder){
        if($this->dossier != $folder) {
            $query = "select max(classement) as maxClassement from ".Contenu::TABLE." where dossier='" . $folder . "'";
            $resul = $this->query($query);
            $this->classement =  $this->get_result($resul, 0, "maxClassement") + 1;

            $param_old = Contenudesc::calculer_clef_url_reecrite($this->id, $this->dossier);
            $param_new = Contenudesc::calculer_clef_url_reecrite($this->id, $folder);

            $query_reec = "select * from ".Reecriture::TABLE." where param='&$param_old' and lang=$lang and actif=1";

            $resul_reec = $this->query($query_reec);

            while($row_reec = $this->fetch_object($resul_reec)) {

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
     * delete in caracval table all the record for this content id
     * 
     * @return boolean if id paramter is false or empty
     */
    protected function cleanCaracteristique()
    {
        if(! $this->id) return false;
        
        $query = "delete from ".Caracval::TABLE." where contenu=".$this->id;
        $this->query($query);
    }
    
    /**
     * 
     * save in stock table if needed this content with 0 to all column (valeur and surplus)
     * 
     * @return boolean if id paramter is false or empty
     */
    protected function associateDeclinaison()
    {
        if(! $this->id) return false;
        
        $query = "SELECT d.id from ".Declidisp::TABLE." d LEFT JOIN ".Rubdeclinaison::TABLE." r ON d.declinaison = r.declinaison WHERE r.dossier=".$this->dossier;
        $resul = $this->query($query);


   	while($resul && $row = $this->fetch_object($resul)){

                $stock = new Stock();
                $stock->declidisp=$row->id;
                $stock ->contenu=$this->id;
                $stock->valeur=0;
                $stock->surplus=0;
                $stock->add();
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
    
    public function getAccessoryList(){
        $return = array();
        
        $query = "select * from ".Accessoire::TABLE." where contenu=".$this->id." order by classement";
	$resul = $this->query($query);
        
        

	while($resul && $row = $this->fetch_object($resul)){
            $contenu = new Contenu();
            $contenudesc = new Contenudesc();
	
            $contenu->charger_id($row->accessoire);
            $contenudesc->charger($contenu->id);

            $rubadesc = new Dossierdesc();
            $rubadesc->charger($contenu->dossier);

            $return[] = array(
                "contenu" => $contenudesc->titre,
                "dossier" => $rubadesc->titre,
                "id" => $row->id
            );
	}
        
        return $return;
    }
}
