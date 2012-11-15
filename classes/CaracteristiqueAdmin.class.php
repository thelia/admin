<?php

class CaracteristiqueAdmin extends Caracteristique
{
    
    public function getInstance()
    {
        return new CaracteristiqueAdmin();
    }
    
    public function getProductList(\Produit $produit, $lang)
    {
        if($produit->id == "") return array();
        
        $return = array();
        
        $query = "select c.caracteristique as id, c.titre from ".Caracteristiquedesc::TABLE." c left join ".Rubcaracteristique::TABLE." rc on rc.caracteristique=c.caracteristique and c.lang=$lang where rc.rubrique=".$produit->rubrique;
        foreach($this->query_liste($query) as $caracteristique)
        {
            $return[$caracteristique->id]["titre"] = $caracteristique->titre;
            $query2 = "select c.id, c.caracteristique, cd.titre from ".Caracdisp::TABLE." c left join ".Caracdispdesc::TABLE." cd on cd.caracdisp = c.id and cd.lang = $lang where c.caracteristique=".$caracteristique->id." order by cd.classement";
            $resul = $this->query($query2);
            if($this->num_rows($resul))
            {
                while($resul && $row = $this->fetch_object($resul))
                {
                    $return[$caracteristique->id]["caracdisp"][] = array(
                        "caracdisp" => $row->id,
                        "titre" => $row->titre
                    );
                }
                
            } else {
                $return[$caracteristique->id]["caracdisp"] = null;
            }
        }
        return $return;
    }
    
}