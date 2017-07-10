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

    echo 'Ext.namespace("Toc.jobs");';

    include('jobs_actions_panel.php');
    include('jobs_actions_grid.php');
    include('jobs_action_browser.php');
    include('jobs_subscriptions_grid.php');
    include('jobs_subscription_dialog.php');
    include('jobs_subscriptions_dialog.php');
    include('jobs_schedule_notification_panel.php');
    include('jobs_schedule_range_panel.php');
    include('jobs_parameters_panel.php');
    include('jobs_schedule_minutes_container.php');
    include('jobs_schedule_month_container.php');
    include('jobs_schedule_weekday_container.php');
    include('jobs_schedule_monthday_container.php');
    include('jobs_schedule_hours_container.php');
    include('jobs_schedule_panel.php');
    include('jobs_schedule_dialog.php');
    include('jobs_execution_dialog.php');
    include('jobs_data_panel.php');
    include('jobs_main_panel.php');
    include('jobs_dialog.php');
    include('jobs_grid.php');
?>

Ext.override(TocDesktop.JobsWindow, {

createWindow: function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('jobs-win');

if (!win) {
var pnl = new Toc.jobs.mainPanel({owner: this});

win = desktop.createWindow({
id: 'jobs-win',
title: '<?php echo $osC_Language->get('heading_jobs_title'); ?>',
width: 800,
height: 400,
iconCls: 'icon-jobs-win',
layout: 'fit',
items: pnl
});
}
win.show();
},

createjobsDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('jobs-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.jobs.jobsDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
},
createJobExecutionDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('jobs-execution-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.jobs.JobExecutionDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});