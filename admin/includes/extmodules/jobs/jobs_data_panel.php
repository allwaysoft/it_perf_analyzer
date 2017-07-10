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

Toc.jobs.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.jobs.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.jobs.DataPanel, Ext.Panel, {
  getDataPanel: function() {
    this.pnlData = new Ext.Panel({
      layout: 'form',
      border: false,
      autoHeight: true,
      style: 'padding: 6px',
      items: [
        Toc.content.ContentManager.getContentStatusFields(),
        {xtype:'textfield', fieldLabel: 'Label', name: 'nickname', id: 'content_order',width : 620},
        {xtype:'textarea', fieldLabel: 'Description', name: 'description', id: 'jobs_intro',maxLength : 500,width : 620,height:280}
      ]
    });

    return this.pnlData;
  }
});