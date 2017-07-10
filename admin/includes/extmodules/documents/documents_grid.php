<?php
/*
  $Id: documents_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.documents.DocumentsGrid = function(config) {
  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords,forceFit : true
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'documents',
      action: 'list_documents'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'documents_id'
    },  [
      'url',
      'icon',
      'size',
      'file_owner',
      'documents_id',
      'documents_name',
      'documents_description',
      'content_status',
      'action',
      'date_created',
      'date_modified',
      'date_published',
      'created_by',
      'modified_by',
      'published_by'
    ]),
    autoLoad: false
  });

var that = this;

  renderPublish = function(status) {
        if(that.permissions.can_publish == 1)
        {
          if (status == 1) {
           return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
          } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
          }
        }

        return '';
    };

  renderDescription = function(desc) {
    return '<div style = "white-space : normal">' + desc + '</div>';
  };
  
  config.rowActions = new Ext.ux.grid.RowActions({
    tpl: new Ext.XTemplate(
      '<div class="ux-row-action">'
      +'<tpl for="action">'
      +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
      +'</tpl>'
      +'</div>'
    ),
    actions:['','',''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.plugins = config.rowActions;
  
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {header: '', dataIndex: 'icon', width : 5, align: 'center'},
    {id: 'documents_name', header: 'Nom Document', dataIndex: 'documents_name', width : 15,renderer: renderDescription},
    {header: 'Description', dataIndex: 'documents_description', width: 15,renderer: renderDescription},
    {header: 'Publie', align: 'center', renderer: renderPublish, dataIndex: 'content_status', width : 5},
    {id: 'date_created', header: 'Date Creation', dataIndex: 'date_created', width : 10, align: 'center'},
    {id: 'date_modified', header: 'Date Modification', dataIndex: 'date_modified', width : 10, align: 'center'},
    {id: 'date_published', header: 'Date Publication', dataIndex: 'date_published', width : 10, align: 'center'},
    {id: 'created_by', header: 'Cree Par', dataIndex: 'created_by', width : 10, align: 'center'},
    {id: 'modified_by', header: 'Modifie Par', dataIndex: 'modified_by', width : 10, align: 'center'},
    {id: 'published_by', header: 'Publie Par', dataIndex: 'published_by', width : 10, align: 'center'},
    config.rowActions
  ]);
  //config.autoExpandColumn = 'documents_name';
  config.rowActions.on('action', this.onRowAction, this);
  
  config.txtSearch = new Ext.form.TextField({
    emptyText: ''
  });
  
  config.tbar = [{
    text: TocLanguage.btnAdd,
    hidden : true,
    iconCls: 'icon-upload',
    handler: function() {
      this.onAdd();
    },
      scope: this
  },
    '-',
    {
    text: TocLanguage.btnDelete,
    iconCls: 'remove',
    hidden : true,
    handler: this.onBatchDelete,
    scope: this
  }, '-', { 
    text: TocLanguage.btnRefresh,
    iconCls: 'refresh',
    handler: this.onRefresh,
    scope: this
  }, '->', config.txtSearch, ' ',
  {
    iconCls : 'search',
    handler : this.onSearch,
    scope : this
  }];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        hidden : true,
        iconCls:'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        hidden : true,
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
  
  Toc.documents.DocumentsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.documents.DocumentsGrid, Ext.grid.GridPanel, {
  onAdd: function(documentsId, inTabDocuments) {
    var path = this.mainPanel.getCategoryPath();
    var dlg = this.owner.createDocumentsDialog();
      
    dlg.on('saveSuccess', function(){
     this.onRefresh();
     this.mainPanel.pnlCategoriesTree.refresh();
    }, this);
    
    dlg.setTitle('Nouveau document');
    dlg.show(null,path);
  },

  setPermissions: function(permissions,categoriesId) {
    this.permissions = permissions;
    this.bottomToolbar.items.items[0].hide();
    this.bottomToolbar.items.items[2].hide();

    this.topToolbar.items.items[0].hide();
    this.topToolbar.items.items[2].hide();
    if(permissions)
    {
        if(permissions.can_write == 1 || permissions.can_modify == 1)
        {
            this.bottomToolbar.items.items[0].show();
            this.topToolbar.items.items[0].show();
        }
        else
        {
            this.bottomToolbar.items.items[0].hide();
            this.topToolbar.items.items[0].hide();
        }
        if(permissions.can_modify !== 0)
        {
            this.bottomToolbar.items.items[2].show();
            this.topToolbar.items.items[2].show();
        }
        else
        {
            this.bottomToolbar.items.items[2].hide();
            this.topToolbar.items.items[2].hide();
        }

       var store = this.getStore();

       store.baseParams['can_modify'] = permissions.can_modify;
       store.baseParams['can_write'] = permissions.can_write;
       store.baseParams['can_see'] = permissions.can_see;
       store.baseParams['can_publish'] = permissions.can_publish;
       store.baseParams['can_read'] = permissions.can_read;
       store.baseParams['categories_id'] = categoriesId;
       this.categoriesId = categoriesId;
       store.load();
    }
  },

  onDownload: function (record) {
    url = record.get('url');
    params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
    window.open(url, "",params);
  },

  onGrdDbClick: function(grid, row) {
    var record = grid.getStore().getAt(row);
    var isDirectory = record.get('is_directory');
    var fileName = record.get('file_name');

    if (isDirectory == true) {
      var directory = this.mainPanel.getDirectoryTreePanel().getCurrentPath() + '/' + fileName;
      this.mainPanel.getDirectoryTreePanel().setCurrentPath(directory);
    } else {
      this.onEdit(fileName);
    }
  },
  
  onEdit: function(record) {
  },
  
  onDelete: function(record) {
    var documentsId = record.get('documents_id');
    var documentsName = record.get('documents_cache_filename');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'documents',
              action: 'delete_document',
              documents_id: documentsId,
              documents_name: documentsName
            }, 
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.getStore().reload();
                this.mainPanel.pnlCategoriesTree.refresh();
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

  refreshGrid: function (categoriesId) {
    var store = this.getStore();

    store.baseParams['categories_id'] = categoriesId;
    this.categoriesId = categoriesId;
    store.load();
  },
  
  onBatchDelete: function() {
    var selection = this.getSelectionModel().selections,
    keys = selection.keys,
    result = [];
      
    Ext.each(keys, function(key, index) {
      result = result.concat(key + ':' + selection.map[key].get('documents_cache_filename'));
    });
  
    if (result.length > 0) {    
      var batch = result.join(',');
    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'documents',
                action: 'delete_documents',
                batch: batch
              },
              callback: function(options, success, response){
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
                  this.mainPanel.pnlCategoriesTree.refresh();
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
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onSearch: function() {
    var documents_name = this.txtSearch.getValue();
    var store = this.getStore(); 
    
    store.baseParams['documents_name'] = documents_name;
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

      case 'icon-download-record':
        this.onDownload(record);
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
                var documents_id = this.getStore().getAt(row).get('documents_id');
                var module = 'setDocumentStatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        var flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, documents_id, flag);

                        break;
                }
            }
        }
    },

    onAction: function(action, documents_id, flag) {
        var that = this;
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'documents',
                action: action,
                documents_id: documents_id,
                flag: flag
            },
            callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result && result.success == true) {
                    var store = this.getStore();
                    store.getById(documents_id).set('content_status', flag);
                    store.commitChanges();

                    TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});
