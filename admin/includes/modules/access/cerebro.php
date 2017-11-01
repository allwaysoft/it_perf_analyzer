<?php

  class osC_Access_Cerebro extends osC_Access {
    var $_module = 'cerebro',
        $_group = 'modules',
        $_icon = 'export.png',
        $_title,
        $_sort_order = 1;

    function osC_Access_Cerebro() {
      $this->_title = 'Cerebro';
    }
  }
?>
