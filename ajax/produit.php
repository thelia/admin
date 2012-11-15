<?php

require_once __DIR__ . '/../pre.php';
require_once __DIR__ . '/../auth.php';


if(! est_autorise("acces_catalogue")) exit; 


switch($action){
   case 'changeDisplay' :  
       ProductAdmin::getInstance($product_id)->changeColumn("ligne", ($display == 'true')?1:0);
       break;
   case 'changePromo':
       ProductAdmin::getInstance($product_id)->changeColumn("promo", ($display == 'true')?1:0);
       break;
   case 'changeNew':
       ProductAdmin::getInstance($product_id)->changeColumn("nouveaute", ($display == 'true')?1:0);
       break;
}

?>