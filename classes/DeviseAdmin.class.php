<?php

class DeviseAdmin extends Devise
{
    
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            if(!$this->charger($id))
            {
                throw new TheliaAdminException("Devise not found", TheliaAdminException::DEVISE_NOT_FOUND);
            }
        }
    }


    public static function getInstance($id = 0)
    {
        return new DeviseAdmin($id);
    }

    public function getList()
    {
        return $this->query_liste("select * from ".$this->table);
    }
    
    /**
    * Modifier une devise existante
    *
    * @param int $id
    * @param string $nom
    * @param float $taux
    * @param string $symbole
    * @param string $code
    * @param int $defaut 0 ou 1
    */
   public function modifier($nom, $taux, $symbole, $code, $defaut) {
        if ($this->id =="")
        {
            throw new TheliaAdminException("Devise not found", TheliaAdminException::DEVISE_NOT_FOUND);
        }

        $this->nom = $nom;
        $this->taux = $taux;
        $this->symbole = $symbole;
        $this->code = $code;
        $this->defaut = $defaut;

        $this->maj();

        ActionsModules::instance()->appel_module("moddevise", new Devise($this->id));
   }
   
    /**
     * Ajouter une nouvelle devise
     *
     * @param string $nom
     * @param float $taux
     * @param string $symbole
     * @param string $code
     */
    public function ajouter($nom, $taux, $symbole, $code) {
        $devise = new Devise();

        $devise->nom = $nom;
        $devise->taux = $taux;
        $devise->symbole = $symbole;
        $devise->code = $code;

        $devise->add();

        ActionsModules::instance()->appel_module("ajoutdevise", $devise);
        
        $this->redirect();
    }
    
    /**
    * Supprimer une devise existante
    */
    public function supprimer() {
        if($this->id == "") throw new TheliaAdminException("Devise not found", TheliaAdminException::DEVISE_NOT_FOUND);
        $devise = new Devise($this->id);
        parent::delete();
        ActionsModules::instance()->appel_module("suppdevise", $devise);
        
        $this->redirect();
    }
   
   public function redirect()
   {
       redirige('devise.php');
   }
}