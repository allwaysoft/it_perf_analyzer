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

  echo 'Ext.namespace("Toc.lettrage");';
  
  include('lettrage_grid.php');
?>

Ext.override(TocDesktop.LettrageWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('lettrage-win');
     
    if(!win){
      var grd = new Toc.lettrage.LettrageGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'lettrage-win',
        title: "Lettrage Comptes",
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
