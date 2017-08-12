<?php
/*
  $Id: newsletters.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class osC_Win_service_Admin
{
    function startService($name)
    {
        global $toC_Json;

        $answer = win32_start_service($name);

        if ($answer == WIN32_NO_ERROR) {
            $feedback = 'success';
            $response = array('success' => true, 'feedback' => $feedback);
        }
        else
        {
            $feedback = $answer;
            $response = array('success' => false, 'feedback' => $feedback);
        }

        return $response;
    }
    
    function stopService($name)
    {
        global $toC_Json;

        $answer = win32_stop_service($name);

        if ($answer == WIN32_NO_ERROR) {
            $feedback = 'success';
            $response = array('success' => true, 'feedback' => $feedback);
        }
        else
        {
            $feedback = $answer;
            $response = array('success' => false, 'feedback' => $feedback);
        }
        
        return $response;
    }
}

?>
