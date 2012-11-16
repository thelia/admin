<?php

class SMTPAdmin extends Smtpconfig
{
    
    public function __construct() {
        parent::__construct();
        
        $this->charger(1);
    }
    
    public function add($nom, $valeur)
    {
        $this->nom = $nom;
        $this->valeur = $valeur;
        $this->protege = 0;
        $this->cache = 0;
        
        if($this->nom!=="" && !self::testVariableExists($this->nom))
        {
            parent::add();
            
            ActionsModules::instance()->appel_module("addvariable", $variable);
            
            redirige("variable.php");
        }
        else
        {
            throw new TheliaAdminException("impossible to add new promo", TheliaAdminException::SMTP_EDIT_ERROR, null, $this);
        }
    }
    
    public function edit($request)
    {
        $this->serveur = $request->request->get("serveur");
        $this->port = $request->request->get("port");
        $this->username = $request->request->get("username");
        $this->password = $request->request->get("password");
        $this->secure = $request->request->get("secure");
        $this->active = $request->request->get("active");
        
        if($this->id > 0)
        {
            $this->maj();
        }
        else
        {
            $this->id = 1;
            $this->add();
        }
            
        redirige("smtp.php");
    }
}