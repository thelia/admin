<?php

class ToolBoxAdmin extends Baseobj
{   
    public static function getNext($objet, $parent = 'parent', $ranking = 'classement')
    {
        $qNext = "SELECT * FROM `$objet->table` WHERE " . ($parent===false?"1":"$parent='" . $objet->$parent. "'") ." AND `$ranking`>'" . $objet->$ranking. "' ORDER BY `$ranking` ASC LIMIT 1";
        $aNext = $objet->query_liste($qNext, 'Rubrique');
        
        return $aNext[0]?$aNext[0]:false;
    }
    
    public static function getPrevious($objet, $parent = 'parent', $ranking = 'classement')
    {
        $qPrevious = "SELECT * FROM `$objet->table` WHERE " . ($parent===false?"1":"$parent='" . $objet->$parent. "'") ." AND `$ranking`<'" . $objet->$ranking. "' ORDER BY `$ranking` DESC LIMIT 1";
        $aPrevious = $objet->query_liste($qPrevious, 'Rubrique');
        
        return $aPrevious[0]?$aPrevious[0]:false;
    }
}
?>
