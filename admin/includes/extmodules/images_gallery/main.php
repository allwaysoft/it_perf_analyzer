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

  echo 'Ext.namespace("Toc.images_gallery");';

  include('images_gallery_dialog.php');
  include('images_gallery_grid.php');
  include('images_gallery_main_panel.php');
?>

Ext.override(TocDesktop.ImagesGalleryWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('images_gallery-win');
     
    if (!win) {
      pnl = new Toc.images_gallery.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'images_gallery-win',
        title: 'Gestion des Galleries d\'images',
        width: 800,
        height: 400,
        iconCls: 'icon-images_gallery-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createImagesGalleryDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('images_gallery-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.images_gallery.ImagesGalleryDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});