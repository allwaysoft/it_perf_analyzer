<?php

  require_once('includes/application_top.php');
  
  if(!isset($_SESSION['admin'])) {
    osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT));
  }

  require_once('includes/classes/json.php');
  $toC_Json = new toC_Json();

  require_once('includes/classes/desktop_settings.php');
  $toC_Desktop_Settings = new toC_Desktop_Settings();
  
  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Content-Type: application/x-javascript');  
  
  $token = toc_generate_token(); 
?>

var token = '<?php echo $token ?>';

Ext.Ajax.extraParams = {token: token};
Ext.data.Connection.prototype.extraParams = {token: token};
Ext.data.ScriptTagProxy.prototype.extraParams = {token: token};

/*
 * Desktop configuration
 */
TocDesktop = new Ext.app.App({
  loader: '<?php echo osc_href_link_admin(FILENAME_LOAD); ?>',
  
  json: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
  
  init :function(){
    Ext.QuickTips.init();
  },
  
  // config for the start menu
  getStartConfig : function(){
    return {
      iconCls: 'user',
      title: '<?php echo $_SESSION['admin']['username']; ?>',
      toolItems: [{
        text: TocLanguage.Logout,
        iconCls: 'logout',
        handler: function() { 
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'login',
              action: 'logoff'
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                window.location = "<?php echo osc_href_link_admin(FILENAME_DEFAULT); ?>";
              }
            }
          });
        }
      }],
      toolPanelWidth: 115
    };
  },
    
  /**
   * Return modules.
   */
  getModules: function(){
    return <?php echo $toC_Desktop_Settings->getModules();  ?>;
  },
  
  /**
   * Return the launchers object.
   */
  getLaunchers : function(){
    return <?php echo $toC_Desktop_Settings->getLaunchers(); ?>;
  },
  
  /**
   * Return the Styles object.
   */
  getStyles : function(){
    return <?php echo $toC_Desktop_Settings->getStyles(); ?>;
  },
  
  /**
   * Return the gadgets in the sidebar.
  */
  getGadgets: function() {
    return <?php echo $toC_Desktop_Settings->getGadgets();?>;
  },
  
  /***
   * Return the sidebar status.
  */
  isSidebarOpen: function() {
    return <?php echo $toC_Desktop_Settings->isSidebarOpen(); ?>;
  },
  
  /**
   * Check whether the configuration wizard is complete.
   */
  isWizardComplete : function(){
    return <?php echo $toC_Desktop_Settings->isWizardComplete(); ?>;
  }
});

<?php
  echo $toC_Desktop_Settings->outputModules();
?>