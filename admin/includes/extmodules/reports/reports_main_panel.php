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
Toc.reports.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlCategoriesTree = new Toc.CategoriesTreePanel({owner: config.owner, parent: this,showHome : 0,showContentCount : 1,checkPermission : 1,content_type : 'reports'});
  config.grdCategories = new Toc.reports.reportsGrid({owner: config.owner, mainPanel: this});
  
  config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlCategoriesTree, config.grdCategories];
  
  Toc.reports.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdCategories.refreshGrid(categoryId);
    this.grdCategories.setPermissions(this.pnlCategoriesTree.getCategoryPermissions());
  },
  
  getCategoriesTree: function() {
    return this.pnlCategoriesTree;
  },

  getCategoryPath: function(){
        return this.pnlCategoriesTree.getCategoriesPath();
  },

  getCategoryPermissions: function(){
    return this.pnlCategoriesTree.getCategoryPermissions();
  }
});
