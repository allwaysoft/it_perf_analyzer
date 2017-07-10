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

    echo 'Ext.namespace("Toc.customers");';

    include('customers_dialog.php');
    include('group_tree_panel.php');
    include('customers_grid.php');
    include('customers_data_panel.php');
    include('customers_address_panel.php');
    //  include('store_credits_grid.php');
    include('update_balance_dialog.php');
    include('customers_main_panel.php');
    //  include('customers_wishlist_grid.php');
?>

Ext.override(TocDesktop.CustomersWindow, {
createWindow : function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('customers-win');

if (!win) {
pnl = new Toc.customers.mainPanel({owner: this});

win = desktop.createWindow({
id: 'customers-win',
title: '<?php echo $osC_Language->get('heading_title'); ?>',
width: 850,
height: 400,
iconCls: 'icon-customers-win',
layout: 'fit',
items: pnl
});
}

win.show();
},

createCustomersDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('customers-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.customers.CustomersDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
},

createUpdateBalanceDialog : function(CustomersId) {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('update-balance-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({},Toc.customers.UpdateBalanceDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});