<?php
/*
  $Id: main.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.production");';
?>

Ext.override(TocDesktop.ProductionWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('production-win');
     
    if (!win) {
      var dashboard = new Toc.ProductionDashboard({owner : this});

      var tabDashboard = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [dashboard]
      });

      win = desktop.createWindow({
        id: 'production-win',
        title: 'Production T2SA',
        width: 800,
        height: 400,
        iconCls: 'icon-servers-win',
        layout: 'fit',
        items: tabDashboard
      });
    }

    win.show();
    win.maximize();
  }
});