<?php

class ImageAdmin extends Image {
     
    public function getMaxRanking($type, $idType)
    {
        $qRanking = "SELECT MAX(classement) AS maxRanking FROM " . self::TABLE . " WHERE $type='$idType'";
        
        return $this->get_result($this->query($qRanking), 0, 'maxRanking');
    }
}
