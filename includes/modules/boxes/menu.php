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

    class osC_Boxes_menu extends osC_Modules
    {
        var $_title,
        $_code = 'menu',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

        function osC_Boxes_menu()
        {
            $this->_title = 'Menu';
        }

        function initialize()
        {
            global $osC_CategoryTree, $osC_Template, $current_category_id;

//            $osC_CategoryTree->reset();
            //$osC_CategoryTree->setShowCategoryProductCount(false);

            //$this->_content = $osC_CategoryTree->buildBranchOnly($current_category_id);
        }

        function install()
        {
            global $osC_Database;

            parent::install();

            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Show Product Count', 'BOX_CATEGORIES_SHOW_PRODUCT_COUNT', '1', 'Show the amount of products each category has', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Display Drop Down Menu', 'BOX_CATEGORIES_DISPLAY_DROP_DOWN_MENU', '1', 'Use MenuMatic to display drop down menu', '6', '1', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Drop Down Menu Effect', 'BOX_CATEGORIES_DROP_DOWN_MENU_EFFECT', 'slide & fade', 'Drop Down Menu Effect', '6', '2', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(\'fade\', \'slide\', \'slide & fade\'))', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Drop Down Menu Duration', 'BOX_CATEGORIES_DROP_DOWN_MENU_DURATION', '600', 'Drop Down Menu Duration', '6', '3', now())");
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array('BOX_CATEGORIES_SHOW_PRODUCT_COUNT', 'BOX_CATEGORIES_DISPLAY_DROP_DOWN_MENU', 'BOX_CATEGORIES_DROP_DOWN_MENU_EFFECT', 'BOX_CATEGORIES_DROP_DOWN_MENU_DURATION');
            }

            return $this->_keys;
        }
    }

?>