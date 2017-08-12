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

Toc.cartes.AmplitudemainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  config.title = 'Cartes AMPLITUDE';

  config.pnlAgencesTree = new Toc.cartes.AgencesTreePanel({owner: config.owner, parent: this});
  config.grdcartes = new Toc.cartes.cartesAmplitudeGrid({owner: config.owner, mainPanel: this});
  
  config.pnlAgencesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlAgencesTree, config.grdcartes];

  Toc.cartes.AmplitudemainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.cartes.AmplitudemainPanel, Ext.Panel, {

  onPnlCategoriesTreeNodeSelectChange: function(categoryId,count) {
    if(count)
    {
       this.grdcartes.refreshGrid(categoryId,count);
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
