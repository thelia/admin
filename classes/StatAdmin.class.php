<?php

class StatAdmin extends Baseobj {


    public function getNbClient()
    {
        return $this->executeCountQuery("select count(id) as nb from ".Client::TABLE);
    }
    
    public function getNbCategory()
    {
        return $this->executeCountQuery("select count(id) as nb from ".  Rubrique::TABLE);
    }
    
    
    public function getNbProduct()
    {
        return $this->executeCountQuery("select count(id) as nb from ". Produit::TABLE);
    }
    
    public function getNbProductOnLine()
    {
        return $this->executeCountQuery("select count(id) as nb from ". Produit::TABLE. " where ligne=1");
    }
    
    public function getNbProductOffLine()
    {
        return $this->executeCountQuery("select count(id) as nb from ". Produit::TABLE. " where ligne=0");
    }
    
    public function getNbCommand()
    {
        return $this->executeCountQuery("select count(id) as nb from ". Commande::TABLE);
    }
    
    public function getNbCommandToBeProcess($period = "")
    {
        $query = "select count(id) as nb from ". Commande::TABLE." where statut<".Commande::TRAITEMENT;
        $resPeriod = $this->getPeriod($period,'datefact');
        $query .= ' AND '.$resPeriod;
        return $this->executeCountQuery($query);
    }
    
    public function getNbCommandToPaid()
    {
        return $this->executeCountQuery("select count(id) as nb from ". Commande::TABLE." where statut=".Commande::NONPAYE);
    }
    public function getNbCommandPaid()
    {
        return $this->executeCountQuery("select count(id) as nb from ". Commande::TABLE." where statut=".Commande::PAYE);
    }
    
    public function getNbCommandProcessed($period = "")
    {
        $query = "select count(id) as nb from ". Commande::TABLE." where statut=".Commande::TRAITEMENT;
        $resPeriod = $this->getPeriod($period,'datefact');
        $query .= ' AND '.$resPeriod;
        return $this->executeCountQuery($query);
    }
    
    public function getNbCommandSend()
    {
        return $this->executeCountQuery("select count(id) as nb from ". Commande::TABLE." where statut=".Commande::EXPEDIE);
    }
    
    public function getNbCommandCanceled($period = "")
    {
        $query = "select count(id) as nb from ". Commande::TABLE." where statut=".Commande::ANNULE;
        $resPeriod = $this->getPeriod($period,'datefact');
        $query .= ' AND '.$resPeriod;
        return $this->executeCountQuery($query);
    }
    
    public function getChippingPrice($period, $date = null)
    {
        $query = 'SELECT SUM(c.port) AS port FROM '.Commande::TABLE.' c WHERE c.statut NOT IN ('.Commande::ANNULE.','.Commande::NONPAYE.')';
        $resPeriod = $this->getPeriod($period,'c.datefact', $date);
        $query .= ' AND '.$resPeriod;
        return $this->executeCountQuery($query, "port");
    }
    
    public function getDiscount($period, $date = null)
    {
        $query = 'SELECT SUM(c.remise) AS remise FROM '.Commande::TABLE.' c WHERE c.statut NOT IN ('.Commande::ANNULE.','.Commande::NONPAYE.')';
        $resPeriod = $this->getPeriod($period,'c.datefact', $date);
        $query .= ' AND '.$resPeriod;
        return $this->executeCountQuery($query, "remise");
    }
    
    protected function getBaseTurnover()
    {
        return 'SELECT SUM(v.quantite*v.prixu) AS ca FROM '.Venteprod::TABLE.' v left join '.Commande::TABLE.' c ON c.id=v.commande WHERE c.statut NOT IN ('.Commande::ANNULE.','.Commande::NONPAYE.')';
    }
    
    public function getTurnover($period)
    {
        $query = $this->getBaseTurnover();
        $resPeriod = $this->getPeriod($period,'c.datefact');
        $query .= 'AND '.$resPeriod;
        return $this->executeCountQuery($query, 'ca') + $this->getChippingPrice($period) - $this->getDiscount($period);
    }
    
    public function getTurnoverWithoutChippingPrice($period)
    {
        $query = $this->getBaseTurnover();
        $resPeriod = $this->getPeriod($period,'c.datefact');
        $query .= 'AND '.$resPeriod;
        return $this->executeCountQuery($query, 'ca') - $this->getDiscount($period);
    }
    
    public function getAverageCart($period)
    {
        $ca = $this->getTurnover($period);
        $query = 'SELECT count(c.id) AS nbCommande FROM '.Commande::TABLE.' c WHERE c.statut NOT IN ('.Commande::ANNULE.','.Commande::NONPAYE.')';
        $resPeriod = $this->getPeriod($period,'c.datefact');
        $query .= ' AND '.$resPeriod;
        $nbCommande = $this->executeCountQuery($query, 'nbCommande');
        return $nbCommande ? ($ca/$nbCommande):0;
    }
    
    public function getDetailTurnover($period){
        $query = 'SELECT c.datefact, SUM(v.quantite*v.prixu) AS ca FROM '.Venteprod::TABLE.' v left join '.Commande::TABLE.' c ON c.id=v.commande WHERE c.statut NOT IN ('.Commande::ANNULE.','.Commande::NONPAYE.')';
        $resperiod = $this->getPeriod($period, 'c.datefact');
        $query .= 'AND '.$resperiod.' GROUP BY c.datefact';
        $return = array();
        foreach($this->query_liste($query) as $turnover)
        {
            $return[] = array(
                "date" => $turnover->datefact,
                "ca" => formatter_somme($turnover->ca + $this->getChippingPrice(null, $turnover->datefact) - $this->getDiscount(null, $turnover->datefact))
            );
        }
        
        return $return;
    }
    
    public function getPeriod($period, $column, $date = null){
        $res = array();
        switch($period){
            
            case 'yesterday':
                $res = $column.' like "'.date('Y-m-d', strtotime('-1 day')).'"';
                break;
            case 'last30days':
                $res = $column.' BETWEEN "'.date('Y-m-d', strtotime('-30 day')).'" AND "'. date('Y-m-d').'"';
                break;
            case "month":
                $res = $column.' BETWEEN "'.date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y'))).'" AND "'.date('Y-m-d',  mktime(0, 0, 0, date('m')+1, 1, date('Y'))).'"';
                break;
            case "lastmonth":
                $res = $column.' BETWEEN "'.date('Y-m-d', mktime(0, 0, 0, date('m')-1, 1, date('Y'))).'" AND "'.date('Y-m-d',  mktime(0, 0, 0, date('m'), 1, date('Y'))).'"';
                break;
            case "year":
                $res = $column.' BETWEEN "'.date('Y-m-d', mktime(0, 0, 0, 1, 1, date('Y'))).'" AND "'.date('Y-m-d', mktime(0,0,0,12,31,date('Y'))).'"';
                break;
            case "lastyear":
                $res = $column.' BETWEEN "'.date('Y-m-d', mktime(0, 0, 0, 1, 1, date('Y')-1)).'" AND "'.date('Y-m-d', mktime(0,0,0,12,31,date('Y')-1)).'"';
                break;
            case 'yesterday':
                $res = $column.' like "'.date('Y-m-d', strtotime("yesterday")).'"';
                break;
            case 'today':
                $res = $column.' like "'.date('Y-m-d').'"';
                break;
            default:
                if ($date)
                {
                    $res = $column. ' like "'.$date.'"';
                } else {
                    $res = 1;
                }
                break;
        }
            
        return $res;
            
    }
    
    public function executeCountQuery($query, $alias = 'nb', $default=0){
        $num = CacheBase::getCache()->get($query);
        
        if ($num < 0 || $num == "")
        {
            try
            {
                $resul = $this->query($query, true);
                $num = $this->get_result($resul, 0, $alias);
            } catch (Exception $e) {
                Tlog::error("error for this query : ".$query);
                Tlog::error($e->getMessage());
                return $default;
            }
            CacheBase::getCache()->set($query, $num);
        }  
        return $num;
    }
}





?>
