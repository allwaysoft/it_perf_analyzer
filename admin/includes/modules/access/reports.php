<?php

  class osC_Access_Reports extends osC_Access {
    var $_module = 'reports',
        $_group = 'reports',
        $_icon = 'page.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Reports() {
      $this->_title = 'Reporting';
    }
  }
?>
