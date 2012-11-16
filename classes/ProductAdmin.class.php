<?php

class ProductAdmin extends Produit {
     
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
    
    public function __construct($id = 0, $ref = '') {
        parent::__construct();
        
        if($id > 0){
            $this->charger_id($id);
        }
        else if($ref != ''){
            $this->charger($ref);
        }
        
        $this->extends[] = new AttachementAdmin();
        
        $this->setAttachement("image", new ImageFile('produit', $this->id));
        $this->setAttachement("document", new DocumentFile('produit', $this->id));
    }
    
    
    /**
     * 
     * @param string $id
     * @return \ProductAdmin
     */
    public static function getInstance($id = 0){
        return new ProductAdmin($id);
    }
    
    /**
     * 
     * @param string $ref
     * @return \ProductAdmin
     */
    public static function getInstanceByRef($ref){
        return new ProductAdmin(0, $ref);
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
        
        redirige('parcourir.php?parent='.$parent);
    }
    
    public function modifyOrder($type, $parent){
        $this->changer_classement($this->ref, $type);
        
        redirige('parcourir.php?parent='.$parent);
    }
    
    public function changeOrder($newClassement, $parent){
        $this->modifier_classement($this->id, $newClassement);
        
        redirige('parcourir.php?parent='.$parent);
    }
    
    public function changeAttachementPosition($attachement, $id, $type, $lang, $tab)
    {
        $this->getAttachement($attachement)->modclassement($id, $type);
        redirige("produit_modifier.php?ref=".$this->ref."&rubrique=".$this->rubrique."&lang=".$lang."&tab=".$tab);
    }
    
    public function deleteAttachement($attachement, $id, $lang, $tab)
    {
        $this->getAttachement($attachement)->supprimer($id);
        redirige("produit_modifier.php?ref=".$this->ref."&rubrique=".$this->rubrique."&lang=".$lang."&tab=".$tab);
    }
    
    public static function cleanRef($ref)
    {
        $ref = str_replace(" ", "", $ref);
        $ref = str_replace("/", "", $ref);
        $ref = str_replace("+", "", $ref);
        $ref = str_replace(".", "-", $ref);
        $ref = str_replace(",", "-", $ref);
        $ref = str_replace(";", "-", $ref);
        $ref = str_replace("'", "", $ref);
        $ref = str_replace("\n", "", $ref);
        $ref = str_replace("\"", "", $ref);
        
        return $ref;
    }
    
    public static function cleanPrice($price)
    {
        return str_replace(',','.',$price);
    }
    
    /**
     * 
     * try to crate a new product. The $ref parameter must be a unique value in the table and title can not be empty
     * 
     * if everything is ok, this method redirect to the product modification page. 
     * Else a table with detail error is return.
     * 
     * @param string $ref
     * @param string $title
     * @param string $category
     * @return boolean|array
     */
    public function add($ref, $title, $category){
        $error = false;
        $errorTab = array(
            "ref" => false,
            "title" => false
        );
        
        $ref = self::cleanRef($ref);
        
        if($ref == '' || self::exist_ref($ref)){
            $error = true;
            $errorTab["ref"] = true;
        }
        
        if($title == ''){
            $error = true;
            $errorTab["title"] = true;
        }
        
        if($error === false){
            $this->ref = $ref;
            $this->datemodif = date('Y-m-d H:i:s');
            $this->rubrique = $category;
            $this->classement = $this->getMaxRanking($category) + 1;
            $this->id = parent::add();
            
            $productdesc = new Produitdesc();
            $productdesc->titre = $title;
            $productdesc->lang = ActionsLang::instance()->get_id_langue_courante();
            $productdesc->produit = $this->id;
            $productdesc->add();
            
            $this->cleanCaracteristique();
            
            $productdesc->reecrire();

            ActionsModules::instance()->appel_module("ajoutprod", $this);
            
            redirige('produit_modifier.php?ref='.$this->ref.'&rubrique='.$this->rubrique);
        }
        
        return ($error)?$errorTab:false;
        
    }
    
    public function getMaxRanking($parent)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE rubrique='$parent'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
    
    public function modify($lang, $price, $price2, $ecotaxe, $promo, $category, $new, $perso, $weight, $stock, $tva, $online, $title, $chapo, $description, $postscriptum, $urlsuiv, $rewriteurl, $caracteristique, $declinaison, $images, $documents, $tab)
    {
        if($this->id == '')
        {
            throw new TheliaAdminException("Product not found", TheliaAdminException::PRODUCT_NOT_FOUND);
        }
        
        $produitdesc = new Produitdesc($this->id, $lang);
        
        if($produitdesc->id == '')
        {
            CacheBase::getCache()->reset_cache();
            
            $produitdesc->produit = $this->id;
            $produitdesc->lang = $lang;
            $produitdesc->id = $produitdesc->add();
        }
        
        $this->datemodif = date('Y-m-d H:i:s');
        $this->prix = self::cleanPrice($price);
        $this->prix2 = self::cleanPrice($price2);
        $this->ecotaxe = self::cleanPrice($ecotaxe);
        
        $this->checkRewrite($category);
        
        
        $this->promo = ($promo == 'on')?1:0;
        $this->nouveaute = ($new == 'on')?1:0;
        $this->ligne = ($online == 'on')?1:0;
        
        $this->perso = $perso;
        $this->poids = $weight;
        $this->checkStock($stock, $declinaison);
        $this->checkCaracteristique($caracteristique);
        $this->tva = self::cleanPrice($tva);
        
        $produitdesc->chapo = str_replace("\n", "<br />", $chapo);
        $produitdesc->titre = $title;
        $produitdesc->postscriptum = $postscriptum;
        $produitdesc->description = $description;
        
        $this->maj();
        $produitdesc->maj();
        
        $produitdesc->reecrire($rewriteurl);
        $this->setLang($lang);
        $this->updateImage($images);
        $this->getImageFile()->ajouter("photo", array("jpg", "gif", "png", "jpeg"), "uploadimage");
        $this->updateDocuments($documents);
        $this->getDocumentFile()->ajouter("document_", array(), "uploaddocument");
        
        ActionsModules::instance()->appel_module("modprod", $this);
        
        if ($urlsuiv)
        {
            redirige('parcourir.php?parent='.$this->rubrique);
        } else {
            redirige('produit_modifier.php?ref='.$this->ref.'&rubrique='.$this->rubrique.'&tab='.$tab.'&lang='.$lang);
        }
        
        
    }

    protected function checkCaracteristique($caracteristique)
    {
        
        
        foreach($caracteristique as $index => $value)
        {
            $this->query("delete from $caracval->table where produit=" . $this->id . " and caracteristique=" . $index);
            if ( is_array($value) )
            {
                foreach ($value as $caracdisp)
                {
                    $caracval = new Caracval(); 
                    $caracval->produit = $this->id;
                    $caracval->caracteristique = $index;
                    $caracval->caracdisp = $caracdisp;
                    $caracval->add();
                }
            } else {
                $caracval = new Caracval();
                $caracval->produit = $this->id;
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
                $stock->produit = $this->id;
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
    
    public function checkRewrite($category){
        if($this->rubrique != $category) {
            $query = "select max(classement) as maxClassement from ".Produit::TABLE." where rubrique='" . $category . "'";
            $resul = $this->query($query);
            $this->classement =  $this->get_result($resul, 0, "maxClassement") + 1;

            $param_old = Produitdesc::calculer_clef_url_reecrite($this->id, $this->rubrique);
            $param_new = Produitdesc::calculer_clef_url_reecrite($this->id, $category);

            $query_reec = "select * from ".Reecriture::TABLE." where param='&$param_old' and lang=$lang and actif=1";

            $resul_reec = $this->query($query_reec);

            while($row_reec = $this->fetch_object($resul_reec)) {

                $tmpreec = new Reecriture();
                $tmpreec->charger_id($row_reec->id);
                $tmpreec->param = "&$param_new";
                $tmpreec->maj();
            }
            
            $this->rubrique = $category;
        }
    }
    
    /**
     * 
     * delete in caracval table all the record for this product id
     * 
     * @return boolean if id paramter is false or empty
     */
    protected function cleanCaracteristique()
    {
        if(! $this->id) return false;
        
        $query = "delete from ".Caracval::TABLE." where produit=".$this->id;
        $this->query($query);
    }
    
    /**
     * 
     * save in stock table if needed this product with 0 to all column (valeur and surplus)
     * 
     * @return boolean if id paramter is false or empty
     */
    protected function associateDeclinaison()
    {
        if(! $this->id) return false;
        
        $query = "SELECT d.id from ".Declidisp::TABLE." d LEFT JOIN ".Rubdeclinaison::TABLE." r ON d.declinaison = r.declinaison WHERE r.rubrique=".$this->rubrique;
        $resul = $this->query($query);


   	while($resul && $row = $this->fetch_object($resul)){

                $stock = new Stock();
                $stock->declidisp=$row->id;
                $stock ->produit=$this->id;
                $stock->valeur=0;
                $stock->surplus=0;
                $stock->add();
	}
    }
    
    /**
     * 
     * Return an array of product for the current category
     * 
     * @param string $rubrique id of the current category
     * @param string $critere order by clause
     * @param string $order ASC or DESC
     * @param string $alpha if order is alpha pu "alpha"
     * @return Array
     */
    public function getList($rubrique, $critere, $order, $alpha) {
        
        $return = array();

	if($alpha == "alpha"){
		$query = "select p.id, p.ref, p.rubrique, p.stock, p.prix, p.prix2, p.promo, p.ligne, p.nouveaute, p.classement from ".Produit::TABLE." p LEFT JOIN ".Produitdesc::TABLE." pd ON pd.produit = p.id and lang="  . ActionsLang::instance()->get_id_langue_courante() . " where p.rubrique=\"$rubrique\" order by pd.$critere $order";
	}else{
		$query = "select id, ref, rubrique, stock, prix, prix2, promo, ligne, nouveaute, classement from ".Produit::TABLE." where rubrique=\"$rubrique\" order by $critere $order";
	}
                
	$resul = $this->query($query);
	$i=0;
	while($resul && $row = $this->fetch_object($resul)){


		$produitdesc = new Produitdesc();
		$produitdesc->charger($row->id);
                
		if (! $produitdesc->affichage_back_office_permis()) continue;


		$image = new Image();
		$query_image = "select * from ".Image::TABLE." where produit=\"" . $row->id . "\" order by classement limit 0,1";
		$resul_image = $image->query($query_image);
		$row_image = $image->fetch_object($resul_image, 'image');
                
                $return[] = array(
                    "ref" => $row->ref,
                    "id" => $row->id,
                    "rubrique" => $row->rubrique,
                    "stock" => $row->stock,
                    "prix" => $row->prix,
                    "prix2" => $row->prix2,
                    "promo" => $row->promo,
                    "ligne" => $row->ligne,
                    "nouveaute" => $row->nouveaute,
                    "classement" => $row->classement,
                    "titre" => $produitdesc->titre,
                    "langue_courante" => $produitdesc->est_langue_courante(),
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
        
        $query = "SELECT p.id, p.ref, p.rubrique, ROUND(IF(p.promo=1, p.prix2, p.prix), 2) AS prix, p.promo, p.nouveaute, p.ligne, pd.titre
            FROM " . Produit::TABLE . " p
                LEFT JOIN " . Produitdesc::TABLE . " pd
                    ON p.id=pd.produit AND pd.lang='" . ActionsLang::instance()->get_id_langue_courante() . "'
            WHERE p.ref LIKE '%$searchTerm%'
                OR pd.titre LIKE '%$searchTerm%'
                OR pd.description LIKE '%$searchTerm%'";
                
	$resul = $this->query($query);
	while($resul && $row = $this->fetch_object($resul))
        {
            $return[] = array(
                "ref" => $row->ref,
                "id" => $row->id,
                "rubrique" => $row->rubrique,
                "prix" => $row->prix,
                "promo" => $row->promo,
                "nouveaute" => $row->nouveaute,
                "ligne" => $row->ligne,
                "titre" => $row->titre
                );
	}
                
        return $return;
    }
    
    public function getAccessoryList(){
        $return = array();
        
        $query = "select * from ".Accessoire::TABLE." where produit=".$this->id." order by classement";
	$resul = $this->query($query);
        
        

	while($resul && $row = $this->fetch_object($resul)){
            $produit = new Produit();
            $produitdesc = new Produitdesc();
	
            $produit->charger_id($row->accessoire);
            $produitdesc->charger($produit->id);

            $rubadesc = new Rubriquedesc();
            $rubadesc->charger($produit->rubrique);

            $return[] = array(
                "produit" => $produitdesc->titre,
                "rubrique" => $rubadesc->titre,
                "id" => $row->id
            );
	}
        
        return $return;
    }
}
