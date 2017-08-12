<?php

?>
Toc.databases.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlRolesTree = new Toc.databases.GroupTreePanel({owner: config.owner, parent: this});
  config.grdDatabases = new Toc.DatabasesGrid({owner: config.owner, mainPanel: this});
  
  config.pnlRolesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlRolesTree, config.grdDatabases];
  
  Toc.databases.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdDatabases.refreshGrid(categoryId);
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
