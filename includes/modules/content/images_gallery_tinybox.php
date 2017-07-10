<?php
/*
  $Id: images_gallery.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Content_images_gallery_tinybox extends osC_Modules
    {
        var $_title,
        $_code = 'images_gallery_tinybox',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_images_gallery_tinybox()
        {
            $this->_title = 'images_gallery_tinybox';
        }

        function initialize()
        {
            global $osC_Database, $current_category_id, $osC_Language, $osC_Image, $osC_Template;

            $current_category_id = $current_category_id > 0 ? $current_category_id : -1;

            $Qimages = $osC_Database->query('select s.*, atoc.*  from :table_slide_images s left join :table_content_to_categories atoc on atoc.content_id = s.image_id  where s.language_id = :language_id and atoc.content_type = "images"');
            $Qimages->appendQuery('and atoc.categories_id = :categories_id ');
            $Qimages->bindInt(':categories_id', $current_category_id);
            $Qimages->appendQuery('order by s.sort_order ');
            $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
            $Qimages->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
            $Qimages->bindInt(':language_id', $osC_Language->getID());
            $Qimages->execute();

            if ($Qimages->numberOfRows() > 0) {

                $osC_Template->addJavascriptFilename('ext/tinybox/packed.js');
                $osC_Template->addStyleSheet('ext/tinybox/style.css');

                $js_content = '';

                $i = 0;

                while ($Qimages->next())
                {
                    $link = 'images/images_gallery/thumbnails/' . $Qimages->value('image');
                    $target = 'images/images_gallery/large/' . $Qimages->value('image');
                    $div_id = 'tinybox_image_' . $i;
                    $this->_content .= '<img class="productImage" id ="' . $div_id . '" title="' . $Qimages->value('description') . '" src="' . $link . '" alt="' . $Qimages->value('description') . '"/>';

                    $content = 'var content' . $i . ' = \'<img src="' . trim($target) . '"/>\';T$("' . $div_id . '").onclick = function(){TINY.box.show(content' . $i . ',0,0,0,1)};';
                    $js_content .= $content;

                    $i++;
                }
            }

            $Qimages->freeResult();

            $this->_content .= '<script type="text/javascript">' . $js_content . '</script>';
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
