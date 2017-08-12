<?php
/*
  $Id: homepage_info.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Homepage_info extends osC_Access {
    var $_module = 'homepage_info',
        $_group = 'articles',
        $_icon = 'articles.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Homepage_info() {      
      $this->_title = 'Deconnexion HotSpot';
    }
  }
?>