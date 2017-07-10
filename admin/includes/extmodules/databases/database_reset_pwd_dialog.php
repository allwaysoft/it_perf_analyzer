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

Toc.databases.ResetPwdDialog = function(config) {

  config = config || {};
  
  config.id = 'databases_reset_pwd_dialog-win';
  config.title = 'Reinitialiser un mot de passe';
  config.region = 'center';
  config.width = 443;
  config.height = 130;
  config.modal = true;
  config.iconCls = 'icon-databases-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: 'Envoyer',
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
  
  Toc.databases.ResetPwdDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.databases.ResetPwdDialog, Ext.Window, {
  
  show: function (json,caller) {
    if(json)
    {
       this.username = json.username || null;
       this.db_user = caller.db_user || null;
       this.db_pass = caller.db_pass || null;
       this.db_port = caller.port || null;
       this.db_sid = caller.sid || null;
       this.db_host = caller.host || null;
       this.typ = caller.typ || null;
    }
    
    this.frmUser.form.reset();
    this.frmUser.form.baseParams['account'] = this.username;
    this.frmUser.form.baseParams['db_user'] = this.db_user;
    this.frmUser.form.baseParams['db_pass'] = this.db_pass;
    this.frmUser.form.baseParams['db_port'] = this.db_port;
    this.frmUser.form.baseParams['db_sid'] = this.db_sid;
    this.frmUser.form.baseParams['db_host'] = this.db_host;
    this.frmUser.form.baseParams['label'] = caller.label;
    this.frmUser.form.baseParams['databases_id'] = caller.databases_id;
    Toc.databases.ResetPwdDialog.superclass.show.call(this);
    this.loadUser(this.frmUser);
  },

  loadUser : function(panel){
     if (this.username) {
      if(panel)
      {
        panel.getEl().mask('Chargement infos User....');
      }
        
      this.frmUser.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'users',
          action: 'get_user'
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }
        },
        failure: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }
        },
        scope: this
      });
    }
  },
  
  buildForm: function(config) {
    this.frmUser = new Ext.form.FormPanel({
      //layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'users',
        action : 'change_pwd'
      },
      deferredRender: false,
      items: [
         {xtype:'textfield', fieldLabel: 'Nom', name: 'name', id: 'name',allowBlank:false,style:"width: 300px;"},
         {xtype:'textfield', fieldLabel: 'Email', name: 'email', id: 'email',allowBlank:false,style:"width: 300px;",vtype:'email'}
      ]
    });
    
    return this.frmUser;
  },

  submitForm: function() {
    this.frmUser.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
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