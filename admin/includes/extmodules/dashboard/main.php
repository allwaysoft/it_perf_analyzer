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

  include('templates/default/extensions/portal/portal-min.js');

  echo 'Ext.namespace("Toc.dashboard");';  
  
  include('dashboard.php');   
?>

Ext.override(TocDesktop.DashboardWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('dashboard-win');
    
    if (!win) {         
      win = desktop.createWindow(null, Toc.dashboard.Dashboard);
    }   
    
    win.show();
  }
});