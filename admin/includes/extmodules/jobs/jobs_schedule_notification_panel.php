<?php
/*
  $Id: jobs_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.jobs.ScheduleNotificationPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.jobs.ScheduleNotificationPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.jobs.ScheduleNotificationPanel, Ext.Panel, {
  getDataPanel: function() {
    this.pnlData = new Ext.Panel({
      layout: 'form',
          border: false,
          labelSeparator: ' ',
          //columnWidth: .9,
          autoHeight: true,
          defaults: {
            //anchor: '97%'
          },
          items: [
            {xtype:'textfield', fieldLabel: 'À', name: 'to',width : 650,allowBlank : false},
            {xtype:'textfield', fieldLabel: 'CC', name: 'cc',width : 650},
            {xtype:'textfield', fieldLabel: 'BCC', name: 'bcc',width : 650},
            {xtype:'textfield', fieldLabel: 'Objet', name: 'subject',width : 650,allowBlank : false},
            {xtype: 'htmleditor',
        fieldLabel: 'Message',
        height : 250,
        width : 650,
        name: 'body'}
          ]
    });

    return this.pnlData;
  }
});