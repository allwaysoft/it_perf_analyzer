<?php
/*
  $Id: index.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

<?php
global $current_category_id;
    if ($current_category_id == 0) {        
        echo $osC_Language->get('index_text');
    }

?>