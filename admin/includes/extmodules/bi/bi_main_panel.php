<?php

?>
Toc.bi.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlCategoriesTree = new Toc.CategoriesTreePanel({owner: config.owner, parent: this,showHome : 0,showContentCount : 1,checkPermission : 1,content_type : 'dashboards'});
  config.grdCategories = new Toc.bi.reportsGrid({owner: config.owner, mainPanel: this});
  
  config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlCategoriesTree, config.grdCategories];
  
  Toc.bi.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.bi.mainPanel, Ext.Panel, {
  
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
