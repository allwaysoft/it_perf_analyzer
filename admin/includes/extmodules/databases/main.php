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

  echo 'Ext.namespace("Toc.databases");';

  //include('database_file_viewer.php');
  //include('database_dir_grid.php');
  //include('database_dir_browser.php');
  //include('database_fs_grid.php');
  //include('database_move_file_dialog.php');
  //include('database_move_datafile_dialog.php');
  //include('database_data_panel.php');
  //include('database_tables_grid.php');
  //include('database_dialog.php');
  //include('database_grid.php');
  //include('database_create_user_dialog.php');
  //include('database_reset_pwd_dialog.php');
  //include('database_users_grid.php');
  //include('database_log_panel.php');
  //include('database_subscribers_grid.php');
  //include('database_add_subscriber_dialog.php');
?>

Ext.override(TocDesktop.DatabasesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('databases-win');
     
    if (!win) {
      var grd = new Toc.DatabasesGrid({owner: this});
      //var grd = new Toc.databasesGrid({owner: this});
      var availability_grd = new Toc.databasesAvailabilityDashboard({owner: this});
      var dashboard = new Toc.DatabaseDashboard({owner: this});
      var perf_grd = new Toc.databasesPerfDashboard({owner: this});
      var space_grd = new Toc.databaseSpaceDashboard({owner: this});
      var ogg_grd = new Toc.GoldenGateDashboardPanel({owner: this});
      //var dataguard_grd = new Toc.DataGuardDashboardPanel({owner: this});


      var tabdashboard = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
         grd,dashboard,availability_grd,perf_grd,space_grd,ogg_grd
      ]
    });

      win = desktop.createWindow({
        id: 'databases-win',
        title: 'Oracle Databases',
        width: 800,
        height: 400,
        iconCls: 'icon-databases-win',
        layout: 'fit',
        items: tabdashboard
      });
    }
    
    win.show();
    win.maximize();
    //availability_grd.buildItems(availability_grd);
  },
  
  createDatabasesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('databases-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.DatabasesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },

  createMoveDatafileDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('move-datafile-dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.MovedatafileDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  },

  createDirDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('databases_dir_dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.Dirbrowser);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  },

  createFileViewerDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('databases_file_viewer-win');

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.FileViewer);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  },

  createMoveFileDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('move-file-dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.MovefileDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  },

  getCategoryPath: function(){
    return '-1';
  }
});