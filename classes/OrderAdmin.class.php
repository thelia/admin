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
    
    public function getPaymentTypesList()
    {
        $q = "select * from " . Modules::TABLE . " where type=1 and actif=1";
        return $this->query_liste($q, 'Modules');
    }
    
    public function getDeliveryTypesList()
    {
        $q = "select * from " . Modules::TABLE . " where type=2 and actif=1";
        return $this->query_liste($q, 'Modules');
    }
    
    public function createOrder($facturation_raison, $facturation_entreprise, $facturation_nom, $facturation_prenom, $facturation_adresse1, $facturation_adresse2, $facturation_adresse3, $facturation_cpostal, $facturation_ville, $facturation_tel, $facturation_pays, $livraison_raison, $livraison_entreprise, $livraison_nom, $livraison_prenom, $livraison_adresse1, $livraison_adresse2, $livraison_adresse3, $livraison_cpostal, $livraison_ville, $livraison_tel, $livraison_pays, $type_paiement, $type_transport, $fraisport, $remise, $client_selected, $ref_client, $email, \Panier $panier)
    {
        $client = new Client();
        
        if($client_selected == 1)
            $clientOK = $client->charger_ref($ref_client);
        else
        {
            if($email != '' && $client->charger_mail($email))
                $existeDeja = 1;
            elseif($email != '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
                $badFormat = 1;
            else
            {
                $client->email = $email;
                $client->raison = $facturation_raison;
                $client->entreprise = $facturation_entreprise;
                $client->prenom = $facturation_prenom;
                $client->nom = $facturation_nom;
                $client->adresse1 = $facturation_adresse1;
                $client->adresse2 = $facturation_adresse2;
                $client->adresse3 = $facturation_adresse3;
                $client->cpostal = $facturation_cpostal;
                $client->ville = $facturation_ville;
                $client->tel = $facturation_tel;
                $client->pays = $facturation_pays;
                
                $pass = genpass(8);
                $client->motdepasse = $pass;
            }
        }
        
        $facturationAddress = new Venteadr();
        $facturationAddress->raison = $facturation_raison;
        $facturationAddress->entreprise = $facturation_entreprise;
        $facturationAddress->prenom = $facturation_prenom;
        $facturationAddress->nom = $facturation_nom;
        $facturationAddress->adresse1 = $facturation_adresse1;
        $facturationAddress->adresse2 = $facturation_adresse2;
        $facturationAddress->adresse3 = $facturation_adresse3;
        $facturationAddress->cpostal = $facturation_cpostal;
        $facturationAddress->ville = $facturation_ville;
        $facturationAddress->tel = $facturation_tel;
        $facturationAddress->pays = $facturation_pays;
        
        $livraisonAddress = new Venteadr();
        $livraisonAddress->raison = $livraison_raison;
        $livraisonAddress->entreprise = $livraison_entreprise;
        $livraisonAddress->prenom = $livraison_prenom;
        $livraisonAddress->nom = $livraison_nom;
        $livraisonAddress->adresse1 = $livraison_adresse1;
        $livraisonAddress->adresse2 = $livraison_adresse2;
        $livraisonAddress->adresse3 = $livraison_adresse3;
        $livraisonAddress->cpostal = $livraison_cpostal;
        $livraisonAddress->ville = $livraison_ville;
        $livraisonAddress->tel = $livraison_tel;
        $livraisonAddress->pays = $livraison_pays;
        
        $order = new Commande();
        $order->date = date("Y-m-d H:i:s");
        $order->livraison = "L" . date("ymdHis") . strtoupper(ereg_caracspec(substr($client->prenom,0, 3)));
        $order->transport = $type_transport;
        $order->paiement = $type_paiement;
        $order->port = $fraisport;
        $order->remise = $remise;
        $order->statut = Commande::NONPAYE;
        
        if($facturationAddress->raison!="" && $facturationAddress->prenom!="" && $facturationAddress->nom!="" && $facturationAddress->adresse1 !="" && $facturationAddress->cpostal!="" && $facturationAddress->ville !="" && $facturationAddress->pays !="" && $livraisonAddress->raison!="" && $livraisonAddress->prenom!="" && $livraisonAddress->nom!="" && $livraisonAddress->adresse1 !="" && $livraisonAddress->cpostal!="" && $livraisonAddress->ville !="" && $livraisonAddress->pays !="" && $order->transport != "" && $order->paiement != "" && $panier->nbart > 1 && ( $clientOK || ($client_selected!=1 && !$existeDeja && !$badFormat) ) && $email!='')
        {
            echo 5;
            exit;
            
            $facturationAddress->id = $facturationAddress->add();
            $livraisonAddress->id = $livraisonAddress->add();
            if(!$client->id)
            {
                $client->crypter();
                $client->id = $client->add();
                $client->ref = date("ymdHi") . genid($client->id, 6);
		$client->maj();
                ClientAdmin::getInstance()->sendMailCreation($client, $pass);
            }
            
            $devise = ActionsDevises::instance()->get_devise_courante();
            
            $order->adrfact = $facturationAddress->id;
            $order->adrlivr = $livraisonAddress->id;
            $order->client = $client->id;
            $order->devise = $devise->id;
            $commande->taux = $devise->taux;
            
            $commande->lang = ActionsLang::instance()->get_id_langue_courante();
            
            $order->id = $order->add();
            
            $order->ref = "C" . date("ymdHi") . genid($order->id, 6);

            $order->maj();
        }
        else
        {
            if($existeDeja)
                throw new TheliaAdminException("error creating order",  TheliaAdminException::EMAIL_ALREADY_EXISTS);
            if($badFormat)
                throw new TheliaAdminException("error creating order",  TheliaAdminException::EMAIL_FORMAT_ERROR);
            else
                throw new TheliaAdminException("error creating order",  TheliaAdminException::ORDER_ADD_ERROR);
        }
        
       //$this->redirect();
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
