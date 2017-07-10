<?php
/*
  $Id: slide_images.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
require('includes/classes/slide_images.php');
require('includes/classes/image.php');

class toC_Json_Slide_Images
{

    function listSlideImages()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $Qimages = $osC_Database->query('select s.*, atoc.*  from :table_slide_images s left join :table_content_to_categories atoc on atoc.content_id = s.image_id  where s.language_id = :language_id and atoc.content_type = "images"');

        if ($current_category_id != 0) {
            $Qimages->appendQuery('and atoc.categories_id = :categories_id ');
            $Qimages->bindInt(':categories_id', $current_category_id);
        }

        if (!empty($_REQUEST['search'])) {
            $Qimages->appendQuery('and s.description like :description');
            $Qimages->bindValue(':description', '%' . $_REQUEST['search'] . '%');
        }

        $Qimages->appendQuery('order by s.sort_order ');
        $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
        $Qimages->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
        $Qimages->bindInt(':language_id', $osC_Language->getID());
        $Qimages->setExtBatchLimit($start, $limit);
        $Qimages->execute();

        $records = array();
        while ($Qimages->next()) {
            $image = '';
            if (file_exists('../images/images_gallery/mini/' . $Qimages->value('image'))) {
                list($orig_width, $orig_height) = getimagesize('../images/images_gallery/mini/' . $Qimages->value('image'));
                $width = intval($orig_width * 80 / $orig_height);

                $image = '<img src="../images/images_gallery/mini/' . $Qimages->value('image') . '" width="' . $width . '" height="80" />';
            }

            $records[] = array('image_id' => $Qimages->valueInt('image_id'),
                               'image' => $image,
                               'image_url' => $Qimages->value('image_url'),
                               'sort_order' => $Qimages->value('sort_order'),
                               'status' => $Qimages->value('status'));
        }
        $Qimages->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $Qimages->getBatchSize(),
                          EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listContentImages()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        if(empty($_REQUEST['content_id']) || empty($_REQUEST['content_type']))
        {
            $records = array();
            $response = array(EXT_JSON_READER_TOTAL => 0,
                              EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
            return;
        }

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $content_id = $_REQUEST['content_id'];

        $Qimages = $osC_Database->query('select s.*  from :table_slide_images s where s.language_id = :language_id and s.image_id in (select image_id from :table_content_images where content_id = :content_id and content_type = :content_type)');

        if (!empty($_REQUEST['search'])) {
            $Qimages->appendQuery('and s.description like :description');
            $Qimages->bindValue(':description', '%' . $_REQUEST['search'] . '%');
        }

        $Qimages->appendQuery('order by s.sort_order ');
        $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
        $Qimages->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
        $Qimages->bindInt(':language_id', $osC_Language->getID());
        $Qimages->bindInt(':content_id', $content_id);
        $Qimages->bindValue(':content_type',$_REQUEST['content_type']);
        $Qimages->setExtBatchLimit($start, $limit);
        $Qimages->execute();

        while ($Qimages->next()) {
            $image = '';
            if (file_exists('../images/' . $Qimages->value('image'))) {
                list($orig_width, $orig_height) = getimagesize('../images/' . $Qimages->value('image'));
                $width = intval($orig_width * 80 / $orig_height);

                $image = '<img src="../images/' . $Qimages->value('image') . '" width="' . $width . '" height="80" />';
            }

            $records[] = array('image_id' => $Qimages->valueInt('image_id'),
                               'image' => $image,
                               'image_url' => $Qimages->value('image_url'),
                               'sort_order' => $Qimages->value('sort_order'),
                               'status' => $Qimages->value('status'));
        }
        $Qimages->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $Qimages->getBatchSize(),
                          EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function setStatus()
    {
        global $toC_Json, $osC_Language;

        if (toC_Slide_Images_Admin::setStatus($_REQUEST['image_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag']
                    : null))
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function deleteSlideImage()
    {
        global $toC_Json, $osC_Language;

        if (toC_Slide_Images_Admin::delete($_REQUEST['image_id'])) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function deleteSlideImages()
    {
        global $toC_Json, $osC_Language;

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $id) {
            if (!toC_Slide_Images_Admin::delete($id)) {
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

    function loadSlideImages()
    {
        global $toC_Json, $osC_Database;

        $data = toC_Slide_Images_Admin::getData($_REQUEST['image_id']);

        $Qimage = $osC_Database->query('select * from :table_slide_images where image_id = :image_id');
        $Qimage->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
        $Qimage->bindInt(':image_id', $_REQUEST['image_id']);
        $Qimage->execute();

        while ($Qimage->next()) {
            list($orig_width, $orig_height) = getimagesize('../images/images_gallery/mini/' . $Qimage->value('image'));
            $width = intval($orig_width * 80 / $orig_height);

            $image = '<img src="../images/images_gallery/mini/' . $Qimage->value('image') . '" width="' . $width . '" height="80" style="margin-left: 112px" />';

            $data['description[' . $Qimage->valueInt('language_id') . ']'] = $Qimage->value('description');
            $data['image_url[' . $Qimage->valueInt('language_id') . ']'] = $Qimage->value('image_url');
            $data['slide_image' . $Qimage->valueInt('language_id')] = $image;
        }

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function saveSlideImages()
    {
        global $toC_Json, $osC_Language,$osC_Image;

        $osC_Image = new osC_Image_Admin();

        header('Content-Type: text/html');

        $data = array('status' => $_REQUEST['status'],
                      'image_url' => $_REQUEST['image_url'],
                      'description' => $_REQUEST['description'],
                      'sort_order' => $_REQUEST['sort_order'],
                      'categories_id' => $_REQUEST['categories_id']
        );

        $error = false;
        $feedback = array();
        if (!isset($_REQUEST['image_id'])) {
            foreach ($osC_Language->getAll() as $l) {
                if (empty($_FILES['image' . $l['id']]['name'])) {
                    $error = true;
                    $feedback[] = sprintf($osC_Language->get('ms_error_image_empty'), $l['name']);
                }
            }
        }

        if (isset($_REQUEST[categories_id])) {
            $data['categories'] = explode(',', $_REQUEST[categories_id]);
        }

        if ($error === false) {
            if (toC_Slide_Images_Admin::save((isset($_REQUEST['image_id']) ? $_REQUEST['image_id'] : null), $data)) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br>' . implode('<br>', $feedback));
        }

        echo $toC_Json->encode($response);
    }

    function saveContentImage()
    {
        global $toC_Json, $osC_Language;

        header('Content-Type: text/html');

        $data = array('status' => $_REQUEST['status'],
                      'image_url' => $_REQUEST['image_url'],
                      'description' => $_REQUEST['description'],
                      'sort_order' => $_REQUEST['sort_order'],
                      'content_id' => $_REQUEST['content_id'],
                      'content_type' => $_REQUEST['content_type'],
        );

        $error = false;
        $feedback = array();
        if (!isset($_REQUEST['image_id'])) {
            foreach ($osC_Language->getAll() as $l) {
                if (empty($_FILES['image' . $l['id']]['name'])) {
                    $error = true;
                    $feedback[] = sprintf($osC_Language->get('ms_error_image_empty'), $l['name']);
                }
            }
        }

        if (isset($_REQUEST[categories_id])) {
            $data['categories'] = explode(',', $_REQUEST[categories_id]);
        }

        if ($error === false) {
            if (toC_Slide_Images_Admin::save((isset($_REQUEST['image_id']) ? $_REQUEST['image_id'] : null), $data)) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br>' . implode('<br>', $feedback));
        }

        echo $toC_Json->encode($response);
    }
}

?>
