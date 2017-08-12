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

  echo 'Ext.namespace("Toc.ctx");';

//include('agences_panel.php');
  //include('ctx_data_panel.php');
  include('agences_tree_panel.php');
  include('ctx_grid.php');
  include('ctxgrid.php');
  include('amplitude_ctx_main_panel.php');
  include('ctx_main_panel.php');
  //include('ctx_dialog.php');
?>

Ext.override(TocDesktop.CtxWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('ctx-win');

    if (!win) {
      pnl = new Toc.ctx.AmplitudemainPanel({owner: this});
      ctxpnl = new Toc.ctx.mainPanel({owner: this});

      var tabctx = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
         pnl,ctxpnl
      ]
    });

      win = desktop.createWindow({
        id: 'ctx-win',
        title: 'Clients en Contentieux',
        width: 800,
        height: 400,
        iconCls: 'icon-ctx-win',
        layout: 'fit',
        items: tabctx
      });
    }

    win.show();
    win.maximize();
  }
});