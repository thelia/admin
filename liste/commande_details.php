<?php

function liste_venteprod($commande){
    
    $return = array();
    
    $query = "SELECT * FROM ".Venteprod::TABLE." WHERE commande=".$commande->id;
    
    foreach($commande->query_liste($query) as $row){
        
        if($row->parent == 0){
            $return[$row->id][] = array(
                "ref" => $row->ref,
                "title" => nl2br($row->titre),
                "price" => round($row->prixu,2),
                "qtity" => $row->quantite,
                "total" => round($row->quantite * $row->prixu, 2)
            );
        }
        else {
            $return[$row->parent][] = array(
                "ref" => $row->ref,
                "title" => nl2br($row->titre),
                "price" => round($row->prixu,2),
                "qtity" => $row->quantite,
                "total" => round($row->quantite * $row->prixu, 2)
            );
        }        
    }
    
    return $return;
}
?>
