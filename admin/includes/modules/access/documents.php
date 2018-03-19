<?php

  class osC_Access_Documents extends osC_Access {
    var $_module = 'documents',
        $_group = 'articles',
        $_icon = 'page.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Documents() {      
      $this->_title = 'Documents';
    }
  }
?>
