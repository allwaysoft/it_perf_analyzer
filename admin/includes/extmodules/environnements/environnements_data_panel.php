<?php
/*
  $Id: environnements_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.environnements.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.environnements.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.environnements.DataPanel, Ext.Panel, {
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
            Toc.content.ContentManager.getContentStatusFields(),
            {xtype:'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_order'); ?>', name: 'content_order', id: 'content_order'},
            {xtype:'fileuploadfield', fieldLabel: '<?php echo $osC_Language->get('field_image'); ?>', name: 'environnements_image'},
            {xtype:'textarea', fieldLabel: 'Intro', name: 'environnements_intro', id: 'environnements_intro',maxLength : 500,height:200}
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
  }
});