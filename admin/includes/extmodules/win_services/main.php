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

  echo 'Ext.namespace("Toc.win_services");';
  include('service_install_dialog.php');
  include('win_services_grid.php');
?>

Ext.override(TocDesktop.WinServicesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('win_services-win');

    if (!win) {
      var grd = new Toc.win_services.WinServicesGrid({owner: this});

      win = desktop.createWindow({
        id: 'win_services-win',
        title: 'Services',
        width: 800,
        height: 400,
        iconCls: 'icon-win_services-win',
        layout: 'fit',
        items: grd
      });
    }

    win.show();
  },

  createWinServicesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('win_services-dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.win_services.WinServicesDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }

});
