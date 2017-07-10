Toc.content.ContentCopyDialog = function (config) {
    config = config || {};

    config.id = 'content-copy-dialog-win';
    config.layout = 'fit';
    config.width = 400;
    config.height = 500;
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

    Toc.content.ContentCopyDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.content.ContentCopyDialog, Ext.Window, {

    show: function (content_ids, cPath, content_type) {
        content_ids = content_ids || null;
        this.cPath = cPath || null;
        this.content_type = content_type || null;

        this.frmCategories.form.reset();
        this.frmCategories.form.baseParams['content_ids'] = content_ids;
        this.frmCategories.form.baseParams['content_type'] = content_type;

        Toc.content.ContentMoveDialog.superclass.show.call(this);
        this.pnlPages.refresh();
    },

    buildForm: function () {
        this.pnlPages = new Toc.content.CategoriesPanel();
        this.pnlPages.setTitle('Espaces');

        this.tabreports = new Ext.TabPanel({
            activeTab: 0,
            region: 'center',
            defaults:{
                hideMode:'offsets'
            },
            deferredRender: false,
            items: [this.pnlPages]
        });

        this.frmCategories = new Ext.form.FormPanel({
            fileUpload: true,
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'content',
                action: 'copy_content'
            },
            deferredRender: false,
            items: [this.tabreports]
        });

        return this.frmCategories;
    },

   submitForm: function () {
        this.frmCategories.form.baseParams['content_categories_id'] = this.pnlPages.getCategories();

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