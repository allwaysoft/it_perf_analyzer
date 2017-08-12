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
Toc.images_gallery.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlCategoriesTree = new Toc.CategoriesTreePanel({owner: config.owner, parent: this});
  config.grdImages = new Toc.images_gallery.ImagesGalleryGrid({owner: config.owner, mainPanel: this});
  
  config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlCategoriesTree, config.grdImages];
  
  Toc.images_gallery.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.images_gallery.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdImages.refreshGrid(categoryId);
  },
  
  getCategoriesTree: function() {
    return this.pnlCategoriesTree;
  },

  getCategoryPath: function(){
        return this.pnlCategoriesTree.getCategoriesPath();
  }
});
