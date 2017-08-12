<?php

  class osC_Access_Databases_space extends osC_Access {
    var $_module = 'databases_space',
        $_group = 'databases',
        $_icon = 'database_save.png',
        $_title,
        $_sort_order = 40;

    function osC_Access_Databases_space() {
            
      $this->_title = 'Espaces';
    }
  }
?>
