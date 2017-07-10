<?php
/*
  $Id: contact_groupe_tree.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Contact_groupeTree {
    var $root_contact_groupe_id = 0,
        $max_level = 0,
        $data = array(),
        $root_start_string = '',
        $root_end_string = '',
        $parent_start_string = '',
        $parent_end_string = '',
        $parent_group_start_string = '<ul>',
        $parent_group_end_string = '</ul>',
        $child_start_string = '<li>',
        $child_end_string = '</li>',
        $breadcrumb_separator = '_',
        $breadcrumb_usage = true,
        $spacer_string = '',
        $spacer_multiplier = 1,
        $follow_cpath = false,
        $cpath_array = array(),
        $cpath_start_string = '',
        $cpath_end_string = '',
        $show_contact_groupe_contact_count = false,
        $contact_groupe_contact_count_start_string = '&nbsp;(',
        $contact_groupe_contact_count_end_string = ')';

    function osC_Contact_groupeTree($load_from_database = true) {
      global $osC_Database, $osC_Cache, $osC_Language;

      if (SERVICES_CATEGORY_PATH_CALCULATE_PRODUCT_COUNT == '1') {
        $this->show_contact_groupe_contact_count = true;
      }

      if ($load_from_database === true) {
        if ($osC_Cache->read('contact_groupe_tree-' . $osC_Language->getCode(), 720)) {
          $this->data = $osC_Cache->getCache();
        } else {
          $Qcontact_groupes = $osC_Database->query('select c.contact_groupes_id, c.parent_id, c.contact_groupes_image, cd.contact_groupes_name, cd.contact_groupes_url, cd.contact_groupes_page_title, cd.contact_groupes_meta_keywords, cd.contact_groupes_meta_description from :table_contact_groupes c, :table_contact_groupes_description cd where c.contact_groupes_id = cd.contact_groupes_id and cd.language_id = :language_id and c.contact_groupes_status = 1 order by c.parent_id, c.sort_order, cd.contact_groupes_name');
          $Qcontact_groupes->bindTable(':table_contact_groupes', 'contact_groupes');
          $Qcontact_groupes->bindTable(':table_contact_groupes_description', 'contact_groupes_description');
          $Qcontact_groupes->bindInt(':language_id', $osC_Language->getID());
          $Qcontact_groupes->execute();

          $this->data = array();

          while ($Qcontact_groupes->next()) {
            $this->data[$Qcontact_groupes->valueInt('parent_id')][$Qcontact_groupes->valueInt('contact_groupes_id')] = array('name' => $Qcontact_groupes->value('contact_groupes_name'), 'url' => $Qcontact_groupes->value('contact_groupes_url'), 'page_title' => $Qcontact_groupes->value('contact_groupes_page_title'), 'meta_keywords' => $Qcontact_groupes->value('contact_groupes_meta_keywords'), 'meta_description' => $Qcontact_groupes->value('contact_groupes_meta_description'), 'image' => $Qcontact_groupes->value('contact_groupes_image'), 'count' => 0);
          }

          $Qcontact_groupes->freeResult();

          if ($this->show_contact_groupe_contact_count === true) {
            $this->calculateContact_groupeContactCount();
          }

          $osC_Cache->writeBuffer($this->data);
        }
      }
    }

    function setData(&$data_array) {
      if (is_array($data_array)) {
        $this->data = array();

        for ($i=0, $n=sizeof($data_array); $i<$n; $i++) {
          $this->data[$data_array[$i]['parent_id']][$data_array[$i]['contact_groupes_id']] = array('name' => $data_array[$i]['contact_groupes_name'], 'count' => $data_array[$i]['contact_groupes_count']);
        }
      }
    }

    function reset() {
      $this->root_contact_groupe_id = 0;
      $this->max_level = 0;
      $this->root_start_string = '';
      $this->root_end_string = '';
      $this->parent_start_string = '';
      $this->parent_end_string = '';
      $this->parent_group_start_string = '<ul>';
      $this->parent_group_end_string = '</ul>';
      $this->child_start_string = '<li>';
      $this->child_end_string = '</li>';
      $this->breadcrumb_separator = '_';
      $this->breadcrumb_usage = true;
      $this->spacer_string = '';
      $this->spacer_multiplier = 1;
      $this->follow_cpath = false;
      $this->cpath_array = array();
      $this->cpath_start_string = '';
      $this->cpath_end_string = '';
      $this->show_contact_groupe_contact_count = true;
      $this->contact_groupe_contact_count_start_string = '&nbsp;(';
      $this->contact_groupe_contact_count_end_string = ')';
    }

    function buildBranch($parent_id, $level = 0) {
      $result = $this->parent_group_start_string;

      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $contact_groupe_id => $contact_groupe) {
          if ($this->breadcrumb_usage == true) {
            $contact_groupe_link = $this->buildBreadcrumb($contact_groupe_id);
          } else {
            $contact_groupe_link = $contact_groupe_id;
          }

          $result .= $this->child_start_string;

          if (isset($this->data[$contact_groupe_id])) {
            $result .= $this->parent_start_string;
          }

          if ($level == 0) {
            $result .= $this->root_start_string;
          }

          if ( ($this->follow_cpath === true) && in_array($contact_groupe_id, $this->cpath_array) ) {
            $link_title = $this->cpath_start_string . $contact_groupe['name'] . $this->cpath_end_string;
          } else {
            $link_title = $contact_groupe['name'];
          }

          $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $contact_groupe_link), $link_title);

          if ($this->show_contact_groupe_contact_count === true) {
            $result .= $this->contact_groupe_contact_count_start_string . $contact_groupe['count'] . $this->contact_groupe_contact_count_end_string;
          }

          if ($level == 0) {
            $result .= $this->root_end_string;
          }

          if (isset($this->data[$contact_groupe_id])) {
            $result .= $this->parent_end_string;
          }

          $result .= $this->child_end_string;

          if (isset($this->data[$contact_groupe_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
            if ($this->follow_cpath === true) {
              if (in_array($contact_groupe_id, $this->cpath_array)) {
                $result .= $this->buildBranch($contact_groupe_id, $level+1);
              }
            } else {
              $result .= $this->buildBranch($contact_groupe_id, $level+1);
            }
          }
        }
      }

      $result .= $this->parent_group_end_string;

      return $result;
    }

    function buildBranchArray($parent_id, $level = 0, $result = '') {
      if (empty($result)) {
        $result = array();
      }

      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $contact_groupe_id => $contact_groupe) {
          if ($this->breadcrumb_usage == true) {
            $contact_groupe_link = $this->buildBreadcrumb($contact_groupe_id);
          } else {
            $contact_groupe_link = $contact_groupe_id;
          }

          $result[] = array('id' => $contact_groupe_link,
                            'title' => str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . $contact_groupe['name']);

          if (isset($this->data[$contact_groupe_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
            if ($this->follow_cpath === true) {
              if (in_array($contact_groupe_id, $this->cpath_array)) {
                $result = $this->buildBranchArray($contact_groupe_id, $level+1, $result);
              }
            } else {
              $result = $this->buildBranchArray($contact_groupe_id, $level+1, $result);
            }
          }
        }
      }

      return $result;
    }    

    function buildBreadcrumb($contact_groupe_id, $level = 0) {
      $breadcrumb = '';

      foreach ($this->data as $parent => $contact_groupes) {
        foreach ($contact_groupes as $id => $info) {
          if ($id == $contact_groupe_id) {
            if ($level < 1) {
              $breadcrumb = $id;
            } else {
              $breadcrumb = $id . $this->breadcrumb_separator . $breadcrumb;
            }

            if ($parent != $this->root_contact_groupe_id) {
              $breadcrumb = $this->buildBreadcrumb($parent, $level+1) . $breadcrumb;
            }
          }
        }
      }

      return $breadcrumb;
    }

    function buildTree() {
      return $this->buildBranch($this->root_contact_groupe_id);
    }

    function getTree($parent_id = '') {
      return $this->buildBranchArray((empty($parent_id) ? $this->root_contact_groupe_id : $parent_id));
    }

    function exists($id) {
      foreach ($this->data as $parent => $contact_groupes) {
        foreach ($contact_groupes as $contact_groupe_id => $info) {
          if ($id == $contact_groupe_id) {
            return true;
          }
        }
      }

      return false;
    }

    function getChildren($contact_groupe_id, &$array) {
      foreach ($this->data as $parent => $contact_groupes) {
        if ($parent == $contact_groupe_id) {
          foreach ($contact_groupes as $id => $info) {
            $array[] = $id;
            $this->getChildren($id, $array);
          }
        }
      }

      return $array;
    }

    function getData($id) {
      foreach ($this->data as $parent => $contact_groupes) {
        foreach ($contact_groupes as $contact_groupe_id => $info) {
          if ($id == $contact_groupe_id) {
            return array('id' => $id,
                         'name' => $info['name'],
                         'page_title' => $info['page_title'],
                         'meta_keywords' => $info['meta_keywords'],
                         'meta_description' => $info['meta_description'],
                         'parent_id' => $parent,
                         'image' => $info['image'],
                         'count' => $info['count']
                        );
          }
        }
      }

      return false;
    }

    function calculateContact_groupeContactCount() {
      global $osC_Database;

      $totals = array();

      $Qtotals = $osC_Database->query('select p2c.contact_groupes_id, count(*) as total from :table_contacts p, :table_contacts_to_contact_groupes p2c where p2c.contacts_id = p.contacts_id and p.contacts_status = :contacts_status group by p2c.contact_groupes_id');
      $Qtotals->bindTable(':table_contacts', 'contacts');
      $Qtotals->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
      $Qtotals->bindInt(':contacts_status', 1);
      $Qtotals->execute();

      while ($Qtotals->next()) {
        $totals[$Qtotals->valueInt('contact_groupes_id')] = $Qtotals->valueInt('total');
      }

      $Qtotals->freeResult();

      foreach ($this->data as $parent => $contact_groupes) {
        foreach ($contact_groupes as $id => $info) {
          if (isset($totals[$id]) && ($totals[$id] > 0)) {
            $this->data[$parent][$id]['count'] = $totals[$id];

            $parent_contact_groupe = $parent;
            while ($parent_contact_groupe != $this->root_contact_groupe_id) {
              foreach ($this->data as $parent_parent => $parent_contact_groupes) {
                foreach ($parent_contact_groupes as $parent_contact_groupe_id => $parent_contact_groupe_info) {
                  if ($parent_contact_groupe_id == $parent_contact_groupe) {
                    $this->data[$parent_parent][$parent_contact_groupe_id]['count'] += $this->data[$parent][$id]['count'];

                    $parent_contact_groupe = $parent_parent;
                    break 2;
                  }
                }
              }
            }
          }
        }
      }

      unset($totals);
    }

    function getNumberOfContacts($id) {
      foreach ($this->data as $parent => $contact_groupes) {
        foreach ($contact_groupes as $contact_groupe_id => $info) {
          if ($id == $contact_groupe_id) {
            return $info['count'];
          }
        }
      }

      return false;
    }

    function setRootContact_groupeID($root_contact_groupe_id) {
      $this->root_contact_groupe_id = $root_contact_groupe_id;
    }

    function setMaximumLevel($max_level) {
      $this->max_level = $max_level;
    }

    function setRootString($root_start_string, $root_end_string) {
      $this->root_start_string = $root_start_string;
      $this->root_end_string = $root_end_string;
    }

    function setParentString($parent_start_string, $parent_end_string) {
      $this->parent_start_string = $parent_start_string;
      $this->parent_end_string = $parent_end_string;
    }

    function setParentGroupString($parent_group_start_string, $parent_group_end_string) {
      $this->parent_group_start_string = $parent_group_start_string;
      $this->parent_group_end_string = $parent_group_end_string;
    }

    function setChildString($child_start_string, $child_end_string) {
      $this->child_start_string = $child_start_string;
      $this->child_end_string = $child_end_string;
    }

    function setBreadcrumbSeparator($breadcrumb_separator) {
      $this->breadcrumb_separator = $breadcrumb_separator;
    }

    function setBreadcrumbUsage($breadcrumb_usage) {
      if ($breadcrumb_usage === true) {
        $this->breadcrumb_usage = true;
      } else {
        $this->breadcrumb_usage = false;
      }
    }

    function setSpacerString($spacer_string, $spacer_multiplier = 2) {
      $this->spacer_string = $spacer_string;
      $this->spacer_multiplier = $spacer_multiplier;
    }

    function setContact_groupePath($cpath, $cpath_start_string = '', $cpath_end_string = '') {
      $this->follow_cpath = true;
      $this->cpath_array = explode($this->breadcrumb_separator, $cpath);
      $this->cpath_start_string = $cpath_start_string;
      $this->cpath_end_string = $cpath_end_string;
    }

    function setFollowContact_groupePath($follow_cpath) {
      if ($follow_cpath === true) {
        $this->follow_cpath = true;
      } else {
        $this->follow_cpath = false;
      }
    }

    function setContact_groupePathString($cpath_start_string, $cpath_end_string) {
      $this->cpath_start_string = $cpath_start_string;
      $this->cpath_end_string = $cpath_end_string;
    }

    function setShowContact_groupeContactCount($show_contact_groupe_contact_count) {
      if ($show_contact_groupe_contact_count === true) {
        $this->show_contact_groupe_contact_count = true;
      } else {
        $this->show_contact_groupe_contact_count = false;
      }
    }

    function setContact_groupeContactCountString($contact_groupe_contact_count_start_string, $contact_groupe_contact_count_end_string) {
      $this->contact_groupe_contact_count_start_string = $contact_groupe_contact_count_start_string;
      $this->contact_groupe_contact_count_end_string = $contact_groupe_contact_count_end_string;
    }

    function getParentCategories($contact_groupe_id, &$contact_groupes) {
      foreach ($this->data as $parent => $sub_contact_groupes) {
        foreach ($sub_contact_groupes as $id => $info) {
          if ( ($id == $contact_groupe_id) && ($parent != $this->root_contact_groupe_id) ) {
            $contact_groupes[] = $parent;
            $this->getParentCategories($parent, $contact_groupes);
          }
        }
      }
    }

    function getFullcPath($contact_groupes_id){
      if ( ereg('_', $contact_groupes_id) ){
        return $contact_groupes_id;
      } else {
        $contact_groupes = array();
        $this->getParentCategories($contact_groupes_id, $contact_groupes);

        $contact_groupes = array_reverse($contact_groupes);
        $contact_groupes[] = $contact_groupes_id;
        $cPath = implode('_', $contact_groupes);

        return $cPath;
      }
    }

    function getContact_groupeUrl($cPath) {
      $cPath = $this->getFullcPath($cPath);
      $contact_groupes = @explode('_', $cPath);

      if(sizeof($contact_groupes) > 1){
        $contact_groupe_id = end($contact_groupes);
        $parent_id = $contact_groupes[sizeof($contact_groupes)-2];
      }else{
        $contact_groupe_id = $cPath;
        $parent_id = $this->root_contact_groupe_id;
      }

      $contact_groupe_url = $this->data[$parent_id][$contact_groupe_id]['url'];
      
      return $contact_groupe_url;
    }
    
    function getContact_groupeName($cPath){
      $cPath = $this->getFullcPath($cPath);
      $contact_groupes = @explode('_', $cPath);

      if(sizeof($contact_groupes) > 1){
        $contact_groupe_id = end($contact_groupes);
        $parent_id = $contact_groupes[sizeof($contact_groupes)-2];
      }else{
        $contact_groupe_id = $cPath;
        $parent_id = $this->root_contact_groupe_id;
      }

      $contact_groupe_name = $this->data[$parent_id][$contact_groupe_id]['name'];
      return $contact_groupe_name;
    }
    
    function buildExtJsonTreeArray($parent_id = 0, $tree_node_cls = 'x-tree-node-collapsed') {
      $result = array();
      
      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $contact_groupe_id => $contact_groupe) {
          $data = array('id' => $contact_groupe_id, 'text' => $contact_groupe['name']);

          if (isset($this->data[$contact_groupe_id])) {
            $data['children'] = $this->buildExtJsonTreeArray($contact_groupe_id, $tree_node_cls);
          } else {
            $data['leaf'] = true;
            $data['cls'] = $tree_node_cls;
          }
          
          $result[] = $data;
        }
      }
      
      return $result;
    }
  }
  
  /**
   * class toC_Contact_groupeTree
   */
  class toC_Contact_groupeTree extends osC_Contact_groupeTree {
    var $leading_string = '';
    
    function setLeadingString($leading_string) {
      $this->leading_string = $leading_string;
    }
    
    function buildBranch($parent_id, $level = 0) {
      $result = $this->parent_group_start_string;
  
      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $contact_groupe_id => $contact_groupe) {
          if ($this->breadcrumb_usage == true) {
            $contact_groupe_link = $this->buildBreadcrumb($contact_groupe_id);
          } else {
            $contact_groupe_link = $contact_groupe_id;
          }
  
          $result .= $this->child_start_string;
  
          if (isset($this->data[$contact_groupe_id])) {
            $result .= $this->parent_start_string;
          }
  
          if ($level == 0) {
            $result .= $this->root_start_string;
          }
  
          if ( ($this->follow_cpath === true) && in_array($contact_groupe_id, $this->cpath_array) ) {
            $link_title = $this->cpath_start_string . $contact_groupe['name'] . $this->cpath_end_string;
          } else {
            $link_title = $contact_groupe['name'];
          }
          
          if ($this->show_contact_groupe_contact_count === true) {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $contact_groupe_link), str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . $this->leading_string . $link_title . $this->contact_groupe_contact_count_start_string . $contact_groupe['count'] . $this->contact_groupe_contact_count_end_string);
          } else {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $contact_groupe_link), str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . $this->leading_string . $link_title);
          }
  
          if ($level == 0) {
            $result .= $this->root_end_string;
          }
  
          if (isset($this->data[$contact_groupe_id])) {
            $result .= $this->parent_end_string;
          }
  
          $result .= $this->child_end_string;
  
          if (isset($this->data[$contact_groupe_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
            if ($this->follow_cpath === true) {
              if (in_array($contact_groupe_id, $this->cpath_array)) {
                $result .= $this->buildBranch($contact_groupe_id, $level+1);
              }
            } else {
              $result .= $this->buildBranch($contact_groupe_id, $level+1);
            }
          }
        }
      }
  
      $result .= $this->parent_group_end_string;
  
      return $result;
    }
    
    function buildCompleteBranch($contact_groupes, $level = 0) {
      $result = ($level == 0) ? '<ul id="contact_groupesTree">' : '<ul>';
      
      if ( is_array($contact_groupes) && !empty($contact_groupes) ) {
        foreach($contact_groupes as $contact_groupes_id => $contact_groupe) {
          $contact_groupe_link = $contact_groupes_id;
          
          $result .= $this->child_start_string;// . '<div class="linkebox"><span class="navileft"><img src="images/pfeil_blue.gif" alt="" style="vertical-align: middle;">&nbsp';     
                 
          if ( ($this->follow_cpath === true) && in_array($contact_groupe_id, $this->cpath_array) ) {
            $link_title = $this->cpath_start_string . $contact_groupe['name'] . $this->cpath_end_string;
          } else {
            $link_title = $contact_groupe['name'];
          }
          
          if ($this->show_contact_groupe_contact_count === true) {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $contact_groupe_link), $link_title . $this->contact_groupe_contact_count_start_string . $contact_groupe['count'] . $this->contact_groupe_contact_count_end_string);
          } else {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $contact_groupe_link), $this->leading_string . $link_title);
          }       
          
          if(in_array($contact_groupes_id, array_keys($this->data))) {
            $result .= $this->buildCompleteBranch($this->data[$contact_groupes_id], $level + 1, $contact_groupes_id);
          }
          
          $result .= $this->child_end_string;// . '</span></div>';
        }
      }
      
      $result .= '</ul>';
      
      return $result;
    }
    
    function buildCompleteTree() {
      return $this->buildCompleteBranch($this->data[0], 0);
    }
  }  
?>