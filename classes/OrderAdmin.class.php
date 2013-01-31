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
    
    public static function getInstanceByRef($ref = '')
    {
        $orderAdmin = new OrderAdmin($id);
        $orderAdmin->charger_ref($ref);
        return $orderAdmin;
    }
    
    public function getRequest($type = 'list', $search = '', $critere = 'date', $order = 'DESC', $debut = 0, $nbres = 30)
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
                    ORDER BY $critere $order
                    LIMIT $debut, $nbres";
    }
    
    public function getList($critere, $order, $debut, $nbres, $search = '')
    {
        $return = array();

        $qOrders = $this->getRequest('list', $search, $critere, $order, $debut, $nbres);
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
        
        $return = array();
        
        if($searchTerm==='' && count($clientFoundList) == 0)
            return $return;
        
        $qOrders = "SELECT * FROM " . self::TABLE . "
            WHERE ref like '%$searchTerm%'
                OR facture like '%$searchTerm%'
                OR transaction like '%$searchTerm%'
                " . (count($clientFoundList)>0?" OR client IN (" . implode(',', $clientFoundList) . ")":'') . "
                " . (strtotime($searchTerm)?" OR date LIKE '" . date('Y-m-d', strtotime($searchTerm)) . "%'":'') . "
            LIMIT 100";
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
            $thisOrderArray['transaction']  = $theOrder->transaction;
            $thisOrderArray['facture']  = $theOrder->facture;
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
    
    public function editVenteAdr($id, $raison, $entreprise, $nom, $prenom, $adresse1, $adresse2, $adresse3, $cpostal, $ville, $tel, $pays)
    {
            $addressToEdit = new Venteadr();
            if($addressToEdit->charger($id))
            {
                $addressToEdit->raison = $raison;
                $addressToEdit->entreprise = $entreprise;
                $addressToEdit->prenom = $prenom;
                $addressToEdit->nom = $nom;
                $addressToEdit->adresse1 = $adresse1;
                $addressToEdit->adresse2 = $adresse2;
                $addressToEdit->adresse3 = $adresse3;
                $addressToEdit->cpostal = $cpostal;
                $addressToEdit->ville = $ville;
                $addressToEdit->tel = $tel;
                $addressToEdit->pays = $pays;
                                
                if($addressToEdit->raison!="" && $addressToEdit->prenom!="" && $addressToEdit->nom!="" && $addressToEdit->adresse1 !="" && $addressToEdit->cpostal!="" && $addressToEdit->ville !="" && $addressToEdit->pays !="")
                {
                    $addressToEdit->maj();
                    
                    ActionsModules::instance()->appel_module("apres_modifierventeadr", $addressToEdit);
                }
                else
                {
                    throw new TheliaAdminException("impossible to edit venteadr",  TheliaAdminException::ORDER_VENTEADR_EDIT_ERROR);
                }
            }
            
            $this->redirect();
    }
    
    public function redirect()
    {
        redirige("commande_details.php?ref=".$this->ref);
    }
}
