<?php


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
                        },
                        {xtype:'textarea', fieldLabel: 'Description', name: 'description', id: 'users_intro',maxLength : 500,height:150}
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});