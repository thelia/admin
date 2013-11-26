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
        
        ActionsModules::instance()->appel_module("modprod", new Produit($this->ref));
    }
    
    public function delete($parent,$id = 0){
        if($id > 0){
            $this->charger_id($id);
        }
        $produit = new Produit($this->ref);
        parent::delete();

        ActionsModules::instance()->appel_module("supprod", $produit);
        
        redirige('parcourir.php?parent='.$parent);
    }
    
    public function modifyOrder($type, $parent){
        $this->changer_classement($this->id, $type);

        redirige('parcourir.php?parent='.$parent);
    }
    
    public function changeOrder($newClassement, $parent){
        $this->modifier_classement($this->id, $newClassement);
        
        redirige('parcourir.php?parent='.$parent);
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
            $this->ligne = 1;
            $this->classement = $this->getMaxRanking($category) + 1;
            $this->id = parent::add();
            
            $productdesc = new Produitdesc();
            $productdesc->titre = $title;
            $productdesc->lang = ActionsLang::instance()->get_id_langue_courante();
            $productdesc->produit = $this->id;
            $productdesc->add();
            
            $this->cleanCaracteristique();
            
            $productdesc->reecrire();

            ActionsModules::instance()->appel_module("ajoutprod", new Produit($this->ref));
            
            redirige('produit_modifier.php?ref='.$this->ref.'&rubrique='.$this->rubrique);
        }
        
        return ($error)?$errorTab:false;
        
    }
    
    public function getMaxRanking($parent)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE rubrique='$parent'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }

    public function duplicate($duplicateRef, $duplicateDescription, $duplicateInfos, $duplicateFeatures, $duplicateVariants, $duplicateAccessories, $autoAccessories, $duplicateAssociatedContents, $duplicatePictures, $duplicateDocuments)
    {
        $ref = self::cleanRef($duplicateRef);

        if($ref == '' || self::exist_ref($ref)){
            throw new TheliaAdminException("Ref already exists", TheliaAdminException::REF_ALREADY_EXISTS);
        }

        $duplicatedProduct = new Produit($this->ref);
        $duplicatedProduct->id = '';
        $duplicatedProduct->ref = $ref;
        $duplicatedProduct->datemodif = '0000-00-00 00:00:00';
        $duplicatedProduct->ligne = 0;
        $duplicatedProduct->stock = 0;
        $duplicatedProduct->classement = $this->getMaxRanking($duplicatedProduct->rubrique) + 1;

        if(!$duplicateInfos) {

            $duplicatedProduct->prix = 0;
            $duplicatedProduct->ecotaxe = 0;
            $duplicatedProduct->promo = 0;
            $duplicatedProduct->prix2 = 0;
            $duplicatedProduct->nouveaute = 0;
            $duplicatedProduct->perso = 0;
            $duplicatedProduct->garantie = 0;
            $duplicatedProduct->poids = 0;
            $duplicatedProduct->tva = 0;
        }

        $duplicatedProduct->id = $duplicatedProduct->add();

        if($duplicateDescription) {
            foreach($this->query_liste("SELECT * FROM " . Produitdesc::TABLE . " WHERE produit='$this->id'", 'Produitdesc') as $produitdesc) {
                $produitdesc->id = '';
                $produitdesc->produit = $duplicatedProduct->id;
                $produitdesc->add();
            }
        } else {
            $produitdesc = new Produitdesc();
            $produitdesc->produit = $duplicatedProduct->id;
            $produitdesc->add();
        }

        if($duplicateFeatures) {
            foreach($this->query_liste("SELECT * FROM " . Caracval::TABLE . " WHERE produit='$this->id'", 'Caracval') as $caracval) {
                $caracval->id = '';
                $caracval->produit = $duplicatedProduct->id;
                $caracval->add();
            }
        }

        if($duplicateVariants) {
            foreach($this->query_liste("SELECT * FROM " . Stock::TABLE . " WHERE produit='$this->id'", 'Stock') as $stock) {
                $stock->id = '';
                $stock->produit = $duplicatedProduct->id;
                $stock->valeur = 0;
                $stock->add();
            }
            foreach($this->query_liste("SELECT * FROM " . Exdecprod::TABLE . " WHERE produit='$this->id'", 'Exdecprod') as $exdecprod) {
                $exdecprod->id = '';
                $exdecprod->produit = $duplicatedProduct->id;
                $exdecprod->add();
            }
        }

        if($duplicateAccessories) {
            foreach($this->query_liste("SELECT * FROM " . Accessoire::TABLE . " WHERE produit='$this->id'", 'Accessoire') as $accessoire) {
                $accessoire->id = '';
                $accessoire->produit = $duplicatedProduct->id;
                $accessoire->add();
            }
        }

        if($autoAccessories) {
            $accessoire = new AccessoireAdmin();
            $accessoire->produit = $duplicatedProduct->id;
            $accessoire->accessoire = $this->id;
            $accessoire->classement = $accessoire->getMaxRanking($duplicatedProduct->id);
            $accessoire->add();

            $accessoire = new AccessoireAdmin();
            $accessoire->produit = $this->id;
            $accessoire->accessoire = $duplicatedProduct->id;
            $accessoire->classement = $accessoire->getMaxRanking($this->id);
            $accessoire->add();
        }

        if($duplicateAssociatedContents) {
            foreach($this->query_liste("SELECT * FROM " . Contenuassoc::TABLE . " WHERE type=1 AND objet='$this->id'", 'Contenuassoc') as $contenuassoc) {
                $contenuassoc->id = '';
                $contenuassoc->objet = $duplicatedProduct->id;
                $contenuassoc->add();
            }
        }

        if($duplicatePictures) {
            foreach($this->query_liste("SELECT * FROM " . Image::TABLE . " WHERE produit='$this->id'", 'ImageAdmin') as $image) {

                $imagePath = sprintf(__DIR__ . "/../../client/gfx/photos/produit/%s", $image->fichier);

                if(file_exists($imagePath)) {
                    $increment = 0;
                    do {
                        $increment++;
                        $duplicatedName = 'D' . $increment . '_' . $image->fichier;
                        $exists = file_exists($duplicatedName);
                    } while($exists);

                    $duplicatedImagePath = sprintf(__DIR__ . "/../../client/gfx/photos/produit/%s", $duplicatedName);

                    copy($imagePath, $duplicatedImagePath);

                    $image->fichier = $duplicatedName;

                    $originalId = $image->id;
                    $image->id = '';
                    $image->produit = $duplicatedProduct->id;
                    $image->classement = $image->getMaxRanking('produit', $duplicatedProduct->id);
                    $image->id = $image->add();

                    foreach($this->query_liste("SELECT * FROM " . Imagedesc::TABLE . " WHERE image='$originalId'", 'Imagedesc') as $imagedesc) {
                        $imagedesc->id = '';
                        $imagedesc->image = $image->id;
                        $imagedesc->add();
                    }
                }
            }
        }

        if($duplicateDocuments) {
            foreach($this->query_liste("SELECT * FROM " . Document::TABLE . " WHERE produit='$this->id'", 'DocumentAdmin') as $document) {

                $documentPath = sprintf(__DIR__ . "/../../client/document/%s", $document->fichier);

                if(file_exists($documentPath)) {
                    $increment = 0;
                    do {
                        $increment++;
                        $duplicatedName = 'D' . $increment . '_' . $document->fichier;
                        $exists = file_exists($duplicatedName);
                    } while($exists);

                    $duplicatedDocumentPath = sprintf(__DIR__ . "/../../client/gfx/photos/produit/%s", $duplicatedName);

                    copy($documentPath, $duplicatedDocumentPath);

                    $document->fichier = $duplicatedName;

                    $originalId = $document->id;
                    $document->id = '';
                    $document->produit = $duplicatedProduct->id;
                    $document->classement = $document->getMaxRanking('produit', $duplicatedProduct->id);
                    $document->id = $document->add();

                    foreach($this->query_liste("SELECT * FROM " . Documentdesc::TABLE . " WHERE document='$originalId'", 'Documentdesc') as $documentdesc) {
                        $documentdesc->id = '';
                        $documentdesc->document = $document->id;
                        $documentdesc->add();
                    }
                }
            }
        }

        redirige('produit_modifier.php?ref='.$duplicatedProduct->ref.'&rubrique='.$duplicatedProduct->rubrique);
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
        
      //  $this->rubrique = $category;
        
        $this->checkRewrite($category, $lang);
        
        
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
        $produitdesc->postscriptum = str_replace("\n", "<br />", $postscriptum);
        $produitdesc->description = $description;

        $this->maj();
        $produitdesc->maj();
                
        $produitdesc->reecrire($rewriteurl);
        $this->setLang($lang);
        $this->updateImage($images);
        $this->getImageFile()->ajouter("photo", array("jpg", "gif", "png", "jpeg"), "uploadimage");
        $this->updateDocuments($documents);
        $this->getDocumentFile()->ajouter("document_", array(), "uploaddocument");

        ActionsModules::instance()->appel_module("modprod", new Produit($this->ref));
        
        if ($urlsuiv)
        {
            redirige('parcourir.php?parent='.$this->rubrique);
        } else {
            redirige('produit_modifier.php?ref='.$this->ref.'&rubrique='.$this->rubrique.'&tab='.$tab.'&lang='.$lang);
        }
        
        
    }

    protected function checkCaracteristique($caracteristique)
    {

        $this->query("delete from ".Caracval::TABLE." where produit=" . $this->id);
        foreach($caracteristique as $index => $value)
        {

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
            
            $exdecprod = new Exdecprod();
            if($value['exdecprod'] == '')
            {
                if(!$exdecprod->charger($this->id, $index))
                {
                    $exdecprod->produit = $this->id;
                    $exdecprod->declidisp = $index;
                    $exdecprod->id = $exdecprod->add();
                }
            }
            else
            {
                if($exdecprod->charger($this->id, $index))
                {
                    $exdecprod->delete();
                }
            }
        }
        
        if($nb > 0) $this->stock = $nb;
    }
    
    public function checkRewrite($category, $lang){
        if($this->rubrique != $category) {
            $query = "select max(classement) as maxClassement from ".Produit::TABLE." where rubrique='" . $category . "'";
            $resul = $this->query($query);
            $this->classement =  $this->get_result($resul, 0, "maxClassement") + 1;

            $param_old = Produitdesc::calculer_clef_url_reecrite($this->id, $this->rubrique);
            $param_new = Produitdesc::calculer_clef_url_reecrite($this->id, $category);
            
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
		$query = "select p.id, p.ref, p.rubrique, p.stock, p.prix, p.prix2, p.promo, p.ligne, p.nouveaute, p.classement, p.tva from ".Produit::TABLE." p LEFT JOIN ".Produitdesc::TABLE." pd ON pd.produit = p.id and lang="  . ActionsLang::instance()->get_id_langue_courante() . " where p.rubrique=\"$rubrique\" order by pd.$critere $order";
	}else{
		$query = "select id, ref, rubrique, stock, prix, prix2, promo, ligne, nouveaute, classement, tva from ".Produit::TABLE." where rubrique=\"$rubrique\" order by $critere $order";
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
                    "variants" => $this->getVariants($row->id),
                    "prix" => $row->prix,
                    "prix2" => $row->prix2,
                    "promo" => $row->promo,
                    "ligne" => $row->ligne,
                    "nouveaute" => $row->nouveaute,
                    "classement" => $row->classement,
                    "tva" => $row->tva,
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
    
    public function match($ref, $max_accepted = 5)
    {
        if(strlen($ref) == 0)
            return('KO');

        $product = new Produit();

        $q =    "SELECT p.id, p.ref, pd.titre, p.prix, p.prix2, p.promo, p.tva, p.rubrique
                FROM $product->table p
                    LEFT JOIN " . Produitdesc::TABLE . " pd
                        ON p.id=pd.produit AND pd.lang='" . ActionsLang::instance()->get_id_langue_courante() . "'
                WHERE
                    p.ref LIKE '$ref%'
                ";
        $r = $product->query($q);

        if($product->num_rows($r) == 0)
            return('KO');

        if($product->num_rows($r) > $max_accepted)
            return('TOO_MUCH:' . $product->num_rows($r));

        
        $retour = array();
        while($r && $a = $product->fetch_object($r)) {
            $retour[] = array(
                "ref"       =>  $a->ref,
                "titre"     =>  $a->titre,
                "prix"      =>  $a->prix,
                "prix2"     =>  $a->prix2,
                "promo"     =>  $a->promo,
                "tva"       =>  $a->tva,
                "rubrique"  =>  $a->rubrique,
                "stock"     =>  $this->getVariants($a->id),
            );
        }

        return(json_encode($retour));
    }
    
    public function getVariants($id = 0)
    {
        if($id)
            $this->charger_id($id);
        
        if(!$this->id)
            return 0;
        
        $q =    "SELECT dd.declinaison  AS declinaison_id, dd.id AS declidisp_id, ddd.titre  AS declidisp_titre, d.titre AS declinaison_titre, s.valeur AS declidisp_stock, s.surplus AS declidisp_surplus
                FROM " . Stock::TABLE . " s
                    LEFT JOIN " . Declidisp::TABLE . " dd
                        ON dd.id=s.declidisp
                    LEFT JOIN " . Declidispdesc::TABLE . " ddd
                        ON ddd.declidisp=dd.id AND ddd.lang='" . ActionsLang::instance()->get_id_langue_courante() . "'
                    LEFT JOIN " . Declinaisondesc::TABLE . " d
                        ON d.declinaison=dd.declinaison AND ddd.lang='" . ActionsLang::instance()->get_id_langue_courante() . "'
                    LEFT JOIN " . Exdecprod::TABLE . " edp
                        ON edp.produit='$this->id' AND edp.declidisp=dd.id
                    LEFT JOIN " . Rubdeclinaison::TABLE . " rd
                        ON rd.rubrique='$this->rubrique' AND rd.declinaison=dd.declinaison
                WHERE s.produit='$this->id' AND ISNULL(edp.id) AND NOT ISNULL(rd.id)";
        $r = $this->query($q);
        if($this->num_rows($r) == 0)
            return $this->stock;
        
        $retour = array();
        while($r && $a = $this->fetch_object($r)) {
            if(!$retour[$a->declinaison_id] || count($retour[$a->declinaison_id])==0)
                $retour[$a->declinaison_id] = array(
                    'titre'         =>  $a->declinaison_titre,
                    'declinaisons'  =>  array(
                        array(
                            "declidisp_id"      =>  $a->declidisp_id,
                            "declidisp_titre"   =>  $a->declidisp_titre,
                            "declidisp_stock"   =>  $a->declidisp_stock,
                            "declidisp_surplus" =>  $a->declidisp_surplus,
                        )
                    ),
                );
            else
                $retour[$a->declinaison_id]['declinaisons'][] = array(
                    "declidisp_id"      =>  $a->declidisp_id,
                    "declidisp_titre"   =>  $a->declidisp_titre,
                    "declidisp_stock"   =>  $a->declidisp_stock,
                    "declidisp_surplus" =>  $a->declidisp_surplus,
                );
        }
        
        return $retour;
    }

    public static function truncate($string, $length)
    {
        if (strlen($string) < $length) return $string;

        return substr($string, 0, $length) . "...";
    }
}
