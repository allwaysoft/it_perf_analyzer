<?php
?>
Toc.categories.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlCategoriesTree = new Toc.CategoriesTreePanel({owner: config.owner, parent: this,cp:1,content_type : 'pages'});
  config.grdCategories = new Toc.categories.CategoriesGrid({owner: config.owner, mainPanel: this});
  
  config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlCategoriesTree, config.grdCategories];
  
  Toc.categories.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.categories.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
this.grdCategories.setPermissions(this.pnlCategoriesTree.getCategoryPermissions());
    this.grdCategories.refreshGrid(categoryId);
  },
  
  getCategoriesTree: function() {
    return this.pnlCategoriesTree;
  }
});
