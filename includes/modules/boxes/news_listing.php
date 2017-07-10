<?php
/*
  $Id: news_listing.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Boxes_news_listing extends osC_Modules
    {
        var $_title,
        $_code = 'news_listing',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

        function osC_Boxes_news_listing()
        {
            $this->_title = 'Flash news';
        }

        function initialize()
        {
            global $osC_Database, $osC_Language, $current_category_id;

            $current_category_id = $current_category_id > 0 ? $current_category_id : -1;

            $Qnews = $osC_Database->query('select a.*, cd.*,c.*, atoc.*  from :table_news a left join :table_content c on a.news_id = c.content_id left join  :table_content_description cd on a.news_id = cd.content_id left join :table_content_to_categories atoc on atoc.content_id = a.news_id  where cd.language_id = :language_id and atoc.content_type = "news" and c.content_type = "news" and atoc.categories_id = :categories_id order by c.date_created desc');
            $Qnews->bindTable(':table_news', TABLE_NEWS);
            $Qnews->bindTable(':table_content', TABLE_CONTENT);
            $Qnews->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
            $Qnews->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
            $Qnews->bindInt(':language_id', $osC_Language->getID());
            $Qnews->bindInt(':categories_id', $current_category_id);
            $Qnews->setExtBatchLimit(0, 5);
            $Qnews->execute();

            if ($Qnews->numberOfRows() > 0) {
                $this->_content = '<ul>';

                while ($Qnews->next()) {
                    $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_NEWS, 'news&news_id=' . $Qnews->value('news_id')), $Qnews->value('content_name')) . '</li>';
                }

                $this->_content .= '</ul>';
            }

            $Qnews->freeResult();
        }

        function install()
        {
            global $osC_Database;

            parent::install();
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum List Size', 'BOX_NEWS_MAX_LIST', '10', 'Maximum amount of news to show in the listing', '6', '0', now())");
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array('BOX_NEWS_MAX_LIST');
            }

            return $this->_keys;
        }
    }

?>
