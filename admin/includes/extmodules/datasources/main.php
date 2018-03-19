<?php
  echo 'Ext.namespace("Toc.datasources");';

  include('datasources_main_panel.php');
?>

Ext.override(TocDesktop.DatasourcesWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('datasources-win');
     
    if(!win){
      var pnl = new Toc.datasources.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'datasources-win',
        title: 'Sources de Données',
        width: 800,
        height: 600,
        iconCls: 'icon-datasources-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    //win.maximize();
    pnl.start(win);
  }
});