<?php
/*
  $Id: tbsexplorer_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.tbsexplorer.tbsexplorerDialog = function(config) {
//console.debug(config);
  
  config = config || {};
  
  config.id = 'tbsexplorer-dialog-win';
  config.title = 'Tablespace Explorer';
  config.layout = 'fit';
  config.width = 985;
  config.height = 500;
  config.modal = true;
  config.iconCls = 'icon-tbsexplorer-win';
  config.items = this.buildForm(config);
  
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
  
  Toc.tbsexplorer.tbsexplorerDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.tbsexplorer.tbsexplorerDialog, Ext.Window, {

  show: function(json,id, cId) {
    //console.debug(json);
    if(json)
    {
       this.databases_id = json.databases_id || null;
       this.user = json.user || null;
       this.pass = json.pass || null;
       this.port = json.port || null;
       this.host = json.host || null;
    }

    var categoriesId = cId || -1;
    
    this.frmServer.form.reset();  
    this.frmServer.form.baseParams['databases_id'] = this.databases_id;
    this.frmServer.form.baseParams['current_category_id'] = categoriesId;

    Toc.tbsexplorer.tbsexplorerDialog.superclass.show.call(this);
    //var store = this.pnlTbs.getStore();

    //store.load();
  },

  getContentPanel: function(config) {
    //console.debug(config);
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
    //this.pnlFS =  new Toc.fsGrid({host : config.host,server_port : config.server_port,server_pass : config.server_pass,server_user : config.server_user,servers_id : config.servers_id,owner : config.owner});
    this.pnlTbs =  new Toc.tbsGrid({label:config.label,sid:config.sid,host : config.host,db_port : config.db_port,db_pass : config.db_pass,db_user : config.db_user,owner : config.owner,server_port : config.server_port,server_typ : config.server_typ,server_pass : config.server_pass,server_user : config.server_user,servers_id : config.servers_id});
    //this.pnlFS.setTitle('FS');
        
    this.tabtbsexplorer = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [this.pnlTbs]
    });

    return this.tabtbsexplorer;
  },
  
  buildForm: function(config) {
    this.frmServer = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'tbsexplorer',
        action : 'save_server'
      },
      deferredRender: false,
      items: [this.getContentPanel(config)]
    });

    return this.frmServer;
  },
  
  submitForm : function() {
    var params = {
    };

    this.frmServer.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
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