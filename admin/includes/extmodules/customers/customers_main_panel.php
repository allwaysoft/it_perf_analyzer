<?php
/*
  $Id: customers_main_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.mainPanel = function(config) {
  config = config || {};
   
  config.layout = 'border';
  
  config.pnlGroupTree = new Toc.customers.GroupTreePanel({owner: config.owner, parent: this});
  config.grdCustomers = new Toc.customers.CustomersGrid({owner: config.owner,mainPanel: this});
  
  config.grdCustomers.on('selectchange', this.onGrdCustomersSelectChange, this);
  config.grdCustomers.getStore().on('load', this.onGrdCustomersLoad, this);
  config.pnlGroupTree.on('selectchange', this.onPnlGroupTreeNodeSelectChange, this);
  
  config.items = [config.pnlGroupTree,config.grdCustomers];    
    
  Toc.customers.mainPanel.superclass.constructor.call(this, config);    
};

Ext.extend(Toc.customers.mainPanel, Ext.Panel, {

  onGrdCustomersLoad: function() {
    if (this.grdCustomers.getStore().getCount() > 0) {
      this.grdCustomers.getSelectionModel().selectFirstRow();
      record = this.grdCustomers.getStore().getAt(0);
      
      this.onGrdCustomersSelectChange(record);
    } else {
    }
  },
  onPnlGroupTreeNodeSelectChange: function(categoryId) {
    this.grdCustomers.refreshGrid(categoryId);
  },
  
  getGroupTree: function() {
    return this.pnlGroupTree;
  },

  onGrdCustomersSelectChange: function(record) {
  }
});