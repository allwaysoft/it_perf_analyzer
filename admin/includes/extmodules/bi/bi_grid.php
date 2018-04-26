<?php

?>

Toc.bi.reportsGrid = function(config) {
  var that = this;
  config = config || {};
  config.region = 'center';
  config.title = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'reports',
      action: 'list_dashboards'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'dashboards_id'
    }, [
      'dashboards_id',
      'content_status',
      'content_name',
      'reports_uri',
      'content_description',
      'created_by',
      'can_modify',
      'can_write',
      'hide_edit',
      'hide_delete',
      'can_publish'
    ]),
    autoLoad: false
  });

  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'run', qtip: TocLanguage.run},
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit,hideIndex : 'hide_edit'},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete,hideIndex : 'hide_delete'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;

  renderPublish = function(status) {
    var currentRow = that.store.data.items[0];
    var json = currentRow.json;
    switch(json.can_publish)
    {
        case undefined:
        case 'undefined':
        case '0':
        case 0:
        if(status == 1) {
           return '<img src="images/icon_status_green.gif"/>&nbsp;<img src="images/icon_status_red_light.gif"/>';
        }else {
           return '<img src="images/icon_status_green_light.gif"/>&nbsp;<img src="images/icon_status_red.gif"/>';
        }
        case '1':
        case 1:
           if(status == 1) {
               return '<img class="img-button" src="images/icon_status_green.gif"/>&nbsp;<img class="img-button btn-status-off" style="cursor: pointer"src="images/icon_status_red_light.gif"/>';
           }else {
               return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif"/>&nbsp;<img class="img-button" src="images/icon_status_red.gif"/>';
        }
    }
  };
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'content_description', header: 'Description', dataIndex: 'content_description', sortable: true},
    { id: 'created_by', header: TocLanguage.creator, dataIndex: 'created_by', sortable: true},
    { header: 'Publié', align: 'center', renderer: renderPublish, dataIndex: 'content_status'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'content_description';

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
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
      text: TocLanguage.copy,
      iconCls: 'icon-copy-record',
      handler: this.onBathCopy,
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
  
  Toc.bi.reportsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.bi.reportsGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = new Toc.bi.reportsDialog({permissions : this.permissions});
    //var path = this.mainPanel.getCategoryPath();
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(null,null, this.categoriesId);
  },

  setPermissions: function(permissions) {
//console.debug(permissions);
    this.topToolbar.items.items[0].disable();
    this.topToolbar.items.items[4].disable();
    this.topToolbar.items.items[6].disable();
    if(permissions)
    {
        if(permissions.can_write === 1 || permissions.can_modify !== 0 || permissions.can_publish === 1)
        {
            this.topToolbar.items.items[0].enable();
        }
        if(permissions.can_modify == '')
        {
            //this.topToolbar.items.items[0].enable();
        }
    }

    this.permissions = permissions;
  },

  onEdit: function(record) {
    var dlg = new Toc.bi.reportsDialog({permissions : this.permissions});
    //var path = this.mainPanel.getCategoryPath();
    dlg.setTitle(record.get("content_name"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get("dashboards_id"),record.get("created_by"),this.categoriesId);
  },

  onRun: function(record) {
    this.getEl().mask('Chargement Metadata ...');
            Ext.Ajax.request({
                method : 'GET',
                url: Toc.CONF.CONN_URL,
                params: {
                    module : 'databases',
                    action: 'get_currentuser'
                },
                callback: function (options, success, response) {
                    this.getEl().unmask();
                    var result = Ext.decode(response.responseText);

                    if(result.success)
                    {
                       if (this.items) {
                        this.removeAll(true);
                       }

                       //var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo REDASH_URL . '/login?next=' . REDASH_URL . '/data_sources&email='; ?>' + result.username + '&password=12345'},height: 600,width: 600});

                       var cmp = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: record.get("reports_uri") + '?fullscreen&username=' + result.username + '@gmail.com&password=' + '<?php echo METABASE_DEV_PASS; ?>'},height: 600,id: 'dashboard_iframe' + record.get("dashboards_id"),width: 600});
                       var pnl = new Ext.Panel();
                       pnl.add(cmp);

                       var win = new Ext.Window({
                         title: record.get("content_name"),
                         width: 870,
                         height: 400,
                         iconCls: 'icon-report-win',
                         layout: 'fit',
                         items: pnl
                      });

                      win.show();
                      win.maximize();
                    }
                    else
                    {
                       if(windows && windows.close)
                       {
                         windows.close();
                       }

                       Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                    }
                },
                scope: this
            });
  },

  runDashboard: function(record) {
    this.getEl().mask('Chargement Metadata ...');
            Ext.Ajax.request({
                method: 'GET',
                url: '<?php echo METABASE_URL; ?>' + '/api/user/current',
                headers: {
                    Accept: 'application/json',
                    'Content-Type' : 'application/json'
                },
                callback: function (options, success, response) {
                    this.getEl().unmask();
                    var result = Ext.decode(response.responseText);

                    if(result.id > 0)
                    {
                      if(this.tab)
                      {
                        var cmp = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none'},height: 600,id: 'dashboard_iframe' + record.get("dashboards_id"),width: 600});
                        var pnl = new Ext.Panel({id : 'pnl_iframe_' + record.get("dashboards_id"),closable : true});

                        //console.debug(cmp);
                        this.tab.remove('pnl_iframe_' + record.get("dashboards_id"));
                        this.tab.add(pnl);
                        this.tab.activate(pnl);
                        pnl.add(cmp);
                        pnl.setTitle(record.get("content_name"));
                        pnl.doLayout(true, true);
                        this.tab.doLayout(true, true);
                        cmp.el.dom.src = record.get("reports_uri") + '?id=' + result.id + '&username=' + result.email + '&password=' + '<?php echo METABASE_DEV_PASS; ?>';

                        cmp.el.dom.onload = function() {
                            console.log('iframe onload ...')
                            pnl.getEl().unmask();
                        };

                        pnl.getEl().mask('<?php echo $osC_Language->get('loading'); ?>' + ' ' + record.get("content_name"));
                      }
                    }
                    else
                    {
                       Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                    }
                },
                scope: this
            });
  },

  onBathMove: function () {
    var keys = this.getSelectionModel().selections.keys;

    if (keys.length > 0) {
      var batch = keys.join(',');
      var dialog = new Toc.content.ContentMoveDialog();
      dialog.setTitle('Deplacer vers un autre Espace ...');

      dialog.on('saveSuccess', function() {
        this.mainPanel.getCategoriesTree().refresh();
      }, this);

      dialog.show(batch,null,'reports');
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
      }
  },

  onBathCopy: function () {
    var keys = this.getSelectionModel().selections.keys;

    if (keys.length > 0) {
      var batch = keys.join(',');
      var dialog = new Toc.content.ContentCopyDialog();
      dialog.setTitle('Copier vers autres Espace ...');

      dialog.on('saveSuccess', function() {
        this.mainPanel.getCategoriesTree().refresh();
      }, this);

      dialog.show(batch,null,'reports');
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
      }
  },

  onDelete: function(record) {
    var reportsId = record.get('dashboards_id');
    var owner = record.get("created_by");
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'reports',
              action: 'delete_dashboard',
              dashboards_id: reportsId,
              owner : owner
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

  onBatchDelete: function() {
    var keys = this.selModel.selections.keys;
    
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
                module: 'reports',
                action: 'delete_dashboards',
                batch: batch
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
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
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

      case 'run':
        //this.onRun(record);
        this.runDashboard(record);
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
        var reportsId = this.getStore().getAt(row).get('dashboards_id');
        var module = 'setDashboardStatus';

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, reportsId, flag);

            break;
        }
      }
    }
  },
  
  onAction: function(action, reportsId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'reports',
        action: action,
        dashboards_id: reportsId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(reportsId).set('content_status', flag);
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