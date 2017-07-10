<?php
/*
  $Id: abandoned_cart.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Databases_space extends osC_Access {
    var $_module = 'databases_space',
        $_group = 'databases',
        $_icon = 'database_save.png',
        $_title,
        $_sort_order = 4;

    function osC_Access_Databases_space() {
            
      $this->_title = 'Espaces';
    }
  }
?>
