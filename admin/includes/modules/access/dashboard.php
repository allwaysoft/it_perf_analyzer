<?php

  class osC_Access_Dashboard extends osC_Access {
    var $_module = 'dashboard',
        $_group = 'tools',
        $_icon = 'info.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Dashboard() {
      global $osC_Language;
      
      $this->_title = $osC_Language->get('access_dashboard_title');
    }
  }
?>
