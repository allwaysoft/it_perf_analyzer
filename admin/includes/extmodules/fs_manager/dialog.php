<?php
/*
  $Id: fsmanager_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.fsmanager.fsmanagerDialog = function(config) {
  
  config = config || {};
  
  config.id = 'fsmanager-dialog-win';
  config.title = 'FS Explorer';
  config.layout = 'fit';
  config.width = 800;
  config.height = 400;
  config.modal = true;
  config.iconCls = 'icon-fsmanager-win';
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
  
  Toc.fsmanager.fsmanagerDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.fsmanager.fsmanagerDialog, Ext.Window, {

  show: function(json,id, cId) {
    //console.debug(json);
    if(json)
    {
       this.servers_id = json.servers_id || null;
       this.user = json.user || null;
       this.pass = json.pass || null;
       this.port = json.port || null;
       this.host = json.host || null;
       this.typ = json.typ || null;
    }

    var categoriesId = cId || -1;
    
    this.frmServer.form.reset();  
    this.frmServer.form.baseParams['servers_id'] = this.servers_id;
    this.frmServer.form.baseParams['current_category_id'] = categoriesId;

    Toc.fsmanager.fsmanagerDialog.superclass.show.call(this);
    var store = this.pnlFS.getStore();

    store.load();
  },

  getContentPanel: function(config) {
    //console.debug(config);
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
    this.pnlFS =  new Toc.fsGrid({typ : config.typ,host : config.host,server_port : config.server_port,server_pass : config.server_pass,server_user : config.server_user,servers_id : config.servers_id,owner : config.owner});
    //this.pnlFS.setTitle('FS');
        
    this.tabfsmanager = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [this.pnlFS]
    });

    return this.tabfsmanager;
  },
  
  buildForm: function(config) {
    this.frmServer = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'fsmanager',
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