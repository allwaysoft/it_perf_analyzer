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

    class osC_Content_events_images_gallery extends osC_Modules
    {
        var $_title,
        $_code = 'events_images_gallery',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_events_images_gallery()
        {
            $this->_title = 'events_images_gallery';
        }

        function initialize()
        {
            if (isset($_REQUEST['events_id']) && is_numeric($_REQUEST['events_id'])) {
                global $osC_Database;

                $Qimages = $osC_Database->query('select id, image from :table_content_images where content_id = :content_id and content_type = "events" order by sort_order');
                $Qimages->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                $Qimages->bindInt(':content_id', $_REQUEST['events_id']);
                $Qimages->execute();

                if ($Qimages->numberOfRows() > 0) {

                    global $osC_Template;
                    
                    $osC_Template->addJavascriptFilename('ext/tinybox/packed.js');
                    $osC_Template->addStyleSheet('ext/tinybox/style.css');

                    $js_content = '';

                    $i = 0;

                    while ($Qimages->next())
                    {
                        $link = 'images/content/thumbnails/' . $Qimages->value('image');
                        $target = 'images/content/large/' . $Qimages->value('image');
                        $div_id = 'tinybox_event_image_' . $i;
                        $this->_content .= '<img class="productImage" id ="' . $div_id . '" title="' . $Qimages->value('description') . '" src="' . $link . '" alt="' . $Qimages->value('description') . '"/>';

                        $content = 'var content' . $i . ' = \'<img src="' . trim($target) . '"/>\';T$("' . $div_id . '").onclick = function(){TINY.box.show(content' . $i . ',0,0,0,1)};';
                        $js_content .= $content;

                        $i++;
                    }
                }

                $Qimages->freeResult();

                $this->_content .= '<script type="text/javascript">' . $js_content . '</script>';
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
