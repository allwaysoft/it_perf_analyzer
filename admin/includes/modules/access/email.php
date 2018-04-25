<?php

  class osC_Access_Email extends osC_Access {
    var $_module = 'email',
        $_group = 'communication',
        $_icon = 'email.png',
        $_title,
        $_sort_order = 1000;

    function osC_Access_Email() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_email_title');
    }
  }
?>
