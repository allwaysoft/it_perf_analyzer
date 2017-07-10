<?php
/*
  $Id: articles.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Events {
    
    function &getEntry($id) {

        global $osC_Database;

        $Qevents = $osC_Database->query('select a.*, c.*  from :table_events a left join :table_content c on c.content_id = a.events_id  where a.events_id = :events_id and c.content_type = "events"');

        $Qevents->bindTable(':table_events', TABLE_EVENTS);
        $Qevents->bindTable(':table_content', TABLE_CONTENT);
        $Qevents->bindInt(':events_id', $id);
        $Qevents->execute();

        $data = $Qevents->toArray();

        $Qevents->freeResult();

        $description = array();

        $Qcd = $osC_Database->query('SELECT cd.* FROM :table_content_description cd where cd.content_id = :content_id and cd.content_type = :content_type');
        $Qcd->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
        $Qcd->bindInt(':content_id', $id);
        $Qcd->bindValue(':content_type', 'events');
        $Qcd->execute();

        while ($Qcd->next()) {
            $description['content_name'] = $Qcd->value('content_name');
            $description['content_url'] = $Qcd->value('content_url');
            $description['content_description'] = $Qcd->value('content_description');
            $description['page_title'] = $Qcd->Value('page_title');
            $description['meta_keywords'] = $Qcd->Value('meta_keywords');
            $description['meta_descriptions'] = $Qcd->Value('meta_descriptions');
        }

        $data = array_merge($data, $description);

        return $data;
    }

    function &getListing($categories_id = null) {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $Qevents = $osC_Database->query('select a.events_location,a.events_time,a.events_date,a.events_id,a.events_intro,a.events_date_added, a.events_status, a.events_order, ad.events_name from :table_events a, :table_events_description ad where a.events_id = ad.events_id and ad.language_id = :language_id ');
        if ($categories_id > 0) {
            $Qevents->appendQuery('and a.events_categories_id = :categories_id ');
            $Qevents->bindInt(':categories_id', $categories_id);
        }

        if (!empty($_REQUEST['search'])) {
            $Qevents->appendQuery('and ad.events_name like :events_name');
            $Qevents->bindValue(':events_name', '%' . $_REQUEST['search'] . '%');
        }

        $Qevents->appendQuery('order by a.events_id ');
        $Qevents->bindTable(':table_events', TABLE_EVENTS);
        $Qevents->bindTable(':table_events_description', TABLE_EVENTS_DESCRIPTION);
        $Qevents->bindInt(':language_id', $osC_Language->getID());
        $Qevents->setExtBatchLimit($start, $limit);
        $Qevents->execute();

        return $Qevents;
    }
  }
?>
