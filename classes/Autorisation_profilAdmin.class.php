<?php
class Autorisation_profilAdmin extends Autorisation_profil
{
    function charger($autorisation, $profil){
        return $this->getVars("select * from $this->table where autorisation=\"$autorisation\" and profil=\"$profil\"");
    }
}
