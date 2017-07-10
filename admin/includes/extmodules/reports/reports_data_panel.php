<?php
/*
  $Id: reports_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.reports.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.reports.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports.DataPanel, Ext.Panel, {
  getDataPanel: function() {
    this.pnlData = new Ext.Panel({
      layout: 'form',
          border: false,
          labelSeparator: ' ',
          //columnWidth: .9,
          autoHeight: true,
          defaults: {
            anchor: '97%'
          },
          items: [
            Toc.content.ContentManager.getContentStatusFields(),
            {xtype:'textfield', fieldLabel: 'Nom', name: 'content_name[2]', id: 'content_name'},
            {xtype:'textarea', fieldLabel: 'Description',name: 'content_description[2]',id: 'content_description',maxLength : 500,height:200},
            {xtype:'fileuploadfield', fieldLabel: 'fichier JRXML', name: 'field_jrxml',allowBlank : false}
          ]
    });

    return this.pnlData;
  }
});