<?php

  require('includes/application_top.php');
  require('includes/classes/module_loader.php');
  
  toc_verify_token();
  
  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Content-Type: application/x-javascript');

  toC_Module_Loader::parse();
?>
