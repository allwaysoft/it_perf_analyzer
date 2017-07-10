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

  echo 'Ext.namespace("Toc.roles");';

//  include('roles_permissions_grid_panel.php');
  include('roles_dialog.php');
  include('roles_grid.php');
  include('roles_tree_panel.php');    
?>

Ext.override(TocDesktop.RolesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('roles-win');
     
    if (!win) {
      grd = new Toc.roles.RolesGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'roles-win',
        title: 'Gestionnaire des Profils',
        width: 800,
        height: 400,
        iconCls: 'icon-roles-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createRolesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('roles-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.roles.RolesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
