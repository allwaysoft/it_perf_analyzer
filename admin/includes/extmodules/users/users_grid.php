<?php
/*
  $Id: users_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.users.UsersGrid = function(config) {

  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'users',
      action: 'list_users'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'users_id'
    }, [
      'users_id',
      'administrators_id',
      'image_url',
      'description',
      'user_name',
      'email_address',
      'status',
      'data',
      'account'
    ]),
    autoLoad: true
  });

   var tpl = new Ext.XTemplate(
		'<tpl for="data">',
            '<table><tbody><tr>',
             '<td align="left" valign="top"><img src="{image_url}"/></td>',
             '<td><table><tbody><tr>',
             '<td>{user_name}</td>',
             '</tr><tr>',
             '<td>{description}</td>',
             '</tr></tbody></table></td></tr></tbody></table>',
        '</tpl>'
	);

  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  renderPublish = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };

  renderAccount = function(account) {
    return '<span style="font-size: large;">' + account.user_name + '</span><div style = "white-space : normal">' + account.description + '</div>';
  };

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'image_url', header: '', dataIndex: 'image_url'},
    { id: 'account', header: 'Compte', dataIndex: 'account', sortable: false,align: 'left',renderer: renderAccount,css : "white-space: normal;"},
    { header: 'Status', align: 'center', renderer: renderPublish, dataIndex: 'users_status'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'account';

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    {
      text: TocLanguage.btnDelete,
      iconCls:'remove',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
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
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        handler: function() {
          thisObj.onBatchDelete();
        }
      }
    ],
    beforePageText : TocLanguage.beforePageText,
    firstText: TocLanguage.firstText,
    lastText: TocLanguage.lastText,
    nextText: TocLanguage.nextText,
    prevText: TocLanguage.prevText,
    afterPageText: TocLanguage.afterPageText,
    refreshText: TocLanguage.refreshText,
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg,
    prevStepText: TocLanguage.prevStepText,
    nextStepText: TocLanguage.nextStepText
  });

  Toc.users.UsersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.users.UsersGrid, Ext.grid.GridPanel, {

  onAdd: function() {
    var dlg = this.owner.createUsersDialog();
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(null, null);
  },

  onEdit: function(record) {
    var dlg = this.owner.createUsersDialog();
    dlg.setTitle(record.get("users_name"));

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(record.get("users_id"),record.get("administrators_id"));
  },

  onDelete: function(record) {
    var usersId = record.get('users_id');

    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'users',
              action: 'delete_user',
              users_id: usersId
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
                module: 'users',
                action: 'delete_users',
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
     this.mainPanel.getCategoriesTree().refresh();
  },

  refreshGrid: function (categoriesId) {
    var store = this.getStore();

    store.baseParams['categories_id'] = categoriesId;
    this.categoriesId = categoriesId;
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
        var usersId = this.getStore().getAt(row).get('users_id');
        var module = 'setStatus';

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, usersId, flag);

            break;
        }
      }
    }
  },

  onAction: function(action, usersId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'users',
        action: action,
        users_id: usersId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);

        if (result.success == true) {
          var store = this.getStore();
          store.getById(usersId).set('users_status', flag);
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