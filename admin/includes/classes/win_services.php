<?php

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
