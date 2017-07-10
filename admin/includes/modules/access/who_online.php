<?php
/*
  $Id: whos_online.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Who_Online extends osC_Access {
    var $_module = 'who_online',
        $_group = 'tools',
        $_icon = 'whos_online.png',
        $_title,
        $_sort_order = 1300;

    function osC_Access_Who_Online() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_who_online_title');
    }
  }
?>
