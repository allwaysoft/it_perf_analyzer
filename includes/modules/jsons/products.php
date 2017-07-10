<?php
/*
  $Id: products.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Products {
  
    function compareProducts() {
      global $osC_Language, $toC_Compare_Products;
      
      $osC_Language->load('products');
      
      $content = '<p>';
      $content .= '<h1>' . $osC_Language->get('compare_products_heading') . '</h1>';
      $content .= $toC_Compare_Products->outputCompareProductsTable();
      $content .= '</p>';
      
      echo $content;
    }
  }
  