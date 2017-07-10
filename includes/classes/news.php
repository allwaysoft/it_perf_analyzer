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
      class toC_News {
    
    function &getEntry($id) {

        global $osC_Database;

        $Qnews = $osC_Database->query('select a.*, c.*  from :table_news a left join :table_content c on c.content_id = a.news_id  where a.news_id = :news_id and c.content_type = "news"');

        $Qnews->bindTable(':table_news', TABLE_NEWS);
        $Qnews->bindTable(':table_content', TABLE_CONTENT);
        $Qnews->bindInt(':news_id', $id);
        $Qnews->execute();

        $data = $Qnews->toArray();

        $Qnews->freeResult();

        $description = array();

        $Qcd = $osC_Database->query('SELECT cd.* FROM :table_content_description cd where cd.content_id = :content_id and cd.content_type = :content_type');
        $Qcd->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
        $Qcd->bindInt(':content_id', $id);
        $Qcd->bindValue(':content_type', 'news');
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

//        $product_categories_array = content::getContentCategories($id, 'news');
//        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

    function &getListing($categories_id = null) {
        global $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $Qnews = $osC_Database->query('select a.news_id,a.news_intro,a.news_date_added, a.news_status, a.news_order, ad.news_name from :table_news a, :table_news_description ad where a.news_id = ad.news_id and ad.language_id = :language_id ');
        if ($categories_id > 0) {
            $Qnews->appendQuery('and a.news_categories_id = :categories_id ');
            $Qnews->bindInt(':categories_id', $categories_id);
        }

        if (!empty($_REQUEST['search'])) {
            $Qnews->appendQuery('and ad.news_name like :news_name');
            $Qnews->bindValue(':news_name', '%' . $_REQUEST['search'] . '%');
        }

        $Qnews->appendQuery('order by a.news_id ');
        $Qnews->bindTable(':table_news', TABLE_NEWS);
        $Qnews->bindTable(':table_news_description', TABLE_NEWS_DESCRIPTION);
        $Qnews->bindInt(':language_id', $osC_Language->getID());
        $Qnews->setExtBatchLimit($start, $limit);
        $Qnews->execute();

        return $Qnews;
    }
  }
?>
