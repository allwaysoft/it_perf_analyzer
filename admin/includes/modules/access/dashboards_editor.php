<?php

  class osC_Access_Dashboards_editor extends osC_Access {
    var $_module = 'dashboards_editor',
        $_group = 'reports',
        $_icon = 'products.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Dashboards_editor() {
        global $osC_Language;

        $this->_title = $osC_Language->get('dashboards_editor');
    }
  }
?>
