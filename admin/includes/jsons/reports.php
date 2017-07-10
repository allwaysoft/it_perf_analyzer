<?php
/*
  $Id: reports.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
require('includes/classes/reports.php');
require('includes/classes/email_account.php');
require('includes/classes/email_accounts.php');
require('includes/classes/servers.php');

class toC_Json_Reports
{
    function listReports()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $Qreports = $osC_Database->query('select a.*, cd.*,c.*, atoc.*  from :table_reports a left join :table_content c on a.reports_id = c.content_id left join  :table_content_description cd on a.reports_id = cd.content_id left join :table_content_to_categories atoc on atoc.content_id = a.reports_id  where cd.language_id = :language_id and atoc.content_type = "reports" and c.content_type = "reports" AND cd.content_type = "reports"');

//        if ($current_category_id != 0) {
//            $Qreports->appendQuery('and atoc.categories_id = :categories_id ');
//            $Qreports->bindInt(':categories_id', $current_category_id);
//        }
        $Qreports->appendQuery('and atoc.categories_id = :categories_id ');
        $Qreports->bindInt(':categories_id', $current_category_id);

        if (!empty($_REQUEST['search'])) {
            $Qreports->appendQuery('and cd.content_name like :content_name');
            $Qreports->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        $Qreports->appendQuery('order by cd.content_description ');
        $Qreports->bindTable(':table_reports', TABLE_REPORTS);
        $Qreports->bindTable(':table_content', TABLE_CONTENT);
        $Qreports->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
        $Qreports->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
        $Qreports->bindInt(':language_id', $osC_Language->getID());
        $Qreports->setExtBatchLimit($start, $limit);
        $Qreports->execute();

        $records = array();
        while ($Qreports->next()) {
            if(isset($_REQUEST['permissions']))
            {
                $permissions = explode(',',$_REQUEST['permissions']);
                $records[] = array('reports_id' => $Qreports->ValueInt('reports_id'),
                                   'content_status' => $Qreports->ValueInt('content_status'),
                                   'content_order' => $Qreports->Value('content_order'),
                                   'content_name' => $Qreports->Value('content_name'),
                                   'created_by' => $Qreports->Value('created_by'),
                                   'content_description' => $Qreports->Value('content_description'),
                                   'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[0] != 'undefined' ? $permissions[0] : false,
                                   'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[1] != 'undefined' ? $permissions[1] : false,
                                   'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : $permissions[2] != 'undefined' ? $permissions[2] : false,
                                   'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[3] != 'undefined' ? $permissions[3] : false
                );
            }
            else
            {
                $records[] = array('reports_id' => $Qreports->ValueInt('reports_id'),
                                   'content_status' => $Qreports->ValueInt('content_status'),
                                   'content_order' => $Qreports->Value('content_order'),
                                   'content_name' => $Qreports->Value('content_name'),
                                   'created_by' => $Qreports->Value('created_by'),
                                   'content_description' => $Qreports->Value('content_description'),
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

    function listSubscriptions()
    {
        global $toC_Json, $osC_Database;

        $Qreports = $osC_Database->query("SELECT subscriptions_id,reports_id,job_id,owner,parameters,subscribers,format,CONCAT('CRON : ',schedule,';','Debut : ',date_debut,';','Fin : ',date_fin) schedule from :table_reports_subscriptions where reports_id = :reports_id");

        $Qreports->bindTable(':table_reports_subscriptions', TABLE_REPORTS_SUBSCRIPTIONS);
        $Qreports->bindInt(':reports_id', $_REQUEST['reports_id']);
        $Qreports->execute();

        $records = array();
        while ($Qreports->next()) {
            $records[] = array('subscriptions_id' => $Qreports->ValueInt('subscriptions_id'),
                'reports_name' => $_REQUEST['reports_name'],
                'schedule' => $Qreports->Value('schedule'),
                'owner' => $Qreports->Value('owner'),
                'job_id' => $Qreports->ValueInt('job_id'),
                'parameters' => $Qreports->Value('parameters'),
                'subscribers' => $Qreports->Value('subscribers'),
                'format' => $Qreports->Value('format')
//            ,'details' => toC_Reports_Admin::detailJob($Qreports->ValueInt('job_id'))
            );
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
            $records = array(array('id' => '', 'text' => $osC_Language->get('top_reports_category')));
        }

        foreach ($article_categories as $category) {
            if ($category['reports_categories_id'] != '1') {
                $records[] = array('id' => $category['reports_categories_id'],
                                   'text' => $category['reports_categories_name']);
            }
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadReport()
    {
        global $toC_Json;

        $data = toC_Reports_Admin::getData($_REQUEST['reports_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function loadReportParameters()
    {
        global $toC_Json;

        $data = array('reports_id' => $_REQUEST['reports_id'],
            'owner' => $_REQUEST['owner']);

        $params = toC_Reports_Admin::getParameters($data);

        //$response = array('success' => true, 'params' => $params);

        echo $toC_Json->encode($params);
    }

    function saveReport()
    {
        global $toC_Json, $osC_Language;

        $data = array('content_name' => $_REQUEST['content_name'],
                      'content_url' => '',
                      'created_by' => $_SESSION[admin][username],
                      'modified_by' => $_SESSION[admin][username],
                      'content_description' => $_REQUEST['content_description'],
                      'owner' => $_REQUEST['owner'],
                      'content_order' => 0,
                      'content_status' => $_REQUEST['content_status'],
                      'page_title' => $_REQUEST['content_name'],
                      'meta_keywords' => $_REQUEST['content_name'],
                      'meta_descriptions' => $_REQUEST['content_description']);

        if (isset($_REQUEST[content_categories_id])) {
            $data['categories'] = explode(',', $_REQUEST[content_categories_id]);
        }

        if (toC_Reports_Admin::save((isset($_REQUEST['reports_id']) && ($_REQUEST['reports_id'] != -1)
                    ? $_REQUEST['reports_id'] : null), $data)
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function runReport()
    {
        global $toC_Json;
        $report = toC_Reports_Admin::run($_REQUEST);

        //$response = array('success' => true, 'request' => $toC_Json->decode($report));
        echo $toC_Json->encode($report);
    }

    function sendMsg()
    {
        global $toC_Json;
        $report = toC_Reports_Admin::sendEmail($_REQUEST);

        //$response = array('success' => true, 'request' => $toC_Json->decode($report));
        echo $toC_Json->encode($report);
    }

    function sendMsgattach()
    {
        var_dump($_REQUEST);
        global $toC_Json;
        $report = toC_Reports_Admin::sendEmailAttach($_REQUEST);

        //$response = array('success' => true, 'request' => $toC_Json->decode($report));
        echo $toC_Json->encode($report);
    }

    function sendFile()
    {
        //var_dump($_REQUEST);
        global $toC_Json;
        $report = toC_Reports_Admin::sendFileEmail($_REQUEST);

        //$response = array('success' => true, 'request' => $toC_Json->decode($report));
        echo $toC_Json->encode($report);
    }

    function sendReketor()
    {
        global $toC_Json;
        $report = toC_Reports_Admin::sendReketor($_REQUEST);

        //$response = array('success' => true, 'request' => $toC_Json->decode($report));
        echo $toC_Json->encode($report);
    }

    function log() {
        global $toC_Json;

        $data = $_REQUEST;

        $error = toC_Reports_Admin::addJobDetail($data);

        if ( $error == true ) {
            $response = array('success' => false, 'feedback' => 'NOK');
        }

        if ($error == false) {
            $response = array('success' => true, 'feedback' => 'OK');
        }

        echo $toC_Json->encode($response);
    }

    function runReportJob()
    {
        global $toC_Json;

        if(isset($_REQUEST['query']) && !empty($_REQUEST['query']))
        {
            $job = array('owner' => $_REQUEST['owner'], 'reports_id' => $_REQUEST['reports_id'],'format' => $_REQUEST['format'],'query' => $_REQUEST['query'],'to' => $_REQUEST['to'],'cc' => $_REQUEST['cc'],'bcc' => $_REQUEST['bcc'],'subject' => $_REQUEST['subject'],'body' => $_REQUEST['body'],'priority' => $_REQUEST['priority']);
        }
        else
        {
            $job = toC_Reports_Admin::getJob($_REQUEST['subscriptions_id']);
        }

        //fb($job, 'report_job', FirePHP::INFO);

        //echo $toC_Json->encode($job);echo $toC_Json->encode($job);
        $report = toC_Reports_Admin::runJob($job);

        //fb($report, 'report', FirePHP::INFO);

        //$response = array('success' => true, 'request' => $toC_Json->decode($report));
        echo $toC_Json->encode($report);
    }

    function scheduleReport()
    {
        global $toC_Json;
        //fb($_REQUEST, '$_REQUEST', FirePHP::INFO);

        $state = 'ENABLED';

        if(isset($_REQUEST['state']) && !empty($_REQUEST['state']))
        {
            $state = $_REQUEST['state'];
        }

        $response = toC_Reports_Admin::schedule($_REQUEST,$state);

        //header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function downloadReport()
    {
        global $toC_Json;
//        $report = toC_Reports_Admin::download($_REQUEST);
//
//        $dir = realpath(DIR_WS_REPORTS) . '/';
//        if (!file_exists($dir)) {
//            mkdir($dir, 0777, true);
//        }
//
//        //$file_name = $dir . '/' . $_REQUEST['id'] . '.' . $_REQUEST['format'];
//        $file_name = $dir . '/' . $_REQUEST['comments'];
//        //file_put_contents($file_name,$report);

        if(isset($_REQUEST['subscriptions_id']) && !empty($_REQUEST['subscriptions_id']))
        {
            $job = toC_Reports_Admin::getJob($_REQUEST['subscriptions_id']);

            toC_Reports_Admin::deleteSubscription($_REQUEST['subscriptions_id'],$job['job_id']);
        }

        //$response = array('success' => true, 'file_name' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $_REQUEST['comments']);
        $response = array('success' => true, 'file_name' => HTTP_SERVER . '/' . DIR_REPORT_HTTP_CATALOG . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $_REQUEST['comments']);
        echo $toC_Json->encode($response);
    }

    function startReport()
    {
        global $toC_Json;
        if(isset($_REQUEST['subscriptions_id']) && !empty($_REQUEST['subscriptions_id']))
        {
            $job = toC_Reports_Admin::getJob($_REQUEST['subscriptions_id']);

            $ssh = new Net_SSH2(REPORT_RUNNER,'22');

            if (empty($ssh->server_identifier)) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme','subscriptions_id' => null,'status' =>null);
            } else {
                if (!$ssh->login("guyfomi", "12345")) {
                    $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide','subscriptions_id' => null,'status' =>null);
                } else {
                    $ssh->disableQuietMode();

                    $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=reports&action=run_reportJob&subscriptions_id=" . $_REQUEST['subscriptions_id'] . "' &";
                    //$cmd = "nohup curl 'http://10.100.120.32/dev/admin/json.php' --data 'module=reports&action=run_reportJob&subscriptions_id=" . $_REQUEST['subscriptions_id'] . "&owner=" . $job['owner'] . "&reports_id=" . $job['reports_id'] . "&format=" . $job['format'] . "&query=" . $job['query'] . "&to=" . $job['to'] . "&cc=" . $job['cc'] . "&bcc=" . $job['bcc'] . "' &";
                    //fb($cmd, 'curl', FirePHP::INFO);
                    $ssh->exec($cmd);
                    $ssh->disconnect();
                    $response = array('success' => true, 'msg' => '1','subscriptions_id' => $_REQUEST['subscriptions_id'],'status' => 'starting','comments' => 'Execution du job');
                }
            }

            echo $toC_Json->encode($response);
        }
        else
        {
            $response = array('success' => false, 'msg' => 'Souscription non definie, veuillez creer un job','subscriptions_id' => null,'status' => 'error');

            echo $toC_Json->encode($response);
        }
    }

    function Statusjob()
    {
        global $toC_Json;

        global $toC_Json;

        if(isset($_REQUEST['subscriptions_id']) && !empty($_REQUEST['subscriptions_id']))
        {
            //$report = toC_Reports_Admin::status($_REQUEST);
            sleep(1);
            $report = toC_Reports_Admin::getJobRunDetail($_REQUEST['subscriptions_id']);
            fb($report, 'Statusreport', FirePHP::INFO);

            $response = array('success' => true, 'msg' => '1','subscriptions_id' => $_REQUEST['subscriptions_id'],'status' => $report['STATUS'],'comments' => $report['comments'],'requestId' => $_REQUEST['requestId']);
        }
        else
        {
            $response = array('success' => false, 'msg' => 'Souscription non definie, veuillez creer un job','subscriptions_id' => null,'status' => 'error');
        }

        echo $toC_Json->encode($response);
    }

    function Statusreport()
    {
        global $toC_Json;

        if(isset($_REQUEST['subscriptions_id']) && !empty($_REQUEST['subscriptions_id']))
        {
            //$report = toC_Reports_Admin::status($_REQUEST);
            sleep(1);
            $report = toC_Reports_Admin::getJobRunDetail($_REQUEST['subscriptions_id']);
            //fb($report, 'Statusreport', FirePHP::INFO);

            $response = array('success' => true, 'msg' => '1','subscriptions_id' => $_REQUEST['subscriptions_id'],'status' => $report['STATUS'],'comments' => $report['comments'],'requestId' => $_REQUEST['requestId']);
        }
        else
        {
            $response = array('success' => false, 'msg' => 'Souscription non definie, veuillez creer un job','subscriptions_id' => null,'status' => 'error');
        }

        echo $toC_Json->encode($response);
    }

    function deleteReport()
    {
        if(isset($_REQUEST['reports_id']) && !empty($_REQUEST['reports_id']))
        {
            global $toC_Json, $osC_Language;

            if (toC_Reports_Admin::delete($_REQUEST['reports_id'],$_REQUEST['owner'])) {
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

    function deleteSubscription()
    {
        if(isset($_REQUEST['subscriptions_id']) && !empty($_REQUEST['subscriptions_id']))
        {
            global $toC_Json, $osC_Language;

            if (toC_Reports_Admin::deleteSubscription($_REQUEST['subscriptions_id'],$_REQUEST['job_id'])) {
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

    function deleteReports()
    {
        global $toC_Json, $osC_Language, $osC_Image;

        $osC_Image = new osC_Image_Admin();

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $reports_id) {
            if (!toC_Articles_Admin::delete($reports_id)) {
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

        if (isset($_REQUEST['reports_id']) && content::setStatus($_REQUEST['reports_id'], (isset($_REQUEST['flag'])
                    ? $_REQUEST['flag'] : null),'reports')
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function subscribeReport()
    {
        global $toC_Json, $osC_Language;

        if (toC_Reports_Admin::subscribeReport($_REQUEST['subscriptions_id'],$_REQUEST['to'])
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
        }

        echo $toC_Json->encode($response);
    }
}

?>