<?php
/*
  $Id: new_articles.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Content_dropdown_menu extends osC_Modules
    {
        var $_title,
        $_code = 'dropdown_menu',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_dropdown_menu()
        {
            $this->_title = 'Menu deroulant';
        }

        function initialize()
        {
            global $osC_Template, $osC_CategoryTree;

            $osC_Template->addStyleSheet('ext/tinydropdown2/tinydropdown.css');
//            $osC_Template->addJavascriptFilename('ext/tinydropdown2/packed.js');

            $osC_CategoryTree->reset();
            $osC_CategoryTree->setShowCategoryProductCount(false);

            $this->_content = $osC_CategoryTree->buildDropdownMenu(0);
        }

        function install()
        {
            //global $osC_Database;

            parent::install();
            //
            //      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum Entries To Display', 'MODULE_CONTENT_NEW_PRODUCTS_MAX_DISPLAY', '9', 'Maximum number of new articles to display', '6', '0', now())");
            //      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Contents', 'MODULE_CONTENT_NEW_PRODUCTS_CACHE', '60', 'Number of minutes to keep the contents cached (0 = no cache)', '6', '0', now())");
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array();
            }

            return $this->_keys;
        }
    }

?>
