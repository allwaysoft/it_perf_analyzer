<?php

  class osC_Access_Datasources extends osC_Access {
    var $_module = 'datasources',
        $_group = 'reports',
        $_icon = 'people.png',
        $_title,
        $_sort_order = 50;

    function osC_Access_Datasources() {
      $this->_title = 'Sources de Données';
    }
  }
?>
