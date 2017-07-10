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
require('includes/classes/win_services.php');
class toC_Json_Win_services
{
    function listServices()
    {
        global $toC_Json;

        $total = 0;
        $records = array();

        $smsService = win32_query_service_status('AxMmSvc');
        if ($smsService != false) {
            if ($smsService['CurrentState'] == WIN32_SERVICE_RUNNING) {
                $smsService['CurrentState'] = '1';
            }
            else
            {
                $smsService['CurrentState'] = '0';
            }

            $records[] = array(
                'win_services_id' => '1',
                'ServiceName' => 'AxMmSvc',
                'ServiceDescription' => 'SMS - Email Service',
                'ServiceType' => $smsService['ServiceType'],
                'CurrentState' => $smsService['CurrentState'],
                'ControlsAccepted' => $smsService['Win32ExitCode'],
                'ServiceSpecificExitCode' => $smsService['ServiceSpecificExitCode'],
                'CheckPoint' => $smsService['CheckPoint'],
                'WaitHint' => $smsService['WaitHint'],
                'ProcessId' => $smsService['ProcessId'],
                'ServiceFlags' => $smsService['ServiceFlags']
            );

            $total = $total + 1;
        }

        $smsService = win32_query_service_status('eBankNotificationService');
        if (!is_int($smsService)) {
            if ($smsService['CurrentState'] == WIN32_SERVICE_RUNNING) {
                $smsService['CurrentState'] = '1';
            }
            else
            {
                $smsService['CurrentState'] = '0';
            }

            $records[] = array(
                'win_services_id' => '2',
                'ServiceName' => 'eBankNotificationService',
                'ServiceDescription' => 'Service de Notifications automatiques',
                'ServiceType' => $smsService['ServiceType'],
                'CurrentState' => $smsService['CurrentState'],
                'ControlsAccepted' => $smsService['Win32ExitCode'],
                'ServiceSpecificExitCode' => $smsService['ServiceSpecificExitCode'],
                'CheckPoint' => $smsService['CheckPoint'],
                'WaitHint' => $smsService['WaitHint'],
                'ProcessId' => $smsService['ProcessId'],
                'ServiceFlags' => $smsService['ServiceFlags']
            );

            $total = $total + 1;
        }

        $smsService = win32_query_service_status('Schedule');
        if ($smsService != false) {
            if ($smsService['CurrentState'] == WIN32_SERVICE_RUNNING) {
                $smsService['CurrentState'] = '1';
            }
            else
            {
                $smsService['CurrentState'] = '0';
            }

            $records[] = array(
                'win_services_id' => '3',
                'ServiceName' => 'Schedule',
                'ServiceDescription' => 'Gestionnaire de Taches',
                'ServiceType' => $smsService['ServiceType'],
                'CurrentState' => $smsService['CurrentState'],
                'ControlsAccepted' => $smsService['Win32ExitCode'],
                'ServiceSpecificExitCode' => $smsService['ServiceSpecificExitCode'],
                'CheckPoint' => $smsService['CheckPoint'],
                'WaitHint' => $smsService['WaitHint'],
                'ProcessId' => $smsService['ProcessId'],
                'ServiceFlags' => $smsService['ServiceFlags']
            );

            $total = $total + 1;
        }

        $response = array(EXT_JSON_READER_TOTAL => $total,
                          EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function setStatus()
    {
        global $toC_Json;

        $flag = $_REQUEST['flag'];
        $win_services_id = $_REQUEST['win_services_id'];
        $flag_name = $_REQUEST['flag_name'];
        $name = '';

        switch ($win_services_id) {
            case 1:
                $name = 'AxMmSvc';
                break;
            case 2:
                $name = 'eBankNotificationService';
                break;
            case 3:
                $name = 'Schedule';
                break;
        }

        $feedback = 'nom de service invalide';
        $response = array('success' => true, 'feedback' => $feedback);

        if ($name != '') {
            if ($flag) {
                $response = osC_Win_service_Admin::startService($name);
            }
            else
            {
                $response = osC_Win_service_Admin::stopService($name);
            }
        }

        echo $toC_Json->encode($response);
    }

    function createService()
    {
        global $toC_Json;

        $filename = 'C:\shop\ebank_notifications_services\WindowsService\bin\Debug\EbankNotificationService.exe';

        if (file_exists($filename)) {
            $return = win32_create_service(array(
                                                'service' => 'eBankNotificationService',
                                                'display' => 'Service de Notifications automatiques',
                                                'path' => $filename
                                           ));

            if (is_integer($return) && $return != '1073') {
                $response = array('success' => false, 'feedback' => 'Impossible d installer ce service : ' . $return);
            }
            else
            {
                $response = array('success' => true, 'feedback' => 'Service installe avec succes');
            }
        } else {
            $response = array('success' => false, 'feedback' => "The file $filename does not exist");
        }

        echo $toC_Json->encode($response);
    }

    function deleteService()
    {
        global $toC_Json;
        $name = $_REQUEST['name'];
        if ($name != 'AxMmSvc') {
            $return = win32_delete_service($name);

            if (is_integer($return) && $return != '0') {
                $response = array('success' => false, 'feedback' => 'Impossible de supprimer ce service : ' . $return);
            }
            else
            {
                $response = array('success' => true, 'feedback' => 'Service supprime avec succes');
            }
        }
        else
        {
            $response = array('success' => false, 'feedback' => 'Ce service est requis pour le fonctionnement du systeme');
        }

        echo $toC_Json->encode($response);
    }
}

?>
