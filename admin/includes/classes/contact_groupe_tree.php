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

  require('../includes/classes/contact_groupe_tree.php');

  class osC_Contact_groupeTree_Admin extends osC_Contact_groupeTree {
    var $show_contact_groupe_contact_count = true;

    function osC_Contact_groupeTree_Admin() {
      global $osC_Database, $osC_Language;

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
    }

    function calculateContact_groupeProductCount() {
      global $osC_Database;

      $totals = array();

      $Qtotals = $osC_Database->query('select p2c.contact_groupes_id, count(*) as total from :table_contacts p, :table_contacts_to_contact_groupes p2c where p2c.contacts_id = p.contacts_id group by p2c.contact_groupes_id');
      $Qtotals->bindTable(':table_contacts', 'contacts');
      $Qtotals->bindTable(':table_contacts_to_contact_groupes', 'contacts_to_contact_groupes');
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

    function getPath($contact_groupe_id, $level = 0, $separator = ' ') {
      $path = '';

      foreach ($this->data as $parent => $contact_groupes) {
        foreach ($contact_groupes as $id => $info) {
          if ($id == $contact_groupe_id) {
            if ($level < 1) {
              $path = $info['name'];
            } else {
              $path = $info['name'] . $separator . $path;
            }

            if ($parent != $this->root_contact_groupe_id) {
              $path = $this->getPath($parent, $level+1, $separator) . $path;
            }
          }
        }
      }

      return $path;
    }
  }
?>
