<?php

  class osC_Access_Articles extends osC_Access {
    var $_module = 'articles',
        $_group = 'articles',
        $_icon = 'page.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Articles() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_articles_title');
    }
  }
?>
