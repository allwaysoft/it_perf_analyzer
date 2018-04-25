<?php


  class osC_Access_Servers extends osC_Access {
    var $_module = 'servers',
        $_group = 'servers',
        $_icon = 'people.png',
        $_title,
        $_sort_order = 2;

    function osC_Access_Servers() {
        global $osC_Language;

        $this->_title = $osC_Language->get('servers');
    }
  }
?>