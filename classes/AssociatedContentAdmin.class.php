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

            $return[] = array('id' => $theAssociatedContent->id, 'folder_id' => $folderDescription->dossier, 'folder_titre' => $folderDescription->titre, 'content_id' => $contentDescription->contenu, 'content_titre' => $contentDescription->titre);
        }

        return $return;
    }
    
    /**
     * 
     * todo : utiliser cette methode pour la gestion des contenus associés aux produit // actuellement uniquement pour rubrique
     */
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
    }
    
    /**
     * 
     * todo : utiliser cette methode pour la gestion des contenus associés aux produit // actuellement uniquement pour rubrique
     */
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
        $this->id = '';
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
    }
    
    public function getMaxRanking($object, $type)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE objet='$object' AND type='$type'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
    
    public function updateAssociatedContents($type, $id, $infos)
    {
        foreach($infos as $index => $info)
        {
            if($info["alive"] === '0') {
                $this->delete($index);
            }
            elseif($info["add"] == 1)
            {
                $this->add($info["content"], $type, $id);
            }
        }
    }
}

?>
