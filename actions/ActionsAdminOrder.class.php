<?php
use Symfony\Component\HttpFoundation\Request;

class ActionsAdminOrder extends ActionsAdminBase
{
    
    private static $instance = false;
    
    protected function __construct() { }
    
    /**
     * 
     * @return \ActionsAdminOrder
     */
    public static function getInstance()
    {
        if(self::$instance === false) self::$instance = new ActionsAdminOrder();
        
        return self::$instance;
    }
    
    public function action(Request $request)
    {
        switch($request->get("action"))
        {
            case "editVenteadr":
                OrderAdmin::getInstanceByRef($request->request->get("ref"))->editVenteAdr(
                    $request->request->get("id"), 
                    $request->request->get("raison"),
                    $request->request->get("entreprise"), 
                    $request->request->get("nom"), 
                    $request->request->get("prenom"), 
                    $request->request->get("adresse1"), 
                    $request->request->get("adresse2"), 
                    $request->request->get("adresse3"), 
                    $request->request->get("cpostal"), 
                    $request->request->get("ville"), 
                    $request->request->get("tel"), 
                    $request->request->get("pays")
                );
                break;
            case "createOrder":
                OrderAdmin::getInstance()->createOrder(
                    $request->request->get("facturation_raison"),
                    $request->request->get("facturation_entreprise"), 
                    $request->request->get("facturation_nom"), 
                    $request->request->get("facturation_prenom"), 
                    $request->request->get("facturation_adresse1"), 
                    $request->request->get("facturation_adresse2"), 
                    $request->request->get("facturation_adresse3"), 
                    $request->request->get("facturation_cpostal"), 
                    $request->request->get("facturation_ville"), 
                    $request->request->get("facturation_tel"), 
                    $request->request->get("facturation_pays"),
                    $request->request->get("livraison_raison"),
                    $request->request->get("livraison_entreprise"), 
                    $request->request->get("livraison_nom"), 
                    $request->request->get("livraison_prenom"), 
                    $request->request->get("livraison_adresse1"), 
                    $request->request->get("livraison_adresse2"), 
                    $request->request->get("livraison_adresse3"), 
                    $request->request->get("livraison_cpostal"), 
                    $request->request->get("livraison_ville"), 
                    $request->request->get("livraison_tel"), 
                    $request->request->get("livraison_pays"),
                    $request->request->get("type_paiement"),
                    $request->request->get("type_transport"),
                    $request->request->get("fraisport"),
                    $request->request->get("remise"),
                    $request->request->get("client_selected"),
                    $request->request->get("ref_client"),
                    $request->request->get("email"),
                    $this->getPanier($request),
                    $request->request->get("apply_client_discount") == 'on',
                    $request->request->get("call_mail") == 'on',
                    $request->request->get("call_payment") == 'on'
                );
                break;
        }
    }
    
    public function getPanier(Request $request)
    {        
        $panier = new Panier();
        
        $listeRef =$request->request->get("ref");
        $listeVariants =$request->request->get("perso");
        $listeQuantite =$request->request->get("quantite");
        $listePrixU =$request->request->get("prixu");
        $listeTVA =$request->request->get("tva");
        
        for($i=0; $i<count($listeRef); $i++)
        {   
            if($listeVariants[$i])
            {
                $tabPersoRecu = explode('_', $listeVariants[$i]);
                $ps = new Perso();
                $ps->declinaison = $tabPersoRecu[0];
                $ps->valeur = $tabPersoRecu[1];
            }
            
            $article = $panier->ajouter(
                $listeRef[$i],
                $listeQuantite[$i],
                ($listeVariants[$i] ? array($ps) : array()),
                0,
                1
            );
                    
            if($panier->tabarticle[$article]->produit->ref == $listeRef[$i] && $panier->tabarticle[$article]->perso == ($listeVariants[$i] ? array($ps) : array()) ) {
                $panier->tabarticle[$article]->produit->prix = $listePrixU[$i];
                $panier->tabarticle[$article]->produit->promo = 0;
                $panier->tabarticle[$article]->produit->tva = $listeTVA[$i];
            }
        }
        
        return $panier;
    }
}
