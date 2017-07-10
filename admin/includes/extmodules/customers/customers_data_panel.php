<?php
/*
  $Id: users_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();

  Toc.customers.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.DataPanel, Ext.Panel, {

  getDataPanel: function() {
    this.cboCustomersGroups = Toc.content.ContentManager.getCustomersGroupsCombo();

      this.pnlData = new Ext.Panel({
      layout : 'form',
      border: false,
      labelWidth : 185,
      defaults: {
         anchor: '97%'
      },
      autoHeight: true,
      style: 'padding: 6px',
      items: [
        {xtype: 'checkbox',anchor: '', fieldLabel: 'Actif', name: 'customers_status'},
        {xtype: 'checkbox', anchor: '', fieldLabel: '<?php echo $osC_Language->get('field_newsletter_subscription'); ?>', name: 'customers_newsletter'},
        {xtype: 'textfield', inputType: 'password', fieldLabel: '<?php echo $osC_Language->get('field_password'); ?>', name: 'customers_password'},
        {xtype: 'textfield', inputType: 'password', fieldLabel: '<?php echo $osC_Language->get('field_password_confirmation'); ?>', name: 'confirm_password'},
        this.cboCustomersGroups
      ]
    });

    return this.pnlData;
  }
});