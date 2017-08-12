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

  echo 'Ext.namespace("Toc.signatures");';
  
  include('signatures_grid.php');
?>

Ext.override(TocDesktop.SignaturesWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('signatures-win');
     
    if(!win){
      var grd = new Toc.signatures.SignaturesGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'signatures-win',
        title: "Signatures",
        width: '100%',
        height: '100%',
        iconCls: 'icon-signatures-win',
        resizable : false,
        maximized : true,
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});
