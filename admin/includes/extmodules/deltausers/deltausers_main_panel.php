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
Toc.deltausers.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlRolesTree = new Toc.deltausers.RolesTreePanel({owner: config.owner, parent: this});
  config.grddeltausers = new Toc.deltausers.deltausersGrid({owner: config.owner, mainPanel: this});
  
  config.pnlRolesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlRolesTree, config.grddeltausers];
  
  Toc.deltausers.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.deltausers.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId,count) {
    if(count)
    {
       this.grddeltausers.refreshGrid(categoryId,count);
    }
  },

  refreshTree :function(){
     this.pnlRolesTree.refresh();
  },
  
  getCategoriesTree: function() {
    return this.pnlRolesTree;
  },

  getCategoryPath: function(){
        return this.pnlRolesTree.getCategoriesPath();
  }
});
