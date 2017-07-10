<?php

  class osC_Access_Databases_dashboard extends osC_Access {
    var $_module = 'databases_dashboard',
        $_group = 'databases',
        $_icon = 'dashboard.png',
        $_title,
        $_sort_order = 2;

    function osC_Access_Databases_dashboard() {
            
      $this->_title = 'Tableau de Bord';
    }
  }
?>
