<?php
/*
  $Id: servers_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.servers.serversDialog = function(config) {
  
  config = config || {};
  
  config.id = 'servers-dialog-win';
  config.title = 'Nouveau Serveur';
  config.layout = 'fit';
  config.width = 465;
  config.height = 310;
  config.modal = true;
  config.iconCls = 'icon-servers-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.servers.serversDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.servers.serversDialog, Ext.Window, {

  show: function(json,id, cId) {
    
    if(json)
    {
       this.serversId = json.servers_id || null;
       this.servers_id = json.servers_id || null;
       this.server_user = json.user || null;
       this.server_pass = json.pass || null;
       this.server_port = json.port || null;
       this.host = json.host || null;
       this.typ = json.typ || null;
    }

    //this.serversId = id || null;
    var categoriesId = cId || -1;
    
    this.frmServer.form.reset();  
    this.frmServer.form.baseParams['servers_id'] = this.serversId;
    this.frmServer.form.baseParams['current_category_id'] = categoriesId;

    Toc.servers.serversDialog.superclass.show.call(this);
    this.loadServer(this.pnlData);
  },

  loadServer : function(panel){
     if (this.serversId && this.serversId >= 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement infos serveur....');
      }
        
      this.frmServer.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_server',
          servers_id: this.serversId
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.pnlData.setTyp(action.result.data.typ);
          this.pnlLogs =  new Toc.servers.logPanel({host : this.host,server_port : this.server_port,server_pass : this.server_pass,server_user : this.server_user,server_user : this.server_user,servers_id : this.servers_id,content_id : this.servers_id,content_type : 'servers',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.serversId,content_type : 'servers',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.serversId,content_type : 'servers',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.serversId,content_type : 'servers',owner : Toc.content.ContentManager});

          this.tabservers.add(this.pnlLogs);
          this.tabservers.add(this.pnlDocuments);
          this.tabservers.add(this.pnlLinks);
          this.tabservers.add(this.pnlComments);

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
  },

  getContentPanel: function() {
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
    this.pnlData = new Toc.servers.DataPanel({parent : this});
    this.pnlData.setTitle('Connexion');
        
    this.tabservers = new Ext.TabPanel({
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

    return this.tabservers;
  },
  
  buildForm: function() {
    this.frmServer = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'servers',
        action : 'save_server'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmServer;
  },
  
  submitForm : function() {
    var params = {
    };

    this.frmServer.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      timeout : 60,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      }, 
      scope: this
    });   
  }
});