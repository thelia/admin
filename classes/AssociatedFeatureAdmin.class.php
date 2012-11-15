<?php

class AssociatedFeatureAdmin extends Rubcaracteristique {
    
    public function __construct($rubrique = 0, $caracteristique = 0) {
        parent::__construct($rubrique, $caracteristique);
    }

    public static function getInstance($rubrique = 0, $caracteristique = 0){
        return new AssociatedFeatureAdmin($rubrique, $caracteristique);
    }
    
    public function getList($categoryId)
    {
        $return = array();
        
        $qList = "SELECT * FROM " . self::TABLE . " WHERE rubrique='$categoryId'";
	$rList = $this->query($qList);
	while($rList && $theAssociatedFeature = $this->fetch_object($rList))
        {
            $featureDescription = new Caracteristiquedesc($theAssociatedFeature->caracteristique);
	
            $return[] = array("id" => $theAssociatedFeature->id, "feature" => $featureDescription->titre);
	}

        return $return;
    }
    
    function getListAvailableFeature($idrubrique)
    {
        $return = array();
        
	$qList = "SELECT * FROM " . self::TABLE . " WHERE rubrique=$idrubrique";
	$rList = $this->query($qList);
	
        $notAvailable = array();
        
        while($rList && $theAssociatedFeature = $this->fetch_object($rList))
            $notAvailable[] = $theAssociatedFeature->caracteristique;
	
        $feature = new Caracteristique();
        $qFeatures = "SELECT * FROM " . Caracteristique::TABLE . "" . ((!empty($notAvailable))?" WHERE id NOT IN(" . implode(',', $notAvailable) . ")":'');
        $rFeature = $feature->query($qFeatures);
        
        while($rFeature && $theFeature = $feature->fetch_object($rFeature))
        {
            $featureDescription = new Caracteristiquedesc($theFeature->id);
            $return[] = array('id' => $theFeature->id, 'titre' => $featureDescription->titre);
        }
        
        return $return;
    }
    
    public function delete($associatedFeatureToDeleteId)
    {
        if($this->charger_id($associatedFeatureToDeleteId))
        {
            $category = new Rubrique($this->rubrique);
            
            parent::delete();
            
            ActionsModules::instance()->appel_module("modrub", $category);
        }

        redirige('rubrique_modifier.php?id=' . $category->id . '&tab=associationTab#associatedFeatureAnchor');
    }
    
    public function add($featureToAddId, $categoryId)
    {
        $category = new Rubrique();
        $featureToAdd = new Caracteristique();
        if(!$this->charger($categoryId, $featureToAddId) && $featureToAdd->charger($featureToAddId) && $category->charger($categoryId))
        {
            $this->rubrique = $category->id;
            $this->caracteristique = $featureToAdd->id;
            
            parent::add();

            ActionsModules::instance()->appel_module("modrub", $category);
        }
        
        redirige('rubrique_modifier.php?id=' . $category->id . '&tab=associationTab#associatedFeatureAnchor');
    }
}

?>
