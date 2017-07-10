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

Toc.servers.DataPanel = function(config) {
    config = config || {};

    config.title = 'General';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.servers.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.servers.DataPanel, Ext.Panel, {
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
    getDataPanel: function() {

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
                        {xtype:'textfield', fieldLabel: 'Host', name: 'host', id: 'host',allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'Label', name: 'label', id: 'label',allowBlank:false},
                        {
// Use the default, automatic layout to distribute the controls evenly
// across a single row
                            xtype: 'radiogroup',
                            name:'typ',
                            id:'typ',
                            fieldLabel: 'Type',
                            items: [
                                {boxLabel: 'Windows',autoWidth :true,inputValue:'win', name: 'typ',xtype:'radio',},
                                {boxLabel: 'Linux',autoWidth :true,inputValue:'lin', name: 'typ',checked:true,xtype:'radio',},
                                {boxLabel: 'Aix',autoWidth :true, name: 'typ',inputValue:'aix',xtype:'radio',}
                            ]
                        },
                        {xtype:'numberfield', fieldLabel: 'Port', name: 'port', id: 'port',width:200,allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'User', name: 'user', id: 'user',allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'Pass', name: 'pass', id: 'pass',allowBlank:false}
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});