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

Toc.infoc.CreateUserDialog = function(config) {

  config = config || {};
  
  config.id = 'infoc_create_user_dialog-win';
  config.title = 'Creer un Compte';
  config.region = 'center';
  config.width = 470;
  config.height = 187;
  config.modal = true;
  config.iconCls = 'icon-databases-win';
  config.items = this.buildForm(config);
  
  config.buttons = [
    {
      text: 'OK',
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
  
  Toc.infoc.CreateUserDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.infoc.CreateUserDialog, Ext.Window, {
  
  show: function (json,caller) {
    if(json)
    {
       this.db_user = caller.db_user || null;
       this.databases_id = caller.databases_id || null;
       this.db_pass = caller.db_pass || null;
       this.db_port = caller.port || null;
       this.db_sid = caller.sid || null;
       this.db_host = caller.host || null;
       this.typ = caller.typ || null;
       this.label = caller.label || null;
    }
    
    this.frmUser.form.reset();
    this.frmUser.form.baseParams['username'] = this.username;
    this.frmUser.form.baseParams['label'] = this.label;
    this.frmUser.form.baseParams['databases_id'] = this.databases_id;
    this.frmUser.form.baseParams['db_user'] = this.db_user;
    this.frmUser.form.baseParams['db_pass'] = this.db_pass;
    this.frmUser.form.baseParams['db_port'] = this.db_port;
    this.frmUser.form.baseParams['db_sid'] = this.db_sid;
    this.frmUser.form.baseParams['db_host'] = this.db_host;
    Toc.infoc.CreateUserDialog.superclass.show.call(this);
  },

  buildForm: function(config) {
    config.panel = this;
    //this.tbsCombo = new Toc.content.ContentManager.getTbsCombo(config);
    //this.TemptbsCombo = new Toc.content.ContentManager.getTempTbsCombo(config);
    //this.profileCombo = new Toc.content.ContentManager.getProfilesCombo(config);
    this.roleCombo = new Toc.content.ContentManager.getRolesCombo(config);
    this.frmUser = new Ext.form.FormPanel({
      //layout: 'border',
      url: Toc.CONF.CONN_URL,
      labelWidth : 125,
      baseParams: {  
        module: 'users',
        action : 'create_userinfoc'
      },
      deferredRender: false,
      items: [
         {xtype:'textfield', fieldLabel: 'Libelle', name: 'libelle',allowBlank:false,style:"width: 300px;"},
         {xtype:'textfield', fieldLabel: 'Email', name: 'email',allowBlank:false,style:"width: 300px;",vtype:'email'},
         {xtype:'textfield', fieldLabel: 'Compte', name: 'account',allowBlank:false,style:"width: 300px;"},
         this.roleCombo
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