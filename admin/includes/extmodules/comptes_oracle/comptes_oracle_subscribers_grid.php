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

Toc.comptes_oracle.notificationsGrid = function(config) {
  var that = this;
  config = config || {};
  //config.region = 'center';
  config.loadMask = true;
  config.title = ' Notifications';
  config.border = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.listeners = {
   'rowclick' : this.onRowClick
  };

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'servers',
      action: 'list_subscribers',
      databases_id:config.databases_id
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'email'
    }, [
      'name',
      'email',
      'event'
    ]),
    autoLoad: false
  });

  renderStatus = function(status) {
    if(status == 'ONLINE') {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'name', header: 'Nom', dataIndex: 'name', sortable: true},
    { id: 'email', header: 'Email', dataIndex: 'email', sortable: true,width : 400},
    config.rowActions
  ]);
  config.autoExpandColumn = 'name';
  config.stripeRows = true;

  config.eventds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'servers',
      action: 'list_eventscomptes_oracle'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'event'
    }, [
      'event',
      'label'
    ]),
    autoLoad: true
  });

  config.comboEvents = new Ext.form.ComboBox({
        typeAhead: true,
        name: 'event',
        fieldLabel: 'Evenement',
        width : 400,
        triggerAction: 'all',
        mode: 'local',
        emptyText: '',
        store: config.eventds,
        editable : false,
        valueField: 'event',
        displayField: 'label'
  });

  var thisObj = this;

  config.tbar = [
    {
      text: '',
      iconCls: 'add',
      disabled : true,
      handler: this.onAdd,
      scope: this
    },
    '-',
    { 
      text: '',
      iconCls: 'refresh',
      //disabled : true,
      handler: this.onRefresh,
      scope: this
    },
    '->',
    config.comboEvents
  ];

  config.comboEvents.on('select',function(combo,record,index)
  {
       var event = record.data.event;
       var store = thisObj.getStore();

       thisObj.topToolbar.items.items[0].enable();
       thisObj.topToolbar.items.items[1].enable();

       store.baseParams['event'] = event;
       store.reload();
  });

  config.bbar = new Ext.PageToolbar({
        pageSize: 50,
        store: config.ds,
        steps: Toc.CONF.GRID_STEPS,
        beforePageText: '',
        firstText: '',
        lastText: '',
        nextText: '',
        prevText: '',
        afterPageText: '',
        refreshText: '',
        displayInfo: true,
        displayMsg: '',
        emptyMsg: '',
        prevStepText: '',
        nextStepText: ''
  });

  this.addEvents({'selectchange' : true});
  Toc.comptes_oracle.notificationsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.comptes_oracle.notificationsGrid, Ext.grid.GridPanel, {

  onAdd: function() {
    var dlg = new Toc.comptes_oracle.AddSubscriberDialog();
    var event = this.comboEvents.getValue();
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(this.databases_id,event);
  },

  onDelete: function(record) {
    var event = record.get('event');
    var email = record.get('email');
    
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'users',
                action: 'delete_subscriber',
                databases_id: this.databases_id,
                event : event,
                email : email
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

  refreshGrid: function (categoriesId) {
    var permissions = this.mainPanel.getCategoryPermissions();
    var store = this.getStore();

    store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;
    store.baseParams['categories_id'] = categoriesId;
    this.categoriesId = categoriesId;
    store.reload();
  },

  onSelect:function(combo,record,index)
  {
       var event = record.data.event;
       var store = this.getStore();

       store.baseParams['event'] = event;
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

  setTbs: function(tablespace_name) {
     this.fireEvent('selectchange',tablespace_name);
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
         console.debug(sel);
         var sel = this.getStore().getAt(row);
         this.setTbs(sel.json.tablespace_name);
      }

      if (action != 'img-button') {
        var serversId = this.getStore().getAt(row).get('servers_id');
        var module = 'setStatus';

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, serversId, flag);

            break;
        }
      }
    }
  },

  onRowClick : function(grid,index,obj) {
    console.log(index);
    console.debug(obj);
    var item = grid.getStore().getAt(index);
    console.debug(item);
    this.fireEvent('selectchange',item);
  },
  
  onAction: function(action, serversId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'servers',
        action: action,
        servers_id: serversId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(serversId).set('content_status', flag);
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