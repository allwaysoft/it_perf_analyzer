<?php
/*
  $Id: account.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Account {
  
    function displayPrivacy() {
      global $osC_Language;
      
      $osC_Language->load('info');
      
      require_once('includes/classes/articles.php');
      
      $article = toC_Articles::getEntry(INFORMATION_PRIVACY_NOTICE);

      $content = '<div style="margin: 10px">';
      $content .= '<h1>' . $osC_Language->get('info_privacy_heading') . '</h1>';
      $content .= $article['articles_description'];
      $content .= '</div>';
      
      echo $content;
    }
  }
  