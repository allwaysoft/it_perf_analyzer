<?php
/*
  $Id: info.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    require_once('includes/classes/articles.php');

    class osC_Info_Info extends osC_Template
    {

/* Private variables */

        var $_module = 'info',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'info.php',
        $_page_image = 'table_background_reviews_new.gif';

        function osC_Info_Info()
        {
            global $osC_Database, $osC_Language, $breadcrumb, $osC_Services, $article, $osC_CategoryTree, $current_category_id;

            if (isset($_GET['articles_id']) && !empty($_GET['articles_id'])) {
                $language_id = $osC_Language->getID();
                $cateId = end(explode('=', $_SERVER[HTTP_REFERER]));

                $Qcategories = $osC_Database->query('select cd.categories_name from :table_categories_description cd where cd.categories_id = :categories_id and cd.language_id = :language_id ');
                $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                $Qcategories->bindInt(':categories_id', $cateId);
                $Qcategories->bindInt(':language_id', $language_id);
                $Qcategories->execute();

                $page = $Qcategories->toArray();

                $article = toC_Articles::getEntry($_GET['articles_id']);

                if ($osC_Services->isStarted('breadcrumb')) {
                    $breadcrumb->add($page['categories_name'], osc_href_link(FILENAME_DEFAULT, 'cPath=' . $cateId));
                    $breadcrumb->add($article['content_name'], osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $_GET['articles_id']));
                }

                $this->_page_title = $article['content_name'];

                if (!empty($article['page_title'])) {
                    $this->setMetaPageTitle($article['page_title']);
                }

                if (!empty($article['meta_keywords'])) {
                    $this->addPageTags('keywords', $article['meta_keywords']);
                }

                if (!empty($article['meta_description'])) {
                    $this->addPageTags('description', $article['meta_description']);
                }
            } else {
                $this->_page_title = $osC_Language->get('info_not_found_heading');
                $this->_page_contents = 'info_not_found.php';
            }
        }
    }

?>
