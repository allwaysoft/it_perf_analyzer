<?php
/*
  $Id: administrators_log.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Fs_manager extends osC_Access {
    var $_module = 'fs_manager',
        $_group = 'articles',
        $_icon = 'people.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Fs_manager() {
        $this->_title = 'FS Explorer';
    }
  }
?>
