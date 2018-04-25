<?php
  echo 'Ext.namespace("Toc.dashboards");';

  include('DashboardTreePanel.php');
  include('dashboards_main_panel.php');
?>

Ext.override(TocDesktop.DashboardsWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('asset-win');
     
    if(!win){
      var pnl = new Toc.dashboards.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'dashboards-win',
        title: '<?php echo $osC_Language->get('dashboards'); ?>',
        width: 870,
        height: 400,
        iconCls: 'icon-dashboards-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
  }
});