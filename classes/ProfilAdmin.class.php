<?php
require_once __DIR__ . '/../../classes/Profil.php';

class ProfilAdmin extends Profil
{
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Profile not found", TheliaAdminException::PROFIL_NOT_FOUND);
            }
        }
    }
    
    /**
     * 
     * @return \ProfilAdmin
     */
    public static function getInstance($id = 0)
    {
        return new ProfilAdmin($id);
    }
    
    public function getPermissionIdList()
    {
        $this->verifyLoaded();
        
        $retour= array();
        
        foreach($this->query_liste("SELECT * FROM " . Autorisation_profil::TABLE . " WHERE profil='$this->id' ORDER BY autorisation ASC", 'autorisation_profil') as $autorisation_profil)
        {   
            $retour[] = $autorisation_profil->autorisation;
        }
        
        return $retour;
    }
    
    /**
     * 
     * Verify if a profile is loaded 
     * 
     * @throws TheliaAdminException PROFIL_NOT_FOUND
     */
    protected function verifyLoaded()
    {
        if(!$this->id) throw new TheliaAdminException("Profile not found", TheliaAdminException::PROFIL_NOT_FOUND);
    }
}
