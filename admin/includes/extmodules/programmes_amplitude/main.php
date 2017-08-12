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

  echo 'Ext.namespace("Toc.programmes_amplitude");';
  
  include('programmes_grid.php');
?>

Ext.override(TocDesktop.ProgrammesAmplitudeWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('programmes_amplitude-win');
     
    if(!win){
      var grd = new Toc.programmes_amplitude.ProgrammesAmplitudeGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'programmes_amplitude-win',
        title: "Programmes Amplitude",
        width: 800,
        height: 400,
        iconCls: 'icon-programmes_amplitude-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});
