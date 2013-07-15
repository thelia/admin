<?php

class AssociatedVariantAdmin extends Rubdeclinaison {
    
    public function __construct($rubrique = 0, $declinaison = 0) {
        parent::__construct($rubrique, $declinaison);
    }

    public static function getInstance($rubrique = 0, $declinaison = 0){
        return new AssociatedVariantAdmin($rubrique, $declinaison);
    }
    
    public function getList($categoryId)
    {
        $return = array();
        
        $qList = "SELECT * FROM " . self::TABLE . " WHERE rubrique='$categoryId'";
	$rList = $this->query($qList);
	while($rList && $theAssociatedVariant = $this->fetch_object($rList))
        {
            $variantDescription = new Declinaisondesc($theAssociatedVariant->declinaison);
            
	    $return[] = array("id" => $theAssociatedVariant->id, "variant_id" => $variantDescription->declinaison, "variant_titre" => $variantDescription->titre);
	}

        return $return;
    }
    
    function getListAvailableVariant($idrubrique)
    {
        $return = array();
        
	$qList = "SELECT * FROM " . self::TABLE . " WHERE rubrique=$idrubrique";
	$rList = $this->query($qList);
	
        $notAvailable = array();
        
        while($rList && $theAssociatedVariant = $this->fetch_object($rList))
            $notAvailable[] = $theAssociatedVariant->declinaison;
	
        $variant = new Declinaison();
        $qVariant = "SELECT * FROM " . Declinaison::TABLE . "" . ((!empty($notAvailable))?" WHERE id NOT IN(" . implode(',', $notAvailable) . ")":'');
        $rVariant = $variant->query($qVariant);
        
        while($rVariant && $theVariant = $variant->fetch_object($rVariant))
        {
            $variantDescription = new Declinaisondesc($theVariant->id);
            $return[] = array('id' => $theVariant->id, 'titre' => $variantDescription->titre);
        }
        
        return $return;
    }
    
    public function delete($associatedVariantToDeleteId)
    {
        if($this->charger_id($associatedVariantToDeleteId))
        {
            $category = new Rubrique($this->rubrique);
            
            parent::delete();
            
            ActionsModules::instance()->appel_module("modrub", $category);
        }
    }
    
    public function add($variantToAddId, $categoryId)
    {
        $category = new Rubrique();
        $variantToAdd = new Declinaison();
        
        $this->id = '';
        if(!$this->charger($categoryId, $variantToAddId) && $variantToAdd->charger($variantToAddId) && $category->charger($categoryId))
        {
            $this->rubrique = $category->id;
            $this->declinaison = $variantToAdd->id;
            
            parent::add();

            ActionsModules::instance()->appel_module("modrub", $category);
        }
    }
    
    public function updateAssociatedVariants($categoryId, $infos)
    {
        foreach($infos as $index => $info)
        {
            if($info["alive"] == 0 && $this->charger($categoryId, $index)) {
                $this->delete($this->id);
            }
            elseif($info["alive"] == 1)
            {
                $this->add($index, $categoryId);
            }
        }
    }
}

?>
