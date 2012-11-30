<?php

class DeclinaisonAdmin extends Declinaison
{
    
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Declinaison not found", TheliaAdminException::DECLI_NOT_FOUND);
            }
        }
    }
    
    /**
     * 
     * @return \DeclinaisonAdmin
     */
    public static function getInstance($id = 0)
    {
        return new DeclinaisonAdmin($id);
    }
    
    public function getList()
    {
        $return = array();
        
        $query = "SELECT c.id, c.classement FROM ".Declinaison::TABLE." c order by c.classement";
        foreach($this->query_liste($query) as $declinaison)
        {
            $declidesc = new Declinaisondesc($declinaison->id);
            
            $return[] = array(
                "id" => $declinaison->id,
                "classement" => $declinaison->classement,
                "titre" => $declidesc->titre
            );
        }
        
        return $return;
    }
    
    public function modClassement($type)
    {
        $this->verifyLoaded();
        
        $this->changer_classement($this->id, $type);
        
        redirige("declinaison.php");
    }
    
    protected function verifyLoaded()
    {
        if(!$this->id) throw new TheliaAdminException("Declinaison not found", TheliaAdminException::DECLI_NOT_FOUND);
    }
}