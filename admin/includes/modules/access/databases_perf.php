<?php

  class osC_Access_Databases_perf extends osC_Access {
    var $_module = 'databases_perf',
        $_group = 'databases',
        $_icon = 'polls.png',
        $_title,
        $_sort_order = 3;

    function osC_Access_Databases_perf() {
            
      $this->_title = 'Dashboard Databases';
    }
  }
?>
