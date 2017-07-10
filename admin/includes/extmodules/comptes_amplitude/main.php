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

  echo 'Ext.namespace("Toc.ncp");';

//include('agences_panel.php');
  //include('ctx_data_panel.php');
  include('agences_tree_panel.php');
  include('ncp_grid.php');
  //include('ncpgrid.php');
  include('amplitude_ncp_main_panel.php');
  //include('ncp_main_panel.php');
  //include('ctx_dialog.php');
?>

Ext.override(TocDesktop.ComptesAmplitudeWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('ncp-win');

    if (!win) {
      pnl = new Toc.ncp.AmplitudemainPanel({owner: this});
      //ncppnl = new Toc.ncp.mainPanel({owner: this});

      var tabncp = new Ext.TabPanel({
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
        id: 'ncp-win',
        title: 'Comptes AMPLITUDE',
        width: 800,
        height: 400,
        iconCls: 'icon-ncp-win',
        layout: 'fit',
        items: tabncp
      });
    }

    win.show();
    win.maximize();
  }
});