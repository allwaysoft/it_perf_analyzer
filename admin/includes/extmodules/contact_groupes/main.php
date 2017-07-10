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

  echo 'Ext.namespace("Toc.contact_groupes");';
  
  include('contact_groupes_tree_panel.php');
  include('contact_groupes_grid.php');
  include('contact_groupes_general_panel.php');  
  include('contact_groupes_dialog.php');
  include('contact_groupes_move_dialog.php');
  include('contact_groupes_main_panel.php');  
  
?>

Ext.override(TocDesktop.Contact_groupesWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('contact_groupes-win');
     
    if(!win){
      var pnl = new Toc.contact_groupes.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'contact_groupes-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 870,
        height: 400,
        iconCls: 'icon-contact_groupes-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createContact_groupesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('contact_groupes-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.contact_groupes.Contact_groupesDialog);
      
      dlg.on('saveSuccess', function (feedback, contact_groupesId, text) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }

    return dlg;
  },
  
  createContact_groupesMoveDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('contact_groupes-move-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.contact_groupes.Contact_groupesMoveDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
    
    return dlg;
  }
});