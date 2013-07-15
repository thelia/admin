<?php

function lister_declinaisons($critere, $order) {

	$declinaison = new Declinaison();

	$query = "select * from $declinaison->table order by $critere $order";
	$resul = $declinaison->query($query);

	while($resul && $row = $declinaison->fetch_object($resul)){

		$declinaisondesc = new Declinaisondesc();
		$declinaisondesc->charger($row->id);

		$fond="ligne_".($i++%2 ? "claire":"fonce")."_rub";
		?>
		<ul class="<?php echo($fond); ?>">
			<li style="width:770px;"><span id="titredecli_<?php echo $row->id; ?>" class="texte_edit"><?php echo($declinaisondesc->titre); ?></span></li>
			<li style="width:37px;"><a href="<?php echo "declinaison_modifier.php?id=$row->id"; ?>"><?php echo trad('editer', 'admin'); ?></a></li>
			<li style="width:71px;">
			 <div class="bloc_classement">
			    <div class="classement"><a href="declinaison_modifier.php?id=<?php echo($row->id); ?>&action=modclassement&type=M"><img src="gfx/up.gif" border="0" /></a></div>
			    <div class="classement"><span id="classementdecli_<?php echo $row->id; ?>" class="classement_edit"><?php echo $row->classement; ?></span></div>
			    <div class="classement"><a href="declinaison_modifier.php?id=<?php echo($row->id); ?>&action=modclassement&type=D"><img src="gfx/dn.gif" border="0" /></a></div>
			 </div>
			</li>
			<li style="width:37px; text-align:center;"><a onclick="return suppr_declinaison(<?php echo $row->id ?>);" href="<?php echo "declinaison_modifier.php?id=$row->id&action=supprimer"; ?>"><img src="gfx/supprimer.gif" width="9" height="9" border="0" /></a></li>
		</ul>
		<?php
	}
}

function lister_declinaisons_rubrique($idrubrique){

	$rubdeclinaison = new Rubdeclinaison();
	$query = "select * from $rubdeclinaison->table where rubrique=$idrubrique";
	$resul = $rubdeclinaison->query($query);
	$i=0;
	while($resul && $row = $rubdeclinaison->fetch_object($resul)){
		$fond= $i++%2 ? "fonce" : "claire";
		$declinaisondesc = new Declinaisondesc($row->declinaison);
		?>
       	 <li class="<?php echo $fond; ?>">
		 <div class="cellule" style="width:260px;"><?php echo $declinaisondesc->titre; ?></div>
		 <div class="cellule" style="width:260px;">&nbsp;</div>
		 <div class="cellule_supp"><a href="javascript:declinaison_supprimer(<?php echo $row->declinaison; ?>)"><img src="gfx/supprimer.gif" /></a></div>
		 </li>
	     <?php
	}
}

function declinaison_liste_select($idrubrique){
	$rubdeclinaison = new Rubdeclinaison();
	$query = "select * from $rubdeclinaison->table where rubrique=$idrubrique";
	$resul = $rubdeclinaison->query($query);
	$listeid = "";
	while($resul && $row = $rubdeclinaison->fetch_object($resul)){
		$listeid .= $row->declinaison.",";
	}
	if(strlen($listeid) > 0){
		$listeid = substr($listeid,0,strlen($listeid)-1);

		$declinaison = new Declinaison();
		$query = "select * from $declinaison->table where id NOT IN($listeid)";
		$resul = $declinaison->query($query);
	}
	else{
		$declinaison = new Declinaison();
		$query = "select * from $declinaison->table";
		$resul = $declinaison->query($query);
	}
	?>
	<select class="form_select" id="prod_decli">
 	<option value="">&nbsp;</option>
	<?php
		while($resul && $row = $declinaison->fetch_object($resul)){
			$declinaisondesc = new Declinaisondesc($row->id);
			?>
			<option value="<?php echo $row->id; ?>"><?php echo $declinaisondesc->titre; ?></option>
		<?php
		}
	?>
	</select>
	<?php
}
?>