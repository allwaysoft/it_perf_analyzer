<?php

  class osC_Access_Databases_referentiel extends osC_Access {
    var $_module = 'databases_referentiel',
        $_group = 'databases',
        $_icon = 'tabs.gif',
        $_title,
        $_sort_order = 100;

    function osC_Access_Databases_referentiel() {
            
      $this->_title = 'Referentiel';
    }
  }
?>
