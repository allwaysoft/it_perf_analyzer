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
require('includes/classes/articles.php');
require('includes/classes/articles_categories.php');
require('includes/classes/image.php');

class toC_Json_Articles
{
    function listArticles()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $Qarticles = $osC_Database->query('select a.*, cd.*,c.*, atoc.*  from :table_articles a left join :table_content c on a.articles_id = c.content_id left join  :table_content_description cd on a.articles_id = cd.content_id left join :table_content_to_categories atoc on atoc.content_id = a.articles_id  where cd.language_id = :language_id and atoc.content_type = "articles" and c.content_type = "articles"');

        if ($current_category_id != 0) {
            $Qarticles->appendQuery('and atoc.categories_id = :categories_id ');
            $Qarticles->bindInt(':categories_id', $current_category_id);
        }

        if (!empty($_REQUEST['search'])) {
            $Qarticles->appendQuery('and cd.content_name like :content_name');
            $Qarticles->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        $Qarticles->appendQuery('order by cd.content_name ');
        $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
        $Qarticles->bindTable(':table_content', TABLE_CONTENT);
        $Qarticles->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
        $Qarticles->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
        $Qarticles->bindInt(':language_id', $osC_Language->getID());
        $Qarticles->setExtBatchLimit($start, $limit);
        $Qarticles->execute();

        $records = array();
        while ($Qarticles->next()) {
            if(isset($_REQUEST['permissions']))
            {
                $permissions = explode(',',$_REQUEST['permissions']);
                $records[] = array('articles_id' => $Qarticles->ValueInt('articles_id'),
                                   'content_status' => $Qarticles->ValueInt('content_status'),
                                   'content_order' => $Qarticles->Value('content_order'),
                                   'content_name' => $Qarticles->Value('content_name'),
                                   'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[0],
                                   'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[1],
                                   'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : $permissions[2],
                                   'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[3]
                );
            }
            else
            {
                $records[] = array('articles_id' => $Qarticles->ValueInt('articles_id'),
                                   'content_status' => $Qarticles->ValueInt('content_status'),
                                   'content_order' => $Qarticles->Value('content_order'),
                                   'content_name' => $Qarticles->Value('content_name'),
                                   'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                                   'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                                   'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                                   'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                );
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
                          EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function getArticlesCategories()
    {
        global $toC_Json, $osC_Language;

        $article_categories = toC_Articles_Categories_Admin::getArticlesCategories();

        $records = array();
        if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
            $records = array(array('id' => '', 'text' => $osC_Language->get('top_articles_category')));
        }

        foreach ($article_categories as $category) {
            if ($category['articles_categories_id'] != '1') {
                $records[] = array('id' => $category['articles_categories_id'],
                                   'text' => $category['articles_categories_name']);
            }
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadArticle()
    {
        global $toC_Json;

        $data = toC_Articles_Admin::getData($_REQUEST['articles_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function saveArticle()
    {
        global $toC_Json, $osC_Language, $osC_Image;

        $osC_Image = new osC_Image_Admin();

        //search engine friendly urls
        $formatted_urls = array();
        $urls = $_REQUEST['content_url'];
        if (is_array($urls) && !empty($urls)) {
            foreach ($urls as $languages_id => $url) {
                $url = toc_format_friendly_url($url);
                if (empty($url)) {
                    $url = toc_format_friendly_url($_REQUEST['content_name'][$languages_id]);
                }

                $formatted_urls[$languages_id] = $url;
            }
        }

        $data = array('content_name' => $_REQUEST['content_name'],
                      'articles_intro' => $_REQUEST['articles_intro'],
                      'content_url' => $formatted_urls,
                      'created_by' => $_SESSION[admin][id],
                      'modified_by' => $_SESSION[admin][id],
                      'content_description' => $_REQUEST['content_description'],
                      'content_order' => $_REQUEST['content_order'],
                      'content_status' => $_REQUEST['content_status'],
                      'delimage' => (isset($_REQUEST['delimage']) && ($_REQUEST['delimage'] == 'on') ? '1' : '0'),
                      'page_title' => $_REQUEST['page_title'],
                      'meta_keywords' => $_REQUEST['meta_keywords'],
                      'meta_descriptions' => $_REQUEST['meta_descriptions']);

        if (isset($_REQUEST[content_categories_id])) {
            $data['categories'] = explode(',', $_REQUEST[content_categories_id]);
        }

        if (toC_Articles_Admin::save((isset($_REQUEST['articles_id']) && ($_REQUEST['articles_id'] != -1)
                    ? $_REQUEST['articles_id'] : null), $data)
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function deleteArticle()
    {
        if(isset($_REQUEST['articles_id']) && !empty($_REQUEST['articles_id']))
        {
            global $toC_Json, $osC_Language, $osC_Image;

            $osC_Image = new osC_Image_Admin();

            if (toC_Articles_Admin::delete($_REQUEST['articles_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }
        }
        else
        {
            $response = array('success' => false, 'feedback' => 'Veuillez selectionner l\'article que vous voulez supprimer');
        }

        echo $toC_Json->encode($response);
    }

    function deleteArticles()
    {
        global $toC_Json, $osC_Language, $osC_Database, $osC_Image;

        $osC_Image = new osC_Image_Admin();

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $articles_id) {
            if (!toC_Articles_Admin::delete($articles_id)) {
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

        if (isset($_REQUEST['articles_id']) && content::setStatus($_REQUEST['articles_id'], (isset($_REQUEST['flag'])
                    ? $_REQUEST['flag'] : null),'articles')
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }
}

?>