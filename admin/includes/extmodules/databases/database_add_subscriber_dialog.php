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

Toc.databases.AddSubscriberDialog = function(config) {

  config = config || {};
  
  config.id = 'databases_add_subscriber_dialog-win';
  config.title = 'Ajouter un Souscripteur';
  config.region = 'center';
  config.width = 443;
  config.height = 160;
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
  
  Toc.databases.AddSubscriberDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.databases.AddSubscriberDialog, Ext.Window, {
  
  show: function (databases_id,event) {
    this.frmUser.form.baseParams['databases_id'] = databases_id;
    this.frmUser.form.baseParams['event'] = event;
    Toc.databases.AddSubscriberDialog.superclass.show.call(this);
  },

  buildForm: function() {
    this.frmUser = new Ext.form.FormPanel({
      //layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'users',
        action : 'add_subscriber'
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