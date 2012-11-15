<?php

function lister_commandes($critere, $order, $debut, $nbres, $search = '') {

	$commande = new Commande();

	$i=0;

	$query = "select * from $commande->table where 1 $search order by $critere $order limit $debut,$nbres";

  	$resul = $commande->query($query);
        
        $return = array();

  	while($resul && $row = $commande->fetch_object($resul, 'Commande')){
            
            $thisCommande = array();

            $client = new Client();
            $client->charger_id($row->client);

            $statutdesc = new Statutdesc();
            $statutdesc->charger($row->statut);

            $devise = new Devise();
            $devise->charger($row->devise);

            $total = formatter_somme($row->total(true, true));

            $date = strftime("%d/%m/%y %H:%M:%S", strtotime($row->date));

            $fond="ligne_".($i++%2 ? "claire":"fonce")."_rub";
            
            $thisCommande['ref']  = $row->ref;
            $thisCommande['date'] = $date;
            $thisCommande['client'] = array(
                "entreprise" => $client->entreprise,
                "ref" => $client->ref,
                "nom" => $client->nom,
                "prenom" => $client->prenom
            );
            $thisCommande['total'] = $total;
            $thisCommande['devise'] = $devise->symbole;
            $thisCommande['titre'] = $statutdesc->titre;
            $thisCommande['statut'] = $row->statut;
            $thisCommande['id'] = $row->id;
            
            $return[] = $thisCommande;

	}
        
        return $return;
}
?>