<?php
?>
Toc.server_explorer.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlServerTree = new Toc.server_explorer.TreePanel({owner: config.owner, parent: this,can_edit : false,autoRefresh : true});
  config.explorerPanel = new Ext.form.FormPanel({owner: config.owner, mainPanel: this,can_edit:false,region : 'center',layout : 'fit'});

  config.pnlServerTree.on('selectchange', this.onTreeNodeSelectChange, this);
  
  config.items = [config.pnlServerTree,config.explorerPanel];

  Toc.server_explorer.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.server_explorer.mainPanel, Ext.Panel, {

  onTreeNodeSelectChange: function(node) {
      this.explorerPanel.removeAll();

      Toc.exploreServer(node,this.explorerPanel);
  },
  
  getServerTree: function() {
    return this.pnlServerTree;
  }
});
