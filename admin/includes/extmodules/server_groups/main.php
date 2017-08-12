<?php

  echo 'Ext.namespace("Toc.server_groups");';

//  include('roles_permissions_grid_panel.php');
  include('group_dialog.php');
  include('group_grid.php');
  //include('group_tree_panel.php');
?>

Ext.override(TocDesktop.ServerGroupsWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('server-win');
     
    if (!win) {
      grd = new Toc.server_groups.GroupGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'server-win',
        title: 'Groupes de Serveurs',
        width: 800,
        height: 400,
        iconCls: 'icon-server-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});
