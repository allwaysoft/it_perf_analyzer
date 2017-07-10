<?php
/*
  $Id: tasks_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tasks.PropertiesPanel = function(config) {
  config = config || {};    
  
  config.title = 'Contenu';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm();
    
  Toc.tasks.PropertiesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tasks.PropertiesPanel, Ext.TabPanel, {

  buildForm: function() {
    this.pnlData = new Ext.Panel({
        layout: 'form',
          border: false,
          labelSeparator: ' ',
          autoHeight: true,
          defaults: {
            anchor: '97%'
          },
          items: [
            {xtype:'datefield', fieldLabel: 'Date debut', name: 'start_date', id: 'start_date'},
            {xtype:'datefield', fieldLabel: 'Date Fin', name: 'due_date', id: 'due_date'},
            {xtype:'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_order'); ?>', name: 'tasks_order', id: 'tasks_order'},
            {xtype:'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_image'); ?>', name: 'tasks_image'},
            {xtype:'textarea', fieldLabel: 'Intro', name: 'tasks_intro', id: 'tasks_intro',maxLength : 500,height:200}
          ]
        }
    });

    return this.pnlData;
  } 
});