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

    class osC_Content_articles_slider extends osC_Modules
    {
        var $_title,
        $_code = 'articles_slider',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_articles_slider()
        {
            $this->_title = 'Defilement des articles';
        }

        function initialize()
        {
            global $osC_Database, $current_category_id, $osC_Language, $osC_Template;

            $current_category_id = $current_category_id > 0 ? $current_category_id : -1;

            $Qarticles = $osC_Database->query('select a.*, cd.*,c.*, atoc.*  from :table_articles a left join :table_content c on a.articles_id = c.content_id left join  :table_content_description cd on a.articles_id = cd.content_id left join :table_content_to_categories atoc on atoc.content_id = a.articles_id  where cd.language_id = :language_id and atoc.content_type = "articles" and c.content_type = "articles"');

            if ($current_category_id != 0) {
                $Qarticles->appendQuery('and atoc.categories_id = :categories_id ');
                $Qarticles->bindInt(':categories_id', $current_category_id);
            }

            $Qarticles->appendQuery('order by cd.content_name ');
            $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
            $Qarticles->bindTable(':table_content', TABLE_CONTENT);
            $Qarticles->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
            $Qarticles->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
            $Qarticles->bindInt(':language_id', $osC_Language->getID());
            $Qarticles->execute();

            if ($Qarticles->numberOfRows()) {
                
                $osC_Template->addStyleSheet('ext/jquery_dual_slider/reset.css');
                $osC_Template->addStyleSheet('ext/jquery_dual_slider/layout.css');
                //            $osC_Template->addStyleSheet('ext/jquery_dual_slider/style.css');
                $osC_Template->addStyleSheet('ext/jquery_dual_slider/jquery.dualSlider.0.3.css');
                $osC_Template->addJavascriptFilename('ext/jquery-1.6.3/jquery-1.6.3.min.js');
                $osC_Template->addJavascriptFilename('ext/jquery_dual_slider/jquery.easing.1.3.js');
                $osC_Template->addJavascriptFilename('ext/jquery_dual_slider/jquery.timers-1.2.js');
                $osC_Template->addJavascriptFilename('ext/jquery_dual_slider/jquery.dualSlider.0.3.js');
                $osC_Template->addJavascriptFilename('ext/jquery_dual_slider/script.js');

                $background = '';
                $background .= '<div class = "backgrounds">';
                $this->_content .= '<div class="slider" style="margin-left: 0pt;"><div class="carousel clearfix"><div class="panel" style="left: 534px; width: 212px; height: 250px;"><div class="details_wrapper" style="right: -67px; left: 6px; top: 14px; height: 224px; width: 192px;"><div class="details">';
                $i = 1;
                while ($Qarticles->next()) {
                    $this->_content .= '<div class="detail"><h2>' . $Qarticles->value('content_name') . '</h2><p>' . $Qarticles->value('articles_intro') . '</p>';
                    $this->_content .= osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')),'details...');
                    $this->_content .= '</div>';

                    $background .= '<div class="item">';
                    $background .= '<img src="images/articles/articles_slider/' . $Qarticles->value('articles_image') . '"</img>';
                    $background .= '</div>';

                    $i++;
                }

                $this->_content .= '</div>';
                $this->_content .= '</div>';
                $this->_content .= '<div class = "paging"><div id = "numbers"></div><a href = "javascript:void(0);" class = "previous" title = "Previous">Previous</a><a href = "javascript:void(0);" class = "next" title = "Next">Next</a></div>';
                $this->_content .= '</div>';
                $background .= '</div>';

                $this->_content .= $background;

                $this->_content .= '</div>';
                $this->_content .= '</div>';
                $this->_content .= '</div>';

                $this->_content .= '<div style ="clear:both" ></div>';                
            }

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
