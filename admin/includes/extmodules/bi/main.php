<?php
    echo 'Ext.namespace("Toc.bi");';

    include('bi_data_panel.php');
    include('bi_main_panel.php');
    include('bi_dialog.php');
    include('bi_grid.php');
?>

Ext.override(TocDesktop.BiWindow, {
    createWindow: function() {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('bi-win');

        if (!win) {
            var pnl = new Toc.bi.mainPanel({owner: this});

            win = desktop.createWindow({
                id: 'bi-win',
                title: '<?php echo $osC_Language->get('dashboards'); ?>',
                width: 800,
                height: 400,
                iconCls: 'icon-bi-win',
                layout: 'fit',
                items: pnl
                });
        }

        win.show();
        win.maximize();
    }
});