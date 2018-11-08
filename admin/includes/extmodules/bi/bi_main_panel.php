<?php

?>
Toc.bi.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlCategoriesTree = new Toc.CategoriesTreePanel({owner: config.owner, parent: this,showHome : 0,showContentCount : 1,checkPermission : 1,content_type : 'dashboards'});
  config.tab = new Ext.TabPanel({
    activeTab: 0,
    region : 'center',
    defaults: {
    hideMode: 'offsets'
    },
    deferredRender: true
  });

  config.grdCategories = new Toc.bi.reportsGrid({owner: config.owner, mainPanel: this,tab : config.tab});

  config.tab.add(config.grdCategories);
  
  config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlCategoriesTree, config.tab];
  
  Toc.bi.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.bi.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    //console.debug(this.pnlCategoriesTree);
    this.tab.removeAll();
    this.grdCategories = new Toc.bi.reportsGrid({owner: this.owner, mainPanel: this,tab : this.tab});
    this.tab.add(this.grdCategories);
    this.tab.activate(this.grdCategories);
    this.grdCategories.setTitle(this.pnlCategoriesTree.selModel.selNode.text);
    this.grdCategories.refreshGrid(categoryId);
    this.grdCategories.setPermissions(this.pnlCategoriesTree.getCategoryPermissions());
    this.tab.activate(this.grdCategories);
    this.tab.doLayout();
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