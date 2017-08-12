<?php

  class osC_Access_Database_explorer extends osC_Access {
    var $_module = 'database_explorer',
        $_group = 'databases',
        $_icon = 'database_info.jpg',
        $_title,
        $_sort_order = 4;

    function osC_Access_Database_explorer() {
      $this->_title = "Database Explorer";
    }
  }
?>
