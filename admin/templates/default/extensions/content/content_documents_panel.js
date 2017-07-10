Toc.content.DocumentsPanel = function(config) {
    config = config || {};
    config.title = 'Documents';
    config.listeners = {
        activate : function(panel) {
            if (!this.loaded) {
                this.getStore().reload();
            }
        },
        scope: this
    }

    config.content_id = config.content_id || null;
    config.loadMask = true;
    config.border = false;
    config.viewConfig = {
        emptyText: TocLanguage.gridNoRecords
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'content',
            action: 'list_documents',
            content_id: config.content_id,
            content_type : config.content_type
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'documents_id'
        }, [
            'url',
            'icon',
            'size',
            'file_owner',
            'documents_id',
            'documents_name',
            'documents_filename',
            'documents_cache_filename',
            'documents_description',
            'action',
            'documents_status'
        ]),
        autoLoad: false,
        listeners : {
            load : function() {
                this.loaded = true;
            },scope: this
        }
    });

    renderPublish = function(status) {
        if (status == 1) {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        tpl: new Ext.XTemplate(
            '<div class="ux-row-action">'
                + '<tpl for="action">'
                + '<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
                + '</tpl>'
                + '</div>'
        ),
        actions:['','',''],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.plugins = config.rowActions;

    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        {header: '', dataIndex: 'icon', width : 24},
        {id: 'documents_name', header: 'Nom du document', dataIndex: 'documents_name'},
        {header: 'Description', dataIndex: 'documents_description', width: 350},
        {header: 'Publie', align: 'center', renderer: renderPublish, dataIndex: 'documents_status'},
        config.rowActions
    ]);
    config.autoExpandColumn = 'documents_name';
    config.rowActions.on('action', this.onRowAction, this);

    config.txtSearch = new Ext.form.TextField({
        emptyText: ''
    });

    config.tbar = [
        {
            text: TocLanguage.btnAdd,
            iconCls: 'icon-upload',
            handler: function() {
                this.onAdd();
            },
            scope: this
        },
        '-',
        {
            text: TocLanguage.btnDelete,
            iconCls: 'remove',
            handler: this.onBatchDelete,
            scope: this
        },
        '-',
        {
            text: TocLanguage.btnRefresh,
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->',
        config.txtSearch,
        ' ',
        {
            iconCls : 'search',
            handler : this.onSearch,
            scope : this
        }
    ];

    var thisObj = this;
    config.bbar = new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: config.ds,
        steps: Toc.CONF.GRID_STEPS,
        btnsConfig:[
            {
                text: TocLanguage.btnAdd,
                iconCls:'add',
                handler: function() {
                    thisObj.onAdd();
                }
            },
            {
                text: TocLanguage.btnDelete,
                iconCls:'remove',
                handler: function() {
                    thisObj.onBatchDelete();
                }
            }
        ],
        beforePageText : TocLanguage.beforePageText,
        firstText: TocLanguage.firstText,
        lastText: TocLanguage.lastText,
        nextText: TocLanguage.nextText,
        prevText: TocLanguage.prevText,
        afterPageText: TocLanguage.afterPageText,
        refreshText: TocLanguage.refreshText,
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg,
        prevStepText: TocLanguage.prevStepText,
        nextStepText: TocLanguage.nextStepText
    });

    Toc.content.DocumentsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.DocumentsPanel, Ext.grid.GridPanel, {
    onAdd: function() {
        var dlg = this.owner.createDocumentsDialog();

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.setTitle('Nouveau document');
        dlg.show(this.content_id, this.content_type);
    },

    onDownload: function (record) {
        url = record.get('url');
        params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
        window.open(url, "", params);
    },

    onDelete: function(record) {
        var documentsId = record.get('documents_id');
        var documentsName = record.get('documents_cache_filename');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            TocLanguage.msgDeleteConfirm,
            function(btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'content',
                            action: 'delete_document',
                            documents_id: documentsId,
                            documents_name: documentsName
                        },
                        callback: function(options, success, response) {
                            result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                                this.getStore().reload();
                            } else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                            }
                        },
                        scope: this
                    });
                }
            },
            this
        );
    },

    refreshGrid: function (categoriesId) {
        var store = this.getStore();

        store.baseParams['categories_id'] = categoriesId;
        this.categoriesId = categoriesId;
        store.load();
    },

    onBatchDelete: function() {
        var selection = this.getSelectionModel().selections,
            keys = selection.keys,
            result = [];

        Ext.each(keys, function(key, index) {
            result = result.concat(key + ':' + selection.map[key].get('documents_cache_filename'));
        });

        if (result.length > 0) {
            var batch = result.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                TocLanguage.msgDeleteConfirm,
                function(btn) {
                    if (btn == 'yes') {
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'content',
                                action: 'delete_documents',
                                batch: batch
                            },
                            callback: function(options, success, response) {
                                result = Ext.decode(response.responseText);

                                if (result.success == true) {
                                    TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                                    this.onRefresh();
                                } else {
                                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                                }
                            },
                            scope: this
                        });
                    }
                },
                this
            );
        } else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    onRefresh: function() {
        this.getStore().reload();
    },

    onSearch: function() {
        var documents_name = this.txtSearch.getValue();
        var store = this.getStore();

        store.baseParams['documents_name'] = documents_name;
        store.reload();
    },

    onRowAction: function(grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-edit-record':
                this.onEdit(record);
                break;

            case 'icon-download-record':
                this.onDownload(record);
                break;
        }
    },

    setContentId : function(content_id) {
        this.content_id = content_id || null;
    },

    onClick: function(e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var action = false;

        if (row !== false) {
            var btn = e.getTarget(".img-button");

            if (btn) {
                action = btn.className.replace(/img-button btn-/, '').trim();
            }

            if (action != 'img-button') {
                var documents_id = this.getStore().getAt(row).get('documents_id');
                var module = 'setDocumentStatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        var flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, documents_id, flag);

                        break;
                }
            }
        }
    },

    onAction: function(action, documents_id, flag) {
        var that = this;
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'content',
                action: action,
                documents_id: documents_id,
                flag: flag
            },
            callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(documents_id).set('documents_status', flag);
                    store.commitChanges();

                    TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});
