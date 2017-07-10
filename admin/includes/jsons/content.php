<?php
/*
  $Id: articles.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
    require('includes/classes/image.php');
    if (!class_exists('content')) {
        include('includes/classes/content.php');
    }

    class toC_Json_Content
    {
        function getImages()
        {
            global $toC_Json, $osC_Database, $osC_Session;

            $osC_Image = new osC_Image_Admin();

            $records = array();

            if (isset($_REQUEST['content_id']) && is_numeric($_REQUEST['content_id'])) {
                $Qimages = $osC_Database->query('select id, image from :table_content_images where content_id = :content_id order by sort_order');
                $Qimages->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                $Qimages->bindInt(':content_id', $_REQUEST['content_id']);
                $Qimages->execute();

                while ($Qimages->next()) {
                    $records[] = array('id' => $Qimages->valueInt('id'),
                                       'image' => '<img src="' . DIR_WS_HTTP_CATALOG . 'images/content/mini/' . $Qimages->value('image') . '" border="0" />',
                                       'name' => $Qimages->value('image'),
                                       'size' => number_format(@filesize(DIR_FS_CATALOG . DIR_WS_IMAGES . 'content/originals/' . $Qimages->value('image'))) . ' bytes');
                }
            } else {
                $image_path = '../images/content/_upload/' . $osC_Session->getID() . '/';

                $osC_DirectoryListing = new osC_DirectoryListing($image_path, true);
                $osC_DirectoryListing->setIncludeDirectories('false');

                foreach ($osC_DirectoryListing->getFiles() as $file) {
                    $records[] = array('id' => '',
                                       'image' => '<img src="' . $image_path . $file['name'] . '" border="0" width="' . $osC_Image->getWidth('mini') . '" height="' . $osC_Image->getHeight('mini') . '" />',
                                       'name' => $file['name'],
                                       'size' => number_format($file['size']) . ' bytes');
                }
            }

            $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function uploadImage()
        {
            global $toC_Json, $osC_Database, $osC_Session, $osC_Language;

            $osC_Image = new osC_Image_Admin();

            if (is_array($_FILES)) {
                $content_images = array_keys($_FILES);
                $content_images = $content_images[0];
            }

            $content_images = new upload($content_images);
            if (isset($_REQUEST['content_id']) && $_REQUEST['content_id'] > 0) {
                if ($content_images->exists()) {
                    $image_path = '../images/content/originals/';
                    $content_images->set_destination($image_path);

                    if ($content_images->parse() && $content_images->save()) {
                        $Qimage = $osC_Database->query('insert into :table_content_images (content_id, image, sort_order, date_added,content_type) values (:content_id, :image, :sort_order, :date_added,:content_type)');
                        $Qimage->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                        $Qimage->bindInt(':content_id', $_REQUEST['content_id']);
                        $Qimage->bindValue(':image', $content_images->filename);
                        $Qimage->bindValue(':content_type', $_REQUEST['content_type']);
                        $Qimage->bindInt(':sort_order', 0);
                        $Qimage->bindRaw(':date_added', 'now()');
                        $Qimage->execute();

                        if (!$osC_Database->isError()) {
                            $image_id = $osC_Database->nextID();
                            $new_image_name = $_REQUEST['content_id'] . '_' . $image_id . '_' . $content_images->filename;
                            @rename($image_path . $content_images->filename, $image_path . $new_image_name);

                            $Qupdate = $osC_Database->query('update :table_content_images set image = :image where id = :id');
                            $Qupdate->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                            $Qupdate->bindValue(':image', $new_image_name);
                            $Qupdate->bindInt(':id', $image_id);
                            $Qupdate->execute();
                        }

                        foreach ($osC_Image->getGroups() as $group) {
                            if ($group['id'] != '1') {
                                $osC_Image->resize($new_image_name, $group['id'], 'content');
                            }
                        }
                    }
                }
            } else {
                $image_path = '../images/content/_upload/' . $osC_Session->getID() . '/';
                toc_mkdir($image_path);

                if ($content_images->exists()) {
                    $content_images->set_destination($image_path);
                    $content_images->parse();
                    $content_images->save();
                }
            }

            header('Content-Type: text/html');

            $response['success'] = true;
            $response['feedback'] = $osC_Language->get('ms_success_action_performed');

            echo $toC_Json->encode($response);
        }

        function deleteImage()
        {
            global $toC_Json, $osC_Language, $osC_Session;

            $error = false;

            if (is_numeric($_REQUEST['image'])) {
                $osC_Image = new osC_Image_Admin();

                if (!$osC_Image->delete($_REQUEST['image'], TABLE_CONTENT_IMAGES, 'content')) {
                    $error = true;
                }
            } else {
                $image_path = '../images/content/_upload/' . $osC_Session->getID() . '/';

                if (!osc_remove($image_path . $_REQUEST['image'])) {
                    $error = true;
                }
            }

            if ($error === false) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function listDocuments()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $records = array();

            $content_id = empty($_REQUEST['content_id']) ? -1 : $_REQUEST['content_id'];
            $content_type = empty($_REQUEST['content_type']) ? '' : $_REQUEST['content_type'];

            $Qdocuments = $osC_Database->query("select d.* from :table_content_documents d where d.content_id = :content_id and d.content_type = :content_type order by d.documents_name");

            $Qdocuments->bindInt(':content_id', $content_id);
            $Qdocuments->bindInt(':content_type', $content_type);
            $Qdocuments->bindTable(':table_content_documents', TABLE_CONTENT_DOCUMENTS);
            $Qdocuments->execute();

            while ($Qdocuments->next()) {
                $entry_icon = osc_icon_from_filename($Qdocuments->value('filename'));
                $url = '../cache/documents/' . $Qdocuments->value('cache_filename');
                $action = array(
                    array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download')),
                    array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash')));

                $records[] = array('documents_id' => $Qdocuments->valueInt('documents_id'),
                                   'icon' => $entry_icon,
                                   'action' => $action,
                                   'url' => $url,
                                   'documents_name' => $Qdocuments->value('documents_name'),
                                   'documents_cache_filename' => $Qdocuments->value('cache_filename'),
                                   'documents_filename' => $Qdocuments->value('filename'),
                                   'documents_status' => $Qdocuments->value('documents_status'),
                                   'documents_description' => $Qdocuments->value('documents_description'));
            }

            $response = array(EXT_JSON_READER_TOTAL => $Qdocuments->getBatchSize(),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listComments()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

            $records = array();

            $content_id = empty($_REQUEST['content_id']) ? -1 : $_REQUEST['content_id'];
            $content_type = empty($_REQUEST['content_type']) ? '' : $_REQUEST['content_type'];

            $Qcomments = $osC_Database->query("select c.*,u.image_url,a.user_name from :table_content_comments c inner join :table_users u on (c.created_by = u.administrators_id) inner join :table_administrators a on (c.created_by = a.id) where c.content_id = :content_id and c.content_type = :content_type order by c.comments_date_added desc");

            $Qcomments->bindInt(':content_id', $content_id);
            $Qcomments->bindValue(':content_type', $content_type);
            $Qcomments->bindTable(':table_content_comments', TABLE_CONTENT_COMMENTS);
            $Qcomments->bindTable(':table_users', TABLE_USERS);
            $Qcomments->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qcomments->setExtBatchLimit($start, $limit);
            $Qcomments->execute();

            while ($Qcomments->next()) {
                $image = '<img src="../images/users/mini/' . $Qcomments->value('image_url') . '" width="100" height="80" />';
                $action = array(
                    array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash')));

                $records[] = array('comments_id' => $Qcomments->valueInt('comments_id'),
                                   'action' => $action,
                                   'image_url' => $image,
                                   'comment' => array('user_name' => $Qcomments->value('user_name'), 'comments_description' => $Qcomments->value('comments_description')),
                                   'comments_date_added' => $Qcomments->value('comments_date_added'),
                                   'comments_status' => $Qcomments->value('comments_status'));
            }

            $Qcomments->freeResult();

            $response = array(EXT_JSON_READER_TOTAL => $Qcomments->getBatchSize(),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listLinks()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $records = array();

            $content_id = empty($_REQUEST['content_id']) ? -1 : $_REQUEST['content_id'];
            $content_type = empty($_REQUEST['content_type']) ? '' : $_REQUEST['content_type'];

            $Qlinks = $osC_Database->query("select l.* from :table_content_links l where l.content_id = :content_id and l.content_type = :content_type order by l.links_name");

            $Qlinks->bindInt(':content_id', $content_id);
            $Qlinks->bindInt(':content_type', $content_type);
            $Qlinks->bindTable(':table_content_links', TABLE_CONTENT_LINKS);
            $Qlinks->execute();

            while ($Qlinks->next()) {
                $url = $Qlinks->value('links_url');
                $action = array(
                    array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download')),
                    array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash')));

                $records[] = array('links_id' => $Qlinks->valueInt('links_id'),
                                   'action' => $action,
                                   'links_url' => $url,
                                   'links_name' => $Qlinks->value('links_name'),
                                   'links_status' => $Qlinks->value('links_status'),
                                   'links_description' => $Qlinks->value('links_description'));
            }

            $response = array(EXT_JSON_READER_TOTAL => $Qlinks->getBatchSize(),
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }



        function saveDocument()
        {
            global $toC_Json, $osC_Language;

            $data = array('documents_name' => $_REQUEST['documents_name'],
                          'documents_file' => $_FILES['documents_file_name'],
                          'documents_description' => $_REQUEST['documents_description'],
                          'documents_status' => $_REQUEST['documents_status'],
                          'content_id' => $_REQUEST['content_id'],
                          'content_type' => $_REQUEST['content_type']
            );

            if (content::saveDocument((isset($_REQUEST['documents_id']) && ($_REQUEST['documents_id'] != -1)
                        ? $_REQUEST['documents_id'] : null), $data)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            header('Content-Type: text/html');
            echo $toC_Json->encode($response);
        }

        function saveComment()
        {
            global $toC_Json, $osC_Language;

            $data = array('comment_status' => $_REQUEST['comment_status'],
                          'comments_description' => $_REQUEST['comments_description'],
                          'comment_file_name' => $_FILES['comment_file_name'],
                          'content_id' => $_REQUEST['content_id'],
                          'content_type' => $_REQUEST['content_type']
            );

            if (content::saveComment((isset($_REQUEST['comments_id']) && ($_REQUEST['comments_id'] != -1)
                        ? $_REQUEST['comments_id'] : null), $data)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            header('Content-Type: text/html');
            echo $toC_Json->encode($response);
        }

        function moveContent()
        {
            global $toC_Json, $osC_Language;
            $error = false;

            $data = array(
                'content_id' => $_REQUEST['content_id'],
                'content_type' => $_REQUEST['content_type']
            );

            $category_array = explode('_', $_REQUEST['parent_category_id']);
            $data['categories'] = end($category_array);

            $batch = explode(',', $_REQUEST['content_ids']);

            foreach ($batch as $id) {
                if (!content::saveContentToCategories(null, $id, $_REQUEST['content_type'], $data)
                ) {
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

        function copyContent()
        {
            global $toC_Json, $osC_Language;
            $error = false;

            $data = array(
                'content_id' => $_REQUEST['content_id'],
                'content_type' => $_REQUEST['content_type']
            );

            if (isset($_REQUEST[content_categories_id])) {
                $data['categories'] = explode(',', $_REQUEST[content_categories_id]);
            }

            $batch = explode(',', $_REQUEST['content_ids']);

            foreach ($batch as $id) {
                if (!content::copyContentToCategories(null, $id, $_REQUEST['content_type'], $data)
                ) {
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

        function saveLink()
        {
            global $toC_Json, $osC_Language;

            $data = array('links_name' => $_REQUEST['links_name'],
                          'links_description' => $_FILES['links_description'],
                          'links_target' => $_REQUEST['links_target'],
                          'links_status' => $_REQUEST['links_status'],
                          'links_url' => $_REQUEST['links_url'],
                          'content_id' => $_REQUEST['content_id'],
                          'content_type' => $_REQUEST['content_type']
            );

            if (content::saveLink((isset($_REQUEST['links_id']) && ($_REQUEST['links_id'] != -1)
                        ? $_REQUEST['links_id'] : null), $data)
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            header('Content-Type: text/html');
            echo $toC_Json->encode($response);
        }

        function deleteDocument()
        {
            global $toC_Json, $osC_Language;

            if (content::deleteDocument($_REQUEST['documents_id'], $_REQUEST['documents_name'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function deleteLink()
        {
            global $toC_Json, $osC_Language;

            if (content::deleteLink($_REQUEST['links_id'])) {
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
                if (!content::deleteDocument($documents_id, $filename)) {
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

        function deleteLinks()
        {
            global $toC_Json, $osC_Language;

            $error = false;

            $batchs = explode(',', $_REQUEST['batch']);
            foreach ($batchs as $batch) {
                if (!content::deleteLink($batch)) {
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

        function setCommentStatus()
        {
            global $toC_Json, $osC_Language;

            if (isset($_REQUEST['comments_id']) && content::setCommentStatus($_REQUEST['comments_id'], (isset($_REQUEST['flag'])
                        ? $_REQUEST['flag'] : null))
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function setLinkStatus()
        {
            global $toC_Json, $osC_Language;

            if (isset($_REQUEST['links_id']) && content::setLinkStatus($_REQUEST['links_id'], (isset($_REQUEST['flag'])
                        ? $_REQUEST['flag'] : null))
            ) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function listPermissions()
        {
            global $toC_Json;
            $recs = content::getPermissions($_REQUEST['content_id'], $_REQUEST['content_type']);
            $response = array(EXT_JSON_READER_TOTAL => count($recs),
                              EXT_JSON_READER_ROOT => $recs);

            echo $toC_Json->encode($response);
        }

        function listPerms()
        {
            global $toC_Json;
            $recs = content::getPerms($_REQUEST['content_id'], $_REQUEST['content_type']);
            $response = array(EXT_JSON_READER_TOTAL => $recs['total'],
                EXT_JSON_READER_ROOT => $recs['recs']);

            echo $toC_Json->encode($response);
        }

        function listNotifications()
        {
            global $toC_Json;
            $recs = content::getNotifications($_REQUEST['content_id'], $_REQUEST['content_type']);
            $response = array(EXT_JSON_READER_TOTAL => $recs['total'],
                EXT_JSON_READER_ROOT => $recs['recs']);

            echo $toC_Json->encode($response);
        }

        function setPermission()
        {
            global $toC_Json, $osC_Language;
            $data = array('content_id' => $_REQUEST['content_id'], 'content_type' => $_REQUEST['content_type'], 'roles_id' => $_REQUEST['roles_id'], 'permission' => $_REQUEST['permission'], 'flag' => $_REQUEST['flag']);

            if (!isset($_REQUEST['content_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['content_type'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier le type de contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['roles_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un Role');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['permission'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier une permission pour ce Role');
                echo $toC_Json->encode($response);
                return;
            }

            if (content::setPermission($data['content_id'], $data['content_type'], $data['permission'], $data['roles_id'], $data['flag'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function setNotification()
        {
            global $toC_Json, $osC_Language;
            $data = array('content_id' => $_REQUEST['content_id'], 'content_type' => $_REQUEST['content_type'], 'roles_id' => $_REQUEST['roles_id'], 'permission' => $_REQUEST['permission'], 'flag' => $_REQUEST['flag'], 'email' => $_REQUEST['email']);

            if (!isset($_REQUEST['content_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['content_type'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier le type de contenu');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['roles_id'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier un Role');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['permission'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier une permission pour ce Role');
                echo $toC_Json->encode($response);
                return;
            }

            if (!isset($_REQUEST['email'])) {
                $response = array('success' => false, 'feedback' => 'Veuillez specifier une adresse Email ...');
                echo $toC_Json->encode($response);
                return;
            }

            if (content::setNotification($data['content_id'], $data['content_type'], $data['permission'], $data['roles_id'], $data['flag'], $data['email'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }
    }

?>