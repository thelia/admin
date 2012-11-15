<?php

function modifier_transports($idtransport) {

	$transzone = new Transzone();
	$zone = new Zone();
	$tr = new Modules();

	if ($tr->charger_id($_GET['id'])) {

		$zone = new Zone();
		?>
		<div class="entete_liste_config" style="margin-top:15px;">
			<div class="titre"><?php echo trad('MODIFICATION_TRANSPORT', 'admin').' '.ActionsAdminModules::instance()->lire_titre_module($tr); ?></div>
		</div>

		<ul class="ligne1">
			<li style="width:250px;">
				<select class="form_select" id="zone">
				<?php
					$query = "select * from $zone->table";
					$resul = $transzone->query($query);
					while($resul && $row = $transzone->fetch_object($resul)){
						$test = new Transzone();
						if(! $test->charger($idtransport, $row->id)){
				?>
		     	<option value="<?php echo $row->id; ?>"><?php echo $row->nom; ?></option>
		     	<?php
		     			}
		     		}
		     	?>
				</select>
			</li>
			<li><a href="javascript:ajouter($('#zone').val())"><?php echo trad('AJOUTER_ZONE', 'admin'); ?></a></li>
		</ul>

		<?php
		$query = "select * from $transzone->table where transport=\"" . $idtransport. "\"";
		$resul = $transzone->query($query);

		$i = 0;

		while($resul && $row = $transzone->fetch_object($resul)){
			$zone = new Zone();
			$zone->charger($row->zone);

			$fond="ligne_".($i++%2 ? "fonce":"claire")."_BlocDescription";
			?>
			<ul class="<?php echo $fond; ?>">
					<li style="width:492px;"><?php echo $zone->nom; ?></li>
					<li style="width:32px;"><a href="javascript:supprimer(<?php echo $row->id; ?>)"><?php echo trad('Supprimer', 'admin'); ?></a></li>
			</ul>
			<?php
		}
	}
}
?>