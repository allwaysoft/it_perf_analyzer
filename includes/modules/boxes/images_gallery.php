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

    class osC_Boxes_images_gallery extends osC_Modules
    {
        var $_title,
        $_code = 'images_gallery',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

/* Class constructor */

        function osC_Boxes_images_gallery()
        {
            $this->_title = 'images_gallery';
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

                /*<!--
                    1 ) Reference to the files containing the JavaScript and CSS.
                    These files must be located on your server.
                -->*/

                $osC_Template->addJavascriptFilename('ext/highslide-4.1.12/highslide/highslide-with-gallery.min.js');
                $osC_Template->addStyleSheet('ext/highslide-4.1.12/highslide/highslide.css');

                $this->_content .= '<script type="text/javascript">hs.graphicsDir = \'ext/highslide-4.1.12/highslide/graphics/\';hs.align = \'center\';hs.transitions = [\'expand\', \'crossfade\'];hs.outlineType = \'rounded-white\';hs.fadeInOut = true;';

                // Add the controlbar
                $this->_content .= 'hs.addSlideshow({interval: 5000,repeat: false,useControls: true,fixedControls: \'fit\',overlayOptions: {opacity: 0.75,position: \'bottom center\',hideOnMouseOut: true}});</script>';

                $this->_content .= '<div class="highslide-gallery">';

                while ($Qimages->next())
                {
                    $link = 'images/' . $Qimages->value('image');
                    $this->_content .= osc_link_object($link,osc_image($link,$Qimages->value('description'),200,160),'class="highslide" onclick="return hs.expand(this)"');
                    $this->_content .= '<div class="highslide-caption">' . $Qimages->value('description') . '</div>';
                }

                $this->_content .= '</div>';
                //$this->_content .= '<div class="highslide-container" style="padding: 0pt; border: medium none; margin: 0pt; position: absolute; left: 0pt; top: 0pt; width: 100%; z-index: 1001; direction: ltr;"><a class="highslide-loading" title="Click to cancel" href="javascript:;" style="position: absolute; top: -9999px; opacity: 0.75; z-index: 1;">Loading...</a><div style="display: none;"></div><div class="highslide-viewport highslide-viewport-size" style="padding: 0pt; border: medium none; margin: 0pt; visibility: hidden; display: none;"></div><table cellspacing="0" style="padding: 0pt; border: medium none; margin: 0pt; visibility: hidden; position: absolute; border-collapse: collapse; width: 0pt;"><tbody style="padding: 0pt; border: medium none; margin: 0pt;"><tr style="padding: 0pt; border: medium none; margin: 0pt; height: auto;"><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll 0px 0px transparent; height: 20px; width: 20px;"></td><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll 0px -40px transparent; height: 20px; width: 20px;"></td><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll -20px 0px transparent; height: 20px; width: 20px;"></td></tr><tr style="padding: 0pt; border: medium none; margin: 0pt; height: auto;"><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll 0px -80px transparent; height: 20px; width: 20px;"></td><td style="padding: 0pt; border: medium none; margin: 0pt; position: relative;" class="rounded-white highslide-outline"></td><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll -20px -80px transparent; height: 20px; width: 20px;"></td></tr><tr style="padding: 0pt; border: medium none; margin: 0pt; height: auto;"><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll 0px -20px transparent; height: 20px; width: 20px;"></td><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll 0px -60px transparent; height: 20px; width: 20px;"></td><td style="padding: 0pt; border: medium none; margin: 0pt; line-height: 0; font-size: 0pt; background: url(&quot;file:///D:/cms/highslide-4.1.12/highslide/graphics/outlines/rounded-white.png&quot;) repeat scroll -20px -20px transparent; height: 20px; width: 20px;"></td></tr></tbody></table></div>';
            }

            $Qimages->freeResult();
        }

        function install()
        {
            global $osC_Database;

            parent::install();

            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Slide show mode [vertical, horizontal]', 'MODULE_CONTENT_SLIDE_SHOW_MODE', 'horizontal', 'Slideshow Mode', '6', '0', 'osc_cfg_set_boolean_value(array(\'horizontal\', \'vertical\'))', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Display Slide info', 'MODULE_CONTENT_SLIDE_SHOW_DISPLAY_INFO', 'True', 'Display Slide Info', '6', '0', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image width (px)', 'MODULE_CONTENT_SLIDE_SHOW_WIDTH', '500', 'Image width', '6', '0', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image height (px)', 'MODULE_CONTENT_SLIDE_SHOW_HEIGHT', '210', 'Image height', '6', '0', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Interval (ms)', 'MODULE_CONTENT_SLIDE_SHOW_INTERVAL', '3000', 'slide show interval', '6', '0', now())");
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Duration (ms)', 'MODULE_CONTENT_SLIDE_SHOW_DURATION', '1000', 'slide show duration', '6', '0', now())");
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array('MODULE_CONTENT_SLIDE_SHOW_MODE',
                                     'MODULE_CONTENT_SLIDE_SHOW_DISPLAY_INFO',
                                     'MODULE_CONTENT_SLIDE_SHOW_WIDTH',
                                     'MODULE_CONTENT_SLIDE_SHOW_HEIGHT',
                                     'MODULE_CONTENT_SLIDE_SHOW_INTERVAL',
                                     'MODULE_CONTENT_SLIDE_SHOW_DURATION');
            }

            return $this->_keys;
        }
    }

?>
