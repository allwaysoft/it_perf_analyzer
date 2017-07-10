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

    echo 'Ext.namespace("Toc.fsmanager");';

//    include('dir_grid.php');
//    include('dir_browser.php');
//    include('fs_grid.php');
    include('main_panel.php');
    //include('dialog.php');
    include('fsmanager_grid.php');
?>

Ext.override(TocDesktop.FsManagerWindow, {

createWindow: function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('fsmanager-win');

if (!win) {
var pnl = new Toc.fsmanager.mainPanel({owner: this});

win = desktop.createWindow({
id: 'fsmanager-win',
title: 'FS Explorer',
width: 800,
height: 400,
iconCls: 'icon-fsmanager-win',
layout: 'fit',
items: pnl
});
}

win.show();
},

createfsmanagerDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('fsmanager-dialog-win');

if (!dlg) {
//dlg = desktop.createWindow({}, Toc.fsmanager.fsmanagerDialog);
dlg = desktop.createWindow({}, Toc.FsDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});