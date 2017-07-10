Toc.content.ContentMoveDialog = function (config) {
    config = config || {};

    config.id = 'content-move-dialog-win';
    config.layout = 'fit';
    config.width = 400;
    config.autoHeight = true;
    config.modal = true;
    config.iconCls = 'icon-categories-win';
    config.items = this.buildForm();

    config.buttons = [
        {
            text: TocLanguage.btnSave,
            handler: function () {
                this.submitForm();
                this.disable();
            },
            scope: this
        },
        {
            text: TocLanguage.btnClose,
            handler: function () {
                this.close();
            },
            scope: this
        }
    ];

    this.addEvents({'saveSuccess': true});

    Toc.content.ContentMoveDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.content.ContentMoveDialog, Ext.Window, {

    show: function (content_ids, cPath, content_type) {
        content_ids = content_ids || null;
        this.cPath = cPath || null;
        this.content_type = content_type || null;

        this.frmCategories.form.reset();
        this.frmCategories.form.baseParams['content_ids'] = content_ids;
        this.frmCategories.form.baseParams['content_type'] = content_type;

        Toc.content.ContentMoveDialog.superclass.show.call(this);
    },

    buildForm: function () {
        dsParentCategories = new Ext.data.Store({
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'categories',
                action: 'list_parent_category'
            },
            reader: new Ext.data.JsonReader({
                root: Toc.CONF.JSON_READER_ROOT,
                totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
                fields: [
                    'id',
                    'text'
                ]
            }),
            autoLoad: true,
            listeners: {
                load: function () {
                    this.cboParentCategories.setValue(this.cPath);
                },
                scope: this
            }
        });

        this.cboParentCategories = new Toc.CategoriesComboBox({
            store: dsParentCategories,
            displayField: 'text',
            mode: 'local',
            fieldLabel: 'Espace',
            valueField: 'id',
            hiddenName: 'parent_category_id',
            triggerAction: 'all',
            allowBlank: true,
            editable: false
        });

        this.frmCategories = new Ext.form.FormPanel({
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'content',
                action: 'move_content'
            },
            border: false,
            frame: false,
            autoHeight: true,
            labelAlign: 'top',
            defaults: {anchor: '97%'},
            layoutConfig: { labelSeparator: '' },
            labelWidth: 160,
            items: this.cboParentCategories
        });

        return this.frmCategories;
    },

    submitForm: function () {
        this.frmCategories.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function (form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});