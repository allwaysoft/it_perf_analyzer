<?php

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
