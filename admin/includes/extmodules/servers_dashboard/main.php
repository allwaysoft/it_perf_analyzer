<?php
  echo 'Ext.namespace("Toc.servers_dashboard");';
?>

Ext.override(TocDesktop.ServersDashboardWindow, {

    createWindow: function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('servers_dash-win');

        if (!win) {
            //var grd = new Toc.DatabasesGrid({owner: this});
            //var availability_grd = new Toc.databasesAvailabilityDashboard({owner: this});
            var dashboard = new Toc.ServerDashboard({owner: this,header : false});
            //var perf_grd = new Toc.databasesPerfDashboard({owner: this});
            //var space_grd = new Toc.databaseSpaceDashboard({owner: this});
            //var ogg_grd = new Toc.GoldenGateDashboardPanel({owner: this});
            //var dataguard_grd = new Toc.DataGuardDashboardPanel({owner: this});

            win = desktop.createWindow({
                id: 'servers_dash-win',
                title: 'Performance Servers',
                width: 800,
                height: 400,
                iconCls: 'icon-server-win',
                layout: 'fit',
                items: dashboard
            });
        }

        win.show();
        win.maximize();
    },

    getCategoryPath: function () {
        return '-1';
    }
});