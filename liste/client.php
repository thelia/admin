<?php
function liste_clients($order, $critere, $debut, $nombre)
{
    $i=0;

    $client = new Client();

    $query = "SELECT * FROM $client->table ORDER BY $critere $order LIMIT $debut, $nombre";
    $resul = $client->query($query);
    
    $retour = array();
    
    while($resul && $row = $client->fetch_object($resul))
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
?>