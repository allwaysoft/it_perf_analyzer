<?php
/*
  $Id: users_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.users.DataPanel = function(config) {
    config = config || {};

    config.title = 'Compte';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.users.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.users.DataPanel, Ext.Panel, {

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
                                            fieldLabel: 'Status',
                                            xtype:'radio',
                                            name: 'status',
                                            inputValue: '1',
                                            checked: true,
                                            boxLabel: 'Actif'
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
                                            name: 'status',
                                            boxLabel: 'Inactif'
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Compte',
                            name: 'user_name',
                            allowBlank: false
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Mot de Passe',
                            name: 'user_password',
                            id:  'user_password',
                            inputType: 'password',
                            value : ''
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Email',
                            name: 'email_address',
                            allowBlank: false
                        },{
                            xtype: 'hidden',
                            name: 'staff_id',
                            id: 'staff_id',
                            allowBlank: true
                        },
                        {xtype:'textarea', fieldLabel: 'Description', name: 'description', id: 'users_intro',maxLength : 500,height:150}
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});