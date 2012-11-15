<?php

class ImageFile extends FichierAdminBase
{
    const NOMBRE_UPLOAD = 5;
    
    public function __construct($typeobjet, $idobjet) {
        parent::__construct("Image", self::NOMBRE_UPLOAD, $typeobjet, $idobjet);
    }
    
    protected function chemin_objet($fichier) {
        return sprintf("../client/gfx/photos/$this->typeobjet/%s", $fichier);
    }
}
