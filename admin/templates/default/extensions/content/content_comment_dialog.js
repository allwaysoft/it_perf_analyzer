Toc.content.CommentDialog = function(config) {
    config = config || {};

    config.id = 'comment_comment_dialog-win';
    config.width = 655;
    config.height = 420;
    config.title = 'Ajouter un commentaire';
    config.iconCls = 'icon-comment_comment-win';

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

    Toc.content.CommentDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.content.CommentDialog, Ext.Window, {
    show: function (content_id, content_type) {
        this.content_id = content_id || null;
        this.content_type = content_type || null;

        this.frmComment.form.baseParams['content_id'] = this.content_id;
        this.frmComment.form.baseParams['content_type'] = this.content_type;

        Toc.content.CommentDialog.superclass.show.call(this);
    },

    getCommentPanel: function() {
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
                                    name: 'comment_status',
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
                                    name: 'comment_status',
                                    boxLabel: 'Non'
                                }
                            ]
                        }
                    ]
                },
                {id: 'comment_file_name',xtype: 'fileuploadfield', fieldLabel: 'Fichier',name: 'comment_file_name'
                },
                {xtype: 'htmleditor', fieldLabel: 'Commentaire', name: 'comments_description', height: 280}
            ]
        });

        return this.pnlAttachmentFile;
    },

    buildForm: function() {
        this.frmComment = new Ext.form.FormPanel({
            border: false,
            url: Toc.CONF.CONN_URL,
            fileUpload: true,
            labelWidth: 100,
            baseParams: {
                module: 'content',
                action: 'save_comment'
            },
            layoutConfig: {
                labelSeparator: ''
            },
            items: [
                this.getCommentPanel()
            ]
        });

        return this.frmComment;
    },

    submitForm: function() {
        this.frmComment.form.submit({
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