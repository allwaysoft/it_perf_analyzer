<?php
/*
  $Id: categories_main_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.tbsexplorer.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  
  config.grdFsManager = new Toc.tbsexplorer.tbsexplorerGrid({owner: config.owner, mainPanel: this});
  
  config.items = [config.grdFsManager];
  
  Toc.tbsexplorer.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tbsexplorer.mainPanel, Ext.Panel, {

getCategoryPath: function(){
return '-1';
}

});
