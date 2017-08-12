<?php

?>
Toc.servers.mainPanel = function(config) {
  config = config || {};

  config.layout = 'border';
  config.border = false;

  config.pnlRolesTree = new Toc.servers.GroupTreePanel({owner: config.owner, parent: this});
  config.grdServers = new Toc.ServersGrid({owner: config.owner, mainPanel: this});

  config.pnlRolesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);

  config.items = [config.pnlRolesTree,config.grdServers];

  Toc.servers.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.servers.mainPanel, Ext.Panel, {

  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdServers.refreshGrid(categoryId);
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
