<?php
/*
  $Id: tbsexplorer_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tbsexplorer.tbsexplorerGrid = function(config) {
  var that = this;
  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'databases',
      action: 'list_tbsusage'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'databases_id'
    }, [
      'snaps_id',
      'databases_id',
      'label',
      {name:'size',type:'int'},
      {name:'used',type:'int'},
      {name:'dispo',type:'int'},
      {name:'pct_used',type:'int'},
      'start_date',
      'end_date',
      'host',
      'port',
      'user',
      'pass',
      'sid',
      'server_user',
      'server_pass',
      'server_port',
      'server_typ'
    ]),
    autoLoad: true
  });

  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  renderPublish = function(status) {
    var currentRow = that.store.data.items[0];
    var json = currentRow.json;
    switch(json.can_publish)
    {
        case undefined:
        case 'undefined':
        case '0':
        case 0:
        if(status == 1) {
           return '<img src="images/icon_status_green.gif"/>&nbsp;<img src="images/icon_status_red_light.gif"/>';
        }else {
           return '<img src="images/icon_status_green_light.gif"/>&nbsp;<img src="images/icon_status_red.gif"/>';
        }
        case '1':
        case 1:
           if(status == 1) {
               return '<img class="img-button" src="images/icon_status_green.gif"/>&nbsp;<img class="img-button btn-status-off" style="cursor: pointer"src="images/icon_status_red_light.gif"/>';
           }else {
               return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif"/>&nbsp;<img class="img-button" src="images/icon_status_red.gif"/>';
        }
    }
  };

  renderPct = function(pct) {
    return pct + " %";
  };
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'label', header: 'Serveur', dataIndex: 'label', sortable: true},
    { id: 'host', header: 'Host', dataIndex: 'host', sortable: true, align: 'center'},
    { header: '', align: 'center', dataIndex: 'pct_used',renderer:Toc.content.ContentManager.renderProgress,sortable: true},
    { header: '% Utilisation', align: 'center', dataIndex: 'pct_used',sortable: true,renderer:renderPct},
    { header: 'Taille (GB)', align: 'center', dataIndex: 'size',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Libre (GB)', align: 'center', dataIndex: 'dispo',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Utilise (GB)', align: 'center', dataIndex: 'used',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Derniere Mise à jour', align: 'center', dataIndex: 'start_date',width:160,sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'label';

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
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
  
  Toc.tbsexplorer.tbsexplorerGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tbsexplorer.tbsexplorerGrid, Ext.grid.GridPanel, {
  onEdit: function(record) {
    var json = record.json;
    var dlg = new Toc.tbsexplorer.tbsexplorerDialog({label:record.get("label"),sid : json.sid,host : json.host,db_port : json.port,db_pass : json.pass,db_user : json.user,owner : this.owner,databases_id : record.get("databases_id"),server_user:json.server_user,server_pass:json.server_pass,server_typ:json.server_typ,server_port:json.server_port});
    var path = this.mainPanel.getCategoryPath();
    dlg.setTitle('Tablespaces de la base : ' + record.get("label"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.json,path);
  },
  
  onDelete: function(record) {
    var tbsexplorerId = record.get('tbsexplorer_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'tbsexplorer',
              action: 'delete_article',
              tbsexplorer_id: tbsexplorerId
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
  },

  onBatchDelete: function() {
    var keys = this.selModel.selections.keys;
    
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
                module: 'tbsexplorer',
                action: 'delete_tbsexplorer',
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

  refreshGrid: function (categoriesId) {
    var permissions = this.mainPanel.getCategoryPermissions();
    var store = this.getStore();

    store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;
    store.baseParams['categories_id'] = categoriesId;
    this.categoriesId = categoriesId;
    store.reload();
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

      case 'icon-edit-record':
        this.onEdit(record);
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

      if (action != 'img-button') {
        var tbsexplorerId = this.getStore().getAt(row).get('tbsexplorer_id');
        var module = 'setStatus';

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, tbsexplorerId, flag);

            break;
        }
      }
    }
  },
  
  onAction: function(action, tbsexplorerId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'tbsexplorer',
        action: action,
        tbsexplorer_id: tbsexplorerId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(tbsexplorerId).set('content_status', flag);
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