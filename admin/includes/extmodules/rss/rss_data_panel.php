<?php
/*
  $Id: rss_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.rss.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.rss.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.rss.DataPanel, Ext.Panel, {

  getDataPanel: function() {
    dsParentCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'categories',
        action: 'list_parent_article_category'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'id',
          'text'
        ]
      }),
      autoLoad: true
    });

    this.cboCategories = new Toc.CategoriesComboBox({
      store: dsParentCategories,
      displayField: 'text',
      fieldLabel: 'Page',
      valueField: 'id',
      name: 'rss_categories',
      hiddenName: 'rss_categories_id',
      triggerAction: 'all'
    });

    this.pnlData = new Ext.Panel({
      border: false,
      autoHeight: true,
      style: 'padding: 6px',
      items: [
        {
          layout: 'form',
          border: false,
          labelSeparator: ' ',
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
                      fieldLabel: 'Publie',
                      xtype:'radio',
                      name: 'rss_status',
                      inputValue: '1',
                      checked: true,
                      boxLabel: 'Oui'
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
                      name: 'rss_status',
                      boxLabel: 'Non'
                    }
                  ]
                }
              ]
            },
            this.cboCategories,
            {xtype:'textfield', fieldLabel: 'Titre', name: 'rss_title', id: 'rss_title'},
            {xtype:'textfield', fieldLabel: 'Url', name: 'rss_url', id: 'rss_url'}
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
     this.cboCategories.setValue(categoriesId);
  }, this);
  }
});