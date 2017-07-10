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

  echo 'Ext.namespace("Toc.deltausers");';

  include('roles_tree_panel.php');
  include('deltausers_data_panel.php');
  include('roles_panel.php');
  include('deltausers_main_panel.php');
  include('deltausers_dialog.php');
  include('deltausers_grid.php');
?>

Ext.override(TocDesktop.DeltausersWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('deltausers-win');

    if (!win) {
      pnl = new Toc.deltausers.mainPanel({owner: this});

      win = desktop.createWindow({
        id: 'deltausers-win',
        title: 'Gestion des Comptes Amplitude',
        width: 800,
        height: 400,
        iconCls: 'icon-deltausers-win',
        layout: 'fit',
        items: pnl
      });
    }

    win.show();
  },

  createdeltausersDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('deltausers-dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.deltausers.deltausersDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});