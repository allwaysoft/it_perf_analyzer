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

    echo 'Ext.namespace("Toc.environnements");';

    include('environnements_data_panel.php');
    include('environnements_main_panel.php');
    include('environnements_dialog.php');
    include('environnements_grid.php');
?>

Ext.override(TocDesktop.environnementsWindow, {

createWindow: function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('environnements-win');

if (!win) {
var pnl = new Toc.environnements.mainPanel({owner: this});

win = desktop.createWindow({
id: 'environnements-win',
title: '<?php echo $osC_Language->get('heading_environnements_title'); ?>',
width: 800,
height: 400,
iconCls: 'icon-environnements-win',
layout: 'fit',
items: pnl
});
}

win.show();
},

createenvironnementsDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('environnements-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.environnements.environnementsDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});