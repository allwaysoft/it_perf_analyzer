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

Toc.programmes_amplitude.ProgrammesAmplitudeGrid = function(config) {

  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'users',
      action: 'list_programmes',
      categories_id: -1,
      search:''
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'nprg'
    }, [
      'nprg',
      'lprg',
      'mprg'
    ]),
    listeners: {
        'load' :  function(store,records,options) {
        this.lblInfos.setText(store.data.length + ' Programmes');
      },
      scope : this
    },
    autoLoad: true
  });

  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'run', qtip: 'Lancer'},
      {iconCls: 'debug', qtip: 'Debogguer'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  config.cm = new Ext.grid.ColumnModel([
    { id: 'nprg',header: 'No Programme', align: 'center',dataIndex: 'nprg',sortable:true},
    { id: 'mprg', header: 'Nom', dataIndex: 'mprg', sortable: true,align: 'left'},
    { id: 'lprg', header: 'Libelle Programme', dataIndex: 'lprg', sortable: true,align: 'left'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'lprg';

  config.lblInfos = new Ext.form.Label({
    width: 200,
    text:'O Programmes',
    autoShow:true
  });

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
    '-',
    config.lblInfos,
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
    hidden:true,
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

  Toc.programmes_amplitude.ProgrammesAmplitudeGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.programmes_amplitude.ProgrammesAmplitudeGrid, Ext.grid.GridPanel, {

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

  onRun: function(record) {
    var nprg = record.get('nprg');
    var mprg = record.get('mprg');
    var cuti = record.get('cuti');

    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      'Voulez-vous vraiment executer ce programme ?',
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: 'Demarrage du programme ... veuillez patienter SVP',
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'users',
              action: 'debug_program',
              nprg: nprg,
              mprg: mprg,
              cuti: cuti
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);

              if (result.success == true) {
                Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, result.feedback);
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
      case 'run':
        this.onRun(record);
        break;

      case 'debug':
        this.onDebug(record);
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