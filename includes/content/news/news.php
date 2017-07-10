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

  require_once('includes/classes/news.php');

  class osC_News_News extends osC_Template {

/* Private variables */

    var $_module = 'news',
        $_group = 'news',
        $_page_title,
        $_page_contents = 'news.php',
        $_page_image = 'table_background_reviews_new.gif';

    function osC_News_News() {
      global $osC_Language, $breadcrumb, $osC_Services, $article;

      if (isset($_GET['news_id']) && !empty($_GET['news_id'])) {
        $article = toC_News::getEntry($_GET['news_id']);

        if ($osC_Services->isStarted('breadcrumb')) {
          $breadcrumb->add($article['content_name']);
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
