<?php

// /!\ Patch include profil
require_once __DIR__ . '/../../classes/Profil.php';

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
        $this->veirfyLoaded();
        
        
        
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->identifiant = $identifiant;
        $this->lang = $lang;
        
        if($this->id == $_SESSION['util']->id)
        {
            $admin = new Administrateur();
            $admin->charger_id($this->id);
            $_SESSION["util"] = new Administrateur();
            $_SESSION["util"] = $admin;
        }
        
        $this->maj();
        
        $this->redirect();
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
        $this->veirfyLoaded();
     
        $password = $this->verifyPassword($password, $verifyPassword);
        
        
        $this->motdepasse = $password;
        $this->crypter();
        $this->maj();
        
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
        $this->veirfyLoaded();
        
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
        
        $this->redirect();
    }
    
    protected function redirect()
    {
        redirige("gestadm.php");
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
    protected function veirfyLoaded()
    {
        if(!$this->id) throw new TheliaAdminException("Admin not found", TheliaAdminException::ADMIN_NOT_FOUND);
    }
    
}
