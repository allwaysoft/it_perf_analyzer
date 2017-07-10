<?php
/*
  $Id: information.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_articles_listing extends osC_Modules {
    var $_title,
        $_code = 'articles_listing',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobe.com',
        $_group = 'boxes';

    function osC_Boxes_articles_listing() {

      $this->_title = 'Articles';
    }

    function initialize() {
        global $osC_Database, $current_category_id, $osC_Language;

        $articles_id = -1;
        if (isset($_GET['articles_id']) && !empty($_GET['articles_id'])) {
            $articles_id = $_GET['articles_id'];
        }

        $current_category_id = $current_category_id > 0 ? $current_category_id : -1;

        $Qarticles = $osC_Database->query('select a.*, ad.*, atoc.*  from :table_articles a left join  :table_articles_description ad on a.articles_id = ad.articles_id left join :table_content_to_categories atoc on atoc.content_id = a.articles_id  where ad.language_id = :language_id and atoc.content_type = "articles" and a.articles_status = 1');
        $Qarticles->appendQuery('and atoc.categories_id = :categories_id ');
        $Qarticles->appendQuery('order by a.articles_order ');
        $Qarticles->bindInt(':categories_id', $current_category_id);
        $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
        $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
        $Qarticles->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
        $Qarticles->bindInt(':language_id', $osC_Language->getID());
        $Qarticles->execute();

      $this->_content = '<ul>';

      while ($Qarticles->next()) {
        $style = '';
        if($articles_id == $Qarticles->value('articles_id'))
        {
            $style = 'style = "color:red"';
        }

        $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id='. $Qarticles->value('articles_id')), $Qarticles->value('content_name'),$style) . '</li>';
      }
        
      $this->_content .= '</ul>';

      $Qarticles->freeResult();
    }
  }
?>
