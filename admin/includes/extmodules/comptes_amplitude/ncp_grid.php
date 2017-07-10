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

Toc.ncp.ncpAmplitudeGrid = function(config) {

  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'users',
      action: 'list_AmplitudeNcp'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'ncp'
    }, [
      'age',
      'cli',
      'ncp',
      'clc',
      'nomrest'
    ]),
    listeners: {
         load: function(store,records,options) {
         //store.baseParams['count'] = store.getCount();
         //console.debug(records);
       },
       scope: this
    },
    autoLoad: false
  });

  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-delete-record', qtip: 'Deblocage IGC'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'age',header: 'Agence', dataIndex: 'age',width:50,align: 'center'},
    { id: 'cli',header: 'Code Client', dataIndex: 'cli',width:150,align: 'center'},
    { id: 'client',header: 'Client', dataIndex: 'nomrest'},
    { id: 'ncp', header: 'No Compte', dataIndex: 'ncp',align: 'center',width:150},
    { id: 'clc', header: 'Cle', dataIndex: 'clc',align: 'center',width:25},
    config.rowActions
  ]);
  config.autoExpandColumn = 'client';

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

  Toc.ncp.ncpAmplitudeGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ncp.ncpAmplitudeGrid, Ext.grid.GridPanel, {

  onDelete: function(record) {
    var age = record.get('age');
    var ncp = record.get('ncp');

    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      'Deblocage IGC ?',
      function(btn) {
        if (btn == 'yes') {
          this.getEl().mask('Deblocage en cours, Veuillez patienter SVP ...');
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'users',
              action: 'deblocage_igc',
              age: age,
              ncp:ncp
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
     this.mainPanel.getCategoriesTree().refresh();
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