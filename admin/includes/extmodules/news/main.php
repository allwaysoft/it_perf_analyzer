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

  echo 'Ext.namespace("Toc.news");';

  include('news_data_panel.php');
  include('news_main_panel.php');
  include('news_dialog.php');
  include('news_grid.php');
?>

Ext.override(TocDesktop.NewsWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('news-win');
     
    if (!win) {
      pnl = new Toc.news.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'news-win',
        title: 'Gestion des Annonces',
        width: 800,
        height: 400,
        iconCls: 'icon-news-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createNewsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('news-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.news.NewsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});