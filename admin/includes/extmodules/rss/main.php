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

  echo 'Ext.namespace("Toc.rss");';

  include_once('../admin/includes/extmodules/categories/main.php');
  include('rss_data_panel.php');
  include('rss_main_panel.php');
  include('rss_dialog.php');
  include('rss_grid.php');
?>

Ext.override(TocDesktop.RssWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('rss-win');
     
    if (!win) {
      pnl = new Toc.rss.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'rss-win',
        title: 'Gestion des Flux RSS',
        width: 800,
        height: 400,
        iconCls: 'icon-rss-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createRssDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('rss-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.rss.RssDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});