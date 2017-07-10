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

    class osC_Content_articles_listing extends osC_Modules
    {
        var $_title,
        $_code = 'articles_listing',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_articles_listing()
        {
            $this->_title = 'Liste des articles';
        }

        function initialize()
        {
            global $current_category_id;

            $pieces = explode("index.php", $_SERVER['SCRIPT_NAME']);
            
            if(count($pieces) >= 2)
            {
                global $osC_Database, $osC_Language, $osC_Template, $osC_Image;

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

                if ($current_category_id == -1) {
                    if ($Qarticles->numberOfRows()) {
                        $i = 0;
                        $this->_content .= '<table cellspacing="0" cellpadding="0" border="0"><tbody>';
                        while ($Qarticles->next()) {
                            $this->_content .= '<tr><td style="border-width: 0px;vertical-align: top;" rowspan="1" colspan="1"><p>'
                                               . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $osC_Image->showArticleImage($Qarticles->value('articles_image'), $Qarticles->value('content_name'), '', 'product_info', 'articles')) . '</p></td><td style="border-width: 0px;vertical-align: top;padding-left: 5px;" rowspan="1" colspan="1";><h3 style="font-size : 25px">'
                                               . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $Qarticles->value('content_name')) . '</h3><p>'
                                               . $Qarticles->value('articles_intro') . '</p></td></tr> ';

                            $i++;
                        }

                        $this->_content .= '</tbody></table>';
                    }
                }
                else
                {
                    if ($Qarticles->numberOfRows() == 1) {
                        $i = 0;
                        $this->_content .= '<table cellspacing="0" cellpadding="0" border="0"><tbody>';

                        $this->_content .= '<tr><td style="border-width: 0px;vertical-align: top;" rowspan="1" colspan="1"><p>'
                                           . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $osC_Image->showArticleImage($Qarticles->value('articles_image'), $Qarticles->value('content_name'), '', 'product_info', 'articles')) . '</p></td><td style="border-width: 0px;vertical-align: top;padding-left: 5px;" rowspan="1" colspan="1";><h3  style="margin-top: 0px; margin-bottom: 5px;"><div class="title">'
                                           . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $Qarticles->value('content_name')) . '</div></h3><p>'
                                           . $Qarticles->value('articles_intro') . '</p></td></tr><tr><td style="border-width: 0px;vertical-align: top;" rowspan="1" colspan="2";>'
                                           . $Qarticles->value('articles_description') . '</td></tr> ';

                        $this->_content .= '</tbody></table>';
                    }
                    else
                    {
                        if ($Qarticles->numberOfRows() > 1) {
                            $i = 0;
                            $this->_content .= '<table cellspacing="0" cellpadding="0" border="0"><tbody>';
                            while ($Qarticles->next()) {
                                $this->_content .= '<tr><td style="border-width: 0px;vertical-align: top;" rowspan="1" colspan="1"><p>'
                                                   . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $osC_Image->showArticleImage($Qarticles->value('articles_image'), $Qarticles->value('content_name'), '', 'product_info', 'articles')) . '</p></td><td style="border-width: 0px;vertical-align: top;padding-left: 5px;" rowspan="1" colspan="1";><h3 style="font-size : 25px">'
                                                   . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $Qarticles->value('content_name')) . '</h3><p>'
                                                   . $Qarticles->value('articles_intro') . '</p></td></tr> ';

                                $i++;
                            }

                            $this->_content .= '</tbody></table>';
                        }
                    }
                }

                $Qarticles->freeResult();
            }
        }

        function install()
        {
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
