<?php

function modifier_pays_zone($idzone) {

	$zone = new Zone();
	$zone->charger($idzone);

	$pays = new Pays();
	$query = "select * from $pays->table where zone=\"-1\"";
	$resul = $pays->query($query);
?>
	<div class="entete_liste_config" style="margin-top:15px;">
		<div class="titre"><?php echo trad('MODIFICATION_ZONE', 'admin'); ?></div>
	</div>

	<ul class="ligne1">
		<li style="width:250px;">
			<select class="form_select" id="pays">
			<?php
				while($resul && $row = $pays->fetch_object($resul)){
					$paysdesc = new Paysdesc();
					if ($paysdesc->charger($row->id)) {
			?>
	     	<option value="<?php echo $paysdesc->pays; ?>"><?php echo $paysdesc->titre; ?></option>
			<?php
					}
				}
			?>
			</select>
		</li>
		<li><a href="javascript:ajouter($('#pays').val())"><?php echo trad('AJOUTER_PAYS', 'admin'); ?></a></li>
	</ul>

<?php
		$pays = new Pays();
		$query = "select * from $pays->table where zone=\"" . $idzone . "\"";
		$resul = $pays->query($query);
?>
<?php
		while($resul && $row = $pays->fetch_object($resul)){
			$paysdesc = new Paysdesc();
			$paysdesc->charger($row->id);

			$fond="ligne_".($i++%2 ? "fonce":"claire")."_BlocDescription";
?>
		<ul class="<?php echo $fond; ?>">
			<li style="width:492px;"><?php echo $paysdesc->titre; ?></li>
			<li style="width:32px;"><a href="javascript:supprimer(<?php echo $row->id; ?>)"><?php echo trad('Supprimer', 'admin'); ?></a></li>
		</ul>
<?php
		}
?>
	<ul class="ligne1">
			<li><?php echo trad('Forfait transport: ', 'admin'); ?><input type="text" class="form_inputtext" id="forfait" onclick="this.value=''" value="<?php echo htmlspecialchars($zone->unite); ?>" /></li>
			<li><a href="javascript:forfait($('#forfait').val())"><?php echo trad('VALIDER', 'admin'); ?></a></li>
	</ul>
<?php } ?>