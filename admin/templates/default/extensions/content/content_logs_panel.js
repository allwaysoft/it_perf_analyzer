Toc.content.LogsPanel = function(config) {
    //console.debug(this.config);
    this.conf = config;
    config.region = 'west';
    config = config || {};
    config.title = 'Logs';
    config.header = false;
    config.hideHeaders = true;
    config.width = 200;
    config.lines = [];

    config.content_id = config.content_id || null;
    config.content_type = config.content_type || null;
    config.loadMask = true;
    config.border = true;
    config.viewConfig = {
        emptyText: TocLanguage.gridNoRecords
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_logs',
            content_id: config.content_id,
            content_type : config.content_type,
            user:config.server_user,
            pass:config.server_pass,
            port:config.server_port,
            host:config.host
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'logs_id'
        }, [
            'host',
            'url',
            'content_name',
            'logs_id',
            'can_read',
            'can_write',
            'can_modify',
            'can_publish',
            'lines',
            'size',
            'user',
            'pass',
            'port'
        ]),
        autoLoad: false,
        listeners : {
            load : function(store,records,obj) {
                var i = 0;
                while(i < store.data.items.length)
                {
                    this.lines[store.data.items[i].data.logs_id] = 0;
                    i++;
                }
                this.loaded = true;
            },scope: this
        }
    });

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.plugins = config.rowActions;

    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        {id:'label',header: 'Fichier', dataIndex: 'content_name',width:200}
    ]);
    config.autoExpandColumn = 'label';

    config.tbar = [
        {
            //text: TocLanguage.btnAdd,
            text: '',
            iconCls: 'add',
            handler: function() {
                this.onAdd();
            },
            scope: this
        },
        '-',
        {
            //text: TocLanguage.btnDelete,
            text: '',
            iconCls: 'remove',
            handler: this.onBatchDelete,
            scope: this
        },
        '-',
        {
            //text: TocLanguage.btnRefresh,
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    this.addEvents({'selectchange' : true});
    Toc.content.LogsPanel.superclass.constructor.call(this, config);
    this.getView().scrollOffset = 0;
};

Ext.extend(Toc.content.LogsPanel, Ext.grid.GridPanel, {
    onAdd: function() {
        var dlg = this.owner.createLogsDialog();

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.setTitle('Nouveau document');
        dlg.show(this.conf);
    },

    onDelete: function(record) {
        var documentsId = record.get('logs_id');
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
                            logs_id: documentsId,
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

    onRowClick:function(grid, rowIndex,obj) {
        var record = grid.getStore().getAt(rowIndex);
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

    setLogId: function(json) {
        this.fireEvent('selectchange',json);
    },

    setLogCount: function(logs_id,count){
        var store = this.getStore();

        var i = 0;
        while(i < store.data.items.length)
        {
            var item = store.data.items[i];
            if(item.data.logs_id == logs_id)
            {
                store.data.items[i].data.lines = count;
            }
            i++;
        }
    },

    onClick: function(e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);

        var sel = this.getStore().getAt(row);

        this.setLogId(sel.json);

//        if(sel)
//        {
//            //this.mainPanel.maskText();
//            this.getEl().mask('Chargement log ...');
//            this.logs_id = sel.json.logs_id;
//
//            Ext.Ajax.request({
//                url: Toc.CONF.CONN_URL,
//                timeout:0,
//                params: {
//                    module: 'servers',
//                    action: 'show_log',
//                    host: sel.json.host,
//                    user:this.server_user,
//                    pass:this.server_pass,
//                    port:this.server_port,
//                    url: sel.json.url,
//                    logs_id:sel.json.logs_id,
//                    lines:this.lines[sel.json.logs_id] || 0
//                },
//                callback: function(options, success, response) {
//                    var result = Ext.decode(response.responseText);
//
//                    if (result.success == true) {
//                        this.mainPanel.setLog(result.data.content,this.logs_id);
//                        this.lines[this.logs_id] = result.data.lines;
//                        var i = 0;
//                        var store = this.getStore();
//                        while(i < store.data.items.length)
//                        {
//                            if(store.data.items[i].data.logs_id != this.logs_id)
//                            {
//                                this.lines[store.data.items[i].data.logs_id] = 0;
//                            }
//                            i++;
//                        }
//                        this.getEl().unmask();
//                        TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.data.size + ' MB'});
//                    }
//                    else
//                    {
//                        this.mainPanel.setLog(result.feedback);
//                        TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
//                    }
//                },
//                scope: this
//            });
//        }

//        if (row !== false) {
//            var btn = e.getTarget(".img-button");
//
//            if (btn) {
//                action = btn.className.replace(/img-button btn-/, '').trim();
//            }
//
//            if (action != 'img-button') {
//                var logs_id = this.getStore().getAt(row).get('logs_id');
//                var module = 'setDocumentStatus';
//
//                switch (action) {
//                    case 'status-off':
//                    case 'status-on':
//                        var flag = (action == 'status-on') ? 1 : 0;
//                        this.onAction(module, logs_id, flag);
//
//                        break;
//                }
//            }
//        }
    },

    onAction: function(action, logs_id, flag) {
        var that = this;
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'content',
                action: action,
                logs_id: logs_id,
                flag: flag
            },
            callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(logs_id).set('documents_status', flag);
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
