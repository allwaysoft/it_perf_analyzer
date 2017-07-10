<?php
/*
  $Id: articles.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Articles {
    
    function &getEntry($id) {

        global $osC_Database;

        $Qarticles = $osC_Database->query('select a.*, c.*  from :table_articles a left join :table_content c on c.content_id = a.articles_id  where a.articles_id = :articles_id and c.content_type = "articles"');

        $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
        $Qarticles->bindTable(':table_content', TABLE_CONTENT);
        $Qarticles->bindInt(':articles_id', $id);
        $Qarticles->execute();

        $data = $Qarticles->toArray();

        $Qarticles->freeResult();

        $description = array();

        $Qcd = $osC_Database->query('SELECT cd.* FROM :table_content_description cd where cd.content_id = :content_id and cd.content_type = :content_type');
        $Qcd->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
        $Qcd->bindInt(':content_id', $id);
        $Qcd->bindValue(':content_type', 'articles');
        $Qcd->execute();

        while ($Qcd->next()) {
            $description['content_name'] = $Qcd->value('content_name');
            $description['content_url'] = $Qcd->value('content_url');
            $description['content_description'] = $Qcd->value('content_description');
            $description['page_title'] = $Qcd->Value('page_title');
            $description['meta_keywords'] = $Qcd->Value('meta_keywords');
            $description['meta_descriptions'] = $Qcd->Value('meta_descriptions');
        }

        $Qcd->freeResult();

        $data = array_merge($data, $description);

        return $data;
    }

    function &getListing($categories_id = null) {
      global $osC_Database, $osC_Language;

      $Qarticles = $osC_Database->query('select a.*, ad.*, atoc.*  from :table_articles a left join  :table_articles_description ad on a.articles_id = ad.articles_id left join :table_content_to_categories atoc on atoc.content_id = a.articles_id  where ad.language_id = :language_id and atoc.content_type = "articles"');

      if (is_numeric($categories_id)) {
        $Qarticles->appendQuery('and atoc.categories_id = :categories_id');
        $Qarticles->bindInt(':categories_id', $categories_id);
      }
        
      $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
      $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qarticles->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
      $Qarticles->bindInt(':language_id', $osC_Language->getID());
      $Qarticles->setBatchLimit((isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1), MAX_DISPLAY_SEARCH_RESULTS);
      $Qarticles->execute();

      return $Qarticles;
    }
    
    function getArticleCategoriesEntry($categories_id) {
      global $osC_Database, $osC_Language;

      $Qcategories = $osC_Database->query('select articles_categories_name, articles_categories_page_title, articles_categories_meta_keywords, articles_categories_meta_description from :table_articles_categories_description where articles_categories_id = :articles_categories_id and language_id = :language_id');
      $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':articles_categories_id', $categories_id);
      $Qcategories->bindInt(':language_id', $osC_Language->getID());
      $Qcategories->execute();

      if($Qcategories->numberOfRows() > 0){
        $data = array('articles_categories_name' => $Qcategories->value('articles_categories_name'),
                      'page_title' => $Qcategories->value('articles_categories_page_title'),
                      'meta_keywords' => $Qcategories->value('articles_categories_meta_keywords'),
                      'meta_description' => $Qcategories->value('articles_categories_meta_description'));
      }
      
      return $data;
    }
  }
?>
