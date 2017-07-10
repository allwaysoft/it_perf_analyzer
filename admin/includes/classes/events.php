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
    if (!class_exists('content')) {
        include('includes/classes/content.php');
    }
  class toC_Events_Admin {

    function getData($id) {
        global $osC_Database;

        $Qnews = $osC_Database->query('select a.*, c.*  from :table_events a left join :table_content c on c.content_id = a.events_id  where a.events_id = :events_id and c.content_type = "events"');

        $Qnews->bindTable(':table_events', TABLE_EVENTS);
        $Qnews->bindTable(':table_content', TABLE_CONTENT);
        $Qnews->bindInt(':events_id', $id);
        $Qnews->execute();

        $data = $Qnews->toArray();

        $Qnews->freeResult();

        $description = content::getContentDescription($id, 'events');
        $data = array_merge($data, $description);

        $product_categories_array = content::getContentCategories($id, 'events');
        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

    function setStatus($id, $flag){
        if (isset($id) && content::setStatus($id, (isset($flag)
                    ? $flag : null), 'events')
        ) {
            return true;
        }

        return false;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language, $osC_Image;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qevent = $osC_Database->query('update :table_events set events_date = :events_date, events_intro = :events_intro,events_location = :events_location,events_time = :events_time where events_id = :events_id');
        $Qevent->bindInt(':events_id', $id);
      } else {
        $Qevent = $osC_Database->query('insert into :table_events (events_date,events_intro,events_location,events_time) values (:events_date,:events_intro,:events_location ,:events_time)');
        $Qevent->bindRaw(':events_date_added', 'now()');
      }

      $Qevent->bindTable(':table_events', TABLE_EVENTS);
      $Qevent->bindValue(':events_date', $data['events_date']);
      $Qevent->bindValue(':events_location', $data['events_location']);
      $Qevent->bindValue(':events_time', $data['events_time']);
      $Qevent->bindValue(':events_intro', $data['events_intro']);
      $Qevent->setLogging($_SESSION['module'], $id);
      $Qevent->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      } else {
        if ( is_numeric($id) ) {
          $events_id = $id;
        } else {
          $events_id = $osC_Database->nextID();
        }
      }

  //events images
      if($data['delimage'] == 1){
        $osC_Image->deleteEventsImage($events_id);

        $Qdelete = $osC_Database->query('update :table_events set events_image = NULL where events_id = :events_id');
        $Qdelete->bindTable(':table_events', TABLE_EVENTS);
        $Qdelete->bindInt(':events_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ($error === false) {
        $events_image = new upload('events_image', realpath('../' . DIR_WS_IMAGES . '/events/originals'));
        if ( $events_image->exists() && $events_image->parse() && $events_image->save() ) {
          $Qevent = $osC_Database->query('update :table_events set events_image = :events_image where events_id = :events_id');
          $Qevent->bindTable(':table_events', TABLE_EVENTS);
          $Qevent->bindValue(':events_image', $events_image->filename);
          $Qevent->bindInt(':events_id', $events_id);
          $Qevent->setLogging($_SESSION['module'], $events_id);
          $Qevent->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }else{
            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($events_image->filename, $group['id'], 'events');
              }
            }
          }

        }
      }

        //content
      if ($error === false) {
          $error = !content::saveContent($id, $events_id, 'events', $data);
      }

        //Process Languages
      if ($error === false) {
          $error = !content::saveContentDescription($id, $events_id, 'events', $data);
      }

        //content_to_categories
      if ($error === false) {
          $error = !content::saveContentToCategories($id, $events_id, 'events', $data);
      }

        //images
      if ($error === false) {
          $error = !content::saveImages($events_id, 'events');
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('sefu-events');
        return true;
      }
      $osC_Database->rollbackTransaction();

      return false;
    }


    function delete($id) {
        global $osC_Database, $osC_Image;
        $error = false;

        $osC_Database->startTransaction();

        $osC_Image->deleteNewsImage($id);

        $error = !content::deleteContent($id, 'events');

        if ($error === false) {
            $Qnews = $osC_Database->query('delete from :table_events where events_id = :events_id');
            $Qnews->bindTable(':table_events', TABLE_EVENTS);
            $Qnews->bindInt(':events_id', $id);
            $Qnews->setLogging($_SESSION['module'], $id);
            $Qnews->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $osC_Database->commitTransaction();

                osC_Cache::clear('sefu-news');
                return true;
            }
        }
        $osC_Database->rollbackTransaction();
        return false;
    }
  }
?>
