<?php
/*
  $Id: index.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Index_Index extends osC_Template
    {

/* Private variables */

        var $_module = 'index',
        $_group = 'index',
        $_page_title,
        $_page_contents = 'index.php',
        $_page_image = 'table_background_default.gif';

/* Class constructor */

        function osC_Index_Index()
        {
            global $osC_Database, $osC_Services, $osC_Language, $breadcrumb, $cPath, $cPath_array, $current_category_id, $osC_Category;

            $this->_page_title = sprintf($osC_Language->get('index_heading'), STORE_NAME);

            if (isset($cPath) && (empty($cPath) === false)) {
                if ($osC_Services->isStarted('breadcrumb')) {
                    $Qcategories = $osC_Database->query('select categories_id, categories_name from :table_categories_description where categories_id in (:categories_id) and language_id = :language_id');
                    $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                    $Qcategories->bindRaw(':categories_id', implode(',', $cPath_array));
                    $Qcategories->bindInt(':language_id', $osC_Language->getID());
                    $Qcategories->execute();

                    $categories = array();
                    while ($Qcategories->next()) {
                        $categories[$Qcategories->value('categories_id')] = $Qcategories->valueProtected('categories_name');
                    }

                    $Qcategories->freeResult();

                    for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i++) {
                        $breadcrumb->add($categories[$cPath_array[$i]], osc_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i + 1)))));
                    }
                }

                $osC_Category = new osC_Category($current_category_id);

                $this->_page_title = $osC_Category->getTitle();
                $this->_page_image = 'categories/' . $osC_Category->getImage();

                $page_title = $osC_Category->getPageTitle();
                if (!empty($page_title)) {
                    $this->setMetaPageTitle($page_title);
                }

                $meta_keywords = $osC_Category->getMetaKeywords();
                if (!empty($meta_keywords)) {
                    $this->addPageTags('keywords', $meta_keywords);
                }

                $meta_description = $osC_Category->getMetaDescription();
                if (!empty($meta_description)) {
                    $this->addPageTags('description', $meta_description);
                }
            } else {
                $code = strtoupper($osC_Language->getCode());

                if (defined('HOME_META_KEYWORD_' . $code) && defined('HOME_META_DESCRIPTION_' . $code) && defined('HOME_PAGE_TITLE_' . $code)) {
                    $page_title = constant('HOME_PAGE_TITLE_' . $code);
                    $meta_keywords = constant('HOME_META_KEYWORD_' . $code);
                    $meta_description = constant('HOME_META_DESCRIPTION_' . $code);
                }

                if (!empty($page_title)) {
                    $this->setMetaPageTitle($page_title);
                }

                if (!empty($meta_keywords)) {
                    $this->addPageTags('keywords', $meta_keywords);
                }

                if (!empty($meta_description)) {
                    $this->addPageTags('description', $meta_description);
                }
            }

            if (!isset($_SESSION['template']['code']) || !isset($_SESSION['template']['id'])) {
                $Qtemplate = $osC_Database->query('select t.* from :table_templates t where t.code = (select configuration_value from :table_configuration where configuration_key = "DEFAULT_TEMPLATE")');
                $Qtemplate->bindTable(':table_templates', TABLE_TEMPLATES);
                $Qtemplate->bindTable(':table_configuration', TABLE_CONFIGURATION);
                $Qtemplate->execute();

                while ($Qtemplate->next()) {
                    $_SESSION['template']['code'] = $Qtemplate->value('code');
                    $_SESSION['template']['id'] = $Qtemplate->value('id');
                }

                $Qtemplate->freeResult();
            }
        }

/* Private methods */

        function _process()
        {
            global $current_category_id, $osC_Products;

            include('includes/classes/products.php');
            $osC_Products = new osC_Products($current_category_id);

            if (isset($_GET['filter']) && is_numeric($_GET['filter']) && ($_GET['filter'] > 0)) {
                $osC_Products->setManufacturer($_GET['filter']);
            }

            if (isset($_GET['products_attributes']) && is_array($_GET['products_attributes'])) {
                $osC_Products->setProductAttributesFilter($_GET['products_attributes']);
            }

            if (isset($_GET['sort']) && !empty($_GET['sort'])) {
                if (strpos($_GET['sort'], '|d') !== false) {
                    $osC_Products->setSortBy(substr($_GET['sort'], 0, -2), '-');
                } else {
                    $osC_Products->setSortBy($_GET['sort']);
                }
            }
        }
    }

?>
