<?php

class OrderAdmin extends Commande
{
    public function __construct($id = 0)
    {
        parent::__construct();
        
        if($id > 0)
            $this->charger($id);
    }
    
    public static function getInstance($id = 0)
    {
        return new OrderAdmin($id);
    }
    
    public function getRequest($type = 'list', $search = '', $critere = 'id DESC', $debut = 0, $nbres = 30)
    {
        if($type == 'count')
        {
            $will = "COUNT(*)";
        }
        else
        {
            $will = "*";
        }
        
        return "SELECT $will
                    FROM " . $this->table . "
                    WHERE 1 $search
                    ORDER BY $critere
                    LIMIT $debut, $nbres";
    }
    
    public function getList($critere, $order, $debut, $nbres, $search = '')
    {
        $return = array();

        $qOrders = $this->getRequest('list', $search, $critere, $debut, $nbres);
  	$rOrders = $this->query($qOrders);
        
  	while($rOrders && $theOrder = $this->fetch_object($rOrders, 'Commande'))
        {   
            $thisOrderArray = array();

            $client = new Client();
            $client->charger_id($theOrder->client);

            $statutdesc = new Statutdesc();
            $statutdesc->charger($theOrder->statut);

            $devise = new Devise();
            $devise->charger($theOrder->devise);

            $total = formatter_somme($theOrder->total(true, true));

            $date = strftime("%d/%m/%y %H:%M:%S", strtotime($theOrder->date));

            $thisOrderArray['ref']  = $theOrder->ref;
            $thisOrderArray['date'] = $date;
            $thisOrderArray['client'] = array(
                "entreprise" => $client->entreprise,
                "ref" => $client->ref,
                "nom" => $client->nom,
                "prenom" => $client->prenom
            );
            $thisOrderArray['total'] = $total;
            $thisOrderArray['devise'] = $devise->symbole;
            $thisOrderArray['titre'] = $statutdesc->titre;
            $thisOrderArray['statut'] = $theOrder->statut;
            $thisOrderArray['id'] = $theOrder->id;
            
            $return[] = $thisOrderArray;

	}
        
        return $return;
    }
    
    public function getSearchList($searchTerm, $clientFoundList)
    {
        $searchTerm = $this->escape_string(trim($searchTerm));
        
        return $this->query_liste("SELECT * FROM " . self::TABLE . " WHERE client IN (" . implode(',', $clientFoundList) . ") AND ref like '%$searchTerm%' LIMIT 100");
    }
}
