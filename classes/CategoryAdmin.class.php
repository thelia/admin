<?php

class CategoryAdmin extends Rubrique
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
        
        $this->setAttachement("image", new ImageFile('rubrique', $this->id));
        $this->setAttachement("document", new DocumentFile('rubrique', $this->id));
    }

    /**
     * 
     * @return ActionsAdminCategory
     */
    public static function getInstance($id = 0){
        return new CategoryAdmin($id);        
    }

    public function delete()
    {
        if($this->id > 0)
        {
            $rubrique = new Rubrique($this->id);
            $rubrique->delete();

            ActionsModules::instance()->appel_module("suprub", $rubrique);
        }
        else
        {
            throw new TheliaAdminException("Category does not Exist",  TheliaAdminException::CATEGORY_NOT_FOUND);
        }
        
        redirige("parcourir.php?parent=" . $this->parent);
    }
    
    public function display($display){
        $this->ligne = ($display == 'true')?1:0;        
        $this->maj();
    }
    
    public function add($title, $parent)
    {
        $rubriquedesc = new Rubriquedesc();
        $rubriquedesc->titre = $title;
        
        if($rubriquedesc->titre !== '')
        {
            if(!is_numeric($parent) && $parent<1)
                $parent = 0;
            
            $this->parent = $parent;
            $this->ligne = 1;
            $this->classement = $this->getMaxRanking($parent) + 1;
            $this->id = parent::add();
            
            $rubriquedesc->rubrique = $this->id;
            $rubriquedesc->lang = ActionsLang::instance()->get_id_langue_courante();
            $rubriquedesc->chapo = '';
            $rubriquedesc->description = '';
            $rubriquedesc->postscriptum = '';
            $rubriquedesc->id = $rubriquedesc->add();
            
            $caracteristique = new Caracteristique();
            $qCarac= "select * from $caracteristique->table";
            $rCarac = $caracteristique->query($qCarac);
            while($rCarac && $theCarac = $caracteristique->fetch_object($rCarac))
            {
                $rubcaracteristique = new Rubcaracteristique();
                $rubcaracteristique->rubrique = $this->id;
                $rubcaracteristique->caracteristique = $theCarac->id;
                $rubcaracteristique->add();
            }
            
            $rubriquedesc->reecrire();

            ActionsModules::instance()->appel_module("ajoutrub", new Rubrique($this->id));
            
            redirige("rubrique_modifier.php?id=" . $this->id);
        }
        else
        {
            throw new TheliaAdminException("impossible to add new category", TheliaAdminException::CATEGORY_ADD_ERROR, null, $rubriquedesc);
        }
    }
    
    public function getMaxRanking($parent)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE parent='$parent'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
    
    public function modifyOrder($type, $parent){
        $this->changer_classement($this->id, $type);
        
        redirige('parcourir.php?parent='.$parent);
    }
    
    public function changeOrder($newClassement, $parent){
        $this->modifier_classement($this->id, $newClassement);
        
        redirige('parcourir.php?parent='.$parent);
    }

    public function getList($parent, $critere, $order, $alpha)
    {
        $return = array();
        
	$rubriquedesc = new Rubriquedesc();

	if($alpha == "alpha"){
		$query = "select r.id, r.ligne, r.classement from ".Rubrique::TABLE." r LEFT JOIN ".Rubriquedesc::TABLE." rd ON rd.rubrique=r.id and lang=" . ActionsLang::instance()->get_id_langue_courante() . " where r.parent=\"$parent\" order by rd.$critere $order";
	}else{
		$query = "select id, ligne, classement from ".Rubrique::TABLE." where parent=\"$parent\" order by $critere $order";
	}

	$resul = $this->query($query);

	$i=0;

	while($resul && $row = $this->fetch_object($resul)){
		$rubriquedesc = new Rubriquedesc();
		$rubriquedesc->charger($row->id);

		if (! $rubriquedesc->affichage_back_office_permis()) continue;

		$return[] = array(
                        "id" => $row->id,
                        "ligne" => $row->ligne,
                        "classement" => $row->classement,
                        "titre" => $rubriquedesc->titre,
                        "langue_courante" => $rubriquedesc->est_langue_courante(),
                        "parent" => $parent
                    );

	}
        
        return $return;
    }
    
    public function getBreadcrumbList($parent){
        $tab = array_reverse(chemin_rub($parent));
        
        if($tab[0]->id == '') return array();
        
        return $tab;
    }
    
    public function modify($lang, $parent, $lien, $online, $title, $chapo, $description, $postscriptum, $urlsuiv, $rewriteurl, $associatedContents, $associatedFeatures, $associatedVariants, $images, $documents, $tab)
    {
        if($this->id == '')
        {
            throw new TheliaAdminException("Category not found", TheliaAdminException::CATEGORY_NOT_FOUND);
        }
        
        $rubriquedesc = new Rubriquedesc($this->id, $lang);
        
        if($rubriquedesc->id == '')
        {
            CacheBase::getCache()->reset_cache();
            
            $rubriquedesc->rubrique = $this->id;
            $rubriquedesc->lang = $lang;
            $rubriquedesc->id = $rubriquedesc->add();
        }

        $this->oldParent = $this->parent;
                
        
        if($this->parent != $parent){
            $this->checkOrder($parent);
        }
        $this->parent = $parent;
        $this->lien = $lien;
        $this->ligne = ($online == 'on')?1:0;

        
        $rubriquedesc->chapo = str_replace("\n", "<br />", $chapo);
        $rubriquedesc->titre = $title;
        $rubriquedesc->postscriptum = $postscriptum;
        $rubriquedesc->description = $description;
        
        $this->maj();
        $rubriquedesc->maj();
        
        $rubriquedesc->reecrire($rewriteurl);
        $this->setLang($lang);
        AssociatedContentAdmin::getInstance()->updateAssociatedContents(0, $this->id, $associatedContents);
        AssociatedVariantAdmin::getInstance()->updateAssociatedVariants($this->id, $associatedVariants);
        AssociatedFeatureAdmin::getInstance()->updateAssociatedFeatures($this->id, $associatedFeatures);
        $this->updateImage($images);
        $this->getImageFile()->ajouter("photo", array("jpg", "gif", "png", "jpeg"), "uploadimage");
        $this->updateDocuments($documents);
        $this->getDocumentFile()->ajouter("document_", array(), "uploaddocument");
        
        ActionsModules::instance()->appel_module("modrub", new Rubrique($this->id));
        
        if ($urlsuiv)
        {
            redirige('parcourir.php?parent='.$this->rubrique);
        } else {
            redirige('rubrique_modifier.php?id='.$this->id.'&tab='.$tab.'&lang='.$lang);
        }
        
        
    }
    
    /**
     * 
     * if category change, order must be change in old and new category
     * 
     * @param int $category
     */
    public function checkOrder($parent)
    {
        //in old category
        $this->modifier_classement($this->id, $this->getMaxRanking($this->oldParent) + 1);

        //in new category
        $this->classement = $this->getMaxRanking($parent) + 1;        
    }
}

?>
