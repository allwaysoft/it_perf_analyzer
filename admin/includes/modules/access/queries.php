<?php

  class osC_Access_Queries extends osC_Access {
    var $_module = 'queries',
        $_group = 'reports',
        $_icon = 'page.png',
        $_title,
        $_sort_order = 75;

    function osC_Access_Queries() {
      $this->_title = 'Gestionnaire de Requetes SQL';
    }
  }
?>
