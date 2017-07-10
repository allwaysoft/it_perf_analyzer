Toc.content.LinksDialog = function(config) {
    config = config || {};

    config.id = 'links_links_dialog-win';
    config.width = 450;
    config.height = 315;
    config.iconCls = 'icon-links_links-win';

    config.items = this.buildForm();

    config.buttons = [
        {
            text: TocLanguage.btnSave,
            handler: function() {
                this.submitForm();
            },
            scope: this
        },
        {
            text: TocLanguage.btnClose,
            handler: function() {
                this.close();
            },
            scope: this
        }
    ];

    Toc.content.LinksDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.content.LinksDialog, Ext.Window, {
    show: function (content_id, content_type) {
        this.content_id = content_id || null;
        this.content_type = content_type || null;

        this.frmLink.form.baseParams['content_id'] = this.content_id;
        this.frmLink.form.baseParams['content_type'] = this.content_type;

        Toc.content.LinksDialog.superclass.show.call(this);
    },

    getLinkPanel: function() {
        this.pnlLink = new Ext.Panel({
            border: false,
            layout: 'form',
            defaults: {
                anchor: '96%'
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
                                    name: 'links_status',
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
                                    name: 'links_status',
                                    boxLabel: 'Non'
                                }
                            ]
                        }
                    ]
                },
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
                                    fieldLabel: 'Nouvelle fenetre',
                                    xtype:'radio',
                                    name: 'links_target',
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
                                    name: 'links_target',
                                    boxLabel: 'Non'
                                }
                            ]
                        }
                    ]
                },
                {xtype: 'textfield', fieldLabel: 'Nom', name: 'links_name', allowBlank: false},
                {xtype: 'textfield', fieldLabel: 'Url', name: 'links_url', allowBlank: false},
                {xtype: 'textarea', fieldLabel: 'Description', name: 'links_description', height: 120}
            ]
        });

        return this.pnlLink;
    },

    buildForm: function() {
        this.frmLink = new Ext.form.FormPanel({
            border: false,
            url: Toc.CONF.CONN_URL,
            fileUpload: false,
            labelWidth: 100,
            baseParams: {
                module: 'content',
                action: 'save_link'
            },
            layoutConfig: {
                labelSeparator: ''
            },
            items: [
                this.getLinkPanel()
            ]
        });

        return this.frmLink;
    },

    submitForm: function() {
        this.frmLink.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success:function(form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function(form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});