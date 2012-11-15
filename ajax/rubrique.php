<?php

require_once __DIR__ . '/../pre.php';
require_once __DIR__ . '/../auth.php';


if(! est_autorise("acces_catalogue")) exit; 


switch($action){
   case 'changeDisplay' :  
       CategoryAdmin::getInstance($category_id)->display($display);
       break;
   
   
}

?>
