<?php

  class osC_Access_Kibana extends osC_Access {
    var $_module = 'kibana',
        $_group = 'modules',
        $_icon = 'report.png',
        $_title,
        $_sort_order = 2;

    function osC_Access_Kibana() {
      $this->_title = 'Kibana';
    }
  }
?>
