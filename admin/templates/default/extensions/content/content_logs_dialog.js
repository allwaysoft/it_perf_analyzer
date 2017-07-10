Toc.content.LogsDialog = function(config) {
    config = config || {};

    config.id = 'logs_logs_dialog-win';
    config.width = 450;
    config.height = 280;
    config.iconCls = 'icon-logs_logs-win';

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

    Toc.content.LogsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.content.LogsDialog, Ext.Window, {
    show: function (conf) {
        this.content_id = conf.content_id || null;
        this.servers_id = conf.servers_id || null;
        this.content_type = conf.content_type || null;
        this.host = conf.host || null;
        this.user = conf.server_user || null;
        this.pass = conf.server_pass || null;
        this.port = conf.server_port || null;

        this.frmAttachment.form.baseParams['content_id'] = this.content_id;
        this.frmAttachment.form.baseParams['servers_id'] = this.servers_id;
        this.frmAttachment.form.baseParams['content_type'] = this.content_type;
        this.frmAttachment.form.baseParams['current_category_id'] = -1;
        this.frmAttachment.form.baseParams['host'] = this.host;
        this.frmAttachment.form.baseParams['user'] = this.user;
        this.frmAttachment.form.baseParams['pass'] = this.pass;
        this.frmAttachment.form.baseParams['port'] = this.port;

        Toc.content.LogsDialog.superclass.show.call(this);
    },

    getDataPanel: function() {
        this.pnlData = new Ext.Panel({
            border: false,
            layout: 'form',
            defaults: {
                anchor: '96%'
            },
            items: [
                Toc.content.ContentManager.getContentStatusFields(),
                {xtype: 'textfield', fieldLabel: 'Chemin', name: 'url', allowBlank: false},
                {xtype: 'textfield', fieldLabel: 'Label', name: 'label', allowBlank: false},
                {xtype: 'textarea', fieldLabel: 'Description', name: 'content_description', height: 120}
            ]
        });

        return this.pnlData;
    },

    buildForm: function() {
        this.frmAttachment = new Ext.form.FormPanel({
            border: false,
            url: Toc.CONF.CONN_URL,
            labelWidth: 100,
            baseParams: {
                module: 'servers',
                action: 'save_log'
            },
            layoutConfig: {
                labelSeparator: ''
            },
            items: [
                this.getDataPanel()
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