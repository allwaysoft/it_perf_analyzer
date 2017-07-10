<?php
/*
  $Id: events_listing.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Boxes_events_listing extends osC_Modules
    {
        var $_title,
        $_code = 'events_listing',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

        function osC_Boxes_events_listing()
        {
            $this->_title = 'Evenements';
        }

        function initialize()
        {
            global $osC_Database, $osC_Language, $current_category_id, $osC_Image;

            $events_id = -1;
            if (isset($_GET['events_id']) && !empty($_GET['events_id'])) {
                $events_id = $_GET['events_id'];
            }
            
            $current_category_id = $current_category_id > 0 ? $current_category_id : -1;

            $Qevents = $osC_Database->query('select a.*, cd.*,c.*, atoc.*  from :table_events a inner join :table_content c on a.events_id = c.content_id inner join  :table_content_description cd on a.events_id = cd.content_id inner join :table_content_to_categories atoc on atoc.content_id = a.events_id  where cd.language_id = :language_id and atoc.content_type = "events" and c.content_type = "events" AND cd.content_type = "events"');
            $Qevents->appendQuery('and atoc.categories_id = :categories_id ');
            $Qevents->bindInt(':categories_id', $current_category_id);

            if (!empty($_REQUEST['search'])) {
                $Qevents->appendQuery('and cd.content_name like :content_name');
                $Qevents->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
            }

            $Qevents->appendQuery('order by c.date_created desc ');
            $Qevents->bindTable(':table_events', TABLE_EVENTS);
            $Qevents->bindTable(':table_content', TABLE_CONTENT);
            $Qevents->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
            $Qevents->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
            $Qevents->bindInt(':language_id', $osC_Language->getID());
            $Qevents->setExtBatchLimit(0, 10);
            $Qevents->execute();

            if ($Qevents->numberOfRows() > 0) {

                while ($Qevents->next()) {
                    $style = '';
                    if ($events_id == $Qevents->value('events_id')) {
                        $style = 'style = "color:red"';
                    }

                    $this->_content .= '<table border="0"><tbody><tr><td><table border="0"><tbody><tr>';
                    $this->_content .= '<td>' . osc_link_object(osc_href_link(FILENAME_EVENT, 'events&events_id=' . $Qevents->value('events_id')), $osC_Image->showEventImage($Qevents->value('events_image'), $Qevents->value('content_name'))) . '</td>';
                    $this->_content .= '<td style="font-weight : bold;font-style: italic">' . osc_link_object(osc_href_link(FILENAME_EVENT, 'events&events_id=' . $Qevents->value('events_id')), $Qevents->value('content_name'), $style) . '</td>';
                    $this->_content .= '</tr></tbody></table></td></tr><tr>';
                    $this->_content .= '<tr><td style="font-weight : bold">' . $Qevents->value('events_date') . ' - ' . $Qevents->value('events_location') . '</td></tr>';
                    $this->_content .= '<td>' . $Qevents->value('events_intro') . '</td>';
                    $this->_content .= '</tr></tbody></table>';
                }
            }

            $Qevents->freeResult();
        }

        function install()
        {
            global $osC_Database;

            parent::install();
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum List Size', 'BOX_EVENTS_MAX_LIST', '10', 'Maximum amount of events to show in the listing', '6', '0', now())");
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array('BOX_EVENTS_MAX_LIST');
            }

            return $this->_keys;
        }
    }

?>
