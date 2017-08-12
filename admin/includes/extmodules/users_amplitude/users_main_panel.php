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
Toc.users.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlRolesTree = new Toc.users.RolesTreePanel({owner: config.owner, parent: this});
  config.grdUsers = new Toc.users.UsersGrid({owner: config.owner, mainPanel: this});
  
  config.pnlRolesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlRolesTree, config.grdUsers];
  
  Toc.users.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.users.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdUsers.refreshGrid(categoryId);
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
