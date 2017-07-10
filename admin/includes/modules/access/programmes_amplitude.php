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

  class osC_Access_Programmes_Amplitude extends osC_Access {
    var $_module = 'programmes_amplitude',
        $_group = 'delta',
        $_icon = 'form.gif',
        $_title,
        $_sort_order = 1300;

    function osC_Access_Programmes_Amplitude() {
      $this->_title = 'Programmes Amplitude';
    }
  }
?>
