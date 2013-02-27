<?php
require_once __DIR__ . '/ProfilAdmin.class.php';

class AdministrateurAdmin extends Administrateur
{
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Admin not found", TheliaAdminException::ADMIN_NOT_FOUND);
            }
        }
    }
    
    /**
     * 
     * @return \AdministrateurAdmin
     */
    public static function getInstance($id = 0)
    {
        return new AdministrateurAdmin($id);
    }
    
    /**
     * @return array of Administraeur object
     */
    public function getList()
    {
        return $this->query_liste("SELECT * FROM ".Administrateur::TABLE, "administrateur");
    }
    
    /**
     * 
     * try to update admin's information.
     * 
     * @param string $nom
     * @param string $prenom
     * @param string $identifiant
     * @param int $lang
     * @throws TheliaAdminException ADMIN_NOT_FOUND if admin is not loaded
     */
    public function modify($nom, $prenom, $identifiant, $lang)
    {
        $this->verifyLoaded();
        
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->identifiant = $identifiant;
        $this->lang = $lang;
        
        $this->maj();
        
        if($this->id == $_SESSION['util']->id)
        {
            $admin = new Administrateur();
            $admin->charger_id($this->id);
            $_SESSION["util"] = new Administrateur();
            $_SESSION["util"] = $admin;
        }
        
        ActionsModules::instance()->appel_module("modAdmin", new Administrateur($this->id));
    }
    
    /**
     * 
     * try to update admin's password
     * 
     * @param type $password
     * @param type $verifyPassword
     * @throws TheliaAdminException ADMIN_PASSWORD_NOT_MATCH if password are not equal
     * @throws TheliaAdminException ADMIN_PASSWORD_EMPTY if password is empty
     * @throws TheliaAdminException ADMIN_NOT_FOUND if admin is not loaded
     */
    public function modifyPassword($password, $verifyPassword)
    {
        $this->verifyLoaded();
     
        $password = $this->verifyPassword($password, $verifyPassword);
        
        
        $this->motdepasse = $password;
        $this->crypter();
        $this->maj();
        
        ActionsModules::instance()->appel_module("modAdmin", new Administrateur($this->id));
        
        $this->redirect();
    }
    
    /**
     * 
     * try to delete an admin
     * 
     * @throws TheliaAdminException ADMIN_DELETE_HIMSELF if admin try to delete himself
     * @throws TheliaAdminException ADMIN_NOT_FOUND if admin is not loaded
     * @throws TheliaAdminException ADMIN_IMPOSSIBLE_DETELE_AUTH impossible to delete administrator's auth
     */
    public function delete()
    {
        $this->verifyLoaded();
        
        if($this->id == $_SESSION['util']->id)
        {
            throw new TheliaAdminException("admin can not delete himself", TheliaAdminException::ADMIN_DELETE_HIMSELF);
        }
        
        //try to delete autorisation_administrateur record
        try
        {
            $this->query('DELETE FROM '.Autorisation_administrateur::TABLE.' WHERE administrateur='.$this->id, true);
        } catch(Exception $e) {
            throw new TheliaAdminException("impossible to delete admin's auth", TheliaAdminException::ADMIN_IMPOSSIBLE_DETELE_AUTH);
        }
        
        ActionsModules::instance()->appel_module("beforeDeleteAdmin", new Administrateur($this->id));
        
        parent::delete();
        
        $this->redirect();
    }
    
    public function add($nom, $prenom, $identifiant, $password, $verifyPassword, $lang, $profil)
    {
        $password = $this->verifyPassword($password, $verifyPassword);
        
        if($this->verifyExists($identifiant, $password))
        {
            throw new TheliaAdminException("Admin already exists", TheliaAdminException::ADMIN_ALREADY_EXISTS);
        }
        
        
        if(!$this->verifyProfil($profil))
        {
            throw new TheliaAdminException("Profil does not exists", TheliaAdminException::ADMIN_PROFIL_DOES_NOT_EXISTS);
        }
        
        if(empty($identifiant))
        {
            throw new TheliaAdminException("login can not be empty", TheliaAdminException::ADMIN_LOGIN_EMPTY);
        }
        
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->identifiant = $identifiant;
        $this->motdepasse = $password;
        $this->crypter();
        $this->lang = $lang;
        $this->profil = $profil;
        $this->id = parent::add();
        
        foreach($this->query_liste("SELECT autorisation, lecture, ecriture FROM ".Autorisation_profil::TABLE." WHERE profil=".$profil) as $authProfil)
        {
            $authAdmin = new Autorisation_administrateur();
            $authAdmin->administrateur = $this->id;
            $authAdmin->autorisation = $authProfil->autorisation;
            $authAdmin->lecture = $authProfil->lecture;
            $authAdmin->ecriture = $authProfil->ecriture;
            $authAdmin->add();
        }
        
        ActionsModules::instance()->appel_module("addAdmin", new Administrateur($this->id));
        
        redirige("gestadm_droits.php?administrateur=" . $this->id);
    }
    
    public function changePermissions($profil, $generalPermissions, $pluginsPermissions)
    {
        $this->verifyLoaded();
        
        if($generalPermissions === null) $generalPermissions = array();
        if($pluginsPermissions === null) $pluginsPermissions = array();
        
        $testProfil = new Profil();
        if(!$testProfil->charger_id($profil) && $profil!=0)
            throw new TheliaAdminException('Incorrect parameter $profil : could not load Profil.',  TheliaAdminException::PROFIL_NOT_FOUND);
        
        if($profil != 0)
        {
            $this->profil = $profil;
            $this->maj();
        }
        
        if($this->profil != ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR) {
            foreach($this->query_liste("SELECT * FROM " . Autorisation::TABLE) as $row) {
                $autorisation_administrateur = new Autorisation_administrateur();
                $autorisation_administrateur->charger($row->id, $this->id);

                if(array_key_exists($row->id, $generalPermissions) && $generalPermissions[$row->id] == 'on') {
                    if(!$autorisation_administrateur->id) {
                        $autorisation_administrateur->administrateur = $this->id;
                        $autorisation_administrateur->autorisation = $row->id;
                        $autorisation_administrateur->lecture = 0;
                        $autorisation_administrateur->ecriture = 0;
                        $autorisation_administrateur->id = $autorisation_administrateur->add();
                    }
    
                    $autorisation_administrateur->lecture = 1;
                    $autorisation_administrateur->ecriture = 1;
                    $autorisation_administrateur->maj();
                } else {
                    if($autorisation_administrateur->id)
                    {
                        $autorisation_administrateur->lecture = 0;
                        $autorisation_administrateur->ecriture = 0;
                        $autorisation_administrateur->maj();
                    }
                }
            }

            foreach(ActionsAdminModules::instance()->lister(false, true) as $module) {
                if (ActionsAdminModules::instance()->est_administrable($module->nom)) {
                    $autorisation_modules = new Autorisation_modules();
                    $autorisation_modules->charger($module->id, $this->id);

                    if(array_key_exists($module->id, $pluginsPermissions) && $pluginsPermissions[$module->id] == 'on') {
                        if(!$autorisation_modules->id) {
                            $autorisation_modules->administrateur = $this->id;
                            $autorisation_modules->module = $module->id;
                            $autorisation_modules->id = $autorisation_modules->add();
                        }

                        $autorisation_modules->autorise = 1;
                        $autorisation_modules->maj();
                    } else {
                        if($autorisation_modules->id)
                        {
                            $autorisation_modules->autorise = 0;
                            $autorisation_modules->maj();
                        }
                    }
                }
            }
        }
        
        ActionsModules::instance()->appel_module("changePermissionsAdmin", new Administrateur($this->id));
        
        redirige('gestadm_droits.php?administrateur=' . $this->id);
    }
    
    protected function redirect()
    {
        redirige("gestadm.php");
    }
    
    /**
     * 
     * @return int (0 si profil personnalisé)
     */
    public function getProfile()
    {
        $this->verifyLoaded();
        
        if($this->profil == ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR)
            return ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR;
        
        $adminProfile = array();
        foreach($this->query_liste("SELECT autorisation, lecture, ecriture FROM " . Autorisation_administrateur::TABLE . " WHERE administrateur=" . $this->id . " ORDER BY autorisation ASC") as $authProfil)
        {
            if($authProfil->lecture . $authProfil->ecriture !== '00')
                $adminProfile[$authProfil->autorisation] = $authProfil->lecture . $authProfil->ecriture;
        }
        
            
        foreach($this->query_liste("SELECT * FROM " . Profil::TABLE . " WHERE id <> " . ProfilAdmin::ID_PROFIL_SUPERADMINISTRATEUR) as $profil)
        {
            $thisProfile = array();
            
            foreach($this->query_liste("SELECT autorisation, lecture, ecriture FROM " . Autorisation_profil::TABLE . " WHERE profil=" . $profil->id . " ORDER BY autorisation ASC") as $authProfil)
            {
                if($authProfil->lecture . $authProfil->ecriture !== '00')
                    $thisProfile[$authProfil->autorisation] = $authProfil->lecture . $authProfil->ecriture;
            }
           
            if(
                !key_exists(
                    0,
                    array_merge(
                        array_diff_assoc($adminProfile, $thisProfile),
                        array_diff_assoc($thisProfile, $adminProfile)
                    )
                )
            )
            {
                return $profil->id;
            }
        }
        
        return 0;
    }
    
    /**
     * 
     * verify if the profile exists
     * 
     * @param int $profil
     * @return type
     */
    protected function verifyProfil($profil)
    {
        $pfil = new Profil();
        return $pfil->charger_id($profil);
    }
    
    /**
     * 
     * verify if an admin with login/password couple already exists
     * 
     * @param string $login
     * @param string $password
     * @return int 1/0
     */
    protected function verifyExists($login, $password)
    {
        return $this->charger($login, $password);
    }
    
    /**
     * 
     * @param string $password
     * @param string $verifyPassword
     * @return string correct password
     * @throws TheliaAdminException
     */
    protected function verifyPassword($password, $verifyPassword)
    {
        $password = trim($password);
        $verifyPassword = trim($verifyPassword);
        
        if($password != $verifyPassword)
        {
            throw new TheliaAdminException("Passord does not match",  TheliaAdminException::ADMIN_PASSWORD_NOT_MATCH);
        }
        
        if(empty($password))
        {
            throw new TheliaAdminException("Passord can not be empty",  TheliaAdminException::ADMIN_PASSWORD_EMPTY);
        }
        
        return $password;
        
    }
    
    /**
     * 
     * Verify if an admin is loaded 
     * 
     * @throws TheliaAdminException ADMIN_NOT_FOUND
     */
    protected function verifyLoaded()
    {
        if(!$this->id) 
        {
            throw new TheliaAdminException("Admin not found", TheliaAdminException::ADMIN_NOT_FOUND);
        }
    }
    
}
