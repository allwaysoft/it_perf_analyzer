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

  echo 'Ext.namespace("Toc.documents");';

  include('documents_main_panel.php');
  include('documents_dialog.php');
  include('documents_grid.php');
?>

Ext.override(TocDesktop.DocumentsWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('documents-win');
     
    if (!win) {
      pnl = new Toc.documents.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'documents-win',
        title: 'Documents',
        width: 800,
        height: 400,
        iconCls: 'icon-documents-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
  },
  
  createDocumentsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('documents-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.documents.DocumentsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});