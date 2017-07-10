<?php
/*
  $Id: image_menu.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Content_products_listing extends osC_Modules
    {
        var $_title,
        $_code = 'products_listing',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_products_listing()
        {            
            $this->_title = 'Liste des produits';
        }

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

        function initialize()
        {
            global $osC_Database, $current_category_id, $osC_Image, $osC_Language;

            $Qproducts = $osC_Database->query('select distinct p.products_id,pd.products_short_description, pd.products_name,i.image from :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd, :table_products_to_categories p2c, :table_categories c where p2c.categories_id = :parent_id and c.categories_id = p2c.categories_id and p2c.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc');
            $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
            $Qproducts->bindTable(':table_categories', TABLE_CATEGORIES);
            $Qproducts->bindInt(':parent_id', $current_category_id);

            $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
            $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
            $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
            $Qproducts->bindInt(':default_flag', 1);
            $Qproducts->bindInt(':language_id', $osC_Language->getID());

            if ($Qproducts->numberOfRows()) {
                while ($Qproducts->next()) {
                    $product = new osC_Product($Qproducts->valueInt('products_id'));

                    $this->_content .= '<div style="margin-top: 10px; float:left; width: 25%; text-align: center">' .
                                       '<p><strong>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id')), $Qproducts->value('products_name')) . '</strong></p>' .
                                       osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id')), $osC_Image->show($Qproducts->value('image'), $Qproducts->value('products_name'), '', 'thumbnails'), 'id="productImage' . $Qproducts->value('products_id') . '"') .
                                       '<span style="color:red;font-weight:bold;display:block; padding: 3px; text-align: center">' . $product->getPriceFormated(true) . '</span><p><em><strong> ' . $Qproducts->value('products_short_description') . '</strong></em></p></div>';                                        
                }
            }

            $Qproducts->freeResult();
        }

        function install()
        {
            global $osC_Database;

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
