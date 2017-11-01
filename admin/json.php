<?php

  //ob_start();
  //set_time_limit(0);
  //require('includes/modules/FirePHPCore/fb.php');

  require('includes/application_top.php');
  require('includes/ext_config.php');
  require('includes/classes/json.php');
  
  header('Expires: Thu, 01 Jan 1970 01:00:00 GMT');  
  header('Cache-Control: must-revalidate, cache, public');
  header('Pragma: public');
//  header('Content-Type: application/json, charset=utf-8');

  $dir_fs_www_root = dirname(__FILE__);
  
  $toC_Json = new toC_Json();

  $toC_Json->parse();
?>