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

Toc.databases.dirGrid = function(config) {
  var that = this;
  config = config || {};
  //config.region = 'center';
  config.loadMask = true;
  config.title = '';
  config.border = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  config.paths = [];

  config.listeners = {
   'rowclick' : this.onRowClick
  };

  config.path_store = new Ext.data.SimpleStore({
    fields: ['path'],
    autoLoad: false
  });

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'fsmanager',
      action: 'list_dir'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'file_name'
    }, [
      'type',
      'permission',
      'file_name',
      'icon',
      'owner',
      'group',
      'date_mod',
      'size'
    ]),
    listeners : {
            load : function(store,records,opt) {
               this.owner.setTitle(opt.params.path);
               this.path = opt.params.path;

               var path = Ext.data.Record.create([
                  {name: "path", type: "string"}
               ]);

               var record = new path({
                 path: opt.params.path
               });

               if(this.paths.indexOf(opt.params.path) <= -1)
               {
                  this.path_store.add(record);
                  this.path_store.sort('path','asc');
                  this.paths.push(opt.params.path);
               }
            },
            beforeload : function(store,opt) {
            },scope: this
        },
    autoLoad: false
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-open-record', qtip: 'Ouvrir'},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete},
      {iconCls: 'icon-move-record', qtip: 'Deplacer'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {header: '', dataIndex: 'icon', width : 24},
    {id:'permission',header: 'Permissions', dataIndex: 'permission',align: 'center'},
    {id:'file_name',header: 'Nom', dataIndex: 'file_name'},
    {header: 'Owner', dataIndex: 'owner',align: 'center'},
    {header: 'Group', dataIndex: 'group',align: 'center'},
    {header: 'Date', dataIndex: 'date_mod',align: 'center'},
    { header: 'Taille', align: 'center', dataIndex: 'size',sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'file_name';
  config.stripeRows = true;

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.path_combo = new Ext.form.ComboBox({
    typeAhead: true,
    forceSelection: true,
    mode: 'local',
    triggerAction: 'all',
    fieldLabel : 'Navigation',
    selectOnFocus: true,
    store:config.path_store,
    valueField: 'path',
    displayField: 'path',
    width : 300,
    listeners : {
       select : function(combo,record,index) {
          //console.debug(record);
          var path = record.data.path;
          if(path != this.path)
          {
             this.owner.setPath(path);
          }
       },scope: this
    },
  });

  config.tbar = [
    { 
      text: '',
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    },
    '-',
    {
      text: '',
      iconCls: 'icon-delete-record',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
    {
      text: '',
      iconCls: 'icon-move-record',
      handler: this.onBatchMove,
      scope: this
    },
    '->',
    config.path_combo,
    '-',
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
  Toc.databases.dirGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.dirGrid, Ext.grid.GridPanel, {

  onOpen: function(record) {
    var type = record.get("type");

    if(type == 'folder')
    {
       var folder = record.get("file_name");
       var current_path = this.owner.getPath();
       var new_folder = current_path + '/' + folder;

       if(folder == "..")
       {
          var n = current_path.lastIndexOf("/");
          new_folder = current_path.substring(0,n);
       }

       if(folder == ".")
       {
          new_folder = current_path;
       }

       if(new_folder.length == 0)
       {
          new_folder = '/';
       }

       this.owner.setPath(new_folder);
    }
    else
    {
       var dlg = this.owner.owner.createFileViewerDialog();

       var file = record.get("file_name");
       var current_path = this.owner.getPath();
       var config = this.owner.getConfig();
       var new_file = current_path + '/' + file;
       config.url = new_file;
       dlg.setTitle(new_file);
       dlg.show(config,this.owner);
    }
  },

  onBatchDelete: function() {
    var selections = this.getSelectionModel().selections;
    var keys = selections.keys;
    var items = selections.items;
    var files = '';
    var folders = '';

    if (keys.length > 0) {
      //var batch = keys.join(',');

      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm,
        function(btn) {
            if (btn == 'yes') {
            var config = this.owner.getConfig();
            var current_path = this.owner.getPath();

            var i = 0;
            while(i < items.length)
            {
               var item = items[i];

               if(item.json.file_name != ".." && item.json.file_name != ".")
               {
                  if(item.json.type == 'file')
                  {
                    files = files + current_path + '/' + item.json.file_name + ';';
                  }
                  else
                  {
                    folders = folders + current_path + '/' + item.json.file_name + ';';
                  }
               }

               i++;
            }

            this.el.mask('Suppression en cours ...');
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'fsmanager',
                host:config.host,
                port:config.server_port,
                user:config.server_user,
                pass:config.server_pass,
                action: 'batch_delete',
                files: files,
                folders : folders
              },
              callback: function(options, success, response) {
                this.el.unmask();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                  this.owner.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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

  onBatchMove: function() {
    var selections = this.getSelectionModel().selections;
    var keys = selections.keys;
    var items = selections.items;
    var contents = [];

    if (items.length > 0) {
      //var batch = keys.join(',');

      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        'Voulez vous vraiment deplacer ces elements ?',
        function(btn) {
          if (btn == 'yes') {
            var config = this.owner.getConfig();
            config.module = 'fsmanager';

            var current_path = this.owner.getPath();
            var i = 0;
            while(i < items.length)
            {
               var content = items[i];

               var file = content.json.file_name;
               var new_url = content.json.type == 'file' ? current_path + '/' + file : current_path + '/' + file + '/';

               var _content = {};
               _content.url = new_url;
               _content.typ = content.json.type;
               if(file != '.' && file != '..')
               {
                  contents.push(_content);
               }

               i++;
            }

            var dlg = new Toc.MovefileDialog();
            dlg.setTitle('Deplacer du contenu');
            dlg.on('saveSuccess', function(){
               this.onRefresh();
            }, this);
            dlg.show(config,this.owner,contents);
          }
        },
        this
      );
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },

  onMove: function(record) {
    var type = record.get("type");

    if(type == 'file')
    {
       var dlg = new Toc.MovefileDialog();

       var file = record.get("file_name");
       var current_path = this.owner.getPath();
       var config = this.owner.getConfig();
       var new_file = current_path + '/' + file;
       config.url = new_file;
       config.action = 'move_file';
       config.module = 'fsmanager';
       dlg.setTitle('Deplacer le fichier ' + new_file);
       dlg.on('saveSuccess', function(){
          this.onRefresh();
       }, this);

       var contents = [];
       var content = {};
       content.url = config.url;
       content.typ = 'file';
       contents[0] = content;
       dlg.show(config,this.owner,contents);
    }
  },
  
  onDelete: function(record) {
    var type = record.get("type");
    var url = "";
    var action = 'delete_file';
    var config = this.owner.getConfig();

    if(type == 'folder')
    {
       var folder = record.get("file_name");

       if(folder != ".." && folder != ".")
       {
          var current_path = this.owner.getPath();
          url = current_path + '/' + folder;
          action = 'delete_folder';
       }
       else
       {
          return;
       }
    }
    else
    {
       var file = record.get("file_name");
       var current_path = this.owner.getPath();
       url = current_path + '/' + file;
    }
    
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            console.debug(this);
            this.el.mask('Suppresion en cours ....');
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'fsmanager',
                action: action,
                user:config.server_user,
                pass:config.server_pass,
                port:config.server_port,
                host:config.host,
                url:url
              },
              callback: function(options, success, response) {
                this.el.unmask();
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, this);
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
      
      case 'icon-open-record':
        this.onOpen(record);
        break;

      case 'icon-move-record':
        this.onMove(record);
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