<?php
/*
  $Id: new_articles.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Content_comments extends osC_Modules
    {
        var $_title,
        $_code = 'comments',
        $_author_name = 'Mefobe',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

/* Class constructor */

        function osC_Content_comments()
        {
            $this->_title = 'Commentaires';
        }

        function initialize()
        {
            global $comments, $osC_Database;
            
            $content_id = isset($_GET['articles_id']) && !empty($_GET['articles_id']) ? $_GET['articles_id'] : -1;
            $content_type = isset($_GET['articles_id']) && !empty($_GET['articles_id']) ? 'articles' : '';

            $content_id = isset($_GET['events_id']) && !empty($_GET['events_id']) ? $_GET['events_id'] : $content_id;
            $content_type = isset($_GET['events_id']) && !empty($_GET['events_id']) ? 'events' : $content_type;

            $content_id = isset($_GET['news_id']) && !empty($_GET['news_id']) ? $_GET['news_id'] : $content_id;
            $content_type = isset($_GET['news_id']) && !empty($_GET['news_id']) ? 'news' : $content_type;

            $pieces = explode("index.php", $_SERVER['SCRIPT_NAME']);

            $Qcomments = $osC_Database->query("select c.*,u.image_url,a.user_name from :table_content_comments c left join :table_users u on (c.created_by = u.administrators_id) left join :table_administrators a on (c.created_by = a.id) where c.content_id = :content_id and c.content_type = :content_type order by c.comments_date_added desc");

            $Qcomments->bindInt(':content_id', $content_id);
            $Qcomments->bindValue(':content_type', $content_type);
            $Qcomments->bindTable(':table_content_comments', TABLE_CONTENT_COMMENTS);
            $Qcomments->bindTable(':table_users', TABLE_USERS);
            $Qcomments->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qcomments->execute();

            while ($Qcomments->next()) {
                $image = '<img src="images/users/mini/' . $Qcomments->value('image_url') . '" width="100" height="80" />';

                $comments[] = array('comments_id' => $Qcomments->valueInt('comments_id'),
                                   'image_url' => $image,
                                   'comments_description' => $Qcomments->value('comments_description'),
                                   'user_name' => $Qcomments->value('user_name'),
                                   'comments_date_added' => $Qcomments->value('comments_date_added'),
                                   'comments_status' => $Qcomments->value('comments_status'));
            }

            $Qcomments->freeResult();

            $i = 1;
            if (count($comments) > 0) {
                $this->_content = '<h6 id ="comments-title" >' . count($comments) . ' Commentaires</h6>';
                $this->_content .= '<ol id ="comments_list" style="padding-left: 25px;">';
                foreach ($comments as $comment) {
                    $this->_content .= '<li id="li-comment-' . $i . '" class="comment even thread-even depth-1">';
                    $this->_content .= '<div id="comment-' . $i . '">';
                    $this->_content .= '<div class="comment-author vcard">';
                    //$this->_content .= $comment['image_url'];
                    $this->_content .= '<cite class="fn"><a style="font-size: x-large;" rel="external nofollow" href="#">' . $comment['user_name'] . '</a></cite></div>';
                    $this->_content .= '<div class="comment-meta commentmetadata"><a href="">' . $comment['comments_date_added'] . '</a></div>';
                    $this->_content .= '<div class="comment-body"><p>' . $comment['comments_description'] . '</p>';
                    $this->_content .= '</li>';
                    $i++;
                }

                $this->_content .= '</ol>';
            }
        }

        function install()
        {
            parent::install();
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array();
            }

            return $this->_keys;
        }
    }

?>
