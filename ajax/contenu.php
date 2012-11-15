<?php

require_once __DIR__ . '/../pre.php';
require_once __DIR__ . '/../auth.php';


if(! est_autorise("acces_contenu")) exit; 


switch($action){
   case 'changeDisplay' :  
       ContentAdmin::getInstance($content_id)->changeColumn("ligne", ($display == 'true')?1:0);
       break;
}

?>