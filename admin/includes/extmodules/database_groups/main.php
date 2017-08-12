<?php

  echo 'Ext.namespace("Toc.database_groups");';

//  include('roles_permissions_grid_panel.php');
  include('group_dialog.php');
  include('group_grid.php');
  //include('group_tree_panel.php');
?>

Ext.override(TocDesktop.DatabaseGroupsWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('database-win');
     
    if (!win) {
      grd = new Toc.database_groups.GroupGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'database-win',
        title: 'Groupes de Databases',
        width: 800,
        height: 400,
        iconCls: 'icon-database-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});
