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
        $query = "SELECT p.id, p.tva, p.defaut, p.isocode, p.isoalpha2, p.isoalpha3, ps.titre, p.boutique FROM ".Pays::TABLE." p LEFT JOIN ".Paysdesc::TABLE." ps on p.id=ps.pays WHERE  ps.lang=".ActionsLang::instance()->get_id_langue_courante()." order by ps.titre";
        
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

    public function changeBoutique()
    {
        $this->disableActualBoutique();
        $this->boutique = 1;
        $this->maj();
    }

    public function disableActualBoutique()
    {
        $query = "UPDATE ".Pays::TABLE." set boutique=0 where boutique=1";
        $this->query($query);
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
        $this->isocode = $isocode;
        $this->isoalpha2 = $isoalpha2;
        $this->isoalpha3 = $isoalpha3;
        $this->tva = $tva;
        $this->zone = $zone;
        
        $this->maj();
        
        foreach($descs as $desc)
        {
            $paysdesc = new Paysdesc();
            
            $paysdesc->charger($this->id, $desc["id"]);
            
            $paysdesc->lang = $desc["id"];
            $paysdesc->titre = $desc["titre"];
            $paysdesc->chapo = $desc["chapo"];
            $paysdesc->description = $desc["description"];
            $paysdesc->pays = $this->id;
            
            if($paysdesc->id)
            {
                $paysdesc->maj();
            } else {
                $paysdesc->add();
            }
        }
        
        redirige("pays.php");
    }
    
    public function add($titre, $isocode, $isoalpha2, $isoalpha3, $tva, $zone)
    {
        $titre = trim($titre);
        
        if (empty($titre))
        {
            throw new TheliaAdminException("Title can not be empty", TheliaAdminException::COUNTRY_TITLE_EMPTY);
        }
        
        $this->isocode = $isocode;
        $this->isoalpha2 = $isoalpha2;
        $this->isoalpha3 = $isoalpha3;
        $this->tva = $tva;
        $this->zone = $zone;
        $this->id = parent::add();
        
        $paysdesc = new Paysdesc();
        $paysdesc->pays = $this->id;
        $paysdesc->lang = ActionsAdminLang::instance()->get_id_langue_courante();
        $paysdesc->titre = $titre;
        $paysdesc->add();
        
        redirige("pays.php");
    }
    
    
}
