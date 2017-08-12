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

  class toC_Rss_Admin {

    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qrss = $osC_Database->query('select r.* from :table_rss r where r.rss_id = :rss_id');

      $Qrss->bindTable(':table_rss', TABLE_RSS);
      $Qrss->bindInt(':rss_id', $id);
      $Qrss->execute();

      $data = $Qrss->toArray();

      $Qrss->freeResult();

      return $data;
    }

    function setStatus($id, $flag){
      global $osC_Database;
      $Qstatus = $osC_Database->query('update :table_rss set rss_status= :rss_status, rss_last_modified = now() where rss_id = :rss_id');
      $Qstatus->bindInt(':rss_status', $flag);
      $Qstatus->bindInt(':rss_id', $id);
      $Qstatus->bindTable(':table_rss', TABLE_RSS);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();
      return true;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language, $osC_Image;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qarticle = $osC_Database->query('update :table_rss set rss_status = :rss_status, rss_title = :rss_title,rss_categories_id = :rss_categories_id,rss_url = :rss_url where rss_id = :rss_id');
        $Qarticle->bindInt(':rss_id', $id);
      } else {
        $Qarticle = $osC_Database->query('insert into :table_rss (rss_status,rss_title,rss_categories_id,rss_url) values (:rss_status,:rss_title,:rss_categories_id ,:rss_url)');
        $Qarticle->bindRaw(':rss_date_added', 'now()');
      }

      $Qarticle->bindTable(':table_rss', TABLE_RSS);
      $Qarticle->bindValue(':rss_status', $data['rss_status']);
      $Qarticle->bindValue(':rss_url', $data['rss_url']);
      $Qarticle->bindValue(':rss_title', $data['rss_title']);
      $Qarticle->bindValue(':rss_categories_id', $data['rss_categories']);
      $Qarticle->setLogging($_SESSION['module'], $id);
      $Qarticle->execute();

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('sefu-rss');
        return true;
      }
      $osC_Database->rollbackTransaction();

      return false;
    }


    function delete($id) {
      global $osC_Database, $osC_Image;
      $error = false;

      $osC_Database->startTransaction();

      if ( $error === false ) {
        $Qrss = $osC_Database->query('delete from :table_rss where rss_id = :rss_id');
        $Qrss->bindTable(':table_rss', TABLE_RSS);
        $Qrss->bindInt(':rss_id', $id);
        $Qrss->setLogging($_SESSION['module'], $id);
        $Qrss->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }

        if ( $error === false ) {
          $osC_Database->commitTransaction();

          osC_Cache::clear('sefu-rss');
          return true;
        }
      }
      $osC_Database->rollbackTransaction();
      return false;
    }
  }
?>
