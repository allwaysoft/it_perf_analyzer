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

Toc.databases.usersGrid = function(config) {
  var that = this;
  config = config || {};
  //config.region = 'center';
  config.loadMask = true;
  config.title = 'Utilisateurs';
  config.border = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.listeners = {
   'rowclick' : this.onRowClick
  };

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'servers',
      action: 'list_usersdb',
      db_user:config.db_user,
      db_pass:config.db_pass,
      db_port:config.db_port,
      db_host:config.host,
      db_sid:config.sid
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'username'
    }, [
      'username',
      'status',
      'expiration',
      'creation',
      'icon',
      'authentication_type'
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

  renderPublish = function(status) {
        if (status == 'OPEN') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-locked-record', qtip: 'Reinitialiser Mot de Passe',hideIndex : 'authentication_type'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {header: '', dataIndex: 'icon', width : 24},
    { id: 'username', header: 'Nom', dataIndex: 'username', sortable: true},
    { header: 'Date Creation', align: 'center', dataIndex: 'creation'},
    { header: 'Date Expiration', align: 'center', dataIndex: 'expiration'},
    {header: 'Status', align: 'center', renderer: renderPublish, dataIndex: 'status'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'username';
  config.stripeRows = true;

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true,
    listeners:{
    scope:this,
    specialkey: function(f,e){
      if(e.getKey()==e.ENTER){
      this.onSearch();
      }
    }
  }
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

  this.addEvents({'selectchange' : true});
  Toc.databases.usersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.usersGrid, Ext.grid.GridPanel, {

  onAdd: function() {
    var config = {
       db_user : this.db_user,
       databases_id : this.databases_id,
       db_pass : this.db_pass,
       db_port : this.db_port,
       db_sid : this.sid,
       db_host : this.host,
       label : this.label
    };

    var dlg = new Toc.databases.CreateUserDialog(config);

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(config,this);
  },

  onEdit: function(record) {
    var config = {
       db_user : this.db_user,
       db_pass : this.db_pass,
       db_port : this.db_port,
       db_sid : this.sid,
       db_host : this.host
    };

    var dlg = new Toc.databases.ResetPwdDialog(config);
    dlg.setTitle('Reinitialiser le mot de passe du Compte ' + record.get("username"));

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(record.json,this);
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
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();

    store.baseParams['search'] = filter;
    store.reload();
    store.baseParams['search'] = '';
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-locked-record':
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

            if (action != 'img-button') {
                var username = this.getStore().getAt(row).get('username');
                var module = 'lock_user';
                var flag = 0;

                switch (action) {
                    case 'status-off':
                        module = 'lock_user';
                        flag = 0;
                        this.onAction(module, username,flag);
                        break;
                    case 'status-on':
                        module = 'unlock_user';
                        flag = 1;
                        this.onAction(module, username,flag);
                        break;
                }
            }
        }
  },

  onRowClick : function(grid,index,obj) {
    var item = grid.getStore().getAt(index);
    this.fireEvent('selectchange',item);
  },
  
  onAction: function(action,username, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'users',
        action: action,
        account: username,
        flag: flag,
        label : this.label,
        databases_id : this.databases_id,
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
          //store.getById(username).set('status', flag);
          //store.commitChanges();
          store.reload();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }  
});