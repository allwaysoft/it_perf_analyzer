<?php
/*
  $Id: ctx_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.ctx.ctxGrid = function(config) {

  config = config || {};
  config.region = 'center';
  config.title = 'Contentieux';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords,forceFit : true};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'users',
      action: 'list_ctx',
      db_user : 'delta',
      db_pass : 'delta',
      db_host : '10.100.33.50',
      db_sid : 'CTXV10'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'no_dossier'
    }, [
      'no_dossier',
      'no_client',
      'date_ctx',
      'nom'
    ]),
    listeners: {
         load: function(store,records,options) {
         //store.baseParams['count'] = store.getCount();
         //console.debug(records);
       },
       scope: this
    },
    autoLoad: true
  });

  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-delete-record', qtip: 'Sortir ce client du Contentieux'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'no_dossier',header: 'No Dossier', dataIndex: 'no_dossier',width:20,align: 'center'},
    { id: 'no_client',header: 'No Client', dataIndex: 'no_client',width:20,align: 'center'},
    { id: 'nom', header: 'Nom', dataIndex: 'nom',align: 'center',width:40},
    { id: 'date_ctx', header: 'Date CTX', dataIndex: 'date_ctx',align: 'center',width:20},
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
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
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

  Toc.ctx.ctxGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ctx.ctxGrid, Ext.grid.GridPanel, {

  onDelete: function(record) {
    var cli = record.get('cli');

    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      'Voulez-vous vraiment sortir ce Client du Contentieux ?',
      function(btn) {
        if (btn == 'yes') {
          this.getEl().mask('Sortie en cours, Veuillez patienter SVP ...');
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'users',
              action: 'sortie_ctx',
              cli: cli
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              this.getEl().unmask();

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

  deconnectUser: function (action, cuti, flag,pbar,step,max) {
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
         var cli = this.selModel.selections.items[i].data.cli;
         var module = 'sortie_ctx';

         this.deconnectUser(module, cli,1,this.pBar,step,count);
      }
    }
    else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },

  onRefresh: function() {
    var store = this.getStore();
    store.load();
  },

  refreshGrid: function (categoriesId,count) {
    var store = this.getStore();

    store.baseParams['categories_id'] = categoriesId;
    store.baseParams['count'] = count;
    //store.baseParams['start'] = 0;
    store.baseParams['search'] = '';
    this.categoriesId = categoriesId;
    store.load();
  },

  onSearch: function() {
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();

    store.baseParams['current_category_id'] = this.categoriesId || -1;
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