<?php
/*
  $Id: watermark.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Watermark extends osC_Access {
    var $_module = 'watermark',
        $_group = 'tools',
        $_icon = 'watermark.png',
        $_title,
        $_sort_order = 1400;

    function osC_Access_Watermark() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_watermark_title');
    }
  }
?>
