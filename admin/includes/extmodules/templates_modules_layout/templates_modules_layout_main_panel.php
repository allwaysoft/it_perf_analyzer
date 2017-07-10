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
Toc.templates_modules_layout.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlCategoriesTree = new Toc.CategoriesTreePanel({owner: config.owner, parent: this,filter : '<?php echo DEFAULT_TEMPLATE; ?>'});
  config.grdTemplatesModulesLayout = new Toc.templates_modules_layout.TemplatesModulesLayoutGrid({owner: config.owner, mainPanel: this,set: config.set});
  
  config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlCategoriesTree, config.grdTemplatesModulesLayout];
  
  Toc.templates_modules_layout.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.templates_modules_layout.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdTemplatesModulesLayout.refreshGrid(categoryId);
  },
  
  getCategoriesTree: function() {
    return this.pnlCategoriesTree;
  },

  getCategoryPath: function(){
        return this.pnlCategoriesTree.getCategoriesPath();
  },

  getCategoryPermissions: function(){
    return this.pnlCategoriesTree.getCategoryPermissions();
  },

  setFilter: function(filter){
    this.pnlCategoriesTree.setFilter(filter);
    this.pnlCategoriesTree.refresh();
  }
});
