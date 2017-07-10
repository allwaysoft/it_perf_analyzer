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

  echo 'Ext.namespace("Toc.infoc");';

  include('infoc_create_user_dialog.php');
  include('infoc_reset_pwd_dialog.php');
  include('infoc_users_grid.php');
  //include('infoc_dialog.php');
  include('infoc_subscribers_grid.php');
  include('infoc_add_subscriber_dialog.php');
?>

Ext.override(TocDesktop.InfocentreWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('infocentre-win');
     
    if (!win) {
      var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';

      var pnlUsers =  new Toc.usersGrid({label:'INFOCENTRE',sid:'INFOCEN',host : 'infocentre.intra.bicec',db_port : 1521,db_pass : 'infoc',db_user : 'infoc',owner : this,exclude : ''});
      var pnlNotifications =  new Toc.infoc.notificationsGrid({databases_id : 22,owner : this});

      var tabdatabases = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
         pnlUsers,pnlNotifications
      ]
      });

      var frmDatabase = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'servers',
        action : ''
      },
        deferredRender: false,
        items: [tabdatabases]
      });

      win = desktop.createWindow({
        id: 'infocentre-win',
        title: 'INFOCENTRE',
        width: 800,
        height: 400,
        iconCls: 'icon-infocentre-win',
        layout: 'fit',
        items: tabdatabases
      });
    }
    
    win.show();
    win.maximize();
  },

  getCategoryPath: function(){
    return '-1';
  }
});