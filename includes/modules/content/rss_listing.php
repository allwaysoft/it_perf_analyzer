<?php
/*
  $Id: new_rss.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Content_rss_listing extends osC_Modules
    {
        var $_title,
        $_code = 'rss_listing',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_rss_listing()
        {
            $this->_title = 'RSS News';
        }

        function initialize()
        {
            require_once('ext/last_rss/lastRSS.php');
            global $osC_Database, $current_category_id;

            // Create lastRSS object
            $rss = new lastRSS;

            // Set cache dir and cache time limit (1200 seconds)
            // (don't forget to chmod cahce dir to 777 to allow writing)
            $rss->cache_dir = 'cache';
            $rss->cache_time = 1200;
            $entry_icon = osc_image('admin/templates/default/images/icons/16x16/feed.png', '', null, null, null);

            $current_category_id = $current_category_id > 0 ? $current_category_id : -1;

            $Qrss = $osC_Database->query('select r.* from :table_rss r ');
            $Qrss->appendQuery('where r.rss_categories_id = :categories_id ');
            $Qrss->bindInt(':categories_id', $current_category_id);

            $Qrss->appendQuery('order by r.rss_id ');
            $Qrss->bindTable(':table_rss', TABLE_RSS);
            $Qrss->execute();

            while ($Qrss->next()) {
                if ($rs = $rss->get($Qrss->Value('rss_url'))) {

                    $this->_content .= '<table border="0"><tbody><tr>';
                    $this->_content .= '<td>' . $entry_icon . ' ' . $Qrss->Value('rss_title') . '</td>';
                    $this->_content .= '</tr><tr>';
                    $this->_content .= '<td>';

                    foreach ($rs['items'] as $item) {
                        $this->_content .= '<table border="0"><tbody><tr>';
                        $this->_content .= '<td>' . osc_link_object($item['link'], $item['title']) . '</td>';
                        $this->_content .= '</tr><tr>';
                        $this->_content .= '<td>' . $item['description'] . '</td></tr></tbody></table>';
                    }

                    $this->_content .= '</td></tr></tbody></table>';
                }
                else {
                    echo "Impossible de charger les flux RSS...\n";
                }
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
