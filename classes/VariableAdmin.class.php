<?php

class VariableAdmin extends Variable
{
    
    public function __construct($id = '') {
        parent::__construct();
        if($id > 0)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Variable not found",  TheliaAdminException::VARIABLE_NOT_FOUND);
            }
        }
    }
    /**
     * 
     * @return \VariableAdmin
     */
    public static function getInstance($id = '')
    {
        return new VariableAdmin($id);
    }
    
    public function delete() {
        if($this->id > 0)
        {
            $variable = new Variable($this->nom);
            parent::delete();

            ActionsModules::instance()->appel_module("delvariable", $variable);
        }
        
        redirige("variable.php");
    }
    
    public function getList()
    {
        $return = array();
        $query = "SELECT * FROM " .Variable::TABLE . " WHERE cache=0";
        
        foreach($this->query_liste($query) as $variable)
        {
            $return[] = array(
                "id" => $variable->id,
                "nom" => $variable->nom,
                "valeur" => $variable->valeur,
                "protege" => $variable->protege,
                "cache" => $variable->cache
            );
        }
        
        return $return;
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
            
            ActionsModules::instance()->appel_module("addvariable", new Variable($this->nom));
            
            redirige("variable.php");
        }
        else
        {
            throw new TheliaAdminException("impossible to add new promo", TheliaAdminException::VARIABLE_ADD_ERROR, null, $this);
        }
    }
    
    public function edit($request)
    {
        foreach($this->getList() as $variable)
        {
            if($this->charger_id($variable['id']))
            {
                $this->valeur = $request->request->get("valeur_" . $this->id);
                $this->maj();
                
                ActionsModules::instance()->appel_module("modvariable", new Variable($this->nom));
            }
        }
            
        redirige("variable.php");
    }
    
    public static function testVariableExists($variableName)
    {
        $variable = new Variable($variableName);
        
        return $variable->id;
        
    }
}