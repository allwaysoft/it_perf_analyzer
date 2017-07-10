<?php
/*
  $Id: main.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.sms");';

  include('status_tree_panel.php');
  include('sms_data_panel.php');
  //include('roles_panel.php');
  include('sms_main_panel.php');
  //include('sms_dialog.php');
  include('sms_grid.php');
?>

Ext.override(TocDesktop.SmsWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('sms-win');

    if (!win) {
      pnl = new Toc.sms.mainPanel({owner: this});

      win = desktop.createWindow({
        id: 'sms-win',
        title: 'SMS',
        width: 800,
        height: 400,
        iconCls: 'icon-sms-win',
        layout: 'fit',
        items: pnl
      });
    }

    win.show();
  },

  createsmsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('sms-dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.sms.smsDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});