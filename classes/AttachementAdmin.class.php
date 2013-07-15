<?php

class AttachementAdmin 
{
    private $attachement = array();
    
    public function setImageFile(FichierAdminBase $imageFile)
    {
        $this->setAttachement("image", $imageFile);
    }
    
    public function setDocumentFile(FichierAdminBase $documentFile)
    {
        $this->setAttachement("document", $documentFile);
    }
    
    /**
     * 
     * @param type $attachement
     * @param FichierAdminBase $object
     */
    public function setAttachement($attachement, FichierAdminBase $object)
    {
        $this->attachement[$attachement] = $object;
    }
    
    /**
     * 
     * @return ImageFile
     */
    public function getImageFile()
    {
        return $this->getAttachement("image");
    }
    
    /**
     * 
     * @return DocumentFile
     */
    public function getDocumentFile()
    {
        return $this->getAttachement("document");
    }
    
    /**
     * 
     * return a fileAttachement instance (imageFile, documentFile, etc)
     * 
     * @param string $attachement
     * @return FichierAdminBase
     * @throws TheliaAdminException
     */
    public function getAttachement($attachement)
    {
        if(array_key_exists($attachement, $this->attachement) !== true)
        {
            throw new TheliaAdminException("Attachement file does not Exist",  TheliaAdminException::ATTACHEMENT_NOT_FOUND);
        }
        return $this->attachement[$attachement];
    }
    
    public function getAttachements()
    {
        return $this->attachement;
    }
    
    public function setLang($lang)
    {
        foreach($this->getAttachements() as $attachement)
        {
            $attachement->setLang($lang);
        }
    }
    
    /**
     * 
     * @param boolean|integer $lang
     * @return lang
     */
    public function getImageList($lang = false)
    {
        return $this->getAttachement("image")->getList($lang);
    }
    
    public function getDocumentList($lang = false)
    {
        return $this->getAttachement("document")->getList($lang);
    }
    
    public function updateAttachement($attachement, $files)
    {
        foreach($files as $index => $file)
        {
            if($file["toDelete"] == 1)
                $this->getAttachement($attachement)->supprimer($index);
            else
                $this->getAttachement($attachement)->modifier($index, $file["titre"], $file["chapo"], $file["description"], $file["rank"]);
        }
        
        /*force proper ranking*/
        $this->getAttachement($attachement)->cleanRanking();
    }
    
    public function updateDocuments(array $documents)
    {
        $this->updateAttachement('document', $documents);
    }
    
    public function updateImage(array $images)
    {
        $this->updateAttachement('image', $images);
    }
}
?>
