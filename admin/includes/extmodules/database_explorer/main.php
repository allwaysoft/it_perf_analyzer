<?php
  echo 'Ext.namespace("Toc.database_explorer");';

  include('LayoutTreePanel.php');
  include('explorer_main_panel.php');
?>

Ext.override(TocDesktop.DatabaseExplorerWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('database_explorer-win');
     
    if(!win){
      var pnl = new Toc.database_explorer.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'database_explorer-win',
        title: 'Database Explorer',
        width: 870,
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