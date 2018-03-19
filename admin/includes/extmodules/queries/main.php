<?php
  echo 'Ext.namespace("Toc.queries");';

  include('queries_main_panel.php');
?>

Ext.override(TocDesktop.QueriesWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('queries-win');
     
    if(!win){
      var pnl = new Toc.queries.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'queries-win',
        title: 'Requetes',
        width: 800,
        height: 600,
        iconCls: 'icon-queries-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
    pnl.start(win);
  }
});