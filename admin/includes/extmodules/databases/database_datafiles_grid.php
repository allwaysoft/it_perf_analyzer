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

Toc.databases.datafilesGrid = function(config) {
  var that = this;
  config = config || {};
  //config.region = 'center';
  config.loadMask = true;
  config.title = 'Datafiles';
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  config.listeners = {
        activate : function(panel) {
           //this.getStore().load();
        },
        scope: this
    };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'servers',
      action: 'list_datafiles',
      db_user:config.db_user,
      db_pass:config.db_pass,
      db_port:config.db_port,
      db_host:config.host,
      db_sid:config.sid
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'file_id'
    }, [
      'file_id',
      'file_name',
      'tablespace_name',
      'status',
      'autoextensible',
      {name:'size',type:'int'},
      {name:'blocks',type:'int'},
      {name:'maxsize',type:'int'},
      {name:'increment_by',type:'int'},
      {name:'maxblocks',type:'int'},
      {name:'maxextend',type:'int'},
      {name:'total_pct_used',type:'int'}
    ]),
    autoLoad: false
  });

  renderStatus = function(status) {
    if(status == 'AVAILABLE') {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };

  renderAuto = function(status) {
    if(status == 'YES' || status == 'ON') {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-move-record', qtip: TocLanguage.tipMove}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'file_name', header: 'Nom', dataIndex: 'file_name', sortable: true},
    { id: 'tablespace_name', header: 'Tablespace', dataIndex: 'tablespace_name', sortable: true},
    { header: '%', align: 'center', dataIndex: 'total_pct_used',renderer:Toc.content.ContentManager.renderProgress,sortable: true},
    { header: 'Taille (MB)', align: 'center', dataIndex: 'size',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Inc (MB)', align: 'center', dataIndex: 'increment_by',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Auto Ext', align: 'center', dataIndex: 'autoextensible',sortable: true,renderer:renderAuto},
    { header: 'Max (MB)', align: 'center', dataIndex: 'maxsize',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { id: 'status',header: 'Status', align: 'center', dataIndex: 'status',renderer:renderStatus},
    config.rowActions
  ]);
  config.autoExpandColumn = 'file_name';
  config.stripeRows = true;

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.tbar = [
    {
      text: '',
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    },
    '-', 
    {
      text: '',
      iconCls: 'remove',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
    { 
      text: '',
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    },
    '->',
    config.txtSearch,
    ' ',
    {
      text: '',
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  var thisObj = this;
        
  Toc.databases.datafilesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.datafilesGrid, Ext.grid.GridPanel, {

  onAdd: function() {
    var dlg = this.owner.createDatabasesDialog();
    var path = this.owner.getCategoryPath();
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(null, path,null);
  },

  setPermissions: function(permissions) {
    this.bottomToolbar.items.items[0].disable();
    this.bottomToolbar.items.items[2].disable();

    this.topToolbar.items.items[0].disable();
    this.topToolbar.items.items[2].disable();
    if(permissions)
    {
        if(permissions.can_write == 1 || permissions.can_modify == '')
        {
            this.bottomToolbar.items.items[0].enable();
            this.topToolbar.items.items[0].enable();
        }
        if(permissions.can_modify == '')
        {
            this.bottomToolbar.items.items[2].enable();
            this.topToolbar.items.items[2].enable();
        }
    }
  },

  onEdit: function(record) {
    var dlg = this.owner.createDatabasesDialog();
    var path = this.owner.getCategoryPath();
    dlg.setTitle(record.get("content_name"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.json,path);
  },

  onMove: function(record) {
    var dlg = this.owner.createMoveDatafileDialog();
    dlg.setTitle("Move datafile " + record.get("file_name"));

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(record.json,this.owner);
  },
  
  onDelete: function(record) {
    var DatabasesId = record.get('databases_id');
    
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'databases',
                action: 'delete_database',
                databases_id: DatabasesId
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, this);
  },
  
  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      var batch = keys.join(',');

      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'databases',
                action: 'delete_databases',
                batch: batch
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
                } else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, 
        this
      );
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },

  refreshGrid: function (tablespace_name) {
    var store = this.getStore();

    store.baseParams['tbs'] = tablespace_name;
    store.load();
  },

  onSearch: function() {
    var categoriesId = this.cboCategories.getValue() || null;
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();

    store.baseParams['current_category_id'] = categoriesId;
    store.baseParams['search'] = filter;
    store.reload();
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-move-record':
        this.onMove(record);
        break;
    }
  },

  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var action = false;

    if (row !== false) {
      var btn = e.getTarget(".img-button");

      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
      }
      else
      {
         var sel = this.getStore().getAt(row);
      }

      if (action != 'img-button') {
        var file_id = this.getStore().getAt(row).get('file_id');
        var module = 'setDatafilestatus';

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 'ON' : 'OFF';
            this.onAction(module, file_id, flag);
            break;
        }
      }
    }
  },
  
  onAction: function(action, file_id, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'servers',
        action: action,
        file_id: file_id,
        flag: flag,
        db_user:this.db_user,
        db_pass:this.db_pass,
        db_port:this.db_port,
        db_host:this.host,
        db_sid:this.sid
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(file_id).set('autoextensible', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }  
});