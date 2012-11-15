<?php
	require_once(realpath(dirname(__FILE__)) . "/../../pre.php");
	require_once(realpath(dirname(__FILE__)) . "/../../auth.php");

	require_once(realpath(dirname(__FILE__)) . "/../../../classes/Client.class.php");
	require_once(realpath(dirname(__FILE__)) . "/../../../classes/Commande.class.php");
	require_once(realpath(dirname(__FILE__)) . "/../../../classes/Venteprod.class.php");
	require_once(realpath(dirname(__FILE__)) . "/../../../classes/Statutdesc.class.php");
	require_once(realpath(dirname(__FILE__)) . "/../../../classes/Produit.class.php");
	
	if(isset($_GET["motcle"]) && strlen($_GET["motcle"])>3){
		$motcle = $_GET["motcle"];

if(est_autorise("acces_catalogue")){

  	$i=0;
  	$search="";

  	$search .= "and ref like '%$motcle%'";


  	$produit = new Produit();


   	$query = "select * from $produit->table where 1 $search";
  	$resul = mysql_query($query, $produit->link);

  	$produitdesc = new Produitdesc();

 	$prodlist="";

   	while($row = mysql_fetch_object($resul)){
		 $prodlist .= "'$row->id', ";
 	 }

  	$prodlist = substr($prodlist, 0, strlen($prodlist)-2);

  	$search="";

  	$search .= "and titre like '%$motcle%' or description like '%$motcle%'";


  	$produit = new Produit();


   	$query = "select * from $produitdesc->table where 1 $search";
  	$resul = mysql_query($query, $produitdesc->link);
	if(mysql_num_rows($resul) && $prodlist!="") $prodlist .= ",";

  	$produitdesc = new Produitdesc();

 	$num = 0;

   	while($row = mysql_fetch_object($resul)){
   		$num++;
		 $prodlist .= "'$row->produit', ";
 	 }

	if( substr($prodlist, strlen($prodlist)-2, 1) == ",")
 		$prodlist = substr($prodlist, 0, strlen($prodlist)-2);

	if($num == 1) $prodlist .= "'";

	if($prodlist == "") $search = "where 0";
	else $search = " where id in ($prodlist)";

   	$query = "select * from $produit->table $search";
  	$query = str_replace("'')", "')", $query);
	$resul = mysql_query($query, $produitdesc->link);

  	while($row = mysql_fetch_object($resul)){
		
		$produitdesc->charger($row->id);
		
	$image = new Image();
	$query_image = "select * from $image->table where produit=\"" . $row->id . "\" order by classement limit 0,1";
	$resul_image = mysql_query($query_image, $image->link);
	$row_image = mysql_fetch_object($resul_image);
?>
<li>
	<span class="type-resultat"><i class="icon-tag"></i></span>
	<a href="produit_modifier.php?ref=<?php echo($row->ref); ?>&rubrique=<?php echo($row->rubrique); ?>" class="img">
		<?php if($row_image) { ?><img src="../fonctions/redimlive.php?type=produit&nomorig=<?php echo $row_image->fichier;?>&width=49&height=&opacite=" /><?php }?>
	</a>
	<a href="produit_modifier.php?ref=<?php echo($row->ref); ?>&rubrique=<?php echo($row->rubrique); ?>" class="txt"><?php echo($produitdesc->titre); ?></a>
</li>
<?php	
 /* <ul class="<?php echo($fond); ?>">
	<li style="width:152px;"><a href="produit_modifier.php?ref=<?php echo($row->ref); ?>&rubrique=<?php echo($row->rubrique); ?>"><?php echo($row->ref); ?></a></li>
	<li style="width:400px;"><?php echo($produitdesc->titre); ?></li>
	<li style="width:303px;"><?php echo($row->prix); ?></li>
	<li style="width:20px;"><a href="javascript:supprimer_produit('<?php echo $row->ref ?>','<?php echo($row->rubrique); ?>')"><img src="gfx/supprimer.gif" width="9" height="9" border="0" /></a></li>*/
	}
}
}
?>