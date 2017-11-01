<?php
  echo 'Ext.namespace("Toc.kibana");';

  include('kibana_main_panel.php');
?>

Ext.override(TocDesktop.KibanaWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('kibana-win');
     
    if(!win){
      var pnl = new Toc.kibana.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'kibana-win',
        title: 'KIBANA',
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