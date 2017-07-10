<?php
/*
  $Id: databases_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.fsGrid = function(config) {
  var that = this;
  config = config || {};
  config.loadMask = true;
  config.title = 'FS';
  config.border = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.listeners = {
   'rowclick' : this.onRowClick
  };

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'servers',
      action: 'list_fs',
      user:config.server_user,
      pass:config.server_pass,
      port:config.server_port,
      host:config.host
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'mount'
    }, [
      'mount',
      'fs',
      'typ',
      {name:'size',type:'int'},
      {name:'used',type:'int'},
      {name:'dispo',type:'int'},
      {name:'pct_used',type:'int'}
    ]),
    autoLoad: false
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'mount', header: 'Nom', dataIndex: 'mount', sortable: true},
    { header: '%', align: 'center', dataIndex: 'pct_used',renderer:Toc.content.ContentManager.renderProgress,sortable: true},
    { header: 'Taille (MB)', align: 'center', dataIndex: 'size',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Libre (MB)', align: 'center', dataIndex: 'dispo',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Utilise (MB)', align: 'center', dataIndex: 'used',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { id: 'typ', header: 'Type', dataIndex: 'typ',align: 'center', sortable: true},
    { id: 'fs', header: 'Filesystem', dataIndex: 'fs', sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'fs';
  config.stripeRows = true;

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.tbar = [
    { 
      text: '',
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
  
  var thisObj = this;

  this.addEvents({'selectchange' : true});
  Toc.fsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.fsGrid, Ext.grid.GridPanel, {
  onEdit: function(record) {
    var dlg = new Toc.Dirbrowser();
    dlg.setTitle(record.get("mount"));

    var config = {
       server_user:this.server_user,
       server_pass:this.server_pass,
       server_port:this.server_port,
       host:this.host,
       mount:record.get("mount")
    };
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(config,this.owner);
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },

  refreshGrid: function (categoriesId) {
    var permissions = this.mainPanel.getCategoryPermissions();
    var store = this.getStore();

    store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;
    store.baseParams['categories_id'] = categoriesId;
    this.categoriesId = categoriesId;
    store.reload();
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  },

  onRowClick : function(grid,index,obj) {
    var item = grid.getStore().getAt(index);
    this.fireEvent('selectchange',item);
  }
});