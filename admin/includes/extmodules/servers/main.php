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

    echo 'Ext.namespace("Toc.servers");';

    //include('servers_log_panel.php');
    //include('servers_data_panel.php');
    include('servers_main_panel.php');
    //include('servers_dialog.php');
    //include('servers_grid.php');
?>

Ext.override(TocDesktop.ServersWindow, {

    createWindow: function() {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('servers-win');

        if (!win) {
            var pnl = new Toc.servers.mainPanel({owner: this});

            win = desktop.createWindow({
                id: 'servers-win',
                title: 'Gestion des Serveurs',
                width: 800,
                height: 400,
                iconCls: 'icon-servers-win',
                layout: 'fit',
                items: pnl
            });
        }

        win.show();
        win.maximize();
    },

    createserversDialog: function() {
        var desktop = this.app.getDesktop();
        var dlg = desktop.getWindow('servers-dialog-win');

        if (!dlg) {
            dlg = desktop.createWindow({}, Toc.servers.serversDialog);

            dlg.on('saveSuccess', function(feedback) {
                this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
            }, this);
        }

        return dlg;
    }
});