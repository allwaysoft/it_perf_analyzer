<?php
/*
  $Id: contact_groupes.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/contact_groupes.php');
  require('includes/classes/contact_groupe_tree.php');
  require('includes/classes/image.php');
  require('includes/classes/contacts.php');

  class toC_Json_Categories {
    
    function listCategoriesAll() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcontact_groupes = $osC_Database->query('select c.contact_groupes_id, cd.contact_groupes_name, c.contact_groupes_image, c.parent_id, c.sort_order, c.contact_groupes_status, c.date_added, c.last_modified from :table_contact_groupes c, :table_contact_groupes_description cd where c.contact_groupes_id = cd.contact_groupes_id and cd.language_id = :language_id');
      //$Qcontact_groupes->appendQuery('and c.parent_id = :parent_id');            
      
      if ( isset($_REQUEST['date_added']) && !empty($_REQUEST['date_added']) ) {
        $Qcontact_groupes->appendQuery('and c.date_added > :date_added');
        $Qcontact_groupes->bindValue(':date_added',$_REQUEST['date_added']);
      }   
      
      if ( isset($_REQUEST['search']) && !empty($_REQUEST['search']) ) {
        $Qcontact_groupes->appendQuery('and cd.contact_groupes_name like :contact_groupes_name');
        $Qcontact_groupes->bindValue(':contact_groupes_name',$_REQUEST['search']);
      } 
    
      $Qcontact_groupes->appendQuery('order by c.sort_order, cd.contact_groupes_name');
      $Qcontact_groupes->bindTable(':table_contact_groupes', TABLE_CATEGORIES);
      $Qcontact_groupes->bindTable(':table_contact_groupes_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcontact_groupes->bindInt(':language_id', $osC_Language->getID());
      $Qcontact_groupes->setExtBatchLimit($start, $limit);
      $Qcontact_groupes->execute();
      
      $records = array();
      $osC_CategoryTree = new osC_CategoryTree();
      while ($Qcontact_groupes->next()) {
        $records[] = array('contact_groupes_id' => $Qcontact_groupes->value('contact_groupes_id'),
                           'contact_groupes_name' => $Qcontact_groupes->value('contact_groupes_name'),
                           'status' => $Qcontact_groupes->valueInt('contact_groupes_status'),
                           'path' => $osC_CategoryTree->buildBreadcrumb($Qcontact_groupes->valueInt('contact_groupes_id'))); 
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qcontact_groupes->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      //echo "<pre>", print_r($_REQUEST, true), "</pre>";
      echo $toC_Json->encode($response);
    }
  
    function listCategories() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcontact_groupes = $osC_Database->query('select c.contact_groupes_id, cd.contact_groupes_name, c.contact_groupes_image, c.parent_id, c.sort_order, c.contact_groupes_status, c.date_added, c.last_modified from :table_contact_groupes c, :table_contact_groupes_description cd where c.contact_groupes_id = cd.contact_groupes_id and cd.language_id = :language_id');
      $Qcontact_groupes->appendQuery('and c.parent_id = :parent_id');
      
      if ( isset($_REQUEST['contact_groupes_id']) && !empty($_REQUEST['contact_groupes_id']) ) {
        $Qcontact_groupes->bindInt(':parent_id', $_REQUEST['contact_groupes_id']);  
      } else {
        $Qcontact_groupes->bindInt(':parent_id', 0);
      }   
      
      if ( isset($_REQUEST['date_added']) && !empty($_REQUEST['date_added']) ) {
        $Qcontact_groupes->appendQuery('and c.date_added > :date_added');
        $Qcontact_groupes->bindValue(':date_added',$_REQUEST['date_added']);
      }   
      
      if ( isset($_REQUEST['search']) && !empty($_REQUEST['search']) ) {
        $Qcontact_groupes->appendQuery('and cd.contact_groupes_name like :contact_groupes_name');
        $Qcontact_groupes->bindValue(':contact_groupes_name',$_REQUEST['search']);
      } 
    
      $Qcontact_groupes->appendQuery('order by c.sort_order, cd.contact_groupes_name');
      $Qcontact_groupes->bindTable(':table_contact_groupes', TABLE_CATEGORIES);
      $Qcontact_groupes->bindTable(':table_contact_groupes_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcontact_groupes->bindInt(':language_id', $osC_Language->getID());
      $Qcontact_groupes->setExtBatchLimit($start, $limit);
      $Qcontact_groupes->execute();
      
      $records = array();
      $osC_CategoryTree = new osC_CategoryTree();
      while ($Qcontact_groupes->next()) {
        $records[] = array('contact_groupes_id' => $Qcontact_groupes->value('contact_groupes_id'),
                           'contact_groupes_name' => $Qcontact_groupes->value('contact_groupes_name'),
                           'status' => $Qcontact_groupes->valueInt('contact_groupes_status'),
                           'path' => $osC_CategoryTree->buildBreadcrumb($Qcontact_groupes->valueInt('contact_groupes_id'))); 
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qcontact_groupes->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      //echo "<pre>", print_r($_REQUEST, true), "</pre>";
      echo $toC_Json->encode($response);
    }
    
    function listRatings() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $Qratings = $osC_Database->query('select r.ratings_id, rd.ratings_text from :table_ratings r inner join :table_ratings_description rd on rd.ratings_id = r.ratings_id and rd.languages_id = :languages_id and status = 1');
      $Qratings->bindTable(':table_ratings', TABLE_RATINGS);
      $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qratings->bindInt(':languages_id', $osC_Language->getID());
      $Qratings->execute();
      
      $records = array();
      while ( $Qratings->next() ) {
        $records[] = array(
          'ratings_id' => $Qratings->valueInt('ratings_id'),
          'ratings_text' => $Qratings->value('ratings_text')
        );
      }
        
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      $Qratings->freeResult();                  
     
      echo $toC_Json->encode($response);
    }

    function deleteCategory() {
      global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;
      
      $osC_Image = new osC_Image_Admin();
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      if ( isset($_REQUEST['contact_groupes_id']) && is_numeric($_REQUEST['contact_groupes_id']) && osC_Categories_Admin::delete($_REQUEST['contact_groupes_id']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
         
      echo $toC_Json->encode($response);
    }
    
    function deleteCategories() {
      global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;
      
      $osC_Image = new osC_Image_Admin();
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if (!osC_Categories_Admin::delete($id)) {
          $error = true;
          break;
        }
      }
     
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
      
    function moveCategories(){
      global $toC_Json, $osC_Language;
      
      $error = false;
      $batch = explode(',', $_REQUEST['contact_groupes_ids']);
     
      foreach ($batch as $id) {
        if ( !osC_Categories_Admin::move($id, $_REQUEST['parent_contact_groupe_id']) ) {
          $error = true;
          break;
        }
      }
       
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
        
    function loadCategory(){
      global $toC_Json, $osC_Language, $osC_Database, $osC_CategoryTree;
      
      $osC_CategoryTree = new osC_CategoryTree();
      
      $data = osC_Categories_Admin::getData($_REQUEST['contact_groupes_id']);
      
      $Qcontact_groupes = $osC_Database->query('select c.*, cd.* from :table_contact_groupes c left join :table_contact_groupes_description cd on c.contact_groupes_id = cd.contact_groupes_id where c.contact_groupes_id = :contact_groupes_id  ');
      $Qcontact_groupes->bindTable(':table_contact_groupes', TABLE_CATEGORIES);
      $Qcontact_groupes->bindTable(':table_contact_groupes_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcontact_groupes->bindInt(':contact_groupes_id', $_REQUEST['contact_groupes_id']);
      $Qcontact_groupes->execute();
      
      while ($Qcontact_groupes->next()) {
        $data['contact_groupes_name[' . $Qcontact_groupes->ValueInt('language_id') . ']'] = $Qcontact_groupes->Value('contact_groupes_name');
        $data['contact_groupes_url[' . $Qcontact_groupes->ValueInt('language_id') . ']'] = $Qcontact_groupes->Value('contact_groupes_url');
        $data['page_title['. $Qcontact_groupes->ValueInt('language_id') . ']'] = $Qcontact_groupes->Value('contact_groupes_page_title');
        $data['meta_keywords['. $Qcontact_groupes->ValueInt('language_id') . ']'] = $Qcontact_groupes->Value('contact_groupes_meta_keywords');
        $data['meta_description[' . $Qcontact_groupes->ValueInt('language_id') . ']'] = $Qcontact_groupes->Value('contact_groupes_meta_description');
      }
      $Qcontact_groupes->freeResult();
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function saveCategory() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $parent_id = isset($_REQUEST['parent_contact_groupe_id']) ? end(explode('_', $_REQUEST['parent_contact_groupe_id'])) : null;
      
      //search engine friendly urls
      $formatted_urls = array();
      $urls = $_REQUEST['contact_groupes_url'];
      if (is_array($urls) && !empty($urls)) {
        foreach($urls as $languages_id => $url) {
          $url = toc_format_friendly_url($url);
          if (empty($url)) {
            $url = toc_format_friendly_url($_REQUEST['contact_groupes_name'][$languages_id]);
          }
          
          $formatted_urls[$languages_id] = $url;
        }
      }
      
      $data = array('parent_id' => $parent_id, 
                    'sort_order' => $_REQUEST['sort_order'],
                    'image' => $_FILES['image'],  
                    'contact_groupes_status'  => $_REQUEST['contact_groupes_status'],
                    'name' => $_REQUEST['contact_groupes_name'],
                    'url' => $formatted_urls,
                    'page_title' => $_REQUEST['page_title'],
                    'meta_keywords' => $_REQUEST['meta_keywords'],
                    'meta_description' => $_REQUEST['meta_description'],
                    'flag' => (isset($_REQUEST['contact_flag']))? $_REQUEST['contact_flag']: 0,
                    'ratings' => $_REQUEST['ratings']);
      
      $contact_groupe_id = osC_Categories_Admin::save((isset($_REQUEST['contact_groupes_id']) && is_numeric($_REQUEST['contact_groupes_id']) ? $_REQUEST['contact_groupes_id'] : null), $data); 
      if ( $contact_groupe_id > 0) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'), 'contact_groupes_id' => $contact_groupe_id, 'text' => $_REQUEST['contact_groupes_name'][$osC_Language->getID()]);
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }
    
    function saveCategoryRobot() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $parent_id = isset($_REQUEST['parent_contact_groupe_id']) ? end(explode('_', $_REQUEST['parent_contact_groupe_id'])) : null;
      
      //search engine friendly urls
      $formatted_urls = array();
      $urls = $_REQUEST['contact_groupes_url'];
      if (is_array($urls) && !empty($urls)) {
        foreach($urls as $languages_id => $url) {
          $url = toc_format_friendly_url($url);
          if (empty($url)) {
            $url = toc_format_friendly_url($_REQUEST['contact_groupes_name'][$languages_id]);
          }
          
          $formatted_urls[$languages_id] = $url;
        }
      }
      
      $data = array('parent_id' => $parent_id, 
                    'sort_order' => $_REQUEST['sort_order'],
                    'image' => $_REQUEST['image'],  
                    'contact_groupes_status'  => $_REQUEST['contact_groupes_status'],
                    'name' => $_REQUEST['contact_groupes_name'],
                    'url' => $formatted_urls,
                    'page_title' => $_REQUEST['page_title'],
                    'meta_keywords' => $_REQUEST['meta_keywords'],
                    'meta_description' => $_REQUEST['meta_description'],
                    'flag' => (isset($_REQUEST['contact_flag']))? $_REQUEST['contact_flag']: 0,
                    'ratings' => $_REQUEST['ratings']);
      
      $contact_groupe_id = osC_Categories_Admin::saveFromRobot((isset($_REQUEST['contact_groupes_id']) && is_numeric($_REQUEST['contact_groupes_id']) ? $_REQUEST['contact_groupes_id'] : null), $data); 
      if ( $contact_groupe_id > 0) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'), 'contact_groupes_id' => $contact_groupe_id, 'text' => $_REQUEST['contact_groupes_name'][$osC_Language->getID()]);
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }
          
    function listParentCategory(){
      global $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $records = array(array('id' => '0',
                             'text' => $osC_Language->get('top_contact_groupe')));
      
      foreach ($osC_CategoryTree->getTree() as $value) {
        $records[] = array('id' => $value['id'],
                           'text' => $value['title']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
                          
      echo $toC_Json->encode($response);
    }
    
    function loadCategoriesTree() {
      global $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree();
      $contact_groupes_array = array();

      $contact_groupes_array = $osC_CategoryTree->buildExtJsonTreeArray();
      
      echo $toC_Json->encode($contact_groupes_array);                     
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['contact_groupes_id']) && osC_Categories_Admin::setStatus($_REQUEST['contact_groupes_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : 1), (isset($_REQUEST['contact_flag']) ? $_REQUEST['contact_flag'] : 0)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
  
      echo $toC_Json->encode($response);
    }
  }
?>
