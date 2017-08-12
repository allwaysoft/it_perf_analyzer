<?php


  class osC_Access_Database_groups extends osC_Access {
    var $_module = 'database_groups',
        $_group = 'databases',
        $_icon = 'database.png',
        $_title,
        $_sort_order = 1;

    function osC_Access_Database_groups() {
      $this->_title = "Groupes Databases";
    }
  }
?>
