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

    if (!class_exists('content')) {
        include('includes/classes/content.php');
    }
class toC_Articles_Admin
{
    function getData($id)
    {
        global $osC_Database, $osC_Language;

        $Qarticles = $osC_Database->query('select a.*, c.*  from :table_articles a left join :table_content c on c.content_id = a.articles_id  where a.articles_id = :articles_id and c.content_type = "articles"');

        $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
        $Qarticles->bindTable(':table_content', TABLE_CONTENT);
        $Qarticles->bindInt(':articles_id', $id);
        $Qarticles->execute();

        $data = $Qarticles->toArray();

        $Qarticles->freeResult();

        $description = content::getContentDescription($id, 'articles');
        $data = array_merge($data, $description);

        $product_categories_array = content::getContentCategories($id, 'articles');
        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

    function save($id = null, $data)
    {
        global $osC_Database, $osC_Image;

        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qarticle = $osC_Database->query('update :table_articles set articles_intro = :articles_intro where articles_id = :articles_id');
            $Qarticle->bindInt(':articles_id', $id);
        } else {
            $Qarticle = $osC_Database->query('insert into :table_articles (articles_intro) values (:articles_intro)');
        }

        $Qarticle->bindTable(':table_articles', TABLE_ARTICLES);
        $Qarticle->bindValue(':articles_intro', $data['articles_intro']);
        $Qarticle->setLogging($_SESSION['module'], $id);
        $Qarticle->execute();

        if ($osC_Database->isError()) {
            $error = true;
        } else {
            if (is_numeric($id)) {
                $articles_id = $id;
            } else {
                $articles_id = $osC_Database->nextID();
            }
        }

        //articles images
        if ($data['delimage'] == 1) {
            $osC_Image->deleteArticlesImage($articles_id);

            $Qdelete = $osC_Database->query('update :table_articles set articles_image = NULL where articles_id = :articles_id');
            $Qdelete->bindTable(':table_articles', TABLE_ARTICLES);
            $Qdelete->bindInt(':articles_id', $id);
            $Qdelete->setLogging($_SESSION['module'], $id);
            $Qdelete->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }
        }

        if ($error === false) {
            $articles_image = new upload('articles_image', realpath('../' . DIR_WS_IMAGES . '/articles/originals'));
            if ($articles_image->exists() && $articles_image->parse() && $articles_image->save()) {
                $Qarticle = $osC_Database->query('update :table_articles set articles_image = :articles_image where articles_id = :articles_id');
                $Qarticle->bindTable(':table_articles', TABLE_ARTICLES);
                $Qarticle->bindValue(':articles_image', $articles_image->filename);
                $Qarticle->bindInt(':articles_id', $articles_id);
                $Qarticle->setLogging($_SESSION['module'], $articles_id);
                $Qarticle->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                } else {
                    foreach ($osC_Image->getGroups() as $group) {
                        if ($group['id'] != '1') {
                            $osC_Image->resize($articles_image->filename, $group['id'], 'articles');
                        }
                    }
                }

            }
        }

        //content
        if ($error === false) {
            $error = !content::saveContent($id, $articles_id, 'articles', $data);
        }

        //Process Languages
        if ($error === false) {
            $error = !content::saveContentDescription($id, $articles_id, 'articles', $data);
        }

        //content_to_categories
        if ($error === false) {
            $error = !content::saveContentToCategories($id, $articles_id, 'articles', $data);
        }

        //images
        if ($error === false) {
            $error = !content::saveImages($articles_id, 'articles');
        }
        
        if ($error === false) {
            $osC_Database->commitTransaction();
            osC_Cache::clear('sefu-articles');
            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }


    function delete($id)
    {
        global $osC_Database, $osC_Image;
        $error = false;

        $osC_Database->startTransaction();

        $osC_Image->deleteArticlesImage($id);

        $error = !content::deleteContent($id,'articles');

        if ($error === false) {
            $Qarticles = $osC_Database->query('delete from :table_articles where articles_id = :articles_id');
            $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
            $Qarticles->bindInt(':articles_id', $id);
            $Qarticles->setLogging($_SESSION['module'], $id);
            $Qarticles->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }
        }

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $osC_Database->commitTransaction();
        osC_Cache::clear('sefu-articles');
        return true;
    }
}

?>
