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

  echo 'Ext.namespace("Toc.tasks");';

  include('tasks_data_panel.php');
  include('tasks_main_panel.php');
  include('tasks_dialog.php');
  include('tasks_grid.php');
  include('tasks_general_panel.php');
  include('tasks_meta_info_panel.php');
?>

Ext.override(TocDesktop.TasksWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('tasks-win');
     
    if (!win) {
      pnl = new Toc.tasks.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'tasks-win',
        title: '<?php echo $osC_Language->get('heading_tasks_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-tasks-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createTasksDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('tasks-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.tasks.TasksDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});