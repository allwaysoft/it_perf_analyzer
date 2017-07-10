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

    class osC_Content_articles_slider_tiny1 extends osC_Modules
    {
        var $_title,
        $_code = 'articles_slider_tiny1',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_articles_slider_tiny1()
        {
            $this->_title = 'Defilement des articles (Tiny 1)';
        }

        function initialize()
        {
            global $osC_Database, $current_category_id, $osC_Image, $osC_Language, $osC_Template;

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

                $osC_Template->addStyleSheet('ext/tinyslider/style.css');
                $osC_Template->addJavascriptFilename('ext/tinyslider/packed.js');

                $pagination = '<ul id="pagination" class="pagination">';

                $this->_content .= '<div id="wrapper"><div><div class="sliderbutton"><img src="images/left.gif" width="32" height="38" alt="Previous" onclick="slideshow.move(-1)" /></div></div><div id="slider"><ul>';
                $i = 1;
                while ($Qarticles->next()) {
                    $this->_content .= '<li><table cellspacing="0" cellpadding="0" border="0"><tbody>';
                    $this->_content .= '<tr><td style="border-width: 0px;vertical-align: top;" rowspan="1" colspan="1"><p>'
                                       . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $osC_Image->showArticleImage($Qarticles->value('articles_image'), $Qarticles->value('content_name'), '', 'product_info', 'articles')) . '</p></td><td style="border-width: 0px;vertical-align: top;padding-left: 5px;" rowspan="1" colspan="1";><h3 style="font-size : 25px">'
                                       . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->value('articles_id')), $Qarticles->value('content_name')) . '</h3><p>'
                                       . $Qarticles->value('articles_intro') . '</p></td></tr> ';
                    $this->_content .= '</tbody></table></li>';
                    $pagination .= '<li onclick="slideshow.pos(' . $i . ')"></li>';
                    $i++;

//                    $this->_content .= '<li><img src="images/articles/articles_slider/' . $Qarticles->value('articles_image') . '" alt="' . $Qarticles->value('content_name') . '" /></li>';
//                    $pagination .= '<li onclick="slideshow.pos(' . $i . ')"></li>';
//                    $i++;
                }

                $pagination .= '</ul>';

                $this->_content .= '</ul></div><div class="sliderbutton"><img src="images/right.gif" width="32" height="38" alt="Next" onclick="slideshow.move(1)" /></div>';
                $this->_content .= $pagination;
                $this->_content .= '</div>';

                $this->_content .= '<script type="text/javascript">
var slideshow=new TINY.slider.slide(\'slideshow\',{
	id:\'slider\',
	auto:3,
	resume:true,
	vertical:false,
	navid:\'pagination\',
	activeclass:\'current\',
	position:0
});
</script>';
                $this->_content .= '<div style ="clear: both;" ></div>';                
            }

            $Qarticles->freeResult();
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
