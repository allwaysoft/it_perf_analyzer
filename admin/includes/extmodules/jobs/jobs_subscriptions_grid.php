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

Toc.jobs.subscriptionsGrid = function(config) {
  var that = this;
  config = config || {};
  //config.region = 'center';
  config.loadMask = true;
  config.header = false;
  config.border = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.listeners = {
   'rowclick' : this.onRowClick
  };

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'jobs',
      action: 'list_subscriptions'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'subscriptions_id'
    }, [
      'subscriptions_id',
      'job_id',
      'jobs_name',
      'schedule',
      'owner',
      'parameters',
      'subscribers',
      'format'
    ]),
    autoLoad: false
  });

  renderParameters = function(parameters) {
    var res = parameters.split(";");
    var i = 0;
    var ret = '';
    while(i < res.length)
    {
       ret = ret + '<div style = "white-space : normal">' + res[i] + '</div>';
       i++;
    }
    return ret;
  };

  renderSubscribers = function(subscribers) {
    var res = subscribers.split(";");
    var i = 0;
    var ret = '';
    while(i < res.length)
    {
       ret = ret + '<div style = "white-space : normal">' + res[i] + '</div>';
       i++;
    }
    return ret;
  };

  renderSchedule = function(schedule) {
    var res = schedule.split(";");
    var i = 0;
    var ret = '';
    while(i < res.length)
    {
       ret = ret + '<div style = "white-space : normal">' + res[i] + '</div>';
       i++;
    }
    return ret;
  };

  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete},
      {iconCls: 'feed', qtip: 'Souscrire'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  config.cm = new Ext.grid.ColumnModel([
    { id: 'owner', header: 'Cree par', dataIndex: 'owner',width : 60},
    { id: 'schedule', header: 'Planification', dataIndex: 'schedule',renderer: renderSchedule,width : 200},
    { id: 'parameters', header: 'Parametres', dataIndex: 'parameters',width : 250,renderer: renderParameters},
    { id: 'format', header: 'Format', dataIndex: 'format',align : 'center',width : 50},
    { id: 'subscribers', header: 'Abonnes', dataIndex: 'subscribers',width : 230,renderer: renderSubscribers},
    config.rowActions
  ]);
  config.stripeRows = true;
  //config.autoExpandColumn = 'schedule';

  config.tbar = [
    {
      text: '',
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    { 
      text: '',
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];

  this.addEvents({'selectchange' : true});
  Toc.jobs.subscriptionsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.jobs.subscriptionsGrid, Ext.grid.GridPanel, {
  onRefresh: function() {
    this.getStore().reload();
  },

  onAdd: function() {
    var dlg = new Toc.jobs.jobscheduleDialog();
    var path = this.parent.categoryId;
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.setTitle('Abonnement etat : ' + this.parent.data.content_name);

    dlg.show(this.parent.data,path);
  },

  refreshGrid: function (categoriesId) {
    var permissions = this.mainPanel.getCategoryPermissions();
    var store = this.getStore();

    store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;
    store.baseParams['categories_id'] = categoriesId;
    this.categoriesId = categoriesId;
    store.reload();
  },

  onDelete: function(record) {
    var subscriptions_id = record.get('subscriptions_id');
    var job_id = record.get("job_id");

    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'jobs',
              action: 'delete_subscription',
              subscriptions_id: subscriptions_id,
              job_id : job_id
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);

              if (result.success == true) {
                //this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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

  onSubscribe: function(record) {
    var dlg = new Toc.jobs.subscriptionDialog();

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.setTitle('Adresses Email de notification ...');

    dlg.show(record.data);
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;

      case 'feed':
        this.onSubscribe(record);
        break;
    }
  },

  onRowClick : function(grid,index,obj) {
    var item = grid.getStore().getAt(index);
    this.fireEvent('selectchange',item);
  }
});