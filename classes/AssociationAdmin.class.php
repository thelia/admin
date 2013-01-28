<?php

class AssociationAdmin 
{
    private $association = array();
    
    
    /**
     * 
     * @param type $attachement
     * @param FichierAdminBase $object
     */
    /*public function setAssociation($association, FichierAdminBase $object)
    {
        $this->association[$association] = $object;
    }*/
    
    /**
     * 
     * return a fileAttachement instance (imageFile, documentFile, etc)
     * 
     * @param string $attachement
     * @return FichierAdminBase
     * @throws TheliaAdminException
     */
    /*public function getAssociation($association)
    {
        if(array_key_exists($association, $this->association) !== true)
        {
            throw new TheliaAdminException("Association file does not Exist",  TheliaAdminException::ATTACHEMENT_NOT_FOUND);
        }
        return $this->association[$association];
    }
    
    public function getAssociations()
    {
        return $this->association;
    }
    */
    public function updateAssociation($association, $infos)
    {
        foreach($infos as $index => $infos)
        {
            if($info["alive"] == 1)
                $this->getAssociation($association)->supprimer($index);
        }
    }
}
?>
