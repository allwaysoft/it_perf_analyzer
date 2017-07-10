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

    echo 'Ext.namespace("Toc.tbsexplorer");';

    include('main_panel.php');
    include('dialog.php');
    include('tbsexplorer_grid.php');
?>

Ext.override(TocDesktop.TbsExplorerWindow, {

createWindow: function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('tbsexplorer-win');

if (!win) {
var pnl = new Toc.tbsexplorer.mainPanel({owner: this});

win = desktop.createWindow({
id: 'tbsexplorer-win',
title: 'Tablespace Explorer',
width: 800,
height: 400,
iconCls: 'icon-tbsexplorer-win',
layout: 'fit',
items: pnl
});
}

win.show();
},

createTbsExplorerDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('tbsexplorer-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.tbsexplorer.TbsExplorerDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});