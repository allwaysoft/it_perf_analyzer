<?php

?>

Toc.database_groups.GroupGrid = function(config) {
  
  config = config || {};
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'databases',
      action: 'list_databaseGroups'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'group_id'
    }, 
    [
      'group_id',
      'group_name'
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
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {
      id: 'group_name',
      header: 'Group',
      sortable:true,
      dataIndex: 'group_name'
    },
    config.rowActions
  ]);
  config.autoExpandColumn = 'group_name';

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
      text: TocLanguage.btnAdd,
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    },
    '-', 
    {
      text: TocLanguage.btnDelete,
      iconCls: 'remove',
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
        config.txtSearch
  ];

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

  var thisObj = this;

  Toc.database_groups.GroupGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.database_groups.GroupGrid, Ext.grid.GridPanel, {

  onAdd: function() {
    var dlg = new Toc.database_groups.GroupDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(null);
  },
  
  onEdit: function(record) {
    if(record.data.group_id != undefined)
    {
        var dlg = new Toc.database_groups.GroupDialog();
        dlg.setTitle(record.get("roles_name"));

        dlg.on('saveSuccess', function() {
        this.onRefresh();
        }, this);

        dlg.show(record.data);
    }
    else
    {
        console.dir(record);
        Ext.Msg.alert(TocLanguage.msgErrTitle,'invalide Groupe');
    }
  },
  
  onDelete: function(record) {
    var group_id = record.get('group_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'databases',
              action: 'delete_group',
              group_id: group_id
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
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

  onSearch: function() {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['search'] = filter;
        store.reload();
        store.baseParams['search'] = '';
    },

  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
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
                module: 'roles',
                action: 'delete_roles',
                batch: batch
              },
              callback: function(options, success, response){
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, 
        this
      );
    }else{
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  } 
});