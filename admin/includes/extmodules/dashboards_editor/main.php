<?php
  echo 'Ext.namespace("Toc.dashboards_editor");';

  include('dashboards_editor_main_panel.php');
?>

Ext.override(TocDesktop.DashboardsEditorWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('dashboards_editor-win');
     
    if(!win){
      var pnl = new Toc.dashboards_editor.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'dashboards_editor-win',
        title: 'Dashboard Editor',
        width: 800,
        height: 600,
        iconCls: 'icon-dashboards_editor-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
    pnl.start(win);
  }
});