<?php
  echo 'Ext.namespace("Toc.cerebro");';

  include('cerebro_main_panel.php');
?>

Ext.override(TocDesktop.CerebroWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('cerebro-win');
     
    if(!win){
      var pnl = new Toc.cerebro.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'cerebro-win',
        title: 'CEREBRO',
        width: 870,
        height: 400,
        iconCls: 'icon-report-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
  }
});