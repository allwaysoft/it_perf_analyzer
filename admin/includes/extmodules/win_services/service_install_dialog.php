<?php
/*
  $Id: unit_classes_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.win_services.WinServicesDialog = function (config) {
  config = config || {};
  
  config.id = 'win_services-dialog-win';
  config.layout = 'fit';
  config.width = 400;
  config.height = 120;
  config.modal = true;
  config.iconCls = 'icon-win_services-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
      },
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  this.addEvents({'saveSuccess': true});
  
  Toc.win_services.WinServicesDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.win_services.WinServicesDialog, Ext.Window, {
  
  show: function (win_service_id) {
    var win_service_id = win_service_id || null;

    this.frmWinServices.form.reset();
    this.frmWinServices.form.baseParams['win_service_id'] = win_service_id;
    
    if (win_service_id > 0) {
      this.frmWinServices.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_win_service'
        },
        success: function(form, action) {
          Toc.win_services.WinServicesDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this       
      });
    } else {
      Toc.win_services.WinServicesDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function () {
    this.frmWinServices = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'win_services',
        action: 'create_service'
      },
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 150,
      items: [
        {
          xtype: 'textfield',
          fieldLabel: 'User',
          name: 'user'          
        },
        {
          xtype: 'textfield',
          fieldLabel: 'Password',
          name: 'password'          
        }
      ]
      });
    
    return this.frmWinServices; 
  },
  
  submitForm: function () {
    this.frmWinServices.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback)
        }
      },
      scope: this
    });
  }
});