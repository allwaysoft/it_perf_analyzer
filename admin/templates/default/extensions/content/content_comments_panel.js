Toc.content.CommentsPanel = function(config) {

    config = config || {};
    config.title = 'Commentaires',
    config.listeners = {
        activate : function(panel) {
            if (!this.loaded) {
                this.getStore().reload();
            }
        },
        scope: this
    }

    config.content_id = config.content_id || null;
    config.region = 'center';
    config.loadMask = true;
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'content',
            action: 'list_comments',
            content_id: config.content_id,
            content_type : config.content_type
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'comments_id'
        }, [
            'comments_id',
            'comment',
            'image_url',
            'comments_status'
        ]),
        listeners: {
            load: function() {
                this.loaded = true;
            },
            scope: this
        },
        autoLoad: false
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

    renderPublish = function(status) {
        if (status == 1) {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    renderAccount = function(comment) {
        return '<span style="font-size: large;">' + comment.user_name + '</span><div style = "white-space : normal">' + comment.comments_description + '</div>';
    };

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'image_url', header: '', dataIndex: 'image_url'},
        { id: 'comment', header: 'Comment', dataIndex: 'comment', sortable: false,align: 'left',renderer: renderAccount,css : "white-space: normal;"},
        { header: 'Status', align: 'center', renderer: renderPublish, dataIndex: 'comments_status'},
        config.rowActions
    ]);
    config.autoExpandColumn = 'comment';

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
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
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->',
        config.txtSearch,
        ' ',
        {
            text: '',
            iconCls: 'search',
            handler: this.onSearch,
            scope: this
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

    Toc.content.CommentsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.CommentsPanel, Ext.grid.GridPanel, {

    onAdd: function() {
        var dlg = this.owner.createCommentDialog();
        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show(this.content_id, this.content_type);
    },

    onEdit: function(record) {
    },

    onDelete: function(record) {
        var commentsId = record.get('comments_id');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            TocLanguage.msgDeleteConfirm,
            function(btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'content',
                            action: 'delete_comment',
                            comments_id: commentsId
                        },
                        callback: function(options, success, response) {
                            var result = Ext.decode(response.responseText);

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

    onBatchDelete: function() {
        var keys = this.selModel.selections.keys;

        if (keys.length > 0) {
            var batch = keys.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                TocLanguage.msgDeleteConfirm,
                function(btn) {
                    if (btn == 'yes') {
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'content',
                                action: 'delete_comments',
                                batch: batch
                            },
                            callback: function(options, success, response) {
                                var result = Ext.decode(response.responseText);

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
        } else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    onRefresh: function() {
        this.getStore().reload();
    },

    refreshGrid: function (categoriesId) {
        var store = this.getStore();

        store.baseParams['categories_id'] = categoriesId;
        this.categoriesId = categoriesId;
        store.load();
    },

    onSearch: function() {
        var categoriesId = this.cboCategories.getValue() || null;
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['current_category_id'] = categoriesId;
        store.baseParams['search'] = filter;
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
        }
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
                var commentsId = this.getStore().getAt(row).get('comments_id');
                var module = 'setCommentStatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, commentsId, flag);

                        break;
                }
            }
        }
    },

    onAction: function(action, commentsId, flag) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'content',
                action: action,
                comments_id: commentsId,
                flag: flag
            },
            callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(commentsId).set('comments_status', flag);
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