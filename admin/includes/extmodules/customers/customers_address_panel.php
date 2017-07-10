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

Toc.customers.AddressPanel = function(config) {
  config = config || {};    
  
  config.title = 'Adresse';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.customers.AddressPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.AddressPanel, Ext.Panel, {
   getDataPanel: function() {
    this.cboCountries = Toc.content.ContentManager.getCountriesCombo(this);
    this.cboZones = Toc.content.ContentManager.getZonesCombo();
        
      this.pnlData = new Ext.Panel({
      layout : 'form',
      deferredRender : false,
      border: false,
      defaults: {
         anchor: '97%'
      },
      autoHeight: true,
      style: 'padding: 6px',
      items: [
        {
          layout: 'column',
          border: false,
          items: [
            {
              width: '50%',
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items:[
                {fieldLabel: 'Genre', boxLabel: '<?php echo $osC_Language->get('gender_male'); ?>' , name: 'customers_gender', xtype:'radio', inputValue: 'm', checked: true}
              ]
            },
            {
              layout: 'form',
              border: false,
              items:[
                { hideLabel: true, boxLabel: '<?php echo $osC_Language->get('gender_female'); ?>' , name: 'customers_gender', xtype:'radio', inputValue: 'f'}
              ]
            }
          ]
        },
        {xtype: 'textfield', fieldLabel: 'Prenom', name: 'customers_firstname', allowBlank: false},
        {xtype: 'textfield', fieldLabel: 'Nom', name: 'customers_lastname', allowBlank: false},
        {xtype: 'textfield', fieldLabel: 'No Telephone', name: 'customers_telephone', allowBlank: false},
        {xtype: 'textfield', fieldLabel: 'Fax', name: 'fax_number'},
        {xtype: 'textfield', fieldLabel: 'Email', name: 'customers_email_address', allowBlank: false},
        {xtype: 'textarea', fieldLabel: 'Adresse', name: 'street_address'},
        {xtype: 'textfield', fieldLabel: 'BP', name: 'postcode'},
        {xtype: 'textfield', fieldLabel: 'Ville', name: 'city'},
        this.cboCountries,
        this.cboZones
      ]
    });

    return this.pnlData;
  },

  onCboCountriesSelect: function(combo, record, index) {
    this.cboZones.enable();
    this.cboZones.reset();
    this.cboZones.getStore().baseParams['country_id'] = record.get('country_id');
    this.cboZones.getStore().load();
  }
});