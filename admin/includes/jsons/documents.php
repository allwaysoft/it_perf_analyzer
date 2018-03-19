<?php
/*
  $Id: documents.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
require('includes/classes/documents.php');
//  require('includes/classes/documents_categories.php');
require('includes/classes/image.php');
if (!class_exists('content')) {
    require('includes/classes/content.php');
}
//    require('includes/classes/roles.php');

require('includes/classes/email_account.php');
require('includes/classes/email_accounts.php');

class toC_Json_Documents
{

    function listDocuments()
    {
        global $toC_Json, $osC_Database, $osC_Language;

        $records = array();

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $Qdocuments = $osC_Database->query("select d.documents_id,d.documents_categories_id,d.cache_filename,c.date_created,c.date_modified,c.date_published,c.created_by,c.modified_by,c.published_by,c.content_status,dd.documents_name,dd.documents_description from :table_documents d inner join :table_documents_description dd on d.documents_id = dd.documents_id and dd.languages_id = :language_id inner join :table_content c on c.content_id = d.documents_id where c.content_type = 'documents' ");

        if ($current_category_id != 0) {
            $Qdocuments->appendQuery('and d . documents_categories_id = :categories_id');
            $Qdocuments->bindInt(':categories_id', $current_category_id);
        }
        if (!empty($_REQUEST['documents_name']) && isset($_REQUEST['documents_name'])) {
            $Qdocuments->appendQuery('and dd.documents_name like :documents_name');
            $Qdocuments->bindValue(':documents_name', '%' . $_REQUEST['documents_name'] . '%');
        }

        $download = array('class' => '', 'qtip' => '');
        $edit = array('class' => '', 'qtip' => '');
        $delete = array('class' => '', 'qtip' => '');

        if ($_REQUEST['can_see'] == 1 || $_REQUEST['can_read'] == 1 || $_REQUEST['can_write'] == 1) {
            $download = array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download'));
        }

        if (strlen($_REQUEST['can_modify']) == 0) {
            $download = array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download'));
            $edit = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
            $delete = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash'));
        }

        if ($_REQUEST['can_publish'] == 1) {
            $download = array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download'));
            $edit = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
            $delete = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash'));
        }

        $Qdocuments->bindInt(':language_id', $osC_Language->getID());
        $Qdocuments->bindTable(':table_documents', TABLE_DOCUMENTS);
        $Qdocuments->bindTable(':table_content', TABLE_CONTENT);
        $Qdocuments->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
        $Qdocuments->setExtBatchLimit($start, $limit);
        $Qdocuments->execute();

        //var_dump($Qdocuments);

        while ($Qdocuments->next()) {
            $entry_icon = osc_icon_from_filename($Qdocuments->value('cache_filename'));
            $url = '../cache/documents/' . $Qdocuments->value('cache_filename');
            $action = array(
                $download,
                $edit,
                $delete);

            $records[] = array('documents_id' => $Qdocuments->valueInt('documents_id'),
                'icon' => $entry_icon,
                'action' => $action,
                'url' => $url,
                'content_status' => $Qdocuments->value('content_status'),
                'documents_name' => $Qdocuments->value('documents_name'),
                'documents_cache_filename' => $Qdocuments->value('cache_filename'),
                'documents_filename' => $Qdocuments->value('filename'),
                'date_created' => $Qdocuments->value('date_created'),
                'date_modified' => $Qdocuments->value('date_modified'),
                'date_published' => $Qdocuments->value('date_published'),
                'created_by' => $Qdocuments->value('created_by'),
                'modified_by' => $Qdocuments->value('modified_by'),
                'published_by' => $Qdocuments->value('published_by'),
                'documents_description' => $Qdocuments->value('documents_description'));
        }

        $response = array(EXT_JSON_READER_TOTAL => $Qdocuments->getBatchSize(),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function getDocumentsCategories()
    {
        global $toC_Json, $osC_Language;

        $article_categories = toC_Documents_Categories_Admin::getDocumentsCategories();

        $records = array();
        if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
            $records = array(array('id' => '', 'text' => $osC_Language->get('top_documents_category')));
        }

        foreach ($article_categories as $category) {
            if ($category['documents_categories_id'] != '1') {
                $records[] = array('id' => $category['documents_categories_id'],
                    'text' => $category['documents_categories_name']);
            }
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadDocument()
    {
        global $osC_Database, $toC_Json;

        $data = toC_Documents_Admin::getData($_REQUEST['documents_id']);

        if ($data != false) {
            $Qad = $osC_Database->query('SELECT d.*, dd.* FROM   :table_documents_description dd INNER JOIN :table_documents d ON (dd.documents_id = d.documents_id) where d.documents_id = :documents_id');
            $Qad->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
            $Qad->bindTable(':table_documents', TABLE_DOCUMENTS);
            $Qad->bindInt(':documents_id', $_REQUEST['documents_id']);
            $Qad->execute();

            while ($Qad->next()) {
                $data['documents_name[' . $Qad->value('languages_id') . ']'] = $Qad->value('documents_name');
                $data['documents_description[' . $Qad->value('languages_id') . ']'] = $Qad->value('documents_description');
            }

            $response = array('success' => true, 'data' => $data);
        } else {
            $response = array('success' => false, 'data' => $data);
        }

        echo $toC_Json->encode($response);
    }

    function setDocumentStatus()
    {
        global $toC_Json, $osC_Language;

        $username = $_SESSION[admin][username];

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter');
        } else {
            if (isset($_REQUEST['documents_id']) && toC_Documents_Admin::setDocumentStatus($_REQUEST['documents_id'], (isset($_REQUEST['flag'])
                ? $_REQUEST['flag'] : null),$username)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
            }
        }

        echo $toC_Json->encode($response);
    }

    function saveDocument()
    {
        $username = $_SESSION[admin][username];

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter');
        } else {
            global $toC_Json, $osC_Language;

            $documents_categories = (isset($_REQUEST['parent_category_id']) ? $_REQUEST['parent_category_id']
                : '0');
            $ids = explode('_', $documents_categories);;

            if (count($ids) > 0) {
                $documents_categories = $ids[count($ids) - 1];
            }

            $data = array('documents_name' => $_REQUEST['documents_name'],
                'documents_file' => $_FILES['documents_file_name'],
                'documents_description' => $_REQUEST['documents_description'],
                'documents_status' => $_REQUEST['documents_status'],
                'categories' => $documents_categories,
                'content_status' => isset($_REQUEST['documents_status']) ? $_REQUEST['documents_status'] : 0,
                'content_order' => 0,
                'documents_categories' => $documents_categories
            );

            if (toC_Documents_Admin::save((isset($_REQUEST['documents_id']) && ($_REQUEST['documents_id'] != -1)
                ? $_REQUEST['documents_id'] : null), $data)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
            }

            header('Content-Type: text/html');
        }

        echo $toC_Json->encode($response);
    }

    function deleteDocument()
    {
        global $toC_Json, $osC_Language;

        if (toC_Documents_Admin::delete($_REQUEST['documents_id'], $_REQUEST['documents_name'])) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function deleteDocuments()
    {
        global $toC_Json, $osC_Language;

        $error = false;

        $batchs = explode(',', $_REQUEST['batch']);
        foreach ($batchs as $batch) {
            list($documents_id, $filename) = explode(':', $batch);
            if (!toC_Documents_Admin::delete($documents_id, $filename)) {
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

    function setStatus()
    {
        global $toC_Json, $osC_Language;

        if (isset($_REQUEST['documents_id']) && toC_Documents_Admin::setStatus($_REQUEST['documents_id'], (isset($_REQUEST['flag'])
            ? $_REQUEST['flag'] : null))
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }
}

?>