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

    class osC_Boxes_accordion_menu extends osC_Modules
    {
        var $_title,
        $_code = 'accordion_menu',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

        function osC_Boxes_accordion_menu()
        {
            $this->_title = 'Accordion Menu';
        }

        function initialize()
        {
            global $osC_CategoryTree, $osC_Template, $current_category_id;

            $osC_CategoryTree->reset();
            $osC_CategoryTree->setShowCategoryProductCount(false);

            $osC_Template->addStyleSheet('ext/jquery_accordion_menu/style.css');
            $osC_Template->addJavascriptFilename('ext/jquery-1.6.3/jquery-1.6.3.min.js');
            $osC_Template->addJavascriptFilename('ext/jquery_accordion_menu/menu-collapsed.js');

            $this->_content = $osC_CategoryTree->buildAccordionMenu($current_category_id);
        }

        function install()
        {
            parent::install();
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