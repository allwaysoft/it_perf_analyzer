<?php

  class toC_Module_Loader {
  
    function parse() {
      global $osC_Language;
      
      
      if (isset($_SESSION['admin'])) {
        $access = osC_Access::getLevels();
        ksort($access);
            
        $found = false;
        $module = null;
        foreach ( $access as $group => $links ) {      
          foreach ( $links as $link ) {
            if ( is_array($link['subgroups']) && !empty($link['subgroups']) ) {
              foreach ( $link['subgroups'] as $subgroup ) {
                if($_REQUEST['module'] == $subgroup['identifier']) {
                  $found = true;
                  $module = $link['module'];
                  break;
                }
              }
            } else {
              if($_REQUEST['module'] == ($link['module'] . '-win')) {
                $found = true;
                $module = $link['module'];
                break;
              }
            }
          }
        }       
        
        if ($found === true) {
          $osC_Language->loadIniFile($module . '.php');
          
          if (file_exists('includes/extmodules/' . $module . '/main.php')) {
            include('includes/extmodules/' . $module . '/main.php');
            exit;
          } else {
            echo "{'success': false}";
          }
        }
      }else{
        echo "{'success': false, 'error': 'session_timeout'}";
      }
    }
  }
?>
