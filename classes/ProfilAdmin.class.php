<?php

if(file_exists(__DIR__ . '/../../classes/Profil.php'))
    require_once __DIR__ . '/../../classes/Profil.php'; 

class ProfilAdmin extends Profil
{
    const ID_PROFIL_SUPERADMINISTRATEUR = 1;
    
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
    
    /**
     * 
     * @param array $excludesId liste d'id à exclure
     * @return type
     */
    public function chargerPermier($excludesId)
    {
        $where = '';
        if(count($excludesId)>0)
            $where = "WHERE id NOT IN (" . implode(',', $excludesId) . ")";
        
        return $this->getVars("SELECT * from `$this->table` $where ORDER BY id ASC LIMIT 1");
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
    
    protected function checkFormulation($formulation)
    {
        if(
            !preg_match('#[a-zA-Z0-9]+#', $formulation)
        )
        {
            throw new TheliaAdminException("Bad profile formulation", TheliaAdminException::BAD_PROFILE_FORMULATION);
        }
    }
    
    public function getList()
    {
        return $this->query_liste("SELECT profil, titre FROM ".Profildesc::TABLE." WHERE lang=" . ActionsLang::instance()->get_id_langue_courante());
    }
    
    public function modifiy($name, $description, $generalPermissions)
    {
        if($this->id == ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR) {
            throw new TheliaAdminException("Caanot change Superadministrator permissions", TheliaAdminException::CANNOT_CHANGE_SUPERADMINISTRATOR_PERMISSIONS);
        }
        
        $this->verifyLoaded();
        
        $profileDescription = new Profildesc();
        if($profileDescription->charger($this->id))
        {
            $profileDescription->titre = $name;
            $profileDescription->description = $description;
            $profileDescription->maj();
        }
        
        if($generalPermissions === null) $generalPermissions = array();
        
        foreach($this->query_liste("SELECT * FROM " . Autorisation::TABLE) as $row) {
            $autorisation_profil = new Autorisation_profilAdmin();
            $autorisation_profil->charger($row->id, $this->id);

            if(array_key_exists($row->id, $generalPermissions) && $generalPermissions[$row->id] == 'on') {
                if(!$autorisation_profil->id) {
                    $autorisation_profil->profil = $this->id;
                    $autorisation_profil->autorisation = $row->id;
                    $autorisation_profil->lecture = 0;
                    $autorisation_profil->ecriture = 0;
                    $autorisation_profil->id = $autorisation_profil->add();
                }

                $autorisation_profil->lecture = 1;
                $autorisation_profil->ecriture = 1;
                $autorisation_profil->maj();
            } else {
                if($autorisation_profil->id)
                {
                    $autorisation_profil->lecture = 0;
                    $autorisation_profil->ecriture = 0;
                    $autorisation_profil->maj();
                }
            }
        }
        
        redirige("droits.php?profil=" . $this->id);
    }
    
    public function create($formulation, $name, $description)
    {
        $this->checkFormulation($formulation);
        
        $this->nom = $formulation;
        $this->id = $this->add();
        
        $profileDesc = new Profildesc();
        $profileDesc->profil = $this->id;
        $profileDesc->titre = $name;
        $profileDesc->description = $description;
        $profileDesc->lang = ActionsLang::instance()->get_id_langue_courante();
        $profileDesc->id = $profileDesc->add();
        
        redirige("droits.php?profil=" . $this->id);
    }
    
    public function delete()
    {
        $this->verifyLoaded();
        
        $this->query('DELETE FROM '.  Profildesc::TABLE.' WHERE profil='.$this->id);
        
        parent::delete();
        
        redirige("droits.php");
    }
}
