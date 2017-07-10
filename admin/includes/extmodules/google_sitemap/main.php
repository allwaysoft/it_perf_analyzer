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

  echo 'Ext.namespace("Toc.google_sitemap");';
  
  include('google_sitemap_dialog.php');
?>

Ext.override(TocDesktop.GoogleSitemapWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('google_sitemap-win');
     
    if(!win){
      win = desktop.createWindow(null, Toc.google_sitemap.GoogleSitemapDialog);
    }
    
    win.show();
  }
});
