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

  echo 'Ext.namespace("Toc.permissions");';

  include('roles_tree_panel.php');
  include('permissions_main_panel.php');
?>

Ext.override(TocDesktop.UsersWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('permissions-win');
     
    if (!win) {
      pnl = new Toc.permissions.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'permissions-win',
        title: 'Permissions',
        width: 800,
        height: 400,
        iconCls: 'icon-users-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  }
});