<?php
/*
  $Id: new_articles.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class osC_Content_articles_full extends osC_Modules
{
    var $_title,
    $_code = 'articles_full',
    $_author_name = 'Mefobe',
    $_author_www = 'http://www.mefobemarket.com',
    $_group = 'content';

/* Class constructor */

    function osC_Content_articles_full()
    {
        $this->_title = 'Liste des articles (complets)';
    }

    function initialize()
    {
        global $osC_Database, $current_category_id, $osC_Image, $osC_Language;

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

        $this->_content .= '<table cellspacing="0" cellpadding="0" border="0" style="margin-top: 0px; margin-bottom: 0px;"><tbody>';
            while ($Qarticles->next()) {
                $this->_content .= '<tr><td>' .
                $Qarticles->value('articles_description') . '</td></tr>';
            }

            $this->_content .= '</tbody></table>';

        $Qarticles->freeResult();
    }

    function install()
    {
        //global $osC_Database;

        parent::install();
        //
        //      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum Entries To Display', 'MODULE_CONTENT_NEW_PRODUCTS_MAX_DISPLAY', '9', 'Maximum number of new articles to display', '6', '0', now())");
        //      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Contents', 'MODULE_CONTENT_NEW_PRODUCTS_CACHE', '60', 'Number of minutes to keep the contents cached (0 = no cache)', '6', '0', now())");
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
