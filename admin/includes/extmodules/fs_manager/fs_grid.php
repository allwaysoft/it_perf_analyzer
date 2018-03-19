<?php

?>

Toc.databases.fsGrid = function(config) {
  var that = this;
  config = config || {};
  //config.region = 'center';
  config.loadMask = true;
  config.title = 'FS';
  config.border = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.listeners = {
   'rowclick' : this.onRowClick
  };

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'fsmanager',
      action: 'list_fs',
      user:config.server_user,
      pass:config.server_pass,
      port:config.server_port,
      host:config.host
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'mount'
    }, [
      'mount',
      'fs',
      'typ',
      {name:'size',type:'int'},
      {name:'used',type:'int'},
      {name:'dispo',type:'int'},
      {name:'pct_used',type:'int'}
    ]),
    autoLoad: false
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'mount', header: 'Nom', dataIndex: 'mount', sortable: true},
    { header: '%', align: 'center', dataIndex: 'pct_used',renderer:Toc.content.ContentManager.renderProgress,sortable: true},
    { header: 'Taille (MB)', align: 'center', dataIndex: 'size',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Libre (MB)', align: 'center', dataIndex: 'dispo',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { header: 'Utilise (MB)', align: 'center', dataIndex: 'used',sortable: true,renderer:Toc.content.ContentManager.FormatNumber},
    { id: 'typ', header: 'Type', dataIndex: 'typ',align: 'center', sortable: true},
    { id: 'fs', header: 'Filesystem', dataIndex: 'fs', sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'fs';
  config.stripeRows = true;

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.tbar = [
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
  Toc.databases.fsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.fsGrid, Ext.grid.GridPanel, {
  setPermissions: function(permissions) {
    this.bottomToolbar.items.items[0].disable();
    this.bottomToolbar.items.items[2].disable();

    this.topToolbar.items.items[0].disable();
    this.topToolbar.items.items[2].disable();
    if(permissions)
    {
        if(permissions.can_write == 1 || permissions.can_modify == '')
        {
            this.bottomToolbar.items.items[0].enable();
            this.topToolbar.items.items[0].enable();
        }
        if(permissions.can_modify == '')
        {
            this.bottomToolbar.items.items[2].enable();
            this.topToolbar.items.items[2].enable();
        }
    }
  },

  onEdit: function(record) {
    var dlg = this.owner.createDirDialog();
    dlg.setTitle(record.get("mount"));

    var config = {
       server_user:this.server_user,
       server_pass:this.server_pass,
       server_port:this.server_port,
       host:this.host,
       mount:record.get("mount")
    };
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(config,this.owner);
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
         var sel = this.getStore().getAt(row);
         this.setTbs(sel.json.tablespace_name);
      }

      if (action != 'img-button') {
        var tbs = this.getStore().getAt(row).get('tablespace_name');
        var module = 'setTbstatus';

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 'ONLINE' : 'OFFLINE';
            this.onAction(module, tbs, flag);
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
  
  onAction: function(action, tbs, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'fsmanager',
        action: action,
        tbs: tbs,
        flag: flag,
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
          store.getById(tbs).set('status', flag);
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