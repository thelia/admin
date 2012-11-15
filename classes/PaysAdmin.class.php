<?php

class PaysAdmin extends Pays
{
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            $this->charger($id);
        }
    }
    /**
     * 
     * @param int $id
     * @return \PaysAdmin
     */
    public static function getInstance($id = 0)
    {
        return new PaysAdmin($id);
    }
    
    public function getList()
    {
        $query = "SELECT p.id, p.tva, p.defaut, p.isocode, p.isoalpha2, p.isoalpha3, ps.titre FROM ".Pays::TABLE." p LEFT JOIN ".Paysdesc::TABLE." ps on p.id=ps.pays WHERE  ps.lang=".ActionsLang::instance()->get_id_langue_courante()." order by ps.titre";
        
        return $this->query_liste($query);
    }
    
    public function changeTva()
    {
        $this->tva = ! $this->tva;
        $this->maj();
    }
    
    public function changeDefault()
    {
        $this->disableActualDefault();
        $this->defaut = 1;
        $this->maj();
    }
    
    public function disableActualDefault()
    {
        $query = "UPDATE ".Pays::TABLE." set defaut=0 where defaut=1";
        $this->query($query);
    }
    
    public function delete() {
        parent::delete();
        
        redirige("pays.php");
    }
    
    public function modify($isocode, $isoalpha2, $isoalpha3, $tva, $zone, $descs)
    {
        
    }
    
    
}
