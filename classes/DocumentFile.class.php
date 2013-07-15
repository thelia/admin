<?php

class DocumentFile extends FichierAdminBase
{
    const NOMBRE_UPLOAD = 5;
    
    public function __construct($typeobjet, $idobjet) {
        parent::__construct("Document", self::NOMBRE_UPLOAD, $typeobjet, $idobjet);
    }
    
    protected function chemin_objet($fichier) {
        return sprintf("../client/document/%s", $fichier);
    }
}
