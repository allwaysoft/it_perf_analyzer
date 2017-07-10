<?php
/*
  $Id: news.php $
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
  class toC_News_Admin {

    function getData($id) {
      global $osC_Database;

      $Qnews = $osC_Database->query('select a.*, c.*  from :table_news a left join :table_content c on c.content_id = a.news_id  where a.news_id = :news_id and c.content_type = "news"');

      $Qnews->bindTable(':table_news', TABLE_NEWS);
      $Qnews->bindTable(':table_content', TABLE_CONTENT);
      $Qnews->bindInt(':news_id', $id);
      $Qnews->execute();

      $data = $Qnews->toArray();

      $Qnews->freeResult();

      $description = content::getContentDescription($id, 'news');
      $data = array_merge($data, $description);

      $product_categories_array = content::getContentCategories($id, 'news');
      $data['categories_id'] = implode(',', $product_categories_array);

      return $data;      
    }

    function setStatus($id, $flag){
        if (isset($id) && content::setStatus($id, (isset($flag)
                    ? $flag : null), 'news')
        ) {
            return true;
        }

        return false;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Image;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qarticle = $osC_Database->query('update :table_news set news_intro = :news_intro where news_id = :news_id');
        $Qarticle->bindInt(':news_id', $id);
      } else {
        $Qarticle = $osC_Database->query('insert into :table_news (news_intro) values (:news_intro)');
        $Qarticle->bindRaw(':news_date_added', 'now()');
      }

      $Qarticle->bindTable(':table_news', TABLE_NEWS);
      $Qarticle->bindValue(':news_intro', $data['news_intro']);
      $Qarticle->setLogging($_SESSION['module'], $id);
      $Qarticle->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      } else {
        if ( is_numeric($id) ) {
          $news_id = $id;
        } else {
          $news_id = $osC_Database->nextID();
        }
      }

  //news images
      if($data['delimage'] == 1){
        $osC_Image->deleteNewsImage($news_id);

        $Qdelete = $osC_Database->query('update :table_news set news_image = NULL where news_id = :news_id');
        $Qdelete->bindTable(':table_news', TABLE_NEWS);
        $Qdelete->bindInt(':news_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ($error === false) {
        $news_image = new upload('news_image', realpath('../' . DIR_WS_IMAGES . '/news/originals'));
        if ( $news_image->exists() && $news_image->parse() && $news_image->save() ) {
          $Qarticle = $osC_Database->query('update :table_news set news_image = :news_image where news_id = :news_id');
          $Qarticle->bindTable(':table_news', TABLE_NEWS);
          $Qarticle->bindValue(':news_image', $news_image->filename);
          $Qarticle->bindInt(':news_id', $news_id);
          $Qarticle->setLogging($_SESSION['module'], $news_id);
          $Qarticle->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }else{
            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($news_image->filename, $group['id'], 'news');
              }
            }
          }

        }
      }

        //content
      if ($error === false) {
          $error = !content::saveContent($id, $news_id, 'news', $data);
      }

        //Process Languages
      if ($error === false) {
          $error = !content::saveContentDescription($id, $news_id, 'news', $data);
      }

        //content_to_categories
      if ($error === false) {
          $error = !content::saveContentToCategories($id, $news_id, 'news', $data);
      }

        //images
      if ($error === false) {
          $error = !content::saveImages($news_id, 'news');
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('sefu-news');
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

      $error = !content::deleteContent($id, 'news');

      if ( $error === false ) {
        $Qnews = $osC_Database->query('delete from :table_news where news_id = :news_id');
        $Qnews->bindTable(':table_news', TABLE_NEWS);
        $Qnews->bindInt(':news_id', $id);
        $Qnews->setLogging($_SESSION['module'], $id);
        $Qnews->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }

        if ( $error === false ) {
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
