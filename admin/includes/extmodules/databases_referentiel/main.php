<?php
  echo 'Ext.namespace("Toc.databases_referentiel");';
?>

Ext.override(TocDesktop.DatabasesReferentielWindow, {

    createWindow: function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('databases_ref-win');

        if (!win) {
            var grd = new Toc.DatabasesGrid({owner: this,header : false});
            //var grd = new Toc.databasesGrid({owner: this});
            //var availability_grd = new Toc.databasesAvailabilityDashboard({owner: this});
            //var dashboard = new Toc.DatabaseDashboard({owner: this});
            //var perf_grd = new Toc.databasesPerfDashboard({owner: this});
            //var space_grd = new Toc.databaseSpaceDashboard({owner: this});
            //var ogg_grd = new Toc.GoldenGateDashboardPanel({owner: this});
            //var dataguard_grd = new Toc.DataGuardDashboardPanel({owner: this});

            win = desktop.createWindow({
                id: 'databases_ref-win',
                title: 'Databases',
                width: 800,
                height: 400,
                iconCls: 'icon-databases-win',
                layout: 'fit',
                items: grd
            });
        }

        win.show();
        win.maximize();
    },

    getCategoryPath: function () {
        return '-1';
    }
});