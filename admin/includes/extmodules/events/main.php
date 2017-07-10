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

  echo 'Ext.namespace("Toc.events");';

  include('events_data_panel.php');
  include('events_main_panel.php');
  include('events_dialog.php');
  include('events_grid.php');
?>

Ext.override(TocDesktop.EventsWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('events-win');
     
    if (!win) {
      pnl = new Toc.events.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'events-win',
        title: 'Gestion des Evenements',
        width: 800,
        height: 400,
        iconCls: 'icon-events-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createEventsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('events-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.events.EventsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});