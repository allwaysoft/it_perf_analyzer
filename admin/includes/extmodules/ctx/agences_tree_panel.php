<?php
/*
  $Id: roles_tree_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.ctx.AgencesTreePanel = function(config) {
  config = config || {};
  
  config.region = 'west';
  config.border = false;
  config.autoScroll = true;
  config.containerScroll = true;
  config.split = true;
  config.width = 300;
  config.enableDD = true;
  config.rootVisible = true;
  
  config.root = new Ext.tree.AsyncTreeNode({
    text: 'Agences',
    icon : 'templates/default/images/icons/16x16/feature_products.png',
    draggable: false,
    id: '0',
    expanded: true
  });
  config.currentCategoryId = -1;
    
  config.loader = new Ext.tree.TreeLoader({
    dataUrl: Toc.CONF.CONN_URL,
    preloadChildren: true,
    baseParams: {
      module: 'roles',
      action: 'load_agences_tree_delta',
      src : config.src || '',
      db_user : config.db_user  || '',
      db_pass : config.db_pass  || '',
      db_host : config.db_host  || '',
      db_sid : config.db_sid || ''
    },
    listeners: {
      load: function() {
        this.expandAll();
        var category = this.currentCategoryId || -1;
        var count = this.nodeHash[category].attributes.count;
        this.setCategoryId(category,count);
      },
      scope: this
    }
  });

  config.tbar = [{
    text: TocLanguage.btnRefresh,
    iconCls: 'refresh',
    handler: this.refresh,
    scope: this
  }];

  config.listeners = {
    "click": this.onCategoryNodeClick
  };
  
  this.addEvents({'selectchange' : true});
  
  Toc.ctx.AgencesTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ctx.AgencesTreePanel, Ext.tree.TreePanel, {

  setCategoryId: function(categoryId,count) {
    var currentNode = this.getNodeById(categoryId);
    currentNode = currentNode || this.getRootNode();
    currentNode.select();
    this.currentCategoryId = currentNode.id;

    this.fireEvent('selectchange', this.currentCategoryId,count);
  },

  onCategoryNodeClick: function (node) {
    node.expand();
    this.setCategoryId(node.id,node.attributes.count);
  },

  getCategoriesPath: function(node) {
    var cpath = [];
    node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;

    while (node.id > 0) {
      cpath.push(node.id);
      node = node.parentNode;
    }

    return cpath.reverse().join('_');
  },

  refresh: function() {
    this.root.reload();
  }
});