<?php

class ClientAdmin extends Client
{
    public function __construct($id = 0, $ref = "") {
        parent::__construct();
        
        if($id > 0)
        {
            $this->charger_id($id);
        } else if($ref != "") {
            $this->charger_ref($ref);
        }
    }
    
    /**
     * 
     * @param int $id
     * @return \ClientAdmin
     */
    public static function getInstance($id = 0){
        return new ClientAdmin($id);
    }
    
    /**
     * 
     * @param string $ref
     * @return \ClientAdmin
     */
    public static function getInstanceByRef($ref)
    {
        return new ClientAdmin(0, $ref);
    }
    
    public function deleteOrder($id)
    {
        $commande = new Commande();
        if($commande->charger($id))
        {
            $commande->annuler();
        }
        
        $this->redirect();
    }
    
    public function deleteAddress($id)
    {
        $addressToDelete = new Adresse();
        if($addressToDelete->charger($id))
        {
            $addressToDelete->delete();
        }
        
        $this->redirect();
    }
    
    public function getSearchList($searchTerm)
    {
        $searchTerm = $this->escape_string(trim($searchTerm));
        
        if($searchTerm==='')
            return array();
        
        return $this->query_liste("SELECT * FROM " . self::TABLE . " WHERE nom LIKE '%$searchTerm%' OR prenom LIKE '%$searchTerm%' OR entreprise LIKE '%$searchTerm%' OR ville LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%' LIMIT 100");
    }
    
    public function getRequest($type = 'list', $order='ASC', $critere='nom', $debut=0, $nbres=30)
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
                    WHERE 1
                    ORDER BY $critere $order
                    LIMIT $debut, $nbres";
    }
    
    public function getList($order, $critere, $debut, $nbres)
    {
        $query = $this->getRequest('list', $order, $critere, $debut, $nbres);
        $resul = $this->query($query);
        
        $retour = array();
        
        while($resul && $row = $this->fetch_object($resul))
        {
            $thisClient = array();
            
            $thisClient['ref'] = $row->ref;
            $thisClient['entreprise'] = $row->entreprise;
            $thisClient['nom'] = $row->nom;
            $thisClient['prenom'] = $row->prenom;
            
            $thisClient['email'] = $row->email;
            
            $commande = new Commande();
            $devise = new Devise();
    
            $querycom = "SELECT id FROM $commande->table WHERE client=$row->id AND statut NOT IN(".Commande::NONPAYE.",".Commande::ANNULE.") ORDER BY date DESC LIMIT 0,1";
            $resulcom = $commande->query($querycom);
                
            if($commande->num_rows($resulcom)>0)
            {
                $idcom = $commande->get_result($resulcom,0,"id");
                $commande->charger($idcom);
    
                $devise->charger($commande->devise);
    
                $thisClient['date'] = strftime("%d/%m/%Y %H:%M:%S", strtotime($commande->date));
                $thisClient['somme'] = formatter_somme($commande->total(true, true)) . ' ' . $devise->symbole;
            }
            else
            {
                $thisClient['date'] = '';
                $thisClient['somme'] = '';
            }
            
            $retour[] = $thisClient;
        }
        
        return $retour;
    }
    
    /**
     * 
     * @param float $pourcentage
     * @param int $raison
     * @param string $entreprise
     * @param string $siret
     * @param string $intracom
     * @param string $nom
     * @param string $prenom
     * @param string $adresse1
     * @param string $adresse2
     * @param string $cpostal
     * @param string $ville
     * @param int $pays
     * @param string $telfixe
     * @param string $telpORt
     * @param string $email
     * @param int $type
     */
    public function edit($pourcentage, $raison, $entreprise, $siret, $intracom, $nom, $prenom, $adresse1, $adresse2,$adresse3, $cpostal, $ville, $pays, $telfixe, $telpORt, $email, $type)
    {
        $this->pourcentage = $pourcentage;
        $this->raison = $raison;
        $this->entreprise = $entreprise;
        $this->siret = $siret;
        $this->intracom = $intracom;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->adresse1 = $adresse1;
        $this->adresse2 = $adresse2;
        $this->adresse3 = $adresse3;
        $this->cpostal = $cpostal;
        $this->ville = $ville;
        $this->pays = $pays;
        $this->telfixe = $telfixe;
        $this->telport = $telpORt;
        if($this->email != $email)
        {
            $this->email = '';

            $testClientEmailUnicity = new Client();
            if(filter_var($email, FILTER_VALIDATE_EMAIL) && !$testClientEmailUnicity->charger_mail($email))
                $this->email = $email;
        }

        $this->pourcentage = $pourcentage;
        $this->type = ($type=='on')?1:0;

        $this->tryUpdate();
        
        $this->redirect();
    }
    
    public function editAddress($id, $libelle, $raison, $entreprise, $nom, $prenom, $adresse1, $adresse2, $adresse3, $cpostal, $ville, $tel, $pays)
    {
            $addressToEdit = new Adresse();
            if($addressToEdit->charger($id))
            {
                $addressToEdit->libelle = $libelle;
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
                                
                if($addressToEdit->libelle!="" && $addressToEdit->raison!="" && $addressToEdit->prenom!="" && $addressToEdit->nom!="" && $addressToEdit->adresse1 !="" && $addressToEdit->cpostal!="" && $addressToEdit->ville !="" && $addressToEdit->pays !="")
                {
                    $addressToEdit->maj();
                    
                    ActionsModules::instance()->appel_module("apres_modifierlivraison", $addressToEdit);
                }
                else
                {
                    throw new TheliaAdminException("impossible to edit adresse's client",  TheliaAdminException::CLIENT_ADRESS_EDIT_ERROR);
                }
            }
            
            $this->redirect();
    }
    
    public function addAddress($libelle, $raison, $nom, $prenom, $entreprise, $adresse1, $adresse2, $adresse3, $cpostal, $ville, $pays, $tel)
    {
        $addressToAdd = new Adresse();
        $addressToAdd->libelle = strip_tags($libelle);
        $addressToAdd->raison = strip_tags($raison);
        $addressToAdd->nom = strip_tags($nom);
        $addressToAdd->entreprise = strip_tags($entreprise);
        $addressToAdd->prenom = strip_tags($prenom);
        $addressToAdd->tel = strip_tags($tel);
        $addressToAdd->adresse1 = strip_tags($adresse1);
        $addressToAdd->adresse2 = strip_tags($adresse2);
        $addressToAdd->adresse3 = strip_tags($adresse3);
        $addressToAdd->cpostal = strip_tags($cpostal);
        $addressToAdd->ville = strip_tags($ville);
        $addressToAdd->pays = strip_tags($pays);

        if($addressToAdd->libelle!="" && $addressToAdd->raison!="" && $addressToAdd->prenom!="" && $addressToAdd->nom!="" && $addressToAdd->adresse1 !="" && $addressToAdd->cpostal!="" && $addressToAdd->ville !="" && $addressToAdd->pays !="")
        {
            $addressToAdd->client = $this->id;
            $addressToAdd->id = $addressToAdd->add();

            ActionsModules::instance()->appel_module("aj_adresselivraison", $addressToEdit);

        }
        else
        {
            throw new TheliaAdminException("impossible to add new Adress on this client", TheliaAdminException::CLIENT_ADD_ADRESS);
        }
        
        $this->redirect();
    }
    
    protected function tryUpdate()
    {
        if($this->raison!="" && $this->prenom!="" && $this->nom!="" && $this->email!="" && $this->adresse1 !="" && $this->cpostal!="" && $this->ville !="" && $this->pays !="")
        {
            $this->maj();

            ActionsModules::instance()->appel_module("modcli", new Client($this->id));
        }
        else
        {
           throw new TheliaAdminException("Impossible to edit this client", TheliaAdminException::CLIENT_EDIT_ERROR);
        }
    }
    
    public function sendMailCreation($client, $password)
    {
        $message = new Message("creation_client");
        $messagedesc = new Messagedesc($message->id);

        $sujet = $this->subSendMailCreation($messagedesc->titre, $client, $password);
        $corps = $this->subSendMailCreation($messagedesc->description, $client, $password);
        $corpstext = $this->subSendMailCreation($messagedesc->descriptiontext, $client, $password);

        Mail::envoyer(
            $client->prenom . " " . $client->nom, $client->email,
            Variable::lire("nomsite"), Variable::lire("emailfrom"),
            $sujet,
            $corps, $corpstext);
    }
    
    public function subSendMailCreation($corps, $client, $password)
    {
        $raisondesc = new Raisondesc($client->raison, ActionsLang::instance()->get_id_langue_courante());

        $paysdesc = new Paysdesc();
        $paysdesc->charger($client->pays);

        $corps = str_replace("__NOMSITE__",Variable::lire("nomsite"),$corps);
        $corps = str_replace("__EMAIL__",$client->email,$corps);
        $corps = str_replace("__MOTDEPASSE__",$password,$corps);
        $corps = str_replace("__URLSITE__",Variable::lire("urlsite"),$corps);
        $corps = str_replace("__NOM__",$client->nom,$corps);
        $corps = str_replace("__PRENOM__",$client->prenom,$corps);
        $corps = str_replace("__ADRESSE1__",$client->adresse1,$corps);
        $corps = str_replace("__ADRESSE2__",$client->adresse2,$corps);
        $corps = str_replace("__ADRESSE3__",$client->adresse3,$corps);
        $corps = str_replace("__VILLE__",$client->ville,$corps);
        $corps = str_replace("__CPOSTAL__",$client->cpostal,$corps);
        $corps = str_replace("__TELEPHONE__",$client->telfixe,$corps);
        $corps = str_replace("__CIVILITE__",$raisondesc->court,$corps);
        $corps = str_replace("__PAYS__",$paysdesc->titre,$corps);
        
        return $corps;
    }
    
    public function redirect()
    {
        redirige("client_visualiser.php?ref=".$this->ref);
    }
    
}
