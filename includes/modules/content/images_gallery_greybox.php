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

    class osC_Content_images_gallery_greybox extends osC_Modules
    {
        var $_title,
        $_code = 'images_gallery_greybox',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_images_gallery_greybox()
        {
            $this->_title = 'images_gallery_greybox';
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

            $osC_Template->addJavascriptFilename('ext/greybox/AJS.js');
            $osC_Template->addJavascriptFilename('ext/greybox/AJS_fx.js');
            $osC_Template->addJavascriptFilename('ext/greybox/gb_scripts.js');
            $osC_Template->addStyleSheet('ext/greybox/gb_styles.css');

            while ($Qimages->next())
            {
                $link = 'images/images_gallery/product_info/' . $Qimages->value('image');
                $this->_content .= '<a href="' . $link . '" rel="gb_imageset[nice_pics]" title="' . $Qimages->value('image') . '">' . $Qimages->value('description') . '</a>';
//                $this->_content .= '<div class="highslide-caption">' . $Qimages->value('description') . '</div>';
            }

//                $this->_content .= '</div>';
            }

            $Qimages->freeResult();
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
