<?php
/*
  $Id: rss.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/rss.php');

  class toC_Json_Rss {

    function listRss() {
      global $toC_Json, $osC_Language, $osC_Database;

      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

      $current_category_id  = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

      $Qrss = $osC_Database->query('select r.* from :table_rss r');
      if ($current_category_id > 0) {
        $Qrss->appendQuery('and r.rss_categories_id = :categories_id ');
        $Qrss->bindInt(':categories_id', $current_category_id);
      }

      $Qrss->appendQuery('order by r.rss_id ');
      $Qrss->bindTable(':table_rss', TABLE_RSS);
      $Qrss->setExtBatchLimit($start, $limit);
      $Qrss->execute();

      $records = array();
      while ($Qrss->next()) {
      	$records[] = array('rss_id' => $Qrss->ValueInt('rss_id'),
                           'rss_status' => $Qrss->ValueInt('rss_status'),
                           'rss_url' => $Qrss->Value('rss_url'),
      	                   'rss_title' => $Qrss->Value('rss_title'));
      }

      $response = array(EXT_JSON_READER_TOTAL => $Qrss->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);

      echo $toC_Json->encode($response);
    }

    function loadRss() {
      global $osC_Database, $toC_Json;

      $data = toC_Rss_Admin::getData($_REQUEST['rss_id']);

      $response = array('success' => true, 'data' => $data);

      echo $toC_Json->encode($response);
    }

    function saveRss() {
      global $toC_Json, $osC_Language, $osC_Image;

      $rss_categories = (isset($_REQUEST['rss_categories_id'])? $_REQUEST['rss_categories_id']:'0');
      $ids = explode('_', $rss_categories);;

      if(count($ids) > 0)
      {
          $rss_categories = $ids[count($ids) - 1];
      }

      $data = array('rss_title' => $_REQUEST['rss_title'],
          'rss_categories_id' => $rss_categories,
                    'rss_url' => $_REQUEST['rss_url'],
                    'rss_status' => $_REQUEST['rss_status'],
                    );

      if ( toC_Rss_Admin::save((isset($_REQUEST['rss_id']) && ($_REQUEST['rss_id'] != -1) ? $_REQUEST['rss_id'] : null), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }

    function deleteRss() {
      global $toC_Json, $osC_Language;

      if (toC_Rss_Admin::delete($_REQUEST['rss_id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }

    function deleteRsss() {
      global $toC_Json, $osC_Language;

      $error = false;

      $batch = explode(',', $_REQUEST['batch']);
      foreach($batch as $rss_id) {
        if (!toC_Rss_Admin::delete($rss_id)) {
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

      if ( isset($_REQUEST['rss_id']) && toC_Rss_Admin::setStatus($_REQUEST['rss_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
  }
?>