<?php
/*
  $Id: categories.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_categories extends osC_Modules {
    var $_title,
        $_code = 'categories',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

    function osC_Boxes_categories() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_categories_heading');
    }

    function initialize() {
        global $osC_CategoryTree;

        $osC_CategoryTree->reset();
        $osC_CategoryTree->setShowCategoryProductCount(true);

        $this->_content .= $osC_CategoryTree->buildBranch(0);
    }

    function install() {
      global $osC_Database;

      parent::install();      
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array();
      }

      return $this->_keys;
    }
  }
?>