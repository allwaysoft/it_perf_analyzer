<?php
/*
  $Id: relances.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
require('includes/classes/sms.php');

class toC_Json_Sms
{

    function listSms()
    {
        global $toC_Json, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
        $total = empty($_REQUEST['count']) ? 0 : $_REQUEST['count'];

        $query = 'select * from SmsServer.Messages where directionid = 2 and statusdetailsid = ' . $_REQUEST['categories_id'] . ' order by id desc';

        $QSms = $osC_Database->query($query);
        $QSms->setExtBatchLimit($start, $limit);
        $QSms->execute();

        $records = array();
        while ($QSms->next()) {
            $records[] = array(
                'sms_id' => $QSms->valueInt('ID'),
                'directionid' => $QSms->valueInt('DirectionID'),
                'typeid' => $QSms->valueInt('TypeID'),
                'statusdetailsid' => $QSms->valueInt('StatusDetailsID'),
                'customerid' => $QSms->value('CustomField1'),
                'customer_name' => $QSms->value('CustomField2'),
                'message' => $QSms->value('Body'),
                'no_phone' => $QSms->value('ToAddress')
            );
        }

        //var_dump($QSms);

        $QSms->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadStatusTree()
    {
        global $osC_Database, $toC_Json;

        $query = "select
  count(m.statusdetailsid) as count,
  m.statusdetailsid,
  s.description
from
  SmsServer.Messages m
  left outer join
  SmsServer.StatusDetails s
  on (m.statusdetailsid = s.id)
group by m.statusdetailsid,
  s.description
order by s.description asc";

        $Qcategories = $osC_Database->query($query);
        $Qcategories->execute();

        $records = array();

        //$records [] = array('roles_id' => -1, 'id' => -1, 'text' => 'Tout le monde', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);

        while ($Qcategories->next()) {
            $records [] = array('count' => $Qcategories->value('count'),'roles_id' => $Qcategories->value('statusdetailsid'), 'id' => $Qcategories->value('statusdetailsid'), 'text' => $Qcategories->value('description') . ' (' . $Qcategories->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/phone_icon.jpg', 'leaf' => true);
        }

        $Qcategories->freeResult();

        echo $toC_Json->encode($records);
    }

    function sendSms()
    {
        //var_dump($_REQUEST);
        //osC_Sms_Admin::addDetail("Recuperation du No Phone dans AMPLITUDE ===> trxlogid = " . $_REQUEST['trxlogid']);
        //$phone = osC_Sms_Admin::getPhone($_REQUEST['customerid']);

        //osC_Sms_Admin::addDetail("Insertion du Message dans la BD ===> trxlogid = " . $_REQUEST['trxlogid']);

        $body = $_REQUEST['civilite'] . ' ' . $_REQUEST['lastname'] .
            ', debit de votre ' . $_REQUEST['type_compte'] . ' de XAF ' . $_REQUEST['montant'] . ' le ' . $_REQUEST['date_loc_tran'] . ' a ' . $_REQUEST['time_loc_tran'] . ' pour ' . $_REQUEST['automate'] . ' a ' . $_REQUEST['lieu'] . '. Merci de votre confiance';

        switch ($_REQUEST['lang']) {
            case 'fr':
                break;
            case 'en':
//                $body = $_REQUEST['civilite'] . ' ' . $_REQUEST['lastname'] .
//                    ', debit of your ' . $_REQUEST['type_compte'] . ' of XAF ' . $_REQUEST['montant'] .
//                    ' on the ' . $_REQUEST['date_loc_tran'] . ' at ' . $_REQUEST['time_loc_tran'] . ' for ' . $_REQUEST['automate'] . ' at ' . $_REQUEST['lieu'] . '. Thanks for your trust';

                $body = $_REQUEST['civilite'] . ' ' . $_REQUEST['lastname'] .
                    ', XAF  ' . $_REQUEST['montant'] . ' has been debited to your ' . $_REQUEST['type_compte'] .
                    ' on ' . $_REQUEST['date_loc_tran'] . ' at ' . $_REQUEST['time_loc_tran'] . ' after ' . $_REQUEST['automate'] . ' at ' . $_REQUEST['lieu'] . ' . Thanks for your trust.';
                break;
        }

        global $toC_Json, $osC_Database;

        $QSms = $osC_Database->query('insert into :table_sms (statusid,directionid, typeid,ToAddress,subj,Body,channelid,bodyformatid,FromAddress,customfield1,customfield2,ScheduledTimeSecs,Priority) values (:statusid,:directionid,:typeid,:toaddress,:subject,:body,:channelid,:bodyformatid,:fromaddress,:customfield1,:customfield2,0,1)');

        $QSms->bindTable(':table_sms', 'SmsServer.Messages');
        $QSms->bindInt(':directionid', '2');
        $QSms->bindInt(':typeid', '1');
        $QSms->bindInt(':bodyformatid', '0');
        $QSms->bindInt(':statusid', '1');
        $QSms->bindValue(':fromaddress', 'BICEC');
        $QSms->bindValue(':toaddress', $_REQUEST['phone']);
        $QSms->bindValue(':channelid', '1101');
        $QSms->bindValue(':body', $body);
        $QSms->bindValue(':subject',$_REQUEST['date_loc_tran'] . $_REQUEST['time_loc_tran']);
        $QSms->bindValue(':customfield1', $_REQUEST['customerid']);
        $QSms->bindValue(':customfield2', $_REQUEST['civilite'] . ' ' . $_REQUEST['firstname'] . ' ' . $_REQUEST['lastname']);
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            osC_Sms_Admin::addError($osC_Database->getError());
            return;
        }

        //osC_Sms_Admin::addDetail("Suppression transaction trxlogid = " . $_REQUEST['trxlogid']);
        osC_Sms_Admin::deleteTrans($_REQUEST['trxlogid']);
        //osC_Sms_Admin::addDetail("Fin transaction trxlogid = " . $_REQUEST['trxlogid']);
    }

    function sendAlertgab()
    {
        //var_dump($_REQUEST);
        //osC_Sms_Admin::addDetail("Recuperation du No Phone dans AMPLITUDE ===> trxlogid = " . $_REQUEST['trxlogid']);
        //$phone = osC_Sms_Admin::getPhone($_REQUEST['customerid']);

        //osC_Sms_Admin::addDetail("Insertion du Message dans la BD ===> trxlogid = " . $_REQUEST['trxlogid']);

        $body = 'GAB ' . $_REQUEST['DISPLAYID'] . ' en arret le ' . $_REQUEST['DB_DATE_TIME'] . ' pour motif ' . $_REQUEST['MOTIF'];

        global $toC_Json, $osC_Database;

        $QSms = $osC_Database->query('insert into :table_sms (statusid,directionid, typeid,ToAddress,subj,Body,channelid,bodyformatid,FromAddress,ScheduledTimeSecs,Priority) values (:statusid,:directionid,:typeid,:toaddress,:subject,:body,:channelid,:bodyformatid,:fromaddress,0,1)');

        $QSms->bindTable(':table_sms', 'SmsServer.Messages');
        $QSms->bindInt(':directionid', '2');
        $QSms->bindInt(':typeid', '1');
        $QSms->bindInt(':bodyformatid', '0');
        $QSms->bindInt(':statusid', '1');
        $QSms->bindValue(':fromaddress', 'BCI');
        $QSms->bindValue(':toaddress', $_REQUEST['PHONE']);
        $QSms->bindValue(':channelid', '1102');
        $QSms->bindValue(':body', $body);
        $QSms->bindValue(':subject',$_REQUEST['DISPLAYID'] . ' en arret');
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            osC_Sms_Admin::addError($osC_Database->getError());
            return;
        }

        $QSms = $osC_Database->query('insert into :table_sms (statusid,directionid, typeid,ToAddress,subj,Body,channelid,bodyformatid,FromAddress,ScheduledTimeSecs,Priority) values (:statusid,:directionid,:typeid,:toaddress,:subject,:body,:channelid,:bodyformatid,:fromaddress,0,1)');

        $QSms->bindTable(':table_sms', 'SmsServer.Messages');
        $QSms->bindInt(':directionid', '2');
        $QSms->bindInt(':typeid', '1');
        $QSms->bindInt(':bodyformatid', '0');
        $QSms->bindInt(':statusid', '1');
        $QSms->bindValue(':fromaddress', 'BCI');
        $QSms->bindValue(':toaddress', $_REQUEST['PHONE2']);
        $QSms->bindValue(':channelid', '1102');
        $QSms->bindValue(':body', $body);
        $QSms->bindValue(':subject',$_REQUEST['DISPLAYID'] . ' en arret');
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            osC_Sms_Admin::addError($osC_Database->getError());
            return;
        }

        //osC_Sms_Admin::addDetail("Suppression transaction trxlogid = " . $_REQUEST['trxlogid']);
        osC_Sms_Admin::updateDrapeau($_REQUEST['EVENTID']);
        //osC_Sms_Admin::addDetail("Fin transaction trxlogid = " . $_REQUEST['trxlogid']);
    }

    function resendSms()
    {
        global $toC_Json, $osC_Database;

        if(!isset($_REQUEST['sms_id']) || empty($_REQUEST['sms_id']))
        {
            $response = array('success' => false, 'feedback' => 'Veuillez selectionner le Message à renvoyer');
        }
        else
        {

            $QSms = $osC_Database->query("update :table_sms set Priority = 1,StatusDetailsID = 200,ScheduledTimeSecs = 0,StatusID = 1 where id = " . $_REQUEST['sms_id']);

            $QSms->bindTable(':table_sms', 'SmsServer.Messages');
            $QSms->execute();

            if ($osC_Database->isError()) {
                $response = array('success' => false, 'feedback' => $osC_Database->getError());
            }
            else
            {
                $response = array('success' => true, 'feedback' => 'Message envoye avec succes');
            }
        }

        echo $toC_Json->encode($response);
    }

    function emptyQueue()
    {
        global $toC_Json, $osC_Database;

        $QSms = $osC_Database->query("update :table_sms set Priority = 1,StatusDetailsID = 200,ScheduledTimeSecs = 0,StatusID = 1 where StatusDetailsID = 201");

        $QSms->bindTable(':table_sms', 'SmsServer.Messages');
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
        }
        else
        {
            $response = array('success' => true, 'feedback' => 'Queue videe !!!');
        }

        echo $toC_Json->encode($response);
    }

    function deleteSms()
    {
        global $toC_Json, $osC_Language, $osC_Image;

        $answer = osC_Sms_Admin::delete($_REQUEST['batch']);
        if ($answer == "true") {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $answer);
        }

        echo $toC_Json->encode($response);
    }

    function loadSms()
    {
        global $toC_Json;

        $data = osC_Sms_Admin::getData($_REQUEST['sms_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function startSmsService()
    {
        global $toC_Json;

        $answer = win32_start_service('AxMmSvc');

        if ($answer != WIN32_NO_ERROR) {
            $feedback = 'success';
        } else {
            $feedback = 'error : ' . $answer;
        }

        $response = array('success' => true, 'feedback' => $feedback);
        echo $toC_Json->encode($response);
    }

    function loadSmsTree()
    {
        global $osC_Database, $osC_Language, $toC_Json;

        $Qcategories = $osC_Database->query('SELECT statusdetails.StatusDetails AS categoryId,statusdetails.Description AS categories_name, COUNT(messages.ID) AS count FROM messages.statusdetails statusdetails LEFT OUTER JOIN messages.messages messages ON (statusdetails.StatusDetails = messages.StatusDetails) GROUP BY statusdetails.Description,statusdetails.StatusDetails ORDER BY statusdetails.Description asc');
        $Qcategories->execute();

        $records = array();

        while ($Qcategories->next()) {
            $records[] = array('id' => $Qcategories->value('categoryId'),
                'text' => $Qcategories->value('categories_name') . ' ( ' . $Qcategories->value('count') . ' )',
                'cls' => 'x-tree-node-collapsed',
                'leaf' => true);
        }

        $Qcategories->freeResult();

        echo $toC_Json->encode($records);
    }

    function saveSms()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        if (isset($_REQUEST['sms_id'])) {
            $QSms = $osC_Database->query('update :table_sms set Status = :status,Direction = :direction, Type = :type,ToAddress = :toaddress,Subject = :subject,Body = :body,ChannelID = :channelid,BodyFormat = :bodyformat,FromAddress = :fromaddress where ID = :sms_id');
            $QSms->bindInt(':sms_id', $_REQUEST['sms_id']);
        } else {
            $QSms = $osC_Database->query('insert into :table_sms (Status,Direction, Type,ToAddress,Subject,Body,ChannelID,BodyFormat,FromAddress) values (:status,:direction,:type,:toaddress,:subject,:body,:channelid,:bodyformat,:fromaddress)');
        }

        $QSms->bindTable(':table_sms', 'messages.messages');
        $QSms->bindInt(':direction', '2');
        $QSms->bindInt(':type', '1');
        $QSms->bindInt(':bodyformat', '0');
        $QSms->bindInt(':status', '1');
        $QSms->bindValue(':fromaddress', $_REQUEST['fromaddress']);
        $QSms->bindValue(':toaddress', $_REQUEST['ToAddress']);
        $QSms->bindValue(':channelid', '1001');
        $QSms->bindValue(':body', $_REQUEST['Body']);
        $QSms->bindValue(':subject', $_REQUEST['Subject']);
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            return;
        }

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

        echo $toC_Json->encode($response);
    }

    function getVariables()
    {
        global $toC_Json;

        $keywords = array(
            'COMPANY_NAME' => ('%%COMPANY_NAME%%'),
            'CONTACT_NAME' => ('%%CONTACT_NAME%%'),
            'CONTACT_TELEPHONE' => ('%%CONTACT_TELEPHONE%%'),
            'CONTACT_EMAIL' => ('%%CONTACT_EMAIL%%'),
            'CONTACT_BIRTHDATE' => ('%%CONTACT_BIRTHDATE%%'),
            'CONTACT_CREDIT_BALANCE' => ('%%CONTACT_CREDIT_BALANCE%%'),
            'CONTACT_FAX' => ('%%CONTACT_FAX%%')
        );

        $records = array();
        foreach ($keywords as $key => $value) {
            $records[] = array('id' => $key, 'value' => $value);
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function saveGroupesms()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $Qcustomers = $osC_Database->query('select c.* from :table_customers c where customers_groups_id = :id');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindInt(':id', $_REQUEST['customers_groups_id']);

        $Qcustomers->appendQuery('order by c.customers_firstname');
        $Qcustomers->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            return;
        }

        while ($Qcustomers->next()) {
            $body = $_REQUEST['Body'];
            $body = str_replace('%%COMPANY_NAME%%', STORE_NAME, $body);
            $body = str_replace('%%CONTACT_NAME%%', $Qcustomers->value('customers_firstname') . ' ' . $Qcustomers->value('customers_lastname'), $body);
            $body = str_replace('%%CONTACT_TELEPHONE%%', $Qcustomers->value('customers_telephone'), $body);
            $body = str_replace('%%CONTACT_EMAIL%%', $Qcustomers->value('customers_email_address'), $body);
            $body = str_replace('%%CONTACT_BIRTHDATE%%', $Qcustomers->value('customers_dob'), $body);
            $body = str_replace('%%CONTACT_CREDIT_BALANCE%%', $Qcustomers->value('customers_credits'), $body);
            $body = str_replace('%%CONTACT_FAX%%', $Qcustomers->value('customers_fax'), $body);

            $toaddress = $Qcustomers->value('customers_telephone');
            $toaddress = str_replace(' ', '', $toaddress);

            $QSms = $osC_Database->query('insert into :table_sms (Status,Direction, Type,ToAddress,Subject,Body,ChannelID,BodyFormat,FromAddress) values (:status,:direction,:type,:toaddress,:subject,:body,:channelid,:bodyformat,:fromaddress)');
            $QSms->bindTable(':table_sms', 'messages.messages');
            $QSms->bindInt(':direction', '2');
            $QSms->bindInt(':type', '1');
            $QSms->bindInt(':bodyformat', '0');
            $QSms->bindInt(':status', '1');
            $QSms->bindValue(':fromaddress', $_REQUEST['fromaddress']);
            $QSms->bindValue(':toaddress', $toaddress);
            $QSms->bindValue(':channelid', '1001');
            $QSms->bindValue(':body', $body);
            $QSms->bindValue(':subject', $_REQUEST['Subject']);
            $QSms->execute();

            if ($osC_Database->isError()) {
                $response = array('success' => false, 'feedback' => $osC_Database->getError());
                echo $toC_Json->encode($response);
                return;
            }
        }

        $Qcustomers->freeResult();

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

        echo $toC_Json->encode($response);
    }

    function saveRelance()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $QSms = $osC_Database->query('insert into :table_sms (CustomField1,CustomField2,Status,Direction, Type,ToAddress,Subject,Body,ChannelID,BodyFormat,FromAddress) values (:relances_id,:numepoli,:status,:direction,:type,:toaddress,:subject,:body,:channelid,:bodyformat,:fromaddress)');
        $QSms->bindTable(':table_sms', 'messages.messages');
        $QSms->bindInt(':direction', '2');
        $QSms->bindInt(':type', '1');
        $QSms->bindInt(':bodyformat', '0');
        $QSms->bindInt(':status', '1');
        $QSms->bindValue(':relances_id', $_REQUEST['relances_id']);
        $QSms->bindValue(':numepoli', $_REQUEST['numepoli']);
        $QSms->bindValue(':fromaddress', $_REQUEST['fromaddress']);
        $QSms->bindValue(':toaddress', $_REQUEST['toaddress']);
        $QSms->bindValue(':channelid', '1001');
        $QSms->bindValue(':body', $_REQUEST['body']);
        $QSms->bindValue(':subject', $_REQUEST['subject']);
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            return;
        }

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

        echo $toC_Json->encode($response);
    }
}

?>
