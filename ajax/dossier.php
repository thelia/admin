<?php

require_once __DIR__ . '/../pre.php';
require_once __DIR__ . '/../auth.php';


if(! est_autorise("acces_contenu")) exit; 


switch($action){
   case 'changeDisplay' :  
       FolderAdmin::getInstance($folder_id)->display($display);
       break;
   
   
}

?>
