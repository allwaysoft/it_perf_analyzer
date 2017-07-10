<?php
/*
  $Id: documents_listing.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Boxes_documents_listing extends osC_Modules
    {
        var $_title,
        $_code = 'documents_listing',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

        function osC_Boxes_documents_listing()
        {
            $this->_title = 'Telechargements';
        }

        function initialize()
        {
            include_once('admin/includes/functions/html_output.php');
            global $osC_Database, $osC_Language, $current_category_id, $osC_Image;

            $current_category_id = $current_category_id > 0 ? $current_category_id : -1;

            $Qdocuments = $osC_Database->query('select dd.*, d.* from :table_documents d, :table_documents_description dd where d.documents_id = dd.documents_id and dd.languages_id = :language_id and d.documents_status = 1 and d.documents_categories_id = :documents_categories_id order by d.documents_date_added desc');
            $Qdocuments->bindTable(':table_documents', TABLE_DOCUMENTS);
            $Qdocuments->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
            $Qdocuments->bindInt(':language_id', $osC_Language->getID());
            $Qdocuments->bindInt(':documents_categories_id', $current_category_id);
            //      $Qdocuments->setCache('box-documents_listing-' . $osC_Language->getCode(), 100);
            $Qdocuments->execute();

            $this->_content .= '<table><tbody>';
            while ($Qdocuments->next()) {
                $entry_icon = osc_image('admin/templates/default/images/icons/16x16/' . osc_icon_filename($Qdocuments->value('filename')),'', null, null, null);
                $url = 'cache/documents/' . $Qdocuments->value('cache_filename');
                $this->_content .= '<tr><td align="left" valign="top">'
                                   . $entry_icon . '</td><td align="left" valign="top"><table><tbody><tr><td> '
                                   . osc_link_object($url, $Qdocuments->value('documents_name')) . ' </td></tr><tr><td> '
                                   . $Qdocuments->value('documents_description') . ' </td></tr><tr><td><span style="font-style: italic;">'
                                   . osc_link_object($url,'Telecharger') . '</td></span></tr></tbody></table></td></tr>';                
            }

            $this->_content .= '</tbody></table>';

            $Qdocuments->freeResult();
        }

        function install()
        {
            global $osC_Database;

            parent::install();
            $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum List Size', 'BOX_DOCUMENTS_MAX_LIST', '10', 'Maximum amount of documents to show in the listing', '6', '0', now())");
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array('BOX_DOCUMENTS_MAX_LIST');
            }

            return $this->_keys;
        }
    }

?>
