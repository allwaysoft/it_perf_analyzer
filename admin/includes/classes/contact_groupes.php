<?php
/*
  $Id: contact_groupes.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Contact_groupes_Admin {
    function getData($id, $language_id = null) {
      global $osC_Database, $osC_Language, $osC_CategoryTree;

      if ( empty($language_id) ) {
        $language_id = $osC_Language->getID();
      }

      $Qcontact_groupes = $osC_Database->query('select c.*, cd.* from :table_contact_groupes c left join :table_contact_groupes_description cd on c.contact_groupes_id = cd.contact_groupes_id where c.contact_groupes_id = :contact_groupes_id and cd.language_id = :language_id ');
      $Qcontact_groupes->bindTable(':table_contact_groupes', 'contact_groupes');
      $Qcontact_groupes->bindTable(':table_contact_groupes_description', 'contact_groupes_description');
      $Qcontact_groupes->bindInt(':contact_groupes_id', $id);
      $Qcontact_groupes->bindInt(':language_id', $language_id);
      $Qcontact_groupes->execute();

      $data = $Qcontact_groupes->toArray();

      $data['childs_count'] = sizeof($osC_CategoryTree->getChildren($Qcontact_groupes->valueInt('contact_groupes_id'), $dummy = array()));
      $data['contacts_count'] = $osC_CategoryTree->getNumberOfProducts($Qcontact_groupes->valueInt('contact_groupes_id'));
      
      $cPath = explode('_', $osC_CategoryTree->getFullcPath($Qcontact_groupes->valueInt('contact_groupes_id')));
      array_pop($cPath);
      $data['parent_category_id'] = implode('_',$cPath);
      
      $Qcontact_groupes->freeResult();            

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;
      
      $category_id = '';
      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qcat = $osC_Database->query('update :table_contact_groupes set contact_groupes_status = :contact_groupes_status, sort_order = :sort_order, last_modified = now() where contact_groupes_id = :contact_groupes_id');
        $Qcat->bindInt(':contact_groupes_id', $id);
      } else {
        $Qcat = $osC_Database->query('insert into :table_contact_groupes (parent_id, contact_groupes_status, sort_order, date_added) values (:parent_id, :contact_groupes_status, :sort_order, now())');
        $Qcat->bindInt(':parent_id', $data['parent_id']);
      }

      $Qcat->bindTable(':table_contact_groupes', 'contact_groupes');
      $Qcat->bindInt(':sort_order', $data['sort_order']);
      $Qcat->bindInt(':contact_groupes_status', $data['contact_groupes_status']);
      $Qcat->setLogging($_SESSION['module'], $id);
      $Qcat->execute();
      
      if ( !$osC_Database->isError() ) {
        $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();
        
        if(is_numeric($id)) {
          if($data['contact_groupes_status']){
            $Qpstatus = $osC_Database->query('update :table_contacts set contacts_status = 1 where contacts_id in (select contacts_id from :table_contacts_to_contact_groupes where contact_groupes_id = :contact_groupes_id)');
	          $Qpstatus->bindTable(':table_contacts', 'contacts');
	          $Qpstatus->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
	          $Qpstatus->bindInt(":contact_groupes_id", $id);
	          $Qpstatus->execute(); 
          }else{
            if($data['flag']) {
              $Qpstatus = $osC_Database->query('update :table_contacts set contacts_status = 0 where contacts_id in (select contacts_id from :table_contacts_to_contact_groupes where contact_groupes_id = :contact_groupes_id)');
	            $Qpstatus->bindTable(':table_contacts', 'contacts');
	            $Qpstatus->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
	            $Qpstatus->bindInt(":contact_groupes_id", $id);
	            $Qpstatus->execute();
            }          
          }
        }
        
        if($osC_Database->isError()){
          $error = true;
        }
        
        foreach ($osC_Language->getAll() as $l) {
          if ( is_numeric($id) ) {
            $Qcd = $osC_Database->query('update :table_contact_groupes_description set contact_groupes_name = :contact_groupes_name, contact_groupes_url = :contact_groupes_url, contact_groupes_page_title = :contact_groupes_page_title, contact_groupes_meta_keywords = :contact_groupes_meta_keywords, contact_groupes_meta_description = :contact_groupes_meta_description where contact_groupes_id = :contact_groupes_id and language_id = :language_id');
          } else {
            $Qcd = $osC_Database->query('insert into :table_contact_groupes_description (contact_groupes_id, language_id, contact_groupes_name, contact_groupes_url, contact_groupes_page_title, contact_groupes_meta_keywords, contact_groupes_meta_description) values (:contact_groupes_id, :language_id, :contact_groupes_name, :contact_groupes_url, :contact_groupes_page_title, :contact_groupes_meta_keywords, :contact_groupes_meta_description)');
          }

          $Qcd->bindTable(':table_contact_groupes_description', 'contact_groupes_description');
          $Qcd->bindInt(':contact_groupes_id', $category_id);
          $Qcd->bindInt(':language_id', $l['id']);
          $Qcd->bindValue(':contact_groupes_name', $data['name'][$l['id']]);
          $Qcd->bindValue(':contact_groupes_url', ($data['url'][$l['id']] == '') ? $data['name'][$l['id']] : $data['url'][$l['id']]);
          $Qcd->bindValue(':contact_groupes_page_title', $data['page_title'][$l['id']]);
          $Qcd->bindValue(':contact_groupes_meta_keywords', $data['meta_keywords'][$l['id']]);
          $Qcd->bindValue(':contact_groupes_meta_description', $data['meta_description'][$l['id']]);
          $Qcd->setLogging($_SESSION['module'], $category_id);
          $Qcd->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }                        
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('contact_groupes');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');

        return $category_id;
      }

      $osC_Database->rollbackTransaction();

      return false;
    } 
    
    function delete($id) {
      global $osC_Database, $osC_CategoryTree;
      
      $error = false;
    
      if ( is_numeric($id) ) {
        $osC_CategoryTree->setBreadcrumbUsage(false);

        $contact_groupes = array_merge(array(array('id' => $id, 'text' => '')), $osC_CategoryTree->getTree($id));
        $contacts = array();
        $contacts_delete = array();

        foreach ($contact_groupes as $c_entry) {
          $Qcontacts = $osC_Database->query('select contacts_id from :table_contacts_to_contact_groupes where contact_groupes_id = :contact_groupes_id');
          $Qcontacts->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
          $Qcontacts->bindInt(':contact_groupes_id', $c_entry['id']);
          $Qcontacts->execute();

          while ($Qcontacts->next()) {
            $contacts[$Qcontacts->valueInt('contacts_id')]['contact_groupes'][] = $c_entry['id'];
          }
        }

        foreach ($contacts as $key => $value) {
          $Qcheck = $osC_Database->query('select count(*) as total from :table_contacts_to_contact_groupes where contacts_id = :contacts_id and contact_groupes_id not in :contact_groupes_id');
          $Qcheck->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
          $Qcheck->bindInt(':contacts_id', $key);
          $Qcheck->bindRaw(':contact_groupes_id', '("' . implode('", "', $value['contact_groupes']) . '")');
          $Qcheck->execute();

          if ($Qcheck->valueInt('total') < 1) {
            $contacts_delete[$key] = $key;
          }
        }

        osc_set_time_limit(0);

        foreach ($contact_groupes as $c_entry) {
          $osC_Database->startTransaction();          

          $Qc = $osC_Database->query('delete from :table_contact_groupes where contact_groupes_id = :contact_groupes_id');
          $Qc->bindTable(':table_contact_groupes', 'contact_groupes');
          $Qc->bindInt(':contact_groupes_id', $c_entry['id']);
          $Qc->setLogging($_SESSION['module'], $id);
          $Qc->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
          }                  
	      
          if ($error === false) {
            $Qcd = $osC_Database->query('delete from :table_contact_groupes_description where contact_groupes_id = :contact_groupes_id');
            $Qcd->bindTable(':table_contact_groupes_description', 'contact_groupes_description');
            $Qcd->bindInt(':contact_groupes_id', $c_entry['id']);
            $Qcd->setLogging($_SESSION['module'], $id);
            $Qcd->execute();

            if ( !$osC_Database->isError() ) {
              $Qp2c = $osC_Database->query('delete from :table_contacts_to_contact_groupes where contact_groupes_id = :contact_groupes_id');
              $Qp2c->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
              $Qp2c->bindInt(':contact_groupes_id', $c_entry['id']);
              $Qp2c->setLogging($_SESSION['module'], $id);
              $Qp2c->execute();

              if ( !$osC_Database->isError() ) {
                $osC_Database->commitTransaction();

                osC_Cache::clear('contact_groupes');
                osC_Cache::clear('category_tree');
                osC_Cache::clear('also_purchased');
                osC_Cache::clear('sefu-contacts');
                osC_Cache::clear('new_contacts');

                if ( !osc_empty($Qimage->value('contact_groupes_image')) ) {
                  $Qcheck = $osC_Database->query('select count(*) as total from :table_contact_groupes where contact_groupes_image = :contact_groupes_image');
                  $Qcheck->bindTable(':table_contact_groupes', 'contact_groupes');
                  $Qcheck->bindValue(':contact_groupes_image', $Qimage->value('contact_groupes_image'));
                  $Qcheck->execute();

                  if ( $Qcheck->numberOfRows() === 0 ) {
                    if (file_exists(realpath('../' . DIR_WS_IMAGES . 'contact_groupes/' . $Qimage->value('contact_groupes_image')))) {
                      @unlink(realpath('../' . DIR_WS_IMAGES . 'contact_groupes/' . $Qimage->value('contact_groupes_image')));
                    }
                  }
                }
              } else {
                $osC_Database->rollbackTransaction();
              }
            } else {
              $osC_Database->rollbackTransaction();
            }
          } else {
            $osC_Database->rollbackTransaction();
          }
        }
      
        foreach ($contacts_delete as $id) {
          osC_Products_Admin::delete($id);
        }

        osC_Cache::clear('contact_groupes');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-contacts');
        osC_Cache::clear('new_contacts');

        return true;
      }

      return false;
    }

    function move($id, $new_id) {
      global $osC_Database;

      $category_array = explode('_', $new_id);

      if ( in_array($id, $category_array)) {
        return false;
      }

      $Qupdate = $osC_Database->query('update :table_contact_groupes set parent_id = :parent_id, last_modified = now() where contact_groupes_id = :contact_groupes_id');
      $Qupdate->bindTable(':table_contact_groupes', 'contact_groupes');
      $Qupdate->bindInt(':parent_id', end($category_array));
      $Qupdate->bindInt(':contact_groupes_id', $id);
      $Qupdate->setLogging($_SESSION['module'], $id);
      $Qupdate->execute();

      osC_Cache::clear('contact_groupes');
      osC_Cache::clear('category_tree');
      osC_Cache::clear('also_purchased');

      return true;
    }
    
    function setStatus($id, $flag, $contact_flag) {
      global $osC_Database;
      
      $error = false;
      
      $Qstatus = $osC_Database->query('update :table_contact_groupes set contact_groupes_status = :contact_groupes_status where contact_groupes_id = :contact_groupes_id');
      $Qstatus->bindTable(':table_contact_groupes', 'contact_groupes');
      $Qstatus->bindInt(":contact_groupes_id", $id);
      $Qstatus->bindValue(":contact_groupes_status", $flag);
      $Qstatus->execute();
      
      if( !$osC_Database->isError() ) {
        if ( ($flag == 0) && ($contact_flag == 1) ) {
          $Qupdate = $osC_Database->query('update :table_contacts set contacts_status = 0 where contacts_id in (select contacts_id from :table_contacts_to_contact_groupes where contact_groupes_id = :contact_groupes_id)');
          $Qupdate->bindTable(':table_contacts', 'contacts');
          $Qupdate->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
          $Qupdate->bindInt(":contact_groupes_id", $id);
          $Qupdate->execute();
        }
      }
      
      if( !$osC_Database->isError() ) {
        osC_Cache::clear('contact_groupes');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-contacts');
        osC_Cache::clear('new_contacts');
        
        return true;
      }
      
      return false;
    }
  }
?>