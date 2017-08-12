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

    echo 'Ext.namespace("Toc.streams");';

    include('capture_grid.php');
    include('propagation_grid.php');
    include('apply_grid.php');
?>

Ext.override(TocDesktop.StreamsWindow, {

createWindow: function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('streams-win');

if (!win) {
var pnl_capture = new Toc.streams.captureGrid({owner: this});

win = desktop.createWindow({
id: 'streams-win',
title: 'Gestion des Serveurs',
width: 800,
height: 400,
iconCls: 'icon-streams-win',
layout: 'fit',
items: pnl_capture
});
}

win.show();
},

createstreamsDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('streams-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.streams.streamsDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});