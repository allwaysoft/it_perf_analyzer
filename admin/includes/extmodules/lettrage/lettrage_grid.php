<?php
/*
  $Id: deltausers_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.lettrage.LettrageGrid = function(config) {

  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'users',
      action: 'list_lettrage'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'ncp'
    }, [
      'cuti',
      'unix',
      'lib',
      'ncp'
    ]),
    listeners: {
        'load' :  function(store,records,options) {
      },
      scope : this
    },
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
      {iconCls: 'icon-delete-record', qtip: 'Debloquer ce Compte'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  renderPublish = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };

  renderAccount = function(account) {
    return '<span style="font-size: large;">' + account.user_name + '</span><div style = "white-space : normal">' + account.description + '</div>';
  };

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'ncp', header: 'Compte', dataIndex: 'ncp', sortable: false,align: 'center'},
    { id: 'cuti', header: 'Matricule', dataIndex: 'cuti', sortable: false,align: 'center'},
    { id: 'unix', header: 'Compte Windows', dataIndex: 'unix', sortable: false,align: 'center',width:150},
    { id: 'nom', header: 'Nom', dataIndex: 'lib', sortable: false,align: 'left'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'nom';

  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];

  Toc.lettrage.LettrageGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.lettrage.LettrageGrid, Ext.grid.GridPanel, {

  onAdd: function() {
    var dlg = this.owner.createdeltausersDialog();
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(null, null);
  },

  onEdit: function(record) {
    var dlg = this.owner.createdeltausersDialog();
    dlg.setTitle(record.get("deltausers_name"));

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show(record.get("deltausers_id"),record.get("administrators_id"));
  },

  onDelete: function(record) {
    var unix = record.get('unix');
    var cuti = record.get('cuti');

    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      'Voulez-vous vraiment debloquer ce Compte ?',
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'users',
              action: 'debloque_compte',
              unix: unix,
              cuti: cuti
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

  deconnectUser: function (action, cuti, flag, unix,pbar,step,max) {
    Ext.Ajax.request({
     url: Toc.CONF.CONN_URL,
     params: {
      module: 'users',
      action: action,
      cuti: cuti,
      unix: unix,
      flag: flag
    },
    callback: function (options, success, response) {
    var result = Ext.decode(response.responseText);

    pbar.val = pbar.val + step;
    pbar.count = pbar.count + 1;
    pbar.updateProgress(pbar.val,cuti + " deconnecte ...",true);

    if(pbar.count >= max)
    {
       pbar.reset();
       pbar.hide();
       this.onRefresh();
    }

    if (result.success == true) {
       var store = this.getStore();
       store.getById(cuti).set('status', 0);
       store.commitChanges();
    }
    else
       this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
    },
    scope: this
   });
  },

  onBatchDelete: function() {
    var count = this.selModel.getCount();
    if(count > 0)
    {
      for (var i=0;i<count;i++)
      {
         var cuti = this.selModel.selections.items[i].data.cuti;
         var unix = this.selModel.selections.items[i].data.unix;
         var module = 'deconnectUser';

         this.deconnectUser(module, cuti,1,unix,this.pBar,step,count);
      }
    }
    else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },

  onRefresh: function() {
    var store = this.getStore();

    store.baseParams['categories_id'] = -1;
    store.baseParams['count'] = 0;
    this.categoriesId = -1;
    store.load();
  },

  refreshGrid: function (categoriesId,count) {
    var store = this.getStore();

    store.baseParams['categories_id'] = categoriesId;
    store.baseParams['count'] = 0;
    this.categoriesId = categoriesId;
    store.load();
  },

  onSearch: function() {
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();

    store.baseParams['current_category_id'] = this.categoriesId || -1;
    store.baseParams['search'] = filter;
    store.reload();
    store.baseParams['search'] = '';
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
        var cuti = this.getStore().getAt(row).get('cuti');
        var unix = this.getStore().getAt(row).get('unix');
        var module = 'deconnectUser';

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, cuti, flag,unix);

            break;
        }
      }
    }
  },

  onAction: function(action, cuti, flag,unix) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'users',
        action: action,
        cuti: cuti,
        unix: unix,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);

        if (result.success == true) {
          var store = this.getStore();
          store.getById(cuti).set('status', 0);
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