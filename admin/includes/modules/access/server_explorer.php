<?php

  class osC_Access_Server_explorer extends osC_Access {
    var $_module = 'server_explorer',
        $_group = 'servers',
        $_icon = 'server_info.png',
        $_title,
        $_sort_order = 4;

    function osC_Access_Server_explorer() {
            
      $this->_title = 'Server Explorer';
    }
  }
?>
