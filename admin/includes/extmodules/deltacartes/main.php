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

  echo 'Ext.namespace("Toc.cartes");';

//include('agences_panel.php');
  //include('cartes_data_panel.php');
  include('agences_tree_panel.php');
  include('cartes_grid.php');
  include('cartesgrid.php');
  include('amplitude_cartes_main_panel.php');
  include('cartes_main_panel.php');
  //include('cartes_dialog.php');
?>

Ext.override(TocDesktop.DeltacartesWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('cartes-win');

    if (!win) {
      pnl = new Toc.cartes.AmplitudemainPanel({owner: this});

      var tabcartes = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
         pnl
      ]
    });

      win = desktop.createWindow({
        id: 'cartes-win',
        title: 'Cartes AMPLITUDE',
        width: 800,
        height: 400,
        iconCls: 'icon-cartes-win',
        layout: 'fit',
        items: tabcartes
      });
    }

    win.show();
    win.maximize();
  }
});