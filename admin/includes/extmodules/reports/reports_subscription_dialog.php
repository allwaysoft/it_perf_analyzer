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

Toc.reports.subscriptionDialog = function(config) {

  config = config || {};
  
  //config.id = 'databases-snapshots-dialog-win';
  //config.title = 'Snaphots browser';
  config.layout = 'fit';
  config.width = 900;
  config.height = 105;
  config.modal = true;
  config.iconCls = 'icon-feed-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:'Souscrire',
      handler: function(){
        this.submitForm();
      },
      scope:this
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

  Toc.reports.subscriptionDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.reports.subscriptionDialog, Ext.Window, {
  show: function(record) {
    this.record = record;

    Toc.reports.reportsDialog.superclass.show.call(this);
  },
  buildForm: function() {
    this.frmReport = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reports',
        action : 'subscribe_report'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmReport;
  },
  submitForm : function() {
    var params = {
       subscriptions_id : this.record.subscriptions_id
    };

    this.frmReport.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function(form, action) {
        Ext.Msg.alert(TocLanguage.msgErrTitle,action.result.msg);
      },
      scope: this
    });
  },
  getContentPanel: function() {
    return new Ext.form.TextField({fieldLabel: 'Emails', name: 'to',allowBlank : false,region : 'center'});
  }
});