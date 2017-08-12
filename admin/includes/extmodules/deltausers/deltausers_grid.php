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

Toc.deltausers.deltausersGrid = function(config) {

  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'users',
      action: 'list_deltausers'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'cuti'
    }, [
      'cuti',
      'unix',
      'lib',
      'status'
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
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
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
    { id: 'status',header: 'Status', align: 'center', renderer: renderPublish, dataIndex: 'status'},
    { id: 'cuti', header: 'Matricule', dataIndex: 'cuti', sortable: false,align: 'center'},
    { id: 'unix', header: 'Compte Windows', dataIndex: 'unix', sortable: false,align: 'center',width:150},
    { id: 'nom', header: 'Nom', dataIndex: 'lib', sortable: false,align: 'left'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'nom';

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

  config.pBar = new Ext.ProgressBar({
    hidden: true,
    width: 300,
    hideLabel: true
  });

  config.tbar = [
    {
      text: 'Deconnecter',
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
    config.pBar,
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

  Toc.deltausers.deltausersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.deltausers.deltausersGrid, Ext.grid.GridPanel, {

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
      'Voulez-vous vraiment deconnecter cet utilisateur ?',
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'users',
              action: 'deconnect_user',
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
      this.pBar.reset();
      this.pBar.updateProgress(0,"",true);
      this.pBar.val = 0;
      this.pBar.count = 0;
      this.pBar.show();
      var step = 1/count;

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
     this.mainPanel.getCategoriesTree().refresh();
  },

  refreshGrid: function (categoriesId,count) {
    var store = this.getStore();

    store.baseParams['categories_id'] = categoriesId;
    store.baseParams['count'] = count;
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