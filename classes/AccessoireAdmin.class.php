<?php

class AccessoireAdmin extends Accessoire {
     
    public function getMaxRanking($produit)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE produit='$produit'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
}
