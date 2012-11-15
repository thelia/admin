<?php

class AssociatedContentAdmin extends Contenuassoc {
    
    public function __construct($id = 0) {
        parent::__construct($id);
    }

    public static function getInstance($id = 0){
        return new AssociatedContentAdmin($id);
    }
    
    public function getList($type, $objet)
    {
        $return = array();

        if ($type == 1)
        {
            $objectInstance = new Produit();
            $objectInstance->charger($objet);
        }
        else
        {
            $objectInstance = new Rubrique();
            $objectInstance->charger($objet);
        }

        $qList = "SELECT * FROM " . self::TABLE . " WHERE type='$type' AND objet='$objectInstance->id' ORDER BY classement";
        $rList = $this->query($qList);

        while ($rList && $theAssociatedContent = $this->fetch_object($rList))
        {
            $content = new Contenu($theAssociatedContent->contenu);
            
            $contentDescription = new Contenudesc();
            $contentDescription->charger($content->id);

            $folderDescription = new Dossierdesc();
            $folderDescription->charger($content->dossier);

            $return[] = array('id' => $theAssociatedContent->id, 'folder' => $folderDescription->titre, 'content' => $contentDescription->titre);
        }

        return $return;
    }
    
    public function delete($associatedContentToDeleteId)
    {
        if($this->charger($associatedContentToDeleteId))
        {
            parent::delete();
            
            if ($this->type == 1)
                $objet = new Produit();
            else
                $objet = new Rubrique();

            $objet->charger($this->objet);

            if ($this->type == 1)
                ActionsModules::instance()->appel_module("modprod", $objet);
            else
                ActionsModules::instance()->appel_module("modrub", $objet);
        }

        if ($this->type == 1)
            redirige('produit_modifier.php?ref=' . $objet->ref . '&tab=associationTab#associatedContentAnchor');
        else
            redirige('rubrique_modifier.php?id=' . $objet->id . '&tab=associationTab#associatedContentAnchor');
    }
    
    public function add($contentToAddId, $type, $object)
    {
        if ($type == 1)
        {
            $objectInstance = new Produit();
            $objectInstance->charger($object);
        }
        else
        {
            $objectInstance = new Rubrique();
            $objectInstance->charger($object);
        }
        
        $contentToAdd = new Contenu();
        if(!$this->existe($object, $type, $contentToAddId) && $contentToAdd->charger($contentToAddId))
        {
            $classement = $this->getMaxRanking($objectInstance->id, $type) + 1;

            $this->objet = $objectInstance->id;
            $this->type = $type;
            $this->contenu = $contentToAdd->id;
            $this->classement = $classement;
            
            parent::add();

            if ($this->type == 1)
                ActionsModules::instance()->appel_module("modprod", $objectInstance);
            else
                ActionsModules::instance()->appel_module("modrub", $objectInstance);
        }
        
        if ($this->type == 1)
            redirige('produit_modifier.php?ref=' . $objectInstance->ref . '&tab=associationTab#associatedContentAnchor');
        else
            redirige('rubrique_modifier.php?id=' . $objectInstance->id . '&tab=associationTab#associatedContentAnchor');
    }
    
    public function getMaxRanking($object, $type)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE objet='$object' AND type='$type'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
    
    /*public function modifyOrder($type, $parent){
        $this->changer_classement($this->id, $type);
        
        redirige('parcourir.php?parent='.$parent);
    }
    
    public function changeOrder($newClassement, $parent){
        $this->modifier_classement($this->id, $newClassement);
        
        redirige('parcourir.php?parent='.$parent);
    }*/
}

?>
