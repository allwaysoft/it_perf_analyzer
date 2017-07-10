<?php
/*
  $Id: servers_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.databases.DataPanel = function(config) {
    config = config || {};

    config.title = 'General';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.databases.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.DataPanel, Ext.Panel, {
    setTyp:function(typ){
       var group = Ext.getCmp('typ');
       group.setValue(typ);

       for(i=0;i<group.items.length;i++)
       {
           var item=group.items.items[i];

           if(item.inputValue == typ)
           {
              item.setValue(true);
           }
           else
           {
              item.setValue(false);
           }
       }
    },
    getServerData:function(){
       var data = this.serverCombo.getStore().getById(this.serverCombo.getValue());
       return data;
    },
    loadServers: function(){
        this.serverCombo.getStore().load();
    },
    loadCategories: function(){
        this.categoryCombo.getStore().load();
    },
    setServer : function(servers_id)
        {
            this.serverCombo.getStore().on('load', function() {
                this.serverCombo.setValue(servers_id);
            }, this);

            this.serverCombo.getStore().load();
        },
    setCategory : function(category)
        {
            this.categoryCombo.getStore().on('load', function() {
                this.categoryCombo.setValue(category);
            }, this);

            this.categoryCombo.getStore().load();
        },
    getDataPanel: function() {
        this.serverCombo = Toc.content.ContentManager.getServerCombo();
        this.categoryCombo = Toc.content.ContentManager.getDatabasesCategoryCombo();
        this.pnlData = new Ext.Panel({
            layout: 'form',
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
                        this.serverCombo,
                        {xtype:'textfield', fieldLabel: 'Label', name: 'label', id: 'label',allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'User', name: 'user', id: 'user',allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'Pass', name: 'pass', id: 'pass',allowBlank:false},
                        {xtype:'numberfield', fieldLabel: 'Port', name: 'port', id: 'port',width:200,allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'SID', name: 'sid', id: 'sid',allowBlank:false},
                        this.categoryCombo
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});