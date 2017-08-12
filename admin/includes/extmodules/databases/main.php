<?php


  echo 'Ext.namespace("Toc.databases");';

  include('group_tree_panel.php');
  include('groups_panel.php');
  include('databases_main_panel.php');
?>

Ext.override(TocDesktop.DatabasesWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('databases-win');
     
    if (!win) {
      var pnl = new Toc.databases.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'databases-win',
        title: 'Referentiel Databases',
        width: 800,
        height: 400,
        iconCls: 'icon-databases-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
  }
});