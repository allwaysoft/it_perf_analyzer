<?php

    echo 'Ext.namespace("Toc.servers");';

    include('group_tree_panel.php');
    include('groups_panel.php');
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
                title: 'Referentiel Serveurs',
                width: 800,
                height: 400,
                iconCls: 'icon-servers-win',
                layout: 'fit',
                items: pnl
            });
        }

        win.show();
        win.maximize();
    }
});