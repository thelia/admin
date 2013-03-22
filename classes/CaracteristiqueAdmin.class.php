<?php

class CaracteristiqueAdmin extends Caracteristique
{
    
    public function __construct($id = 0) {
        parent::__construct();
        
        if($id)
        {
            if(!$this->charger_id($id))
            {
                throw new TheliaAdminException("Caracteristique not found", TheliaAdminException::CARAC_NOT_FOUND);
            }
        }
    }
    
    /**
     * 
     * @return \CaracteristiqueAdmin
     */
    public static function getInstance($id = 0)
    {
        return new CaracteristiqueAdmin($id);
    }
    
    public function getProductList(\Produit $produit, $lang)
    {
        if($produit->id == "") return array();
        
        $return = array();
        
        $query = "select c.caracteristique as id, c.titre from ".Caracteristiquedesc::TABLE." c left join ".Rubcaracteristique::TABLE." rc on rc.caracteristique=c.caracteristique and c.lang=$lang where rc.rubrique=".$produit->rubrique;
        foreach($this->query_liste($query) as $caracteristique)
        {
            $return[$caracteristique->id]["titre"] = $caracteristique->titre;
            $query2 = "select c.id, c.caracteristique, cd.titre from ".Caracdisp::TABLE." c left join ".Caracdispdesc::TABLE." cd on cd.caracdisp = c.id and cd.lang = $lang where c.caracteristique=".$caracteristique->id." order by cd.classement";
            $resul = $this->query($query2);
            if($this->num_rows($resul))
            {
                while($resul && $row = $this->fetch_object($resul))
                {
                    $return[$caracteristique->id]["caracdisp"][] = array(
                        "caracdisp" => $row->id,
                        "titre" => $row->titre
                    );
                }
                
            } else {
                $return[$caracteristique->id]["caracdisp"] = null;
            }
        }
        return $return;
    }
    
    public function getList()
    {
        $return = array();
        
        $query = "SELECT c.id, c.classement FROM ".Caracteristique::TABLE." c order by c.classement";
        foreach($this->query_liste($query) as $caracteristique)
        {
            $caracdesc = new Caracteristiquedesc($caracteristique->id);
            
            $return[] = array(
                "id" => $caracteristique->id,
                "classement" => $caracteristique->classement,
                "titre" => $caracdesc->titre
            );
        }
        
        return $return;
    }
    
    public function modifyOrder($type)
    {
        $this->verifyLoaded();
        
        $this->changer_classement($this->id, $type);
    }
    
    public function delete()
    {
        $this->verifyLoaded();
        $caracteristique = new Caracteristique($this->id);
        parent::delete();
        ActionsModules::instance()->appel_module("suppcaracteristique", $caracteristique);
    }
    
    /**
     * 
     * @param string $title Caracteristique title
     * @param string $display display caracteristique in boucle function
     * @param int $addAuto add automatically this caracteristique to all category
     */
    public function add($title, $display, $addAuto)
    {
        
        $title = trim($title);
        
        if(empty($title))
        {
            throw new TheliaAdminException("Title caracteristique empty", TheliaAdminException::CARAC_TITLE_EMPTY);
        }
        
        $this->classement = $this->getMaxRank() + 1;
        
        $this->affiche = ($display != "")?1:0;
        $this->id = parent::add();
        
        $caracdesc = new Caracteristiquedesc();
        $caracdesc->caracteristique = $this->id;
        $caracdesc->titre = $title;
        $caracdesc->lang = ActionsAdminLang::instance()->get_id_langue_courante();
        $caracdesc->add();
        
        if(intval($addAuto) == 1)
        {
            $query = "SELECT id FROM ".Rubrique::TABLE;
            
            foreach(CacheBase::getCache()->query($query) as $rub)
            {
                $rubcaracteristique = new Rubcaracteristique();
                $rubcaracteristique->rubrique = $rub->id;
                $rubcaracteristique->caracteristique = $this->id;
                $rubcaracteristique->add();
            }
        }
        
        ActionsModules::instance()->appel_module("ajcaracteristique", new Caracteristique($this->id));
        
        redirige("caracteristique_modifier.php?id=".$this->id);
    }
    
    public function getMaxRank()
    {
        $query = "SELECT MAX(classement) as maxClassement FROM ".$this->table;
        if($result = $this->query($query))
        {
            return $this->get_result($result, 0, "maxClassement");
        } else {
            return 0;
        }
    }
    
    public function getMaxCaracdispRank($idcaracteristique, $lang)
    {
        $caracdispdesc = new Caracdispdesc();
        $caracdisp = new Caracdisp();

        $query = "
                select
                        max(ddd.classement) as maxClassement
                from
                        $caracdispdesc->table ddd
                left join
                        $caracdisp->table dd on dd.id = ddd.caracdisp
                where
                        lang=$lang
                and
                        dd.caracteristique=$idcaracteristique
        ";

        $resul = $caracdispdesc->query($query);

        return $resul ? intval($caracdispdesc->get_result($resul, 0, "maxClassement")) : 0;
    }
    
    /**
     * 
     * Verify if an admin is loaded 
     * 
     * @throws TheliaAdminException ADMIN_NOT_FOUND
     */
    protected function verifyLoaded()
    {
        if(!$this->id) throw new TheliaAdminException("Caracteristique not found", TheliaAdminException::CARAC_NOT_FOUND);
    }
    
    /**
     * 
     * change the current rank of a caracdispdesc
     * 
     * @param int $idcaracdispdesc caracdisdesc id to modify
     * @param sting $classement position for the new rank of the caracdispdesc
     * @param int $lang current lang
     */
    public function setClassementCaracdisp($idcaracdispdesc, $classement, $lang)
    {
        $caracdispdesc = new Caracdispdesc();

        if ($caracdispdesc->charger($idcaracdispdesc, $lang))
        {
            if ($classement == $caracdispdesc->classement) return;

            if ($classement > $caracdispdesc->classement)
            {
                $offset = -1;
                $between = "$caracdispdesc->classement and $classement";
            }
            else
            {
                $offset = 1;
                $between = "$classement and $caracdispdesc->classement";
            }

            $caracdisp = new Caracdisp();

            $query = "
                    select
                            id
                    from
                            $caracdispdesc->table
                    where
                            lang=$lang
                    and
                            caracdisp in (select id from $caracdisp->table where caracteristique = ".$this->id.")
                    and
                            classement BETWEEN $between
            ";

            $resul = $caracdispdesc->query($query);

            $ddd = new Caracdispdesc();

            while($resul && $row = $caracdispdesc->fetch_object($resul))
            {
                if ($ddd->charger($row->id, $lang))
                {
                    $ddd->classement += $offset;
                    $ddd->maj();
                }
            }

            $caracdispdesc->classement = $classement;
            $caracdispdesc->maj();
            
            
        }
        redirige("caracteristique_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    /**
     * 
     * increment or decrement the current caracdispdesc rank.
     * 
     * @param int $idcaracdispdesc caracdisdesc id to modify
     * @param sting $type M for increase, D pour decrease rank
     * @param int $lang current lang
     */
    function modClassementCaracdisp($idcaracdispdesc, $type, $lang)
    {
        $caracdispdesc = new Caracdispdesc();

        if ($caracdispdesc->charger($idcaracdispdesc, $lang))
        {
            $remplace = new Caracdispdesc();

            if ($type == "M")
            {
                    $where = "classement<" . $caracdispdesc->classement . " order by classement desc";
            } else if ($type == "D") {
                    $where  = "classement>" . $caracdispdesc->classement . " order by classement";
            }

            $caracdisp = new Caracdisp();

            $query = "
                    select
                            *
                    from
                            $caracdispdesc->table
                    where
                            lang=$lang
                    and
                            caracdisp in (select id from $caracdisp->table where caracteristique = ".$this->id.")
                    and
                            $where
                    limit
                            0, 1
            ";

            if ($remplace->getVars($query))
            {
                $sauv = $remplace->classement;

                $remplace->classement = $caracdispdesc->classement;
                $caracdispdesc->classement = $sauv;

                $remplace->maj();
                $caracdispdesc->maj();
            }
        }
        redirige("caracteristique_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    public function modifier($titre, $chapo, $description, $affiche, $caracdisp, $lang)
    {
        $this->verifyLoaded();
                
        $caracdesc = new Caracteristiquedesc($this->id, $lang);
        
        $caracdesc->titre = $titre;
        $caracdesc->chapo = nl2br($chapo);
        $caracdesc->description = nl2br($description);
        
        $this->affiche = ($affiche != "")?1:0;
        
        $this->maj();
        if($caracdesc->id)
        {
            $caracdesc->maj();
        } else {
            $caracdesc->lang = $lang;
            $caracdesc->caracteristique = $this->id;
            $caracdesc->add();
        }
        
        ActionsModules::instance()->appel_module("modcaracteristique", new Caracteristique($this->id));
        
        //Caracdispdesc
        if(!empty($caracdisp) && is_array($caracdisp))
        {
            foreach($caracdisp as $id => $value)
            {
                $caracdispdesc = new Caracdispdesc();
                
                $caracdispdesc->charger_caracdisp($id, $lang);
                
                $caracdispdesc->titre = $value;
                
                if($caracdispdesc->id)
                {
                    $caracdispdesc->maj();
                } else {
                    $caracdispdesc->caracdisp = $id;
                    $caracdispdesc->lang = $lang;
                    $caracdispdesc->classement = $this->getMaxCaracdispRank($this->id, $lang) + 1;
                    
                    $caracdispdesc->add();
                }
                $caracdisp = new Caracdisp($id);
                ActionsModules::instance()->appel_module("modcaracdisp", $caracdisp);
            }
        }
        
        redirige("caracteristique_modifier.php?id=".$this->id."&lang=".$lang);
        
    }
    
    public function addCaracdisp($title, $lang)
    {
        $this->verifyLoaded();
        
        $caracdisp = new Caracdisp();
        $caracdisp->caracteristique = $this->id;
        $caracdisp->id = $caracdisp->add();
        
        $caracdispdesc = new Caracdispdesc();
        $caracdispdesc->caracdisp = $caracdisp->id;
        $caracdispdesc->lang = $lang;
        $caracdispdesc->classement = $this->getMaxCaracdispRank($this->id, $lang) + 1;
        $caracdispdesc->titre = $title;
        
        $caracdispdesc->add();
        
        ActionsModules::instance()->appel_module("ajcaracdisp", $caracdisp);
        
        redirige("caracteristique_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
    public function delCaracdisp($caracdisp, $lang)
    {
        $tcaracdisp = new Caracdisp($caracdisp);
        $tcaracdisp->delete();

        ActionsModules::instance()->appel_module("suppcaracdisp", $tcaracdisp);
        
        redirige("caracteristique_modifier.php?id=".$this->id."&lang=".$lang);
    }
    
}