<?php

  $_SERVER['SCRIPT_FILENAME'] = __FILE__;

  require('includes/application_top.php');

  $osC_Language->load('info');

  $osC_Template = osC_Template::setup('event');

  require('templates/' . $osC_Template->getCode() . '/index.php');

  require('includes/application_bottom.php');
?>
