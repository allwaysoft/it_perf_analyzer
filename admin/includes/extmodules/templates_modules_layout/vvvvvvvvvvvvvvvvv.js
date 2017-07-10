Ext.namespace("Toc.templates_modules_layout");
Toc.templates_modules_layout.mainPanel = function(config) {
    config = config || {};

    config.layout = 'border';
    config.border = false;

    config.pnlCategoriesTree = new Toc.CategoriesTreePanel({owner: config.owner, parent: this});
    config.grdTemplatesModulesLayout = new Toc.templates_modules_layout.TemplatesModulesLayoutGrid({owner: config.owner, mainPanel: this,set: config.set});

    config.pnlCategoriesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);

    config.items = [config.pnlCategoriesTree, config.grdTemplatesModulesLayout];

    Toc.templates_modules_layout.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.templates_modules_layout.mainPanel, Ext.Panel, {

    onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
        this.grdTemplatesModulesLayout.refreshGrid(categoryId);
    },

    getCategoriesTree: function() {
        return this.pnlCategoriesTree;
    },

    getCategoryPath: function() {
        return this.pnlCategoriesTree.getCategoriesPath();
    },

    getCategoryPermissions: function() {
        return this.pnlCategoriesTree.getCategoryPermissions();
    }
});

Toc.templates_modules_layout.TemplatesModulesLayoutDialog = function(config) {

    config = config || {};

    this.filter = null;
    this.set = null;

    config.id = 'templates_modules_layout-dialog-win';
    config.title = 'action_heading_new_order_status';
    config.layout = 'fit';
    config.width = 450;
    config.modal = true;
    config.items = this.buildForm();

    config.buttons = [
        {
            text:TocLanguage.btnSave,
            handler: function() {
                this.submitForm();
            },
            scope:this
        },
        {
            text: TocLanguage.btnClose,
            handler: function() {
                this.close();
            },
            scope:this
        }
    ];

    Toc.templates_modules_layout.TemplatesModulesLayoutDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.templates_modules_layout.TemplatesModulesLayoutDialog, Ext.Window, {

    show: function(boxPageId, filter, set) {
        boxPageId = boxPageId || null;

        this.setFilter(filter, set);
        this.frmLayout.form.reset();
        this.frmLayout.form.baseParams['box_page_id'] = boxPageId;

        if (boxPageId > 0) {
            this.frmLayout.load({
                url: Toc.CONF.CONN_URL,
                params: {
                    action: 'load_box_layout',
                    filter: this.filter,
                    set: this.set
                },
                success: function(form, action) {
                    this.cboModules.disable();

                    Toc.templates_modules_layout.TemplatesModulesLayoutDialog.superclass.show.call(this);
                },
                failure: function(form, action) {
                    Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
                },
                scope: this
            });
        } else {
            Toc.templates_modules_layout.TemplatesModulesLayoutDialog.superclass.show.call(this);
        }
    },

    setFilter: function(filter, set) {
        this.set = set;
        this.filter = filter;

        this.cboModules.getStore().baseParams['set'] = set;

        this.cboPages.getStore().baseParams['filter'] = filter;
        this.cboPages.getStore().baseParams['set'] = set;

        this.cboGroups.getStore().baseParams['filter'] = filter;
        this.cboGroups.getStore().baseParams['set'] = set;

        this.frmLayout.baseParams['filter'] = filter;
        this.frmLayout.baseParams['set'] = set;
    },

    buildForm: function() {
        this.cboModules = new Ext.form.ComboBox({
            allowBlank: false,
            store: new Ext.data.Store({
                url: Toc.CONF.CONN_URL,
                baseParams: {
                    module: 'templates_modules_layout',
                    action: 'get_modules'
                },
                reader: new Ext.data.JsonReader({
                    root: Toc.CONF.JSON_READER_ROOT
                }, [
                    'id',
                    'text'
                ]),
                autoLoad: true
            }),
            fieldLabel: 'Module:',
            triggerAction: 'all',
            readOnly: true,
            name: 'module',
            hiddenName: 'box',
            valueField: 'id',
            displayField: 'text'
        });

        this.cboPages = new Ext.form.ComboBox({
            store: new Ext.data.Store({
                url: Toc.CONF.CONN_URL,
                baseParams: {
                    module: 'templates_modules_layout',
                    action: 'get_pages'
                },
                reader: new Ext.data.JsonReader({
                    root: Toc.CONF.JSON_READER_ROOT
                }, [
                    'id',
                    'text'
                ]),
                autoLoad: true
            }),
            allowBlank: false,
            fieldLabel: 'Pages:',
            name: 'page',
            triggerAction: 'all',
            readOnly: true,
            hiddenName: 'content_page',
            valueField: 'id',
            displayField: 'text'
        });

        this.cboGroups = new Ext.form.ComboBox({
            store: new Ext.data.Store({
                url: Toc.CONF.CONN_URL,
                baseParams: {
                    module: 'templates_modules_layout',
                    action: 'get_groups'
                },
                reader: new Ext.data.JsonReader({
                    root: Toc.CONF.JSON_READER_ROOT
                }, [
                    'id',
                    'text'
                ]),
                autoLoad: true
            }),
            fieldLabel: 'Groupe',
            name: 'group',
            triggerAction: 'all',
            readOnly: true,
            valueField: 'id',
            displayField: 'text'
        });

        this.frmLayout = new Ext.form.FormPanel({
            url: Toc.CONF.CONN_URL,
            baseParams: {
                set: this.set,
                module: 'templates_modules_layout',
                action : 'save_box_layout'
            },
            border:false,
            layout: 'form',
            autoHeight: true,
            defaults: {
                anchor: '97%'
            },
            layoutConfig: {
                labelSeparator: ''
            },
            items: [
                this.cboModules,
                this.cboPages,
                {
                    layout: 'column',
                    border: false,
                    items: [
                        {
                            layout: 'form',
                            border: false,
                            width: 200,
                            items: [
                                {
                                    xtype: 'checkbox',
                                    fieldLabel: 'Spécifique à une page?',
                                    name: 'page_specific',
                                    width: 50
                                }
                            ]
                        }
                    ]
                },
                this.cboGroups,
                {xtype: 'textfield', fieldLabel: 'Nouveau groupe:', name: 'group_new'},
                {xtype: 'textfield', fieldLabel: 'Ordre de tri', name: 'sort_order'}
            ]
        });

        return this.frmLayout;
    },

    submitForm : function() {
        this.frmLayout.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function(form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function(form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            }, scope: this
        });
    }
});
Toc.templates_modules_layout.TemplatesModulesLayoutGrid = function(config) {

    config = config || {};

    config.loadMask = true;
    config.border = false;
    config.region = 'center';
    config.viewConfig = {forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'templates_modules_layout',
            action: 'list_templates_modules_layout',
            set: config.set
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'id'
        }, [
            'id',
            'content_page',
            'boxes_group',
            'sort_order',
            'page_specific',
            'templates_boxes_id',
            'box_title',
            'code'
        ])
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions:[
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        {header: 'Modules',dataIndex: 'box_title'},
        {header: 'Pages',dataIndex: 'content_page', sortable: true},
        {header: 'Spécifique à une page',dataIndex: 'page_specific', sortable: true},
        {header: 'Groupe',dataIndex: 'boxes_group', sortable: true},
        {header: 'Ordre de tri',dataIndex: 'sort_order', sortable: true},
        config.rowActions
    ]);

    dsTemplates = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'templates_modules_layout',
            action: 'get_templates'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
        }, [
            'id',
            'title',
            'default'
        ]),
        autoLoad: true,
        listeners: {
            load: this.onCboTemplatesLoad,
            scope: this
        }
    });

    config.cboTemplates = new Ext.form.ComboBox({
        width:200,
        store: dsTemplates,
        triggerAction: 'all',
        displayField: 'title',
        valueField: 'id',
        hiddenName: 'filter_code',
        readOnly: true,
        listeners: {
            select: this.onCboTemplatesSelect,
            scope: this
        }
    });

    config.tbar = [
        {
            text: TocLanguage.btnAdd,
            iconCls:'add',
            handler: this.onAdd,
            scope: this
        },
        '-',
        {
            text: TocLanguage.btnDelete,
            iconCls:'remove',
            handler: this.onBatchDelete,
            scope: this
        },
        '-',
        {
            text: TocLanguage.btnRefresh,
            iconCls:'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->',
        config.cboTemplates
    ];

    Toc.templates_modules_layout.TemplatesModulesLayoutGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.templates_modules_layout.TemplatesModulesLayoutGrid, Ext.grid.GridPanel, {
    onCboTemplatesLoad: function() {
        var store = this.cboTemplates.getStore();

        var record = store.getAt(store.find('default', '1'));
        this.cboTemplates.setValue(record.get('id'));
        this.onCboTemplatesSelect();
    },

    onCboTemplatesSelect: function() {
        this.getStore().baseParams['id'] = this.cboTemplates.getValue();
        this.onRefresh();
    },

    onAdd: function() {
        var dlg = this.owner.createTemplatesModulesLayoutDialog();
        dlg.setTitle('Nouvelle disposition de modules de thèmes');

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show(null, this.cboTemplates.getValue(), this.set);
    },

    onEdit: function(record) {
        var dlg = this.owner.createTemplatesModulesLayoutDialog();
        dlg.setTitle(record.get("box_title"));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show(record.get("id"), this.cboTemplates.getValue(), this.set);
    },

    onDelete: function(record) {
        var id = record.get('id');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            TocLanguage.msgDeleteConfirm,
            function(btn) {
                if (btn == 'yes') {
                    btn.disabled = true;
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'templates_modules_layout',
                            action: 'delete_box_layout',
                            box_layout_id: record.get('id'),
                            set: this.set
                        },
                        callback: function(options, success, response) {
                            var result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                                this.getStore().reload();
                            } else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                            }
                        }, scope: this
                    });
                }
            }, this);
    },

    onBatchDelete: function() {
        var keys = this.selModel.selections.keys;

        if (keys.length > 0) {
            var batch = keys.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                TocLanguage.msgDeleteConfirm,
                function(btn) {
                    if (btn == 'yes') {
                        btn.disabled = true;
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'templates_modules_layout',
                                action: 'delete_box_layouts',
                                batch: batch,
                                set: this.set
                            },
                            callback: function(options, success, response) {
                                var result = Ext.decode(response.responseText);

                                if (result.success == true) {
                                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                                    this.getStore().reload();
                                } else {
                                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                                }
                            }, scope: this
                        });
                    }
                }, this);
        } else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    onRefresh: function() {
        this.getStore().reload();
    },

    refreshGrid: function (categoriesId) {
        var store = this.getStore();

        <!--    store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;-->
        <!--    store.baseParams['categories_id'] = categoriesId;-->
        <!--    this.categoriesId = categoriesId;-->
        store.reload();
    },

    onRowAction:function(grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
    }
});

Ext.override(TocDesktop.TemplatesModulesLayoutWindow, {

    createWindow : function() {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.id);

        if (!win) {
            var pnl = new Toc.templates_modules_layout.mainPanel({owner: this,set: this.params.set});

            if (this.params.set == 'boxes') {
                var title = 'Disposition des modules de thèmes: Boîtes';
            } else {
                var title = 'Disposition des modules de thèmes: Contenus';
            }

            win = desktop.createWindow({
                id: this.id,
                title: title,
                width: 800,
                height: 400,
                iconCls: this.iconCls,
                layout: 'fit',
                items: pnl
            });
        }

        win.show();
    },

    createTemplatesModulesLayoutDialog: function() {
        var desktop = this.app.getDesktop();
        var dlg = desktop.getWindow('templates_modules_layout-dialog-win');

        if (!dlg) {
            dlg = desktop.createWindow({iconCls: this.iconCls}, Toc.templates_modules_layout.TemplatesModulesLayoutDialog);

            dlg.on('saveSuccess', function(feedback) {
                this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
            }, this);
        }

        return dlg;
    }
});