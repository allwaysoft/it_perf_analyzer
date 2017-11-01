<?php
/*
  $Id: asset_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.asset.assetGrid = function (config) {
  config = config || {};
  
  config.region = 'center';
  config.loadMask = true;
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'asset',
      action: 'list_asset'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'asset_id'
    }, [
      'asset_id',
      'asset_name',
      'status',
      'path'
    ])
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    header: '<?php echo $osC_Language->get("table_heading_action"); ?>',
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}, 
      {iconCls: 'icon-move-record', qtip: TocLanguage.tipMove}, 
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;
  
  var renderActive = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  }; 
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'products_asset_name', header: 'Page', dataIndex: 'asset_name'},
    { header: 'Status', dataIndex: 'status', align: 'center', renderer: renderActive},
    config.rowActions
  ]);
  config.autoExpandColumn = 'products_asset_name';
  
  config.listeners = {"rowdblclick": this.onGrdRowDbClick};
  config.search = new Ext.form.TextField({name: 'search', width: 150});
  
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
      text: TocLanguage.btnMove,
      iconCls: 'icon-move-record',
      handler: this.onBathMove,
      scope: this
    }, 
    '-',
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onSearch,
      scope: this
    }, 
    '->',
    config.search,
    '',
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {thisObj.onAdd();}
      },
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        handler: function() {thisObj.onBatchDelete();}
      },
      {
        text: TocLanguage.btnMove,
        iconCls:'icon-move-record',
        handler: function() {thisObj.onBathMove();}
      }
    ],
    beforePageText: TocLanguage.beforePageText,
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
  Toc.asset.assetGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.asset.assetGrid, Ext.grid.GridPanel, {

  onAdd: function () {
    var dlg = this.owner.createassetDialog();
    
    dlg.on('saveSuccess', function() {
      this.mainPanel.getassetTree().refresh();
    }, this);
        
    dlg.show(null, this.mainPanel.getassetTree().getassetPath(null));
  },
  
  onEdit: function (record) {
    var dlg = this.owner.createassetDialog();
    var parent_id = this.mainPanel.getassetTree().getassetPath(null);
    dlg.setTitle(record.get('asset_name'));
    
    dlg.on('saveSuccess', function() {
      this.mainPanel.getassetTree().refresh();
    }, this);
    
    dlg.show(record.get('asset_id'),parent_id);
  },
  
  onMove: function (record) {
    var dlg = this.owner.createassetMoveDialog();
    dlg.setTitle('<?php echo $osC_Language->get("action_heading_batch_move_asset"); ?>');

    dlg.on('saveSuccess', function() {
      this.mainPanel.getassetTree().refresh();
    }, this);
    
    dlg.show(record.get('asset_id'), this.mainPanel.getassetTree().getassetPath());
  }, 
  
  onBathMove: function () {
    var keys = this.getSelectionModel().selections.keys;

    if (keys.length > 0) {
      var batch = keys.join(',');
      var dialog = this.owner.createassetMoveDialog();
      dialog.setTitle('<?php echo $osC_Language->get("action_heading_batch_move_asset"); ?>');

      dialog.on('saveSuccess', function() {
        this.mainPanel.getassetTree().refresh();
      }, this);
      
      dialog.show(batch);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
      }
  }, 
  
  onDelete: function (record) {
    var assetId = record.get('asset_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'asset',
              action: 'delete_category',
              asset_id: assetId
            },
            callback: function (options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                
                this.mainPanel.getassetTree().refresh();
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
  
  onBatchDelete: function () {
    var keys = this.getSelectionModel().selections.keys;
    if (keys.length > 0) {
      batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm, 
        function (btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              waitMsg: TocLanguage.formSubmitWaitMsg,
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'asset',
                action: 'delete_asset',
                batch: batch
              },
              callback: function (options, success, response) {
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  
                  this.mainPanel.getassetTree().refresh();
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
    
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
      case 'icon-move-record':
        this.onMove(record);
        break;
    }
  },

  onSearch: function () {
    var filter = this.search.getValue() || null;
    var store = this.getStore();
    store.baseParams['search'] = filter;
    
    store.reload();
  },
  
  refreshGrid: function (assetId) {
    var store = this.getStore();

    store.baseParams['asset_id'] = assetId;
    store.load();
  },

  onGrdRowDbClick: function () {
    var assetId = this.getSelectionModel().getSelected().get('asset_id');
    this.mainPanel.getassetTree().setCategoryId(assetId);
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
        var assetId = this.getStore().getAt(row).get('asset_id');
        var module = 'set_status';
        
        switch(action) {
          case 'status-off':
            flag = (action == 'status-on') ? 1 : 0;
            
            Ext.MessageBox.confirm(
              TocLanguage.msgWarningTitle, 
              TocLanguage.msgDisableProducts, 
              function (btn) {
                if (btn == 'no') {
                  this.onAction(module, assetId, flag, 0);
                } else{
                  this.onAction(module, assetId, flag, 1);
                }
              }, 
              this
            );  
            
            break;               
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            
			      this.onAction(module, assetId, flag, 0);
			      break;         
        }
      }
    }
  },
  
  onAction: function(action, assetId, flag, product_flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'asset',
        action: action,
        asset_id: assetId,
        flag: flag,
        product_flag: product_flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(assetId).set('status', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else {
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
      },
      scope: this
    });
  }
});
 