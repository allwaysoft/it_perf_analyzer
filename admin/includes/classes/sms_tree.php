<?php
/*
  $Id: category_tree.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('../includes/classes/category_tree.php');

  class osC_Sms_Admin extends osC_CategoryTree {
    var $show_category_product_count = true;

    function osC_CategoryTree_Admin() {
      global $osC_Database, $osC_Language;

      $Qcategories = $osC_Database->query('SELECT statusdetails.Description AS categories_name, COUNT(messages.ID) AS count FROM messages.statusdetails INNER JOIN messages.messages ON statusdetails.StatusDetails = messages.StatusDetails GROUP BY statusdetails.Description ORDER BY category');      
      $Qcategories->execute();

      $this->data = array();
      $index = 0;
      
      while ($Qcategories->next()) {
        $this->data[1][$index] = array('name' => $Qcategories->value('categories_name'), 'image' => '', 'count' => $Qcategories->value('count'));
        $index = $index + 1;
      }

      $Qcategories->freeResult();      
    }

    function getPath($category_id, $level = 0, $separator = ' ') {
      $path = '';

      foreach ($this->data as $parent => $categories) {
        foreach ($categories as $id => $info) {
          if ($id == $category_id) {
            if ($level < 1) {
              $path = $info['name'];
            } else {
              $path = $info['name'] . $separator . $path;
            }

            if ($parent != $this->root_category_id) {
              $path = $this->getPath($parent, $level+1, $separator) . $path;
            }
          }
        }
      }

      return $path;
    }
  }
?>
