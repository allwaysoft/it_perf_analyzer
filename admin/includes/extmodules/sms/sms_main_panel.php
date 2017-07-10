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
Toc.sms.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlStatusTree = new Toc.sms.StatusTreePanel({owner: config.owner, parent: this});
  config.grdsms = new Toc.sms.smsGrid({owner: config.owner, mainPanel: this});
  
  config.pnlStatusTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);

  var thisObj = this;

  config.task = {
        run: function () {
            thisObj.refreshTree();
        },
        interval: 5000 //5 second
    };

  config.runner = new Ext.util.TaskRunner();
  
  config.items = [config.pnlStatusTree, config.grdsms];
  
  Toc.sms.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.sms.mainPanel, Ext.Panel, {

  start: function () {
        this.runner.start(this.task);
    },

  stop: function () {
        this.runner.stop(this.task);
    },
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId,count) {
    if(count)
    {
       this.grdsms.refreshGrid(categoryId,count);
    }
  },

  refreshTree :function(){
     this.pnlStatusTree.refresh();
  },
  
  getCategoriesTree: function() {
    return this.pnlStatusTree;
  },

  getCategoryPath: function(){
    return this.pnlStatusTree.getCategoriesPath();
  }
});
