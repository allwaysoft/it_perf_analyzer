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

  echo 'Ext.namespace("Toc.who_online");';
  
  include('deltausers_grid.php');
?>

Ext.override(TocDesktop.WhoOnlineWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('who_online-win');
     
    if(!win){
      var grd = new Toc.who_online.WhoOnlineGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'who_online-win',
        title: "Utilisateurs en ligne",
        width: 800,
        height: 400,
        iconCls: 'icon-whos_online-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});
