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

    echo 'Ext.namespace("Toc.reports");';

    //include('database_snapshots_grid.php');
    //include('database_snapshots_dialog.php');
    //include('database_snapshots_browser.php');

    include('reports_formats_panel.php');
    include('reports_subscriptions_grid.php');
    include('reports_subscription_dialog.php');
    include('reports_subscriptions_dialog.php');
    include('reports_schedule_formats_panel.php');
    include('reports_schedule_notification_panel.php');
    include('reports_schedule_range_panel.php');
    include('reports_parameters_panel.php');
    include('reports_schedule_minutes_container.php');
    include('reports_schedule_month_container.php');
    include('reports_schedule_weekday_container.php');
    include('reports_schedule_monthday_container.php');
    include('reports_schedule_hours_container.php');
    include('reports_schedule_panel.php');
    include('reports_schedule_dialog.php');
    include('reports_execution_dialog.php');
    include('reports_data_panel.php');
    include('reports_main_panel.php');
    include('reports_dialog.php');
    include('reports_grid.php');
?>

Ext.override(TocDesktop.ReportsWindow, {

createWindow: function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('reports-win');

if (!win) {
var pnl = new Toc.reports.mainPanel({owner: this});

win = desktop.createWindow({
id: 'reports-win',
title: '<?php echo $osC_Language->get('heading_reports_title'); ?>',
width: 800,
height: 400,
iconCls: 'icon-reports-win',
layout: 'fit',
items: pnl
});
}

win.show();
},

createReportsDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('reports-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.reports.reportsDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
},
createReportExecutionDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('reports-execution-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.reports.reportExecutionDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});