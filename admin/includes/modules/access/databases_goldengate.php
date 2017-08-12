<?php


  class osC_Access_Databases_goldengate extends osC_Access {
    var $_module = 'databases_goldengate',
        $_group = 'databases',
        $_icon = 'tabs.gif',
        $_title,
        $_sort_order = 5;

    function osC_Access_Databases_goldengate() {
            
      $this->_title = 'Goldengate';
    }
  }
?>
