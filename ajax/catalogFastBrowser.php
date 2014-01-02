<?php
require_once(__DIR__ . "/../auth.php");

if(! est_autorise("acces_commandes")) exit;

header('Content-Type: text/html; charset=utf-8');

$root = $_POST['root']?:0;

$retour = array();

//get breadcrumb
$retour['breadcrumb'] = Breadcrumb::getInstance(false)->getFastBrowserCategoryList($root);

//get categories
$retour['categories'] = CategoryAdmin::getInstance()->getList($root, 'classement', 'ASC', '');

//get products
$retour['products'] = ProductAdmin::getInstance()->getList($root, 'classement', 'ASC', '');

die(json_encode($retour));
