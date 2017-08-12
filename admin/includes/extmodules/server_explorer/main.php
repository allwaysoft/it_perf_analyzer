<?php
  echo 'Ext.namespace("Toc.server_explorer");';

  include('LayoutTreePanel.php');
  include('explorer_main_panel.php');
?>

Ext.override(TocDesktop.ServerExplorerWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('server_explorer-win');
     
    if(!win){
      var pnl = new Toc.server_explorer.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'server_explorer-win',
        title: 'Server Explorer',
        width: 870,
        height: 400,
        iconCls: 'icon-servers-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
  }
});