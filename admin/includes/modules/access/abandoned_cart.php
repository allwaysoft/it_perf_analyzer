<?php

  class osC_Access_Abandoned_cart extends osC_Access {
    var $_module = 'recorvered_cart',
        $_group = 'customers',
        $_icon = 'abandoned_cart.png',
        $_title,
        $_sort_order = 1100;

    function osC_Access_Abandoned_cart() {
      global $osC_Language;
            
      $this->_title = $osC_Language->get('access_abandoned_cart_title');
    }
  }
?>
