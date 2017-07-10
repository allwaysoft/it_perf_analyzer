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

  echo 'Ext.namespace("Toc.users");';

  include('roles_tree_panel.php');
  include('users_data_panel.php');
  include('roles_panel.php');
  include('users_main_panel.php');
  include('users_dialog.php');
  include('users_grid.php');
?>

Ext.override(TocDesktop.UsersWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('users-win');
     
    if (!win) {
      pnl = new Toc.users.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'users-win',
        title: 'Gestion des Comptes Utilisateurs',
        width: 800,
        height: 400,
        iconCls: 'icon-users-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createUsersDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('users-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.users.UsersDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});