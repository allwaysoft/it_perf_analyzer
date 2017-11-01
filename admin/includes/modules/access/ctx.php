<?php

  class osC_Access_Ctx extends osC_Access {
    var $_module = 'ctx',
        $_group = 'delta',
        $_icon = 'log.png',
        $_title,
        $_sort_order = 400;

    function osC_Access_Ctx() {
      $this->_title = 'Contentieux';
    }
  }
?>
