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

Toc.tasks.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.tasks.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tasks.DataPanel, Ext.Panel, {

  getDataPanel: function() {
    this.pnlData = new Ext.Panel({
      layout: 'column',
      border: false,
      autoHeight: true,
      style: 'padding: 6px',
      items: [
        {
          layout: 'form',
          border: false,
          labelSeparator: ' ',
          columnWidth: .7,
          autoHeight: true,
          defaults: {
            anchor: '97%'
          },
          items: [
            {
              layout: 'column',
              border: false,
              items: [
                {
                  layout: 'form',
                  border: false,
                  labelSeparator: ' ',
                  width: 200,
                  items: [
                    {
                      fieldLabel: '<?php echo $osC_Language->get('field_publish'); ?>',
                      xtype:'radio',
                      name: 'tasks_status',
                      inputValue: '1',
                      checked: true,
                      boxLabel: '<?php echo $osC_Language->get('field_publish_yes'); ?>'
                    }
                  ]
                },
                {
                  layout: 'form',
                  border: false,
                  width: 200,
                  items: [
                    {
                      hideLabel: true,
                      xtype:'radio',
                      inputValue: '0',
                      name: 'tasks_status',
                      boxLabel: '<?php echo $osC_Language->get('field_publish_no'); ?>'
                    }
                  ]
                }
              ]
            },
            {xtype:'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_order'); ?>', name: 'tasks_order', id: 'tasks_order'},
            {xtype:'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_image'); ?>', name: 'tasks_image'},
            {xtype:'textarea', fieldLabel: 'Intro', name: 'tasks_intro', id: 'tasks_intro',maxLength : 500,height:200}
          ]
        },
        {
          border: false,
          columnWidth: .3,
          items: [
            {xtype: 'panel', name: 'img_url', id: 'article_image_url', border: false}
          ]
        }
      ]
    });

    return this.pnlData;
  },
        
  setCategoryId : function(categoriesId)
  {
     categoriesId = categoriesId == 0 ? -1 : categoriesId;
     this.cboCategories.getStore().on('load', function() {
     console.log("cboCategories.setValue......" + categoriesId);
     this.cboCategories.setValue(categoriesId);
  }, this);
  }
});