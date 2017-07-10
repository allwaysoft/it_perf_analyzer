<?php
/*
  $Id: news_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.news.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.news.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.news.DataPanel, Ext.Panel, {
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
            {xtype:'numberfield', fieldLabel: 'Ordre', name: 'content_order', id: 'content_order'},
            {xtype:'fileuploadfield', fieldLabel: 'Image', name: 'news_image'},
            {xtype:'textarea', fieldLabel: 'Intro', name: 'news_intro', id: 'news_intro',maxLength : 250}
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