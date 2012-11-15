<?php
require_once("pre.php");
require_once("auth.php");
require_once("../fonctions/divers.php");
if (!est_autorise("acces_configuration"))
    exit;


?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once("title.php"); ?>
    </head>
<body>
<?php
$menu = "configuration";
$breadcrumbs = Breadcrumb::getInstance()->getSimpleList(trad('Configuration', 'admin'));
require_once("entete.php");
?>
    <div class="row-fluid">
        <div class="">

        </div>
    </div>
<?php require_once("pied.php"); ?> 
</body>
</html>