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
        $orderAdmin = new OrderAdmin();
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
    
    public function createOrder($facturation_raison, $facturation_entreprise, $facturation_nom, $facturation_prenom, $facturation_adresse1, $facturation_adresse2, $facturation_adresse3, $facturation_cpostal, $facturation_ville, $facturation_tel, $facturation_pays, $livraison_raison, $livraison_entreprise, $livraison_nom, $livraison_prenom, $livraison_adresse1, $livraison_adresse2, $livraison_adresse3, $livraison_cpostal, $livraison_ville, $livraison_tel, $livraison_pays, $type_paiement, $type_transport, $fraisport, $remise, $client_selected, $ref_client, $email, \Panier $panier, $applyClientDiscount, $callMail, $callPayment)
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
        $order->statut = Commande::NONPAYE;
        $order->transaction = genid($order->id, 6);
        
        $module_paiement = new Modules();
	$module_paiement->charger_id($type_paiement);
        
        if($facturationAddress->raison!="" && $facturationAddress->prenom!="" && $facturationAddress->nom!="" && $facturationAddress->adresse1 !="" && $facturationAddress->cpostal!="" && $facturationAddress->ville !="" && $facturationAddress->pays !="" && $livraisonAddress->raison!="" && $livraisonAddress->prenom!="" && $livraisonAddress->nom!="" && $livraisonAddress->adresse1 !="" && $livraisonAddress->cpostal!="" && $livraisonAddress->ville !="" && $livraisonAddress->pays !="" && $order->transport != "" && is_numeric($fraisport) && $fraisport>=0 && is_numeric($remise) && $remise>=0 && $module_paiement->actif && $order->paiement != "" && $panier->nbart > 0 && ( $clientOK || ($client_selected!=1 && !$existeDeja && !$badFormat) ) && $email!='')
        {
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
            $order->taux = $devise->taux;
            
            $order->lang = ActionsLang::instance()->get_id_langue_courante();
            
            $order->id = $order->add();
            
            $order->ref = "C" . date("ymdHi") . genid($order->id, 6);

            $order->maj();
            
            $total = 0;
            
            foreach($panier->tabarticle as $pos => $article) {
                $venteprod = new Venteprod();

                $dectexte = "\n";

                $stock = new Stock();

                foreach($article->perso as $perso) {

                    $declinaison = new Declinaison();
                    $declinaisondesc = new Declinaisondesc();

                    if(is_numeric($perso->valeur) && ActionsModules::instance()->instancier($module_paiement->nom)->defalqcmd) {

                        // diminution des stocks de déclinaison si on est sur un module de paiement qui défalque de suite
                        $stock->charger($perso->valeur, $article->produit->id);
                        $stock->valeur-=$article->quantite;
                        $stock->maj();
                    }

                    $declinaison->charger($perso->declinaison);
                    $declinaisondesc->charger($declinaison->id);

                    // recup valeur declidisp ou string
                    if($declinaison->isDeclidisp($perso->declinaison)){
                        $declidisp = new Declidisp();
                        $declidispdesc = new Declidispdesc();
                        $declidisp->charger($perso->valeur);
                        $declidispdesc->charger_declidisp($declidisp->id);
                        $dectexte .= "- " . $declinaisondesc->titre . " : " . $declidispdesc->titre . "\n";
                    }
                    else
                        $dectexte .= "- " . $declinaisondesc->titre . " : " . $perso->valeur . "\n";

                }

                // diminution des stocks classiques si on est sur un module de paiement qui défalque de suite

                $produit = new Produit($article->produit->ref);

                if(ActionsModules::instance()->instancier($module_paiement->nom)->defalqcmd) {
                    $produit->stock-=$article->quantite;
                    $produit->maj();
                }

                $venteprod->quantite =  $article->quantite;
                $venteprod->prixu =  $article->produit->prix;

                $venteprod->ref = $article->produit->ref;
                $venteprod->titre = $article->produitdesc->titre . " " . $dectexte;
                $venteprod->chapo = $article->produitdesc->chapo;
                $venteprod->description = $article->produitdesc->description;
                $venteprod->tva =  $article->produit->tva;

                $venteprod->commande = $order->id;
                $venteprod->id = $venteprod->add();

                $correspondanceParent[]=$venteprod->id;

                // ajout dans ventedeclisp des declidisp associées au venteprod
                foreach($article->perso as $perso){
                    $declinaison = new Declinaison();
                    $declinaison->charger($perso->declinaison);

                    // si declidisp (pas un champs libre)
                    if($declinaison->isDeclidisp($perso->declinaison)){
                        $vdec = new Ventedeclidisp();
                        $vdec->venteprod = $venteprod->id;
                        $vdec->declidisp = $perso->valeur;
                        $vdec->add();
                    }
                }

                ActionsModules::instance()->appel_module("apresVenteprodAdmin", $venteprod, $pos);

                $total += $venteprod->prixu * $venteprod->quantite;
            }
            
            foreach($correspondanceParent as $id_panier => $id_venteprod) {
                if($panier->tabarticle[$id_panier]->parent>=0) {
                    $venteprod->charger($id_venteprod);
                    $venteprod->parent = $correspondanceParent[$panier->tabarticle[$id_panier]->parent];
                    $venteprod->maj();
                }
            }
            
            if ($client->pourcentage>0 && $applyClientDiscount)
                $order->remise = $total * $client->pourcentage / 100;
            
            $order->remise += $remise;
            if($order->remise > $total)
                $order->remise = $total;
            
            $order->port = $fraisport;
            
            $order->maj();
            
            ActionsModules::instance()->appel_module("aprescommandeadmin", $order);
            
            if($callMail)
                ActionsModules::instance()->instancier($module_paiement->nom)->mail($order);

            if($callPayment)
                ActionsModules::instance()->instancier($module_paiement->nom)->paiement($order);
            else
                self::getInstance($order->id)->redirect();
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
