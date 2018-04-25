<?php

  class osC_Access_Roles extends osC_Access {
    var $_module = 'roles',
        $_group = 'tools',
        $_icon = 'people.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Roles() {      
      $this->_title = 'Profils';
    }
  }
?>