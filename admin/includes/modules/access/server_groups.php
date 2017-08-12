<?php


  class osC_Access_Server_groups extends osC_Access {
    var $_module = 'server_groups',
        $_group = 'servers',
        $_icon = 'server_info.png',
        $_title,
        $_sort_order = 1;

    function osC_Access_Server_groups() {
      $this->_title = "Groupes";
    }
  }
?>
