Toc.content.DocumentsDialog = function(config) {
    config = config || {};

    config.id = 'documents_documents_dialog-win';
    config.width = 450;
    config.height = 280;
    config.iconCls = 'icon-documents_documents-win';

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

    Toc.content.DocumentsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.content.DocumentsDialog, Ext.Window, {
    show: function (content_id, content_type) {
        this.content_id = content_id || null;
        this.content_type = content_type || null;

        this.frmAttachment.form.baseParams['content_id'] = this.content_id;
        this.frmAttachment.form.baseParams['content_type'] = this.content_type;

        Toc.content.DocumentsDialog.superclass.show.call(this);
    },

    getLinkPanel: function() {
        this.pnlAttachmentFile = new Ext.Panel({
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
                                    name: 'documents_status',
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
                                    name: 'documents_status',
                                    boxLabel: 'Non'
                                }
                            ]
                        }
                    ]
                },
                {id: 'documents_file_name',xtype: 'fileuploadfield', fieldLabel: 'Fichier',name: 'documents_file_name'
                },
                {xtype: 'textfield', fieldLabel: 'Nom', name: 'documents_name', allowBlank: false},
                {xtype: 'textarea', fieldLabel: 'Description', name: 'documents_description', height: 120},
                {
                    xtype: 'panel',
                    border: false,
                    id: 'documents_file',
                    style: 'margin-left: 115px; text-decoration: underline'
                }
            ]
        });

        return this.pnlAttachmentFile;
    },

    buildForm: function() {
        this.frmAttachment = new Ext.form.FormPanel({
            border: false,
            url: Toc.CONF.CONN_URL,
            fileUpload: true,
            labelWidth: 100,
            baseParams: {
                module: 'content',
                action: 'save_document'
            },
            layoutConfig: {
                labelSeparator: ''
            },
            items: [
                this.getLinkPanel()
            ]
        });

        return this.frmAttachment;
    },

    submitForm: function() {
        this.frmAttachment.form.submit({
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