<?php
?>
Toc.database_explorer.mainPanel = function(config) {

  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlDatabaseTree = new Toc.database_explorer.TreePanel({owner: config.owner, parent: this,can_edit : false,autoRefresh : true});
  config.explorerPanel = new Ext.form.FormPanel({owner: config.owner, mainPanel: this,can_edit:false,region : 'center',layout : 'fit'});

  config.pnlDatabaseTree.on('selectchange', this.onTreeNodeSelectChange, this);
  
  config.items = [config.pnlDatabaseTree,config.explorerPanel];

  Toc.database_explorer.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.database_explorer.mainPanel, Ext.Panel, {

  onTreeNodeSelectChange: function(node) {

      this.explorerPanel.removeAll();

      if(this.user && this.user != 'admin')
      {
        Toc.exploreDatabase(node,this.explorerPanel,'security');
      }
      else
      {
        Toc.exploreDatabase(node,this.explorerPanel);
      }
  },
  
  getDatabaseTree: function() {
    return this.pnlDatabaseTree;
  }
});
