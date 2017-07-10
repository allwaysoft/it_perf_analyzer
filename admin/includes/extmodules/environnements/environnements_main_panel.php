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
Toc.environnements.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.grdCategories = new Toc.environnements.environnementsGrid({owner: config.owner, mainPanel: this});
  
  config.items = [config.grdCategories];
  
  Toc.environnements.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.environnements.mainPanel, Ext.Panel, {
});
