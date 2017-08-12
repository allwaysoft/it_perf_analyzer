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

  echo 'Ext.namespace("Toc.templates_modules_layout");';
  include('templates_modules_layout_main_panel.php');
  include('templates_modules_layout_dialog.php');
  include('templates_modules_layout_grid.php');
?>


Ext.override(TocDesktop.TemplatesModulesLayoutWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow(this.id);
     
    if(!win){
      var pnl = new Toc.templates_modules_layout.mainPanel({owner: this,set: this.params.set});
      
      if (this.params.set == 'boxes') {
        var title = '<?php echo $osC_Language->get('heading_title_boxes'); ?>';
      } else {
        var title = '<?php echo $osC_Language->get('heading_title_content'); ?>';
      }
      
      win = desktop.createWindow({
        id: this.id,
        title: title,
        width: 800,
        height: 400,
        iconCls: this.iconCls,
        layout: 'fit',
        items: pnl
      });
    }

    win.show();
  },
  
  createTemplatesModulesLayoutDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('templates_modules_layout-dialog-win');
    
    if (!dlg) {      
      dlg = desktop.createWindow({iconCls: this.iconCls}, Toc.templates_modules_layout.TemplatesModulesLayoutDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});