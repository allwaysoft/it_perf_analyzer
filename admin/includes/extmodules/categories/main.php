<?php

  echo 'Ext.namespace("Toc.categories");';

  include('categories_grid.php');
  include('categories_general_panel.php');
  include('categories_dialog.php');
  include('categories_move_dialog.php');
  include('categories_main_panel.php');
  
?>

Ext.override(TocDesktop.CategoriesWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('categories-win');
     
    if(!win){
      var pnl = new Toc.categories.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'categories-win',
        title: '<?php echo $osC_Language->get('workspaces'); ?>',
        width: 870,
        height: 400,
        iconCls: 'icon-categories-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createCategoriesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('categories-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.categories.CategoriesDialog);
      
      dlg.on('saveSuccess', function (feedback, categoriesId, text) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }

    return dlg;
  },
  
  createCategoriesMoveDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('categories-move-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.categories.CategoriesMoveDialog);
      
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
