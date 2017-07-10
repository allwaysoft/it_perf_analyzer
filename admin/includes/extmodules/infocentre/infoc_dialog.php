<?php
/*
  $Id: databases_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.infoc.DatabasesDialog = function(config) {

  config = config || {};
  
  config.id = 'databases_dialog-win';
  config.title = 'New Database';
  config.layout = 'fit';
  config.width = 465;
  config.height = 310;
  config.modal = true;
  config.iconCls = 'icon-databases-win';
  config.items = this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess': true});  
  
  Toc.infoc.DatabasesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.infoc.DatabasesDialog, Ext.Window, {
  
  show: function (json, cId,owner) {
    this.owner = owner || null;
    if(json)
    {
       this.databasesId = json.databases_id || null;
       this.label = json.label || null;
       this.servers_id = json.servers_id || null;
       this.server_user = json.server_user || null;
       this.server_pass = json.server_pass || null;
       this.server_port = json.server_port || null;
       this.db_user = json.db_user || null;
       this.db_pass = json.db_pass || null;
       this.db_port = json.port || null;
       this.sid = json.sid || null;
       this.host = json.host || null;
       this.typ = json.typ || null;
    }

    var categoriesId = cId || -1;
    
    this.frmDatabase.form.reset();
    this.frmDatabase.form.baseParams['databases_id'] = this.databasesId;
    this.frmDatabase.form.baseParams['current_category_id'] = categoriesId;
    Toc.databases.DatabasesDialog.superclass.show.call(this);
    this.loadDatabase(this.pnlData);
    this.setTitle(json.content_name);
  },

  loadDatabase : function(panel){
     if (this.databasesId && this.databasesId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement infos DB....');
      }
        
      this.frmDatabase.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'servers',
          action: 'load_database'
        },
        success: function(form, action) {
          this.pnlData.setServer(action.result.data.servers_id);
          if(panel)
          {
             panel.getEl().unmask();
          }

          //this.tabdatabases.removeAll();

          this.pnlUsers =  new Toc.databases.usersGrid({label:this.label,databases_id : this.databasesId,sid:this.sid,host : this.host,db_port : this.db_port,db_pass : this.db_pass,db_user : this.db_user,owner : this.owner});
          this.pnlLogs =  new Toc.databases.logPanel({host : this.host,server_port : this.server_port,server_pass : this.server_pass,server_user : this.server_user,servers_id : this.servers_id,content_id : this.databasesId,content_type : 'databases',owner : this.owner});
          this.pnlTbs =  new Toc.tbsPanel({sid:this.sid,host : this.host,db_port : this.db_port,db_pass : this.db_pass,db_user : this.db_user,owner : this.owner});
          this.pnlDatafiles =  new Toc.datafilesGrid({sid:this.sid,host : this.host,db_port : this.db_port,db_pass : this.db_pass,db_user : this.db_user,owner : this.owner});
          this.pnlFS =  new Toc.fsGrid({host : this.host,server_port : this.server_port,server_pass : this.server_pass,server_user : this.server_user,servers_id : this.servers_id,owner : this.owner,typ : this.typ});
          this.pnlTables =  new Toc.databases.tablesGrid({sid:this.sid,host : this.host,db_port : this.db_port,db_pass : this.db_pass,db_user : this.db_user,owner : this.owner});
          this.pnlIndexes =  new Toc.databases.indexesGrid({sid:this.sid,host : this.host,db_port : this.db_port,db_pass : this.db_pass,db_user : this.db_user,owner : this.owner});
          this.pnlNotifications =  new Toc.databases.notificationsGrid({databases_id : this.databasesId,owner : this.owner});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.databasesId,content_type : 'databases',owner : this.owner});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.databasesId,content_type : 'databases',owner : this.owner});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.databasesId,content_type : 'databases',owner : this.owner});

          this.tabdatabases.add(this.pnlUsers);
          this.tabdatabases.add(this.pnlLogs);
          this.tabdatabases.add(this.pnlTbs);
          this.tabdatabases.add(this.pnlDatafiles);
          this.tabdatabases.add(this.pnlTables);
          this.tabdatabases.add(this.pnlIndexes);
          this.tabdatabases.add(this.pnlFS);
          this.tabdatabases.add(this.pnlNotifications);
          this.tabdatabases.add(this.pnlDocuments);
          this.tabdatabases.add(this.pnlLinks);
          this.tabdatabases.add(this.pnlComments);

          //this.setWidth(850);
          //this.setHeight(570);
          this.maximize();
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.close();
        },
        scope: this
      });
    }
    else
    {
        this.pnlData.loadServers();
    }
  },

  getContentPanel: function() {
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
    this.pnlData = new Toc.databases.DataPanel({parent : this});
    this.pnlData.setTitle('Connexion');
        
    this.tabdatabases = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData
      ]
    });

    return this.tabdatabases;
  },
  
  buildForm: function() {
    this.frmDatabase = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'servers',
        action : 'save_database'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });
    
    return this.frmDatabase;
  },

  submitForm: function() {
    var data = this.pnlData.getServerData();
    var params = {
       servers_id: data.json.servers_id,
       host: data.json.host
    };

    this.frmDatabase.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});