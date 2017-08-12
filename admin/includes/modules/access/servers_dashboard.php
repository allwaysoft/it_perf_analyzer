<?php

  class osC_Access_Servers_dashboard extends osC_Access {
    var $_module = 'servers_dashboard',
        $_group = 'servers',
        $_icon = 'dashboard.png',
        $_title,
        $_sort_order = 2;

    function osC_Access_Servers_dashboard() {
            
      $this->_title = 'Dashboard Servers';
    }
  }
?>
