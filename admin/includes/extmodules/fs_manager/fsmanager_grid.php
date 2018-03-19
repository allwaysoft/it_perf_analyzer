<?php

?>

Toc.fsmanager.fsmanagerGrid = function(config) {
  var that = this;
  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'servers',
      action: 'list_spaceusage'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'servers_id'
    }, [
      'snaps_id',
      'servers_id',
      'label',
      {name:'size',type:'int'},
      {name:'used',type:'int'},
      {name:'dispo',type:'int'},
      {name:'pct_used',type:'int'},
      'start_date',
      'end_date',
      'host',
      'port',
      'typ',
      'user',
      'pass'
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

  renderPct = function(pct) {
    return pct + " %";
  };
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'label', header: 'Serveur', dataIndex: 'label', sortable: true},
    { id: 'host', header: 'Host', dataIndex: 'host', sortable: true},
    { header: '', align: 'center', dataIndex: 'pct_used',renderer:Toc.content.ContentManager.renderProgress,sortable: true},
    { header: '% Utilisation', align: 'center', dataIndex: 'pct_used',sortable: true,renderer:renderPct},
    { header: 'Taille (GB)', align: 'center', dataIndex: 'size',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Libre (GB)', align: 'center', dataIndex: 'dispo',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Utilise (GB)', align: 'center', dataIndex: 'used',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Derniere Mise à jour', align: 'center', dataIndex: 'start_date',width:160,sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'label';

  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];

  var thisObj = this;
  
  Toc.fsmanager.fsmanagerGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.fsmanager.fsmanagerGrid, Ext.grid.GridPanel, {

  onEdit: function(record) {
    var json = record.json;
    var dlg = new Toc.FsDialog({typ : json.typ,host : json.host,server_port : json.port,server_pass : json.pass,server_user : json.user,servers_id : record.get("servers_id"),owner : this.owner});
    var path = this.mainPanel.getCategoryPath();
    dlg.setTitle('Systeme de fichiers du Serveur : ' + record.get("label"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.json,path);
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

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  }
});