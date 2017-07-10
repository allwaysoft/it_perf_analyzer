<?php
/*
  $Id: events.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/events.php');
  require('includes/classes/image.php');
  
  class toC_Json_Events {
    
    function listEvents() {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $Qevents = $osC_Database->query('select a.*, cd.*,c.*, atoc.*  from :table_events a left join :table_content c on a.events_id = c.content_id left join  :table_content_description cd on a.events_id = cd.content_id left join :table_content_to_categories atoc on atoc.content_id = a.events_id  where cd.language_id = :language_id and atoc.content_type = "events" and c.content_type = "events"');

        if ($current_category_id != 0) {
            $Qevents->appendQuery('and atoc.categories_id = :categories_id ');
            $Qevents->bindInt(':categories_id', $current_category_id);
        }

        if (!empty($_REQUEST['search'])) {
            $Qevents->appendQuery('and cd.content_name like :content_name');
            $Qevents->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        $Qevents->appendQuery('order by cd.content_name ');
        $Qevents->bindTable(':table_events', TABLE_EVENTS);
        $Qevents->bindTable(':table_content', TABLE_CONTENT);
        $Qevents->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
        $Qevents->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
        $Qevents->bindInt(':language_id', $osC_Language->getID());
        $Qevents->setExtBatchLimit($start, $limit);
        $Qevents->execute();

        $records = array();
        while ($Qevents->next()) {
            if (isset($_REQUEST['permissions'])) {
                $permissions = explode(',', $_REQUEST['permissions']);
                $records[] = array('events_id' => $Qevents->ValueInt('events_id'),
                                   'events_date' => $Qevents->Value('events_date'),
                                   'events_location' => $Qevents->Value('events_location'),
                                   'events_image' => $Qevents->Value('events_image'),
                                   'events_time' => $Qevents->Value('events_time'),
                                   'content_status' => $Qevents->ValueInt('content_status'),
                                   'content_order' => $Qevents->Value('content_order'),
                                   'date_created' => $Qevents->Value('date_created'),
                                   'content_name' => $Qevents->Value('content_name'),
                                   'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[0],
                                   'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[1],
                                   'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : $permissions[2],
                                   'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[3]
                );
            }
            else
            {
                $records[] = array('events_id' => $Qevents->ValueInt('events_id'),
                                   'events_date' => $Qevents->Value('events_date'),
                                   'events_location' => $Qevents->Value('events_location'),
                                   'events_image' => $Qevents->Value('events_image'),
                                   'events_time' => $Qevents->Value('events_time'),
                                   'content_status' => $Qevents->ValueInt('content_status'),
                                   'content_order' => $Qevents->Value('content_order'),
                                   'date_created' => $Qevents->Value('date_created'),
                                   'content_name' => $Qevents->Value('content_name'),
                                   'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                                   'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                                   'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                                   'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                );
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
                          EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }
    
    function getEventsCategories() {
      global $toC_Json, $osC_Language;
      
      $article_categories = toC_Events_Categories_Admin::getEventsCategories();
      
      $records = array();
      if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
        $records = array(array('id' => '', 'text' => $osC_Language->get('top_events_category')));
      }
      
      foreach ($article_categories as $category) {
        if ($category['events_categories_id'] != '1') {
          $records[] = array('id' => $category['events_categories_id'],
                             'text' => $category['events_categories_name']);
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function loadArticle() {
      global $osC_Database, $toC_Json;
      
      $data = toC_Events_Admin::getData($_REQUEST['events_id']);

      $response = array('success' => true, 'data' => $data);

      echo $toC_Json->encode($response);
    }
    
    function saveArticle() {
        global $toC_Json, $osC_Language, $osC_Image;

        $osC_Image = new osC_Image_Admin();

        //search engine friendly urls
        $formatted_urls = array();
        $urls = $_REQUEST['content_url'];
        if (is_array($urls) && !empty($urls)) {
            foreach ($urls as $languages_id => $url) {
                $url = toc_format_friendly_url($url);
                if (empty($url)) {
                    $url = toc_format_friendly_url($_REQUEST['content_name'][$languages_id]);
                }

                $formatted_urls[$languages_id] = $url;
            }
        }

        $data = array('content_name' => $_REQUEST['content_name'],
                      'events_date' => $_REQUEST['events_date'],
                      'events_location' => $_REQUEST['events_location'],
                      'events_time' => $_REQUEST['events_time'],
                      'events_intro' => $_REQUEST['events_intro'],
                      'content_url' => $formatted_urls,
                      'created_by' => $_SESSION[admin][id],
                      'modified_by' => $_SESSION[admin][id],
                      'content_description' => $_REQUEST['content_description'],
                      'content_order' => $_REQUEST['content_order'],
                      'content_status' => $_REQUEST['content_status'],
                      'delimage' => (isset($_REQUEST['delimage']) && ($_REQUEST['delimage'] == 'on') ? '1' : '0'),
                      'page_title' => $_REQUEST['page_title'],
                      'meta_keywords' => $_REQUEST['meta_keywords'],
                      'meta_descriptions' => $_REQUEST['meta_descriptions']);

        if (isset($_REQUEST[content_categories_id])) {
            $data['categories'] = explode(',', $_REQUEST[content_categories_id]);
        }
                    
      if ( toC_Events_Admin::save((isset($_REQUEST['events_id']) && ($_REQUEST['events_id'] != -1) ? $_REQUEST['events_id'] : null), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }
    
    function deleteArticle() {
      global $toC_Json, $osC_Language, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();
      
      if (toC_Events_Admin::delete($_REQUEST['events_id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }   
      
      echo $toC_Json->encode($response);
    }
    
    function deleteEvents() {
      global $toC_Json, $osC_Language, $osC_Database, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach($batch as $events_id) {
        if (!toC_Events_Admin::delete($events_id)) {
          $error = true;
          break;
        }
      }
    
      if ($error === false) {      
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['events_id']) && toC_Events_Admin::setStatus($_REQUEST['events_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>