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

use Jasper\JasperReportUnit;
use Jasper\JasperSimpleXml;

if (!class_exists('content')) {
    include('includes/classes/content.php');
}

require_once('includes/modules/jasperclient/Jasper.php');

class toC_Reports_Admin
{
    function getData($id)
    {
        global $osC_Database;

        $Qreports = $osC_Database->query('select a.*, c.*  from :table_reports a left join :table_content c on c.content_id = a.reports_id  where a.reports_id = :reports_id and c.content_type = "reports"');

        $Qreports->bindTable(':table_reports', TABLE_REPORTS);
        $Qreports->bindTable(':table_content', TABLE_CONTENT);
        $Qreports->bindInt(':reports_id', $id);
        $Qreports->execute();

        $data = $Qreports->toArray();

        $Qreports->freeResult();

        $description = content::getContentDescription($id, 'reports');
        $data = array_merge($data, $description);

        $product_categories_array = content::getContentCategories($id, 'reports');
        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

    function getDashboard($id)
    {
        global $osC_Database;

        $Qreports = $osC_Database->query('select a.*, c.*  from :table_dashboards a left join :table_content c on c.content_id = a.dashboards_id  where a.dashboards_id = :dashboards_id and c.content_type = "dashboards"');

        $Qreports->bindTable(':table_dashboards', TABLE_DASHBOARS);
        $Qreports->bindTable(':table_content', TABLE_CONTENT);
        $Qreports->bindInt(':dashboards_id', $id);
        $Qreports->execute();

        $data = $Qreports->toArray();

        $Qreports->freeResult();

        $description = content::getContentDescription($id, 'dashboards');
        $data = array_merge($data, $description);

        $product_categories_array = content::getContentCategories($id, 'dashboards');
        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

    function getJobRunDetail($id)
    {
        global $osC_Database;

        $query = "SELECT * FROM :table_job_run_details where subscriptions_id = :subscriptions_id ORDER BY LOG_ID DESC LIMIT 0, 1";
        $Qreports = $osC_Database->query($query);

        $Qreports->bindTable(':table_job_run_details',TABLE_JOB_RUN_DETAILS);
        $Qreports->bindInt(':subscriptions_id', $id);
        $Qreports->execute();

        $data = $Qreports->toArray();

        $Qreports->freeResult();

        return $data;
    }

    function addJobRunDetail($data) {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();

        $query = "INSERT INTO :table_job_run_details (LOG_DATE,STATUS,comments,subscriptions_id,execution_id) VALUES (SYSDATE(),:status,:comments,:subscriptions_id,:execution_id)";

        $Qinsert = $osC_Database->query($query);
        $Qinsert->bindInt(':subscriptions_id', $data['subscriptions_id']);
        $Qinsert->bindValue(':status',$data['status']);
        $Qinsert->bindValue(':comments',$data['comments']);
        $Qinsert->bindValue(':execution_id',$data['execution_id']);
        $Qinsert->bindTable(':table_job_run_details',TABLE_JOB_RUN_DETAILS);
        $Qinsert->execute();

        if ( $osC_Database->isError() ) {
            $error = true;
        }

        if ($error == false) {
            $osC_Database->commitTransaction();

            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function addJobDetail($data) {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();

        $query = "INSERT INTO :table_logs (task_id,status,comment) VALUES (:task_id,:status,:comment)";

        $Qinsert = $osC_Database->query($query);
        $Qinsert->bindValue(':task_id',$data['task_id']);
        $Qinsert->bindValue(':status',$data['status']);
        $Qinsert->bindValue(':comment',$data['comments']);
        $Qinsert->bindTable(':table_logs',TABLE_LOGS);
        $Qinsert->execute();

        if ( $osC_Database->isError() ) {
            $error = true;
        }

        if ($error == false) {
            $osC_Database->commitTransaction();

            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function addReportExecution($subscriptions_id) {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();

        $query = "INSERT INTO :table_reports_executions (subscriptions_id) VALUES (:subscriptions_id);";

        $Qinsert = $osC_Database->query($query);
        $Qinsert->bindInt(':subscriptions_id', $subscriptions_id);
        $Qinsert->bindTable(':table_reports_executions',TABLE_REPORTS_EXECUTIONS);
        $Qinsert->execute();

        if ( $osC_Database->isError() ) {
            $error = true;
        }

        if ($error == false) {
            $exec_id = $osC_Database->nextID();
            $osC_Database->commitTransaction();

            return $exec_id;
        }

        $osC_Database->rollbackTransaction();

        return -1;
    }

    function getJob($id)
    {
        global $osC_Database;

        $Qarticles = $osC_Database->query('select a.*, c.*  from :table_reports_subscriptions a left join :table_content c on c.content_id = a.subscriptions_id  where a.subscriptions_id = :subscriptions_id and c.content_type = "jobs"');

        $Qarticles->bindTable(':table_reports_subscriptions', TABLE_REPORTS_SUBSCRIPTIONS);
        $Qarticles->bindTable(':table_content', TABLE_CONTENT);
        $Qarticles->bindInt(':subscriptions_id', $id);
        $Qarticles->execute();

        $data = $Qarticles->toArray();

        $Qarticles->freeResult();

        $description = content::getContentDescription($id, 'jobs');
        $data = array_merge($data, $description);

        $product_categories_array = content::getContentCategories($id, 'jobs');
        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

    function getParameters($data)
    {
        $jasper = new Jasper\Jasper(REPORT_SERVER, REPORT_USER, REPORT_PASS);

        $customerSpace = '/reports/' . $data['owner'] . '/' . $data['reports_id'];
        $report = new \Jasper\JasperReportUnit($customerSpace . '/report_unit');

        //$rsrc =  $jasper->search('inputControl','/reports/' . $data['owner'] . '/' . $data['reports_id'] . '/input_controls');
        $rsrc = $jasper->getInputControls($report);

        fb($rsrc, '$rsrc', FirePHP::INFO);

        $params = array();

        $response = array('succes' => true, 'params' => $params, 'msg' => '');

        if (!empty($rsrc)) {
            $xml = new SimpleXMLElement($rsrc);

            //fb($xml, '$xml', FirePHP::INFO);

            $errorCode = (string)$xml->errorCode;

            if (!empty($errorCode)) {
                $msg = (string)$xml->message;

                $i = 0;
                foreach ($xml as $control) {
                    $msg = $msg . '\n' . (string)$control->parameter;
                    $i++;
                }

                $response = array('succes' => false, 'params' => $params, 'msg' => $msg);
            } else {
                $i = 0;
                foreach ($xml as $control) {
                    $params[] = array(
                        'id' => (string)$control->id,
                        'label' => (string)$control->label,
                        'uri' => (string)$control->uri,
                        'type' => (string)$control->type);

                    //fb($control, '$control' . $i, FirePHP::INFO);
                    //fb($resp, '$resp' . $i, FirePHP::INFO);
                    $i++;
                }

                $response = array('succes' => true, 'params' => $params, 'msg' => '1');
            }
        }

        return $response;
    }

    function run($data)
    {
        $jasper = new Jasper\Jasper(REPORT_SERVER, REPORT_USER, REPORT_PASS);

        $response = $jasper->createRequest($data);
        //fb((string)$response, '$response', FirePHP::INFO);
        return $response;
    }

    public static function subscribeReport($subscriptions_id, $emails)
    {
        global $osC_Database;

        $Qreports = $osC_Database->query('select subscribers  from :table_reports_subscriptions where subscriptions_id = :subscriptions_id');

        $Qreports->bindInt(':subscriptions_id', $subscriptions_id);
        $Qreports->bindTable(':table_reports_subscriptions', TABLE_REPORTS_SUBSCRIPTIONS);
        $Qreports->execute();

        $subscribers = "";
        if ($Qreports->next()) {
            $subscribers = $Qreports->Value('subscribers');
        }

        $subscribers = $subscribers . ';' . $emails;
        $Qreports = $osC_Database->query("update :table_reports_subscriptions set subscribers = :emails where subscriptions_id = :subscriptions_id");
        $Qreports->bindInt(':subscriptions_id', $subscriptions_id);
        $Qreports->bindValue(':emails', $subscribers);
        $Qreports->bindTable(':table_reports_subscriptions', TABLE_REPORTS_SUBSCRIPTIONS);
        $Qreports->execute();

        if ($osC_Database->isError()) {
            $_SESSION['LAST_ERROR'] = $osC_Database->getError();
            return false;
        }

        return true;
    }

    function schedule($data,$state = 'ENABLED')
    {
        global $osC_Database;

        $username = $_SESSION[admin][username];

        if (empty($username)) {
//            $response = array('success' => false, 'msg' => 'Votre session est expirée ...');
//            return $response;
            $username = 'admin';
        }

        $builtin = array("token","module", "action", "end_date", "end_time","hour","minute","month","monthday","start_date","start_time","weekday","ORACLE_CONNEXION","MYSQL_CONNEXION","MSSQL_CONNEXION", "SERVER_CONNEXION");

        $query = "";

        if (count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                if (!in_array($key, $builtin)) {
                    $query = $query . $key . "=" . rawurlencode($val) . "&";
                }
            }
        } else {
            foreach ($_GET as $key => $val) {
                if (!in_array($key, $builtin)) {
                    $query = $query . $key . "=" . rawurlencode($val) . "&";
                }
            }
        }

        //fb($data, '$data', FirePHP::INFO);

        //$nick = substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 10)), 0, 5);

        $minutes = isset($data['minutes']) ? str_replace(" ", "", $data['minutes']) : "*";
        $hours = isset($data['hours']) ? str_replace(" ", "", $data['hours']) : "*";
        $monthdays = isset($data['monthdays']) ? str_replace(" ", "", $data['monthdays']) : "*";
        $months = isset($data['months']) ? str_replace(" ", "", $data['months']) : "*";
        $weekdays = isset($data['weekdays']) ? str_replace(" ", "", $data['weekdays']) : "*";

        $schedule = $state == 'ENABLED' ? $minutes . " " . $hours . " " . $monthdays . " " . $months . " " . $weekdays : "";
        //date("Y-m-d
        $start = $state == 'ENABLED' ? $data['start_date'] . "T" . $data['start_time'] . ":00+0100" : "2099-12-31T" . date("H:i") . ":00+0100";
        $end = $state == 'ENABLED' ? $data['end_date'] . "T" . $data['end_time'] . ":00+0100" : "2999-12-31" . "T" . date("H:i") . ":00+0100";

        $builtin = array("ORACLE_CONNEXION","MYSQL_CONNEXION","MSSQL_CONNEXION");

        $params = "";
        if (count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                if (substr($key, 0, 6) == 'PARAM_') {
                    $cle = substr($key, 6);
                    if (!in_array($cle, $builtin)) {
                        $params = $params . $cle . "=" . $val . ";";
                    }
                }
            }
        } else {
            foreach ($_GET as $key => $val) {
                if (substr($key, 0, 6) == 'PARAM_') {
                    $cle = substr($key, 6);
                    if (!in_array($cle, $builtin)) {
                        $params = $params . $cle . "=" . $val . ";";
                    }
                }
            }
        }

        $subscribers = "";

        if (isset($_REQUEST['to']) && !empty($_REQUEST['to'])) {
            $emails = explode(';', $_REQUEST['to']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $data = osC_Mail::parseEmail($email);
                    $subscribers = $subscribers . $data['email'] . ";";
                }
            }
        }

        if (isset($_REQUEST['cc']) && !empty($_REQUEST['cc'])) {
            $emails = explode(';', $_REQUEST['cc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $data = osC_Mail::parseEmail($email);
                    $subscribers = $subscribers . $data['email'] . ";";
                }
            }
        }

        if (isset($_REQUEST['bcc']) && !empty($_REQUEST['bcc'])) {
            $emails = explode(';', $_REQUEST['bcc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $data = osC_Mail::parseEmail($email);
                    $subscribers = $subscribers . $data['email'] . ";";
                }
            }
        }

        $Qschedule = $osC_Database->query("insert into :table_reports_subscriptions (`reports_id`,`schedule`,`owner`,`parameters`,`subscribers`,`format`,`date_debut`,`date_fin`,`query`,`to`,`cc`,`bcc`,`subject`,`body`,`priority`) values (:reports_id,:schedule,:owner,:parameters,:subscribers,:format,:date_debut,:date_fin,:query,:to,:cc,:bcc,:subject,:body,:priority)");
        $Qschedule->bindInt(':reports_id',$_REQUEST['reports_id']);
        $Qschedule->bindValue(':schedule',$schedule);
        $Qschedule->bindValue(':owner',$username);
        $Qschedule->bindValue(':parameters',$params);
        $Qschedule->bindValue(':subscribers',$subscribers);
        $Qschedule->bindValue(':format',$_REQUEST['format']);
        $Qschedule->bindValue(':date_debut',$start);
        $Qschedule->bindValue(':date_fin',$end);
        $Qschedule->bindValue(':query',$query);
        $Qschedule->bindValue(':to',$_REQUEST['to']);
        $Qschedule->bindValue(':cc',$_REQUEST['cc']);
        $Qschedule->bindValue(':bcc',$_REQUEST['bcc']);
        $Qschedule->bindValue(':subject',$_REQUEST['subject']);
        $Qschedule->bindValue(':body',$_REQUEST['body']);
        $Qschedule->bindInt(':priority',$_REQUEST['priority']);
        $Qschedule->bindTable(':table_reports_subscriptions', TABLE_REPORTS_SUBSCRIPTIONS);
        $Qschedule->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'msg' => 'Impossible de creer ce job : ' . $osC_Database->getError());
            return $response;
        } else {
            $subscriptions_id = $osC_Database->nextID();
            $obsidian_query = $query = "module=reports&action=run_reportJob&subscriptions_id=" . $subscriptions_id . "&";

            $jasper = new Jasper\Jasper(REPORT_SERVER, REPORT_USER, REPORT_PASS);

            $data['state'] = $state;
            $response = $jasper->createSchedule($data,$subscriptions_id,$schedule,$start,$end,$obsidian_query);

            if($response['succes'] == true)
            {
                $Qschedule = $osC_Database->query('update :table_reports_subscriptions set `job_id` = :job_id where `subscriptions_id` = :subscriptions_id');
                $Qschedule->bindInt(':job_id',$response['jobId']);
                $Qschedule->bindInt(':subscriptions_id',$subscriptions_id);
                $Qschedule->bindTable(':table_reports_subscriptions', TABLE_REPORTS_SUBSCRIPTIONS);
                $Qschedule->execute();

                if ($osC_Database->isError()) {
                    $response = array('success' => false, 'msg' => 'Impossible de mettre à jour ce job : ' . $osC_Database->getError());
                    return $response;
                }
                else
                {
                    $error = false;
                    $data['content_status'] = 1;
                    $data['content_order'] = 1;

                    //content
                    if ($error === false) {
                        $error = !content::saveContent(null, $subscriptions_id, 'jobs', $data);
                    }

                    global $osC_Language;

                    foreach ($osC_Language->getAll() as $l) {
                        $data['content_name'][$l['id']] = $_REQUEST['content_name'];
                        $data['content_url'][$l['id']] = $_REQUEST['content_name'];
                        $data['content_description'][$l['id']] = $_REQUEST['subject'];
                        $data['page_title'][$l['id']] = $_REQUEST['subject'];
                        $data['meta_keywords'][$l['id']] = '';
                        $data['meta_descriptions'][$l['id']] = '';
                    }
                    //Process Languages
                    if ($error === false) {
                        $error = !content::saveContentDescription(null, $subscriptions_id, 'jobs', $data);
                    }

                    $data['categories'] = $_REQUEST['current_category_id'];
                    //content_to_categories
                    if ($error === false) {
                        $error = !content::saveContentToCategories(null, $subscriptions_id, 'jobs', $data);
                    }
                }
            }
            else
            {
                return $response;
            }

            $response = array('success' => true, 'msg' => '1','subscriptions_id' => $subscriptions_id,'status' => 'run');
        }

        return $response;
    }

    function runJob($job)
    {
        global $osC_Language;
        $i = 0;
        $jasper = new Jasper\Jasper(REPORT_SERVER, REPORT_USER, REPORT_PASS);

        $exec_id = toC_Reports_Admin::addReportExecution($_REQUEST['subscriptions_id']);

        $data = array('subscriptions_id' => $_REQUEST['subscriptions_id'], 'status' => 'starting','execution_id' => $exec_id,'comments' => 'Generation du rapport ...');
        toC_Reports_Admin::addJobRunDetail($data);

        $response = $jasper->createRequestJob($job);

        $requete = new \SimpleXMLElement($response['request']);

        $status = (string)$requete->status;
        $req_id = (string)$requete->requestId;
        $job['id'] = $req_id;
        $action = $status == 'ready' ? 'download_report' : 'status_report';

        switch ($action) {
            case 'download_report':
                download_report:
                $data = array('subscriptions_id' => $_REQUEST['subscriptions_id'], 'status' => 'downloading','execution_id' => $exec_id,'comments' => 'telechargement du fichier de rapport ...');
                toC_Reports_Admin::addJobRunDetail($data);

                $report = toC_Reports_Admin::download($job);

                $dir = realpath(DIR_WS_REPORTS) . '/';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                $file_name = $dir . '/' . $job['id'] . '.' . $job['format'];

                $data = array('subscriptions_id' => $_REQUEST['subscriptions_id'], 'status' => 'saving','execution_id' => $exec_id,'comments' => 'file ===> ' . $file_name);
                toC_Reports_Admin::addJobRunDetail($data);

                file_put_contents($file_name,$report);

                $to = array();
                $emails = explode(';', $job['to']);
                foreach ($emails as $email) {
                    if (!empty($email)) {
                        $to[] = osC_Mail::parseEmail($email);
                    }
                }

                $cc = array();
                if (isset($job['cc']) && !empty($job['cc'])) {
                    $emails = explode(';', $job['cc']);

                    foreach ($emails as $email) {
                        if (!empty($email)) {
                            $cc[] = osC_Mail::parseEmail($email);
                        }
                    }
                }

                $bcc = array();
                if (isset($job['bcc']) && !empty($job['bcc'])) {
                    $emails = explode(';', $job['bcc']);

                    foreach ($emails as $email) {
                        if (!empty($email)) {
                            $bcc[] = osC_Mail::parseEmail($email);
                        }
                    }
                }

                $toC_Email_Account = new toC_Email_Account(4);

                $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                    'id' => $job['subscriptions_id'],
                    'to' => $to,
                    'cc' => $cc,
                    'bcc' => $bcc,
                    'from' => $toC_Email_Account->getAccountName(),
                    'sender' => $toC_Email_Account->getAccountEmail(),
                    'subject' => $job['subject'],
                    'reply_to' => $toC_Email_Account->getAccountEmail(),
                    'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                    'body' => $job['body'],
                    'priority' => $job['priority'],
                    'content_type' => 'html',
                    'notification' => false,
                    'udate' => time(),
                    'date' => date('m/d/Y H:i:s'),
                    'fetch_timestamp' => time(),
                    'messages_flag' => EMAIL_MESSAGE_DRAFT,
                    'attachments' => $file_name);

                $data = array('subscriptions_id' => $_REQUEST['subscriptions_id'], 'status' => 'sending','execution_id' => $exec_id,'comments' => 'envoi du mail ...');
                toC_Reports_Admin::addJobRunDetail($data);

                if ($toC_Email_Account->sendMailJob($mail)) {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                } else {
                    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                }

                break;
            case 'status_report':
                status_report:
                $i++;

                if($i > 100)
                {
                    goto download_report;
                }
                else
                {
                    fb($job, '$job', FirePHP::INFO);
                    $report = toC_Reports_Admin::statusJob($job,$jasper);
                    $status = (string)$report->body->status;
                    $action = $status == 'ready' ? 'download_report' : 'status_report';
                    if($action == 'download_report')
                    {
                        goto download_report;
                    }
                    else
                    {
                        $data = array('subscriptions_id' => $_REQUEST['subscriptions_id'], 'status' => 'executing','execution_id' => $exec_id,'comments' => 'generation du rapport ...');
                        toC_Reports_Admin::addJobRunDetail($data);

                        sleep(2);
                        goto status_report;
                    }
                }
                break;
            default:
                break;
        }

        //fb((string)$response, '$response', FirePHP::INFO);
        //return $response;
        $data = array('subscriptions_id' => $_REQUEST['subscriptions_id'], 'status' => 'complete','execution_id' => $exec_id,'comments' => $job['id'] . '.' . $job['format'],'requestId' => $req_id);
        toC_Reports_Admin::addJobRunDetail($data);

        return 0;
    }

    function sendEmail($data)
    {
        $to = array();
        $emails = explode(';', $data['to']);
        foreach ($emails as $email) {
            if (!empty($email)) {
                $to[] = osC_Mail::parseEmail($email);
            }
        }

        $cc = array();
        if (isset($data['cc']) && !empty($data['cc'])) {
            $emails = explode(';', $data['cc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $cc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $bcc = array();
        if (isset($data['bcc']) && !empty($data['bcc'])) {
            $emails = explode(';', $data['bcc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $bcc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $toC_Email_Account = new toC_Email_Account(4);

        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'from' => $toC_Email_Account->getAccountName(),
            'sender' => $toC_Email_Account->getAccountEmail(),
            'subject' => $data['subject'],
            'reply_to' => $toC_Email_Account->getAccountEmail(),
            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
            'body' => $data['body'],
            'priority' => $data['priority'],
            'content_type' => isset($data['content_type']) ? $data['content_type'] : 'html',
            'notification' => false,
            'udate' => time(),
            'date' => date('m/d/Y H:i:s'),
            'fetch_timestamp' => time(),
            'messages_flag' => EMAIL_MESSAGE_DRAFT,
            'attachments' => $data['attachments']);

        if ($toC_Email_Account->sendMailJob($mail)) {
            $response = array('success' => true, 'feedback' => 'Message envoyé');
        } else {
            $response = array('success' => false, 'feedback' => "Erreur lors de l'envoi du Message");
        }

        return $response;
    }

    function sendEmailAttach($data)
    {
        //var_dump($data);

        $to = array();
        $emails = explode(';', $data['to']);
        foreach ($emails as $email) {
            if (!empty($email)) {
                $to[] = osC_Mail::parseEmail($email);
            }
        }

        $cc = array();
        if (isset($data['cc']) && !empty($data['cc'])) {
            $emails = explode(';', $data['cc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $cc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $bcc = array();
        if (isset($data['bcc']) && !empty($data['bcc'])) {
            $emails = explode(';', $data['bcc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $bcc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $attach = new upload('attach');
        $attach->set_destination('/tmp/');
        $attach->parse();
        $attach->save();

        $toC_Email_Account = new toC_Email_Account(4);

        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'from' => $toC_Email_Account->getAccountName(),
            'sender' => $toC_Email_Account->getAccountEmail(),
            'subject' => $data['subject'],
            'reply_to' => $toC_Email_Account->getAccountEmail(),
            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
            'body' => $data['body'],
            'priority' => $data['priority'],
            'content_type' => 'html',
            'notification' => false,
            'udate' => time(),
            'date' => date('m/d/Y H:i:s'),
            'fetch_timestamp' => time(),
            'messages_flag' => EMAIL_MESSAGE_DRAFT,
            'attachments' => '/tmp/' . $attach['filename']);

        var_dump($mail);

        if ($toC_Email_Account->sendMailJob($mail)) {
            $response = array('success' => true, 'feedback' => 'Message envoyé');
        } else {
            $response = array('success' => false, 'feedback' => "Erreur lors de l'envoi du Message");
        }

        var_dump($attach);

        return $response;
    }

    function sendFileEmail($data)
    {
        //var_dump($data);

        $to = array();
        $emails = explode(';', $data['to']);
        foreach ($emails as $email) {
            if (!empty($email)) {
                $to[] = osC_Mail::parseEmail($email);
            }
        }

        $cc = array();
        if (isset($data['cc']) && !empty($data['cc'])) {
            $emails = explode(';', $data['cc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $cc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $bcc = array();
        if (isset($data['bcc']) && !empty($data['bcc'])) {
            $emails = explode(';', $data['bcc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $bcc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $toC_Email_Account = new toC_Email_Account(4);

        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'from' => $toC_Email_Account->getAccountName(),
            'sender' => $toC_Email_Account->getAccountEmail(),
            'subject' => $data['subject'],
            'reply_to' => $toC_Email_Account->getAccountEmail(),
            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
            'body' => $data['body'],
            'priority' => $data['priority'],
            'content_type' => 'html',
            'notification' => false,
            'udate' => time(),
            'date' => date('m/d/Y H:i:s'),
            'fetch_timestamp' => time(),
            'messages_flag' => EMAIL_MESSAGE_DRAFT,
            'attachments' => '/tmp/' . $data['filename']);

       // var_dump($mail);

        if ($toC_Email_Account->sendMailJob($mail)) {
            $response = array('success' => true, 'feedback' => 'Message envoyé');
        } else {
            $response = array('success' => false, 'feedback' => "Erreur lors de l'envoi du Message");
        }

     //   var_dump($attach);

        return $response;
    }

    function sendReketor($data)
    {
        $to = array();
        $emails = explode(';', $data['to']);
        foreach ($emails as $email) {
            if (!empty($email)) {
                $to[] = osC_Mail::parseEmail($email);
            }
        }

        $cc = array();
        if (isset($data['cc']) && !empty($data['cc'])) {
            $emails = explode(';', $data['cc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $cc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $bcc = array();
        if (isset($data['bcc']) && !empty($data['bcc'])) {
            $emails = explode(';', $data['bcc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $bcc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $toC_Email_Account = new toC_Email_Account(5);

        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'from' => $toC_Email_Account->getAccountName(),
            'sender' => $toC_Email_Account->getAccountEmail(),
            'subject' => $data['subject'],
            'reply_to' => $toC_Email_Account->getAccountEmail(),
            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
            'body' => $data['body'],
            'priority' => $data['priority'],
            'content_type' => 'html',
            'notification' => false,
            'udate' => time(),
            'date' => date('m/d/Y H:i:s'),
            'fetch_timestamp' => time(),
            'messages_flag' => EMAIL_MESSAGE_DRAFT,
            'attachments' => '');

        if ($toC_Email_Account->sendMailJob($mail)) {
            $response = array('success' => true, 'feedback' => 'Message envoyé');
        } else {
            $response = array('success' => false, 'feedback' => "Erreur lors de l'envoi du Message");
        }

        return $response;
    }

    function download($job)
    {
        $jasper = new Jasper\Jasper(REPORT_SERVER, REPORT_USER, REPORT_PASS);

        $response = $jasper->download($job);
        fb((string)$response, '$response', FirePHP::INFO);
        return $response;
    }

    function status($data)
    {
        $jasper = new Jasper\Jasper(REPORT_SERVER);

        $response = $jasper->getStatus($data['id'],'json');
        fb((string)$response, '$response', FirePHP::INFO);
        return $response;
    }

    function statusJob($data,$jasper)
    {
        //$jasper = new Jasper\Jasper(REPORT_SERVER);

        $response = $jasper->getStatus($data['id'],'xml');
        fb((string)$response, '$response', FirePHP::INFO);
        return $response;
    }

    function detailJob($job_id)
    {
        $jasper = new Jasper\Jasper(REPORT_SERVER);

        $response = $jasper->getDetail($job_id);
        //fb((string)$response, '$response', FirePHP::INFO);
        return $response;
    }

    function save($id = null, $data)
    {
        $error = false;

        $username = $_SESSION[admin][username];

        if (empty($username)) {
            $_SESSION['LAST_ERROR'] = 'Votre session est expirée ...';
            return false;
        }

        global $osC_Database;
        $jasper = new Jasper\Jasper(REPORT_SERVER, REPORT_USER, REPORT_PASS);

        //$jasper->deleteResource('/admin/10/report_unit');

        if (is_numeric($id)) {
            $jasper->deleteResource('/reports/' . $data['owner'] . '/' . $id . '/report_unit');

            //delete jrxml templates
            $rsrc = $jasper->search('file', '/reports/' . $data['owner'] . '/' . $id . '/templates');

            if (!empty($rsrc)) {
                $xml = new SimpleXMLElement($rsrc);

                $i = 0;
                foreach ($xml as $jrxml) {
                    //fb($jrxml->uri, '$jrxml_' . $i, FirePHP::INFO);
                    $resp = $jasper->deleteResource($jrxml->uri);
                    //fb($resp, '$resp' . $i, FirePHP::INFO);
                    $i++;
                }
            }

            //delete queries
            $rsrc = $jasper->search('query', '/reports/' . $data['owner'] . '/' . $id . '/query');

            if (!empty($rsrc)) {
                $xml = new SimpleXMLElement($rsrc);

                $i = 0;
                foreach ($xml as $query) {
                    //fb($query->uri, '$query' . $i, FirePHP::INFO);
                    $resp = $jasper->deleteResource($query->uri);
                    //fb($resp, '$resp' . $i, FirePHP::INFO);
                    $i++;
                }
            }

            //delete input controls
            $rsrc = $jasper->search('inputControl', '/reports/' . $data['owner'] . '/' . $id . '/input_controls');

            if (!empty($rsrc)) {
                $xml = new SimpleXMLElement($rsrc);

                $i = 0;
                foreach ($xml as $control) {
                    //fb($control->uri, '$control' . $i, FirePHP::INFO);
                    $resp = $jasper->deleteResource($control->uri);
                    //fb($resp, '$resp' . $i, FirePHP::INFO);
                    $i++;
                }
            }

            //delete datatypes
            $rsrc = $jasper->search('dataType', '/reports/' . $data['owner'] . '/' . $id . '/datatypes');

            if (!empty($rsrc)) {
                $xml = new SimpleXMLElement($rsrc);

                $i = 0;
                foreach ($xml as $type) {
                    //fb($type->uri, '$type' . $i, FirePHP::INFO);
                    $resp = $jasper->deleteResource($type->uri);
                    //fb($resp, '$resp' . $i, FirePHP::INFO);
                    $i++;
                }
            }

            //delete images
            $rsrc = $jasper->search('file', '/reports/' . $data['owner'] . '/' . $id . '/images');

            if (!empty($rsrc)) {
                $xml = new SimpleXMLElement($rsrc);

                $i = 0;
                foreach ($xml as $image) {
                    //fb($image->uri, '$image' . $i, FirePHP::INFO);
                    $resp = $jasper->deleteResource($image->uri);
                    //fb($resp, '$resp' . $i, FirePHP::INFO);
                    $i++;
                }
            }

            //delete lov
            $rsrc = $jasper->search('listOfValues', '/reports/' . $data['owner'] . '/' . $id . '/lov');

            if (!empty($rsrc)) {
                $xml = new SimpleXMLElement($rsrc);

                $i = 0;
                foreach ($xml as $lov) {
                    //fb($lov->uri, '$lov' . $i, FirePHP::INFO);
                    $resp = $jasper->deleteResource($lov->uri);
                    //fb($resp, '$resp' . $i, FirePHP::INFO);
                    $i++;
                }
            }

            //delete folder
            $resp = $jasper->deleteResource('/reports/' . $data['owner'] . '/' . $id);
            //fb($resp, '$resp' . $i, FirePHP::INFO);

        } else {
            $Qreport = $osC_Database->query('insert into :table_reports (reports_host) values (:reports_host)');
            $Qreport->bindValue(':reports_host', $jasper->getHost());
            $Qreport->bindTable(':table_reports', TABLE_REPORTS);
            $Qreport->execute();
        }

        if ($osC_Database->isError()) {
            $error = true;
        } else {
            if (is_numeric($id)) {
                $reports_id = $id;
            } else {
                $reports_id = $osC_Database->nextID();
            }
        }

        $dir = '/tmp';

        //jrxml
        if ($error === false) {
            if (is_numeric($id)) {
                $customerSpace = '/reports/' . $data['owner'] . '/' . $reports_id;
                $userSpace = '/reports/' . $data['owner'];
                //$dir = realpath(DIR_WS_REPORTS) . '/' . $data['owner'];
            } else {
                $customerSpace = '/reports/' . $username . '/' . $reports_id;
                $userSpace = '/reports/' . $username;
                //$dir = realpath(DIR_WS_REPORTS) . '/' . $username;
            }

            $reports_jrxml = new upload('field_jrxml');

            //if (!file_exists($dir)) {
            //    mkdir($dir, 0777, true);
            //}

            $reports_jrxml->set_destination($dir);

            if ($reports_jrxml->exists() && $reports_jrxml->parse() && $reports_jrxml->save()) {

                $folder = new \Jasper\JasperFolder($userSpace);
                $folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $resp = $jasper->createFolder($folder);

                $folder = new \Jasper\JasperFolder($customerSpace);
                $folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $resp = $jasper->createFolder($folder);

                $ic_folder = new \Jasper\JasperFolder($customerSpace . '/input_controls');
                $ic_folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $jasper->createFolder($ic_folder);

                $dt_folder = new \Jasper\JasperFolder($customerSpace . '/datatypes');
                $dt_folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $jasper->createFolder($dt_folder);

                $query_folder = new \Jasper\JasperFolder($customerSpace . '/query');
                $query_folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $jasper->createFolder($query_folder);

                $lov_folder = new \Jasper\JasperFolder($customerSpace . '/lov');
                $lov_folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $jasper->createFolder($lov_folder);

                $img_folder = new \Jasper\JasperFolder($customerSpace . '/images');
                $img_folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $jasper->createFolder($img_folder);

                $templates_folder = new \Jasper\JasperFolder($customerSpace . '/templates');
                $templates_folder->setIsNew('true')
                    ->setPropVersion((string)time());

                $jasper->createFolder($templates_folder);

                $report_name = str_replace(' ', '_', $data['content_name'][2]);

                // Upload a jrxml file
                $report = new \Jasper\JasperJrxml($customerSpace . '/templates/' . $reports_jrxml->filename);
                $report->setIsNew('true')
                    ->setPropVersion((string)time())->setPropIsReference(true);

                $content = file_get_contents($dir . '/' . $reports_jrxml->filename);

                $jasper->createContent($report, $content);

                // Create a reportUnit
                // Instance of the Report
                $report = new \Jasper\JasperReportUnit($customerSpace . '/report_unit');
                $report->setLabel($data['content_name'][2]);
                $report->setDescription($data['content_description'][2]);
                $report->setIsNew('true')
                    ->setPropVersion((string)time());
                // jrxml Template
                $jrxml = $jasper->getResourceDescriptor($customerSpace . '/templates/' . $reports_jrxml->filename);
                $jrxml->setPropRuIsMainReport('true')
                    ->setIsNew('true')
                    ->setPropVersion((string)time());

                //$xml = simplexml_load_file($dir . '/' . $reports_jrxml->filename);
                $xml = new SimpleXMLElement($content);

                $i = 0;
                foreach ($xml->parameter as $param) {
                    $inputControl = new \Jasper\JasperInputControl($customerSpace . '/input_controls/' . $param['name']);
                    $inputControl->setLabel($param['name'])->setName($param['name'])->setIsNew(false);

                    $class = substr($param['class'], strrpos($param['class'], '.', 0) + 1);
                    fb($class, '$class_' . $i, FirePHP::INFO);

                    switch ($class) {
                        case 'String':
                            $inputControl->setPropInputControlType(IC_TYPE_SINGLE_VALUE);

                            $dataType = new \Jasper\JasperDataType($customerSpace . '/datatypes/' . $param['name']);
                            $dataType->setLabel($param['name'])->setName($param['name'])->setIsNew(false);
                            $dataType->setPropDatatypeType(DT_TYPE_TEXT);

                            $jasper->createResource($dataType);

                            $inputControl->addChildResource($dataType);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);
                            break;
                        case 'Boolean':
                            $inputControl->setPropInputControlType(IC_TYPE_BOOLEAN);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);
                            break;
                        case 'Byte':
                            $inputControl->setPropInputControlType(IC_TYPE_SINGLE_VALUE);

                            $dataType = new \Jasper\JasperDataType($customerSpace . '/datatypes/' . $param['name']);
                            $dataType->setLabel($param['name'])->setName($param['name'])->setIsNew(false);
                            $dataType->setPropDatatypeType(DT_TYPE_NUMBER);
                            $dataType->setPropDatatypeMaxValue(127);
                            $dataType->setPropDatatypeMinValue(-128);
                            $dataType->setPropDatatypeStrictMin(true);
                            $dataType->setPropDatatypeStrictMax(true);

                            $jasper->createResource($dataType);

                            $inputControl->addChildResource($dataType);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);
                            break;
                        case 'Date':
                        case 'Timestamp':
                        case 'Time':
                            $inputControl->setPropInputControlType(IC_TYPE_SINGLE_VALUE);

                            $dataType = new \Jasper\JasperDataType($customerSpace . '/datatypes/' . $param['name']);
                            $dataType->setLabel($param['name'])->setName($param['name'])->setIsNew(false);
                            $dataType->setPropDatatypeType(DT_TYPE_DATE);

                            $jasper->createResource($dataType);

                            $inputControl->addChildResource($dataType);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);
                            break;
                        case 'Double':
                        case 'Float':
                        case 'BigDecimal':
                        case 'Number':
                            $inputControl->setPropInputControlType(IC_TYPE_SINGLE_VALUE);

                            $dataType = new \Jasper\JasperDataType($customerSpace . '/datatypes/' . $param['name']);
                            $dataType->setLabel($param['name'])->setName($param['name'])->setIsNew(false);
                            $dataType->setPropDatatypeType(DT_TYPE_NUMBER);

                            $jasper->createResource($dataType);

                            $inputControl->addChildResource($dataType);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);
                            break;
                        case 'Integer':
                            $inputControl->setPropInputControlType(IC_TYPE_SINGLE_VALUE);

                            $dataType = new \Jasper\JasperDataType($customerSpace . '/datatypes/' . $param['name']);
                            $dataType->setLabel($param['name'])->setName($param['name'])->setIsNew(false);
                            $dataType->setPropDatatypeType(DT_TYPE_NUMBER);
                            $dataType->setPropDatatypeMaxValue(2147483647);
                            $dataType->setPropDatatypeMinValue(-2147483648);
                            $dataType->setPropDatatypeStrictMin(true);
                            $dataType->setPropDatatypeStrictMax(true);

                            $jasper->createResource($dataType);

                            $inputControl->addChildResource($dataType);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);
                            break;
                        case 'Long':
                            $inputControl->setPropInputControlType(IC_TYPE_SINGLE_VALUE);

                            $dataType = new \Jasper\JasperDataType($customerSpace . '/datatypes/' . $param['name']);
                            $dataType->setLabel($param['name'])->setName($param['name'])->setIsNew(false);
                            $dataType->setPropDatatypeType(DT_TYPE_NUMBER);
                            $dataType->setPropDatatypeMaxValue(9223372036854775807);
                            $dataType->setPropDatatypeMinValue(-9223372036854775808);
                            $dataType->setPropDatatypeStrictMin(true);
                            $dataType->setPropDatatypeStrictMax(true);

                            $jasper->createResource($dataType);

                            $inputControl->addChildResource($dataType);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);
                            break;
                        case 'Short':
                            $inputControl->setPropInputControlType(IC_TYPE_SINGLE_VALUE);

                            $dataType = new \Jasper\JasperDataType($customerSpace . '/datatypes/' . $param['name']);
                            $dataType->setLabel($param['name'])->setName($param['name'])->setIsNew(false);
                            $dataType->setPropDatatypeType(DT_TYPE_NUMBER);
                            $dataType->setPropDatatypeMaxValue(32767);
                            $dataType->setPropDatatypeMinValue(-32768);
                            $dataType->setPropDatatypeStrictMin(true);
                            $dataType->setPropDatatypeStrictMax(true);

                            $jasper->createResource($dataType);

                            $inputControl->addChildResource($dataType);
                            $jasper->createResource($inputControl);

                            $report->addChildResource($inputControl);

                            break;
                    }

                    $i++;
                }

                $report->setPropRuAlwaysPropmtControls(true);
                $report->setPropRuControlsLayout(3);
                $report->addChildResource($jrxml);
                //    // Abfragesteuerelement bestehend aus InputControl und DataType

//    // Image
//    $img = new \Jasper\JasperImage('/images/adn_logo01');
//    $img->setIsNew('false')
//        ->setPropIsReference('true')
//        ->setPropReferenceUri('/images/adn_logo01');

                // Datasource
//                $mongo = new \Jasper\JasperDatasource();
//                $mongo->setPropIsReference('true');
//                $mongo->setPropReferenceUri('/datasources/mongo_local_test');

                // Put everything together and deploy the report
//                $report->addChildResource($mongo);
//    $report->addChildResource($img);
//    $report->addChildResource($inputControl);

                $report_xml = $report->getXml(true);

                $report->setIsNew(false);
                $jasper->createResource($report);
            } else {
                //fb($reports_jrxml->getLastError(), 'erreur upload', FirePHP::INFO);
                $_SESSION['LAST_ERROR'] = 'Impossible de uploader field_jrxml';
                $error = true;
            }
        }

        //content
        if ($error === false) {
            $error = !content::saveContent($id, $reports_id, 'reports', $data);
        }

        //Process Languages
        if ($error === false) {
            $error = !content::saveContentDescription($id, $reports_id, 'reports', $data);
        }

        //content_to_categories
        if ($error === false) {
            $error = !content::saveContentToCategories($id, $reports_id, 'reports', $data);
        }

        //images
        if ($error === false) {
            $error = !content::saveImages($reports_id, 'reports');
        }

        if ($error === false) {
            osC_Cache::clear('sefu-reports');
            return true;
        }

        return $error;
    }

    function saveDashboard($id = null, $data)
    {
        $error = false;

        $username = $_SESSION[admin][username];

        if (empty($username)) {
            $_SESSION['LAST_ERROR'] = 'Votre session est expirée ...';
            return false;
        }

        global $osC_Database;

        if (is_numeric($id)) {
            $Qreport = $osC_Database->query('update :table_dashboards set reports_uri = :reports_uri where dashboards_id = :dashboards_id');
            $Qreport->bindValue(':dashboards_id',$id);
        } else {
            $Qreport = $osC_Database->query('insert into :table_dashboards (reports_uri) values (:reports_uri)');
        }

        $Qreport->bindValue(':reports_uri', $_REQUEST['reports_uri']);
        $Qreport->bindTable(':table_dashboards', TABLE_DASHBOARS);
        $Qreport->execute();

        if ($osC_Database->isError()) {
            $error = true;
        } else {
            if (is_numeric($id)) {
                $reports_id = $id;
            } else {
                $reports_id = $osC_Database->nextID();
            }
        }

        $dir = '/tmp';

        //content
        if ($error === false) {
            $error = !content::saveContent($id, $reports_id, 'dashboards', $data);
        }

        //Process Languages
        if ($error === false) {
            $error = !content::saveContentDescription($id, $reports_id, 'dashboards', $data);
        }

        //content_to_categories
        if ($error === false) {
            $error = !content::saveContentToCategories($id, $reports_id, 'dashboards', $data);
        }

        //images
        if ($error === false) {
            $error = !content::saveImages($reports_id, 'dashboards');
        }

        if ($error === false) {
            osC_Cache::clear('sefu-dashboards');
            return true;
        }

        return $error;
    }

    function deleteSubscription($subscriptions_id,$job_id) {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();

        $Qdelete = $osC_Database->query('delete from :table_reports_subscriptions where subscriptions_id = :subscriptions_id');
        $Qdelete->bindInt(':subscriptions_id', $subscriptions_id);
        $Qdelete->bindTable(':table_reports_subscriptions', TABLE_REPORTS_SUBSCRIPTIONS);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
            $error = true;
        }

        if ($error === false){
            $jasper = new Jasper\Jasper(REPORT_SERVER);

            $jasper->deleteJob($job_id);
        }

        if ($error == false) {
            $osC_Database->commitTransaction();

            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function delete($id, $owner)
    {
        global $osC_Database, $osC_Image;
        $error = false;

        $osC_Database->startTransaction();

        //$osC_Image->deleteArticlesImage($id);

        $jasper = new Jasper\Jasper(REPORT_SERVER, REPORT_USER, REPORT_PASS);
        $jasper->deleteResource('/' . $owner . '/' . $id . '/report_unit');

        //delete jrxml templates
        $rsrc = $jasper->search('file', '/' . $owner . '/' . $id . '/templates');

        if (!empty($rsrc)) {
            $xml = new SimpleXMLElement($rsrc);

            $i = 0;
            foreach ($xml as $jrxml) {
                $jasper->deleteResource($jrxml->uri);
                $i++;
            }
        }

        //delete queries
        $rsrc = $jasper->search('query', '/' . $owner . '/' . $id . '/query');

        if (!empty($rsrc)) {
            $xml = new SimpleXMLElement($rsrc);

            $i = 0;
            foreach ($xml as $query) {
                $jasper->deleteResource($query->uri);
                $i++;
            }
        }

        //delete input controls
        $rsrc = $jasper->search('inputControl', '/' . $owner . '/' . $id . '/input_controls');

        if (!empty($rsrc)) {
            $xml = new SimpleXMLElement($rsrc);

            $i = 0;
            foreach ($xml as $control) {
                $jasper->deleteResource($control->uri);
                $i++;
            }
        }

        //delete datatypes
        $rsrc = $jasper->search('dataType', '/' . $owner . '/' . $id . '/datatypes');

        if (!empty($rsrc)) {
            $xml = new SimpleXMLElement($rsrc);

            $i = 0;
            foreach ($xml as $type) {
                $jasper->deleteResource($type->uri);
                $i++;
            }
        }

        //delete images
        $rsrc = $jasper->search('file', '/' . $owner . '/' . $id . '/images');

        if (!empty($rsrc)) {
            $xml = new SimpleXMLElement($rsrc);

            $i = 0;
            foreach ($xml as $image) {
                $jasper->deleteResource($image->uri);
                $i++;
            }
        }

        //delete lov
        $rsrc = $jasper->search('listOfValues', '/' . $owner . '/' . $id . '/lov');

        if (!empty($rsrc)) {
            $xml = new SimpleXMLElement($rsrc);

            $i = 0;
            foreach ($xml as $lov) {
                $jasper->deleteResource($lov->uri);
                $i++;
            }
        }

        //delete folder
        $jasper->deleteResource('/' . $owner . '/' . $id);

        $error = !content::deleteContent($id, 'reports');

        if ($error === false) {
            $Qreports = $osC_Database->query('delete from :table_reports where reports_id = :reports_id');
            $Qreports->bindTable(':table_reports', TABLE_REPORTS);
            $Qreports->bindInt(':reports_id', $id);
            $Qreports->setLogging($_SESSION['module'], $id);
            $Qreports->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }
        }

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $osC_Database->commitTransaction();
        osC_Cache::clear('sefu-reports');
        return true;
    }

    function deleteDashboard($id, $owner)
    {
        global $osC_Database, $osC_Image;
        $error = false;

        $osC_Database->startTransaction();

        $error = !content::deleteContent($id, 'dashboards');

        if ($error === false) {
            $Qreports = $osC_Database->query('delete from :table_reports where dashboards_id = :reports_id');
            $Qreports->bindTable(':table_reports', TABLE_DASHBOARS);
            $Qreports->bindInt(':reports_id', $id);
            $Qreports->setLogging($_SESSION['module'], $id);
            $Qreports->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }
        }

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $osC_Database->commitTransaction();
        osC_Cache::clear('sefu-dashboards');
        return true;
    }
}

?>
