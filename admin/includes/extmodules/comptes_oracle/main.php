<?php

  echo 'Ext.namespace("Toc.comptes_oracle");';

  include('comptes_oracle_create_user_dialog.php');
  include('comptes_oracle_reset_pwd_dialog.php');
  include('comptes_oracle_users_grid.php');
  //include('comptes_oracle_dialog.php');
  include('comptes_oracle_subscribers_grid.php');
  include('comptes_oracle_add_subscriber_dialog.php');
?>

Ext.override(TocDesktop.Comptes_OracleWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('comptes_oracle-win');
     
    if (!win) {
      var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';

      var pnlUsers =  new Toc.usersGrid({label:null,sid:'',host : '',db_port : '',db_pass : '',db_user : '',owner : this,exclude : ''});
      var pnlNotifications =  new Toc.comptes_oracle.notificationsGrid({databases_id : '',owner : this});

      var tabdatabases = new Ext.TabPanel({
      activeTab: 0,
      hideParent:true,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [pnlUsers,pnlNotifications]
      });

      var frmDatabase = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'databases',
        action : ''
      },
        deferredRender: false,
        items: [tabdatabases]
      });

      win = desktop.createWindow({
        id: 'comptes_oracle-win',
        title: 'COMPTES ORACLE',
        width: 800,
        height: 400,
        iconCls: 'icon-comptes_oracle-win',
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