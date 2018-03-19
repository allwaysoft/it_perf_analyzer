<?php

  class osC_Access_Bi extends osC_Access {
    var $_module = 'bi',
        $_group = 'reports',
        $_icon = 'page.png',
        $_title,
        $_sort_order = 150;

    function osC_Access_Bi() {
      $this->_title = 'Tableaux de Bord';
    }
  }
?>
