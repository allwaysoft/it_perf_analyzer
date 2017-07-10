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

  class osC_Access_Databases_referentiel extends osC_Access {
    var $_module = 'databases_referentiel',
        $_group = 'databases',
        $_icon = 'tabs.gif',
        $_title,
        $_sort_order = 1;

    function osC_Access_Databases_referentiel() {
            
      $this->_title = 'Referentiel';
    }
  }
?>
