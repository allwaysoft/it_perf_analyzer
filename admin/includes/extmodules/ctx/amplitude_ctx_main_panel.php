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

Toc.ctx.AmplitudemainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  config.title = 'AMPLITUDE';

  config.pnlAgencesTree = new Toc.ctx.AgencesTreePanel({owner: config.owner, parent: this});
  config.grdctx = new Toc.ctx.ctxAmplitudeGrid({owner: config.owner, mainPanel: this});
  
  config.pnlAgencesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlAgencesTree, config.grdctx];

  Toc.ctx.AmplitudemainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ctx.AmplitudemainPanel, Ext.Panel, {

  onPnlCategoriesTreeNodeSelectChange: function(categoryId,count) {
    if(count)
    {
       this.grdctx.refreshGrid(categoryId,count);
    }
  },

  refreshTree :function(){
     this.pnlAgencesTree.refresh();
  },

  getCategoriesTree: function() {
     return this.pnlAgencesTree;
  },

  getCategoryPath: function(){
     return this.pnlAgencesTree.getCategoriesPath();
  }
});
