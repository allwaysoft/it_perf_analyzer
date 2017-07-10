Toc.SessionsGrid = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.started = false;
    config.region = 'center';
    config.loadMask = false;
    config.layout = 'fit';
    config.border = true;
    config.autoHeight = true;
    config.title = 'Sessions';
    //config.columnLines = false;
    //config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_sessions',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid,
            tbs: config.tbs,
            status: 'active'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'sid'
        }, [
            'sid',
            'sessionid',
            'serial',
            'username',
            'command',
            'state',
            'event',
            'client_info',
            'wait_time',
            'seconds_in_wait',
            'logon_time',
            'schemaname',
            'osuser',
            'machine',
            'terminal',
            'program',
            'module',
            'action',
            'sofar',
            'totalwork',
            'pct_pga'
        ]),
        listeners: {
            load: function (store, records, opt) {
                this.lblInfos.setText(store.data.length + ' Sessions actives ...');
                setTimeout(that.refreshData(that), 10000);
            },
            beforeload: function (store, opt) {
                return that.started;
            }, scope: that
        },
        autoLoad: true
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-delete-record', qtip: 'Deconnecter'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'username', header: 'DB User', dataIndex: 'username', width: 8},
        { id: 'osuser', header: 'OS User', dataIndex: 'osuser', width: 8},
        { id: 'machine', header: 'Machine', dataIndex: 'machine', width: 14},
        { id: 'client_info', header: 'Info', dataIndex: 'program', width: 30, renderer: render},
        { id: 'state', header: 'Status', dataIndex: 'state', width: 10},
        { id: 'event', header: 'Event', dataIndex: 'event', width: 20, renderer: render},
        { id: 'seconds_in_wait', header: 'Duree (S)', dataIndex: 'seconds_in_wait', width: 5, align: 'center'},
        { id: 'pga', header: '% PGA', dataIndex: 'pct_pga', width: 5, renderer: Toc.content.ContentManager.renderProgress},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'username';

    var thisObj = this;

    config.combo_freq = Toc.content.ContentManager.getFrequenceCombo();

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.lblInfos = new Ext.form.Label({
        width: 200,
        text:'O Sessions actives',
        autoShow:true
    });

    config.pBar = new Ext.ProgressBar({
        hidden: true,
        width: 300,
        hideLabel: true
    });

    config.tbar = [
        {
            text: '',
            iconCls: 'remove',
            handler: this.onBatchKill,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: this.started ? 'stop' : 'play',
            handler: this.started ? this.onStop : this.onStart,
            scope: this
        },
        '-',
        {
            text: 'ASH',
            iconCls: 'report',
            handler: this.onAsh,
            scope: this
        },
        '-',
        {
            text: 'AWR',
            iconCls: 'awr',
            handler: this.onAwr,
            scope: this
        },
        '-',
        config.lblInfos,
        '-',
        config.pBar,
        '-',
        config.combo_freq,
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

    config.combo_freq.getStore().load();

    config.combo_freq.on('select', function (combo, record, index) {
        thisObj.onStop();
        var category = thisObj.categoryCombo.getValue();
        var freq = thisObj.combo_freq.getValue();
        thisObj.buildItems(category, freq);
    });

    Toc.SessionsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SessionsGrid, Ext.grid.GridPanel, {

    onAsh: function () {
        this.getEl().mask('Creation du job ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'run_ash',
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    if (result.task_id) {
                        var request = {
                            status: "run",
                            task_id: result.task_id,
                            comment: result.comment
                        };

                        this.downloadReport(request);

                    }
                    else {
                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task_id !!!");
                    }
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    },
    onAwr: function () {
        var params = {
                module: 'databases',
                action: 'run_awr',
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid
        };

        var dialog = new Toc.AWRDialog(params);
        dialog.show();
    },
    downloadReport: function (request) {
        var status = request.status;
        var action = "get_jobStatus";

        switch (status) {
            case "run":
                action = "get_jobStatus";
                break;

            case "error":
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, request.comments);
                return;
                break;
            case "complete":
                action = "download_report";
                break;
            default:
                action = "get_jobStatus";
                break;
        }

        this.getEl().mask(request.comments);

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: action,
                task_id: request.task_id,
                comments : request.comments
            },
            callback: function (options, success, response) {
                if (response.responseText) {
                    result = Ext.decode(response.responseText);
                    switch (action) {
                        case 'download_report':
                            this.getEl().unmask();
                            url = result.file_name;
                            params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
                            window.open(url, "", params);
                            this.buttons[0].enable();
                            break;
                        default:
                            var req = result.records[0];
                            //console.debug(req);
                            if(req.task_id)
                            {
                                this.task_id = req.task_id;
                                this.getEl().unmask();
                                setTimeout(this.downloadReport(req), 2000);
                            }
                            else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task id specified !!!");
                            }

                            break;
                    }
                }
                else {
                    setTimeout(this.downloadReport(request), 2000);
                }
            },
            scope: this
        });
    },
    refreshData: function (scope) {
        if (scope) {
            var store = this.getStore();
            store.load();
        }
    },
    killUser: function (sid, serial,pbar,step,max) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'kill_session',
                sid: sid,
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid,
                serial: serial
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                pbar.val = pbar.val + step;
                pbar.count = pbar.count + 1;
                pbar.updateProgress(pbar.val,sid + " deconnecte ...",true);

                if(pbar.count >= max)
                {
                    pbar.reset();
                    pbar.hide();
                    this.onRefresh();
                }

                if (result.success == true) {

                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    },
    onBatchKill : function() {
        var count = this.selModel.getCount();
        if(count > 0)
        {
            this.pBar.reset();
            this.pBar.updateProgress(0,"",true);
            this.pBar.val = 0;
            this.pBar.count = 0;
            this.pBar.show();
            var step = 1/count;

            for (var i=0;i<count;i++)
            {
                var sid = this.selModel.selections.items[i].data.sid;
                var serial = this.selModel.selections.items[i].data.serial;

                this.killUser(sid, serial,this.pBar,step,count);
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },
    onKill: function (record) {
        var sid = record.get('sid');
        var serial = record.get('serial');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            'Voulez-vous vraiment deconnecter cet utilisateur ?',
            function (btn) {
                if (btn == 'yes') {
                    this.getEl().mask('Creation du Job ...');
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'databases',
                            action: 'kill_session',
                            sid: sid,
                            db_user: this.db_user,
                            db_pass: this.db_pass,
                            db_port: this.db_port,
                            db_host: this.host,
                            db_sid: this.sid,
                            serial: serial
                        },
                        callback: function (options, success, response) {
                            this.getEl().unmask();
                            var result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                var params = {
                                    db_user: this.db_user,
                                    db_pass: this.db_pass,
                                    db_port: this.db_port,
                                    db_host: this.host,
                                    db_sid: this.sid,
                                    job_name: result.job_name,
                                    panel: this,
                                    description: 'Deconnexion de la session ' + sid + ':' + serial
                                };

                                this.proc_name = result.proc_name;
                                Toc.watchJob(params);
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
    onStart: function () {
        var that = this;

        this.started = true;
        this.refreshData(this);
        this.topToolbar.items.items[4].setHandler(this.onStop, this);
        this.topToolbar.items.items[4].setIconClass('stop');
    },
    onStop: function () {
        this.started = false;
        this.refreshData(this);
        this.topToolbar.items.items[4].setHandler(this.onStart, this);
        this.topToolbar.items.items[4].setIconClass('play');
    },
    onRefresh: function () {
        this.onStart();
        this.onStop();
    },
    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onKill(record);
                break;
        }
    }
});

Toc.LockTreeGrid = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.started = false;
    config.region = 'center';
    config.loadMask = false;
    //config.width = '25%';
    //config.border = true;
    config.autoHeight = true;
    config.title = 'Verrous';
    config.columnLines = false;
    //config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'lock_tree',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'sid'
        }, [
            'sid',
            'level',
            'osuser',
            'machine',
            'serial',
            'username',
            'object_name',
            'sql_text'
        ]),
        listeners: {
            load: function (store, records, opt) {
                setTimeout(that.refreshData(that), that.freq);
            },
            beforeload: function (store, opt) {
                return that.started;
            }, scope: that
        },
        autoLoad: true
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
            {iconCls: 'icon-delete-record', qtip: 'Deconnecter'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (user) {
        if (user && user.indexOf(";") > 0) {
            var values = user.split(';');

            var level = values[0];
            var name = values[1];

            return Toc.content.ContentManager.lPad(name, name.length + level, '.');
        }

        return '';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'level', header: 'Niveau', dataIndex: 'level', width: 5},
        { id: 'username', header: 'DB User', dataIndex: 'username', width: 10},
        { id: 'osuser', header: 'OS User', dataIndex: 'osuser', width: 10},
        { id: 'machine', header: 'Machine', dataIndex: 'machine', width: 15},
        { id: 'object_name', header: 'Objet', dataIndex: 'object_name', width: 10},
        { id: 'sql_text', header: 'SQL', dataIndex: 'sql_text', width: 50},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'username';

    var thisObj = this;

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.tbar = [
        {
            //text: 'Deconnecter',
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
        },
        '-',
        {
            //text: this.started ? 'Stop' : 'Start',
            text: '',
            iconCls: this.started ? 'stop' : 'play',
            handler: this.started ? this.onStop : this.onStart,
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

    Toc.LockTreeGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.LockTreeGrid, Ext.grid.GridPanel, {


    refreshData: function (scope) {
        if (scope) {
            var store = this.getStore();
            store.load();
        }
    },
    onEdit: function (record) {
        var params = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host,
            db_port: this.port,
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_host: this.host,
            db_sid: this.sid,
            tbs: record.get("name")
        };

        //console.debug(params);
        var dlg = new Toc.TbsBrowser(params);
        dlg.setTitle(this.label + ' : ' + record.get("name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        //dlg.show(params, this.owner);
    },

    onKill: function (record) {
        var sid = record.get('sid');
        var serial = record.get('serial');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            'Voulez-vous vraiment deconnecter cet utilisateur ?',
            function (btn) {
                if (btn == 'yes') {
                    this.getEl().mask('Creation du Job ...');
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'databases',
                            action: 'kill_session',
                            sid: sid,
                            db_user: this.db_user,
                            db_pass: this.db_pass,
                            db_port: this.db_port,
                            db_host: this.host,
                            db_sid: this.sid,
                            serial: serial
                        },
                        callback: function (options, success, response) {
                            this.getEl().unmask();
                            var result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                var params = {
                                    db_user: this.db_user,
                                    db_pass: this.db_pass,
                                    db_port: this.db_port,
                                    db_host: this.host,
                                    db_sid: this.sid,
                                    job_name: result.job_name,
                                    panel: this,
                                    description: 'Deconnexion de la session ' + sid + ':' + serial
                                };

                                this.proc_name = result.proc_name;
                                Toc.watchJob(params);
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
    onStart: function () {
        this.started = true;
        this.refreshData(this);
        this.topToolbar.items.items[4].setHandler(this.onStop, this);
        this.topToolbar.items.items[4].setIconClass('stop');
    },
    onStop: function () {
        this.started = false;
        this.refreshData(this);
        this.topToolbar.items.items[4].setHandler(this.onStart, this);
        this.topToolbar.items.items[4].setIconClass('play');
    },
    onRefresh: function () {
        this.getStore().reload();
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onKill(record);
                break;
        }
    }
});

Toc.LibrayCachePanel = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.started = false;
    config.region = 'center';
    config.loadMask = true;
    config.width = '25%';
    //config.border = true;
    config.autoHeight = true;
    config.title = 'Library Cache';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_librarycache',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.port,
            db_host: config.host,
            db_sid: config.sid,
            tbs: config.tbs
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'namespace'
        }, [
            'namespace',
            'reloads',
            'invalidations',
            'get',
            'pin'
        ]),
        listeners: {
            load: function (store, records, opt) {

            },
            beforeload: function (store, opt) {
            }, scope: this
        },
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'namespace', header: 'Namespace', dataIndex: 'namespace', width: 100},
        { id: 'reloads', header: 'Reloads', dataIndex: 'reloads', width: 20},
        { id: 'invalidations', header: 'Inv', dataIndex: 'invalidations', width: 20},
        { id: 'get', header: '% Gets', dataIndex: 'get', width: 50, renderer: Toc.content.ContentManager.renderProgress},
        { id: 'pin', header: '% Pin', dataIndex: 'pin', width: 50, renderer: Toc.content.ContentManager.renderProgress}
    ]);

    var thisObj = this;

    config.task = {
        run: function () {
            thisObj.getStore().load();
        },
        interval: 10000 //2 second
    };

    config.runner = new Ext.util.TaskRunner();

    config.tools = [
        {
            id: 'refresh',
            qtip: 'Refresh',
            handler: function (event, toolEl, panel) {
                panel.started = !panel.started;

                if (panel.started) {
                    panel.runner.start(panel.task);
                }
                else {
                    panel.runner.stop(panel.task);
                }
            }
        }
    ];

    Toc.LibrayCachePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.LibrayCachePanel, Ext.grid.GridPanel, {

    onEdit: function (record) {
        var params = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host,
            db_port: this.port,
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_host: this.host,
            db_sid: this.sid,
            tbs: record.get("name")
        };

        //console.debug(params);
        var dlg = new Toc.TbsBrowser(params);
        dlg.setTitle(this.label + ' : ' + record.get("name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(params, this.owner);
    },

    onRefresh: function () {
        this.getStore().reload();
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
    }
});

Toc.DatabasesPerfDashboard = function (config) {

    var that = this;
    config = config || {};
    config.region = 'center';
    config.started = false;
    config.layout = 'Column';
    config.loadMask = true;
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            //console.log('activate');
        },
        deactivate: function (panel) {
            //console.log('deactivate');
            this.onStop();
        },
        scope: this
    };

    if (!config.label) {
        config.txtSearch = new Ext.form.TextField({
            width: 100,
            hideLabel: true
        });

        config.combo_freq = Toc.content.ContentManager.getFrequenceCombo();
        config.categoryCombo = Toc.content.ContentManager.getDatabasesCategoryCombo();

        config.tbar = [
            {
                text: '',
                iconCls: 'add',
                handler: this.onAdd,
                scope: this
            },
            '-',
            {
                text: '',
                iconCls: 'refresh',
                handler: this.onRefresh,
                scope: this
            },
            '-',
            {
                //text: this.started ? 'Stop' : 'Start',
                text: '',
                iconCls: this.started ? 'stop' : 'play',
                handler: this.started ? this.onStop : this.onStart,
                scope: this
            },
            '-',
            config.combo_freq,
            '->',
            config.categoryCombo
        ];

        config.combo_freq.getStore().load();
        config.categoryCombo.getStore().load();

        var thisObj = this;

        config.categoryCombo.on('select', function (combo, record, index) {
            thisObj.onStop();
            thisObj.combo_freq.enable();
            var category = record.data.key;
            var freq = thisObj.combo_freq.getValue();
            thisObj.buildItems(category, freq);
        });

        config.combo_freq.on('select', function (combo, record, index) {
            thisObj.onStop();
            var category = thisObj.categoryCombo.getValue();
            var freq = thisObj.combo_freq.getValue();
            thisObj.buildItems(category, freq);
        });
    }

    Toc.DatabasesPerfDashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabasesPerfDashboard, Ext.Panel, {

    onAdd: function () {
        var dlg = this.owner.createDatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(null, path, null, this.owner);
    },

    onRefresh: function () {
        var category = this.categoryCombo.getValue();
        this.buildItems(category);
    },

    buildItems: function (category, freq) {
        if (this.items) {
            this.removeAll(true);
        }

        var frequence = freq || 5000;

        this.getEl().mask('Chargement');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'list_databasesperf',
                category: category || 'all'
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);
                var total = result.total;
                //var body_height = this.getEl().getHeight()/(total/8);

                if (total > 0) {
                    var i = 0;
                    while (i < total) {
                        db = result.records[i];
                        //console.debug(db);
                        db.owner = this.owner;
                        db.freq = frequence;
                        db.width = '12.5%';
                        db.body_height = '140px';

                        var panel = new Toc.TopEventsPanelCharts(db);
                        //console.log(panel.id);
                        //var panel = new Toc.TopEventsPanel(db);

                        //var panel = new Toc.TopWaitClassPanel(db);
                        this.add(panel);
                        //panel.buildItems(db);
                        this.doLayout();
                        i++;
                    }
                }
            },
            scope: this
        });
    },

    onStop: function () {
        var items = this.items.items;
        //console.debug(items);
        var i = 0;
        while (i < items.length) {
            var panel = items[i];
            //console.debug(panel);
            if (panel && panel.stop) {
                panel.stop();
            }
            i++;
        }

        this.started = false;
        this.topToolbar.items.items[4].setHandler(this.onStart, this);
        this.topToolbar.items.items[4].setIconClass('play');
    },

    onStart: function () {
        var items = this.items.items;
        //console.debug(items);
        var i = 0;
        while (i < items.length) {
            var panel = items[i];
            //console.debug(panel);
            if (panel && panel.start) {
                panel.start();
            }
            i++;
        }

        this.started = true;
        this.topToolbar.items.items[4].setHandler(this.onStop, this);
        this.topToolbar.items.items[4].setIconClass('stop');
    }
});

Toc.TopEventsPanelCharts = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.width = this.params.width || '25%';
    config.count = 0;
    config.reqs = 0;
    //config.bodyStyle = 'height:150px';
    //config.height = '20%';
    //config.layout = 'fit';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        render: function (comp) {

            var configChart = function () {
                thisObj.data = [];

                var chart;
                chart = new AmCharts.AmSerialChart();
                chart.width = '100%';
                //chart.height = 150;
                //chart.autoResize = true;

                var type = "line";
                chart.dataProvider = thisObj.data;
                chart.marginTop = 5;
                chart.categoryField = "date";

                // AXES
                // Category
                var categoryAxis = chart.categoryAxis;
                categoryAxis.gridAlpha = 0.07;
                categoryAxis.axisColor = "#DADADA";
                categoryAxis.labelsEnabled = false;
                categoryAxis.startOnAxis = true;

                // Value
                var valueAxis = new AmCharts.ValueAxis();
                valueAxis.stackType = "regular"; // this line makes the chart "stacked"
                valueAxis.gridAlpha = 0.07;
                valueAxis.title = "Sessions";
                valueAxis.titleColor = "green";
                valueAxis.labelsEnabled = true;
                chart.addValueAxis(valueAxis);

                // GRAPHS
                // first graph
                var graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "other";
                graph.valueField = "other";
                graph.lineColor = "#F06EAA";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                // second graph
                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "application";
                graph.valueField = "application";
                graph.lineAlpha = 0;
                graph.lineColor = "#C02800";
                graph.fillAlphas = 0.6;
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                // third graph
                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "configuration";
                graph.valueField = "configuration";
                graph.lineAlpha = 0;
                graph.lineColor = "#5C440B";
                graph.fillAlphas = 0.6;
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "administrative";
                graph.valueField = "administrative";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#717354";
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "concurrency";
                graph.valueField = "concurrency";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#8B1A00";
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "commit";
                graph.valueField = "commit";
                graph.lineColor = "#E46800";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "network";
                graph.valueField = "network";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#9F9371";
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "userio";
                graph.valueField = "userio";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#004AE7";
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "systemio";
                graph.valueField = "systemio";
                graph.lineColor = "#0094E7";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "scheduler";
                graph.valueField = "scheduler";
                graph.lineColor = "#86FF86";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "clustering";
                graph.valueField = "clustering";
                graph.lineColor = "#C9C2AF";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "queueing";
                graph.valueField = "queueing";
                graph.lineColor = "#C2B79B";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                // LEGEND
                //var legend = new AmCharts.AmLegend();
                //legend.position = "top";
                //legend.valueText = "[[value]]";
                //legend.valueWidth = 100;
                //legend.valueAlign = "left";
                //legend.equalWidths = false;
                //legend.periodValueText = "total: [[value.sum]]"; // this is displayed when mouse is not over the chart.
                //chart.addLegend(legend);

                // CURSOR
                //var chartCursor = new AmCharts.ChartCursor();
                //chartCursor.cursorAlpha = 0;
                //chart.addChartCursor(chartCursor);

                // SCROLLBAR
                //var chartScrollbar = new AmCharts.ChartScrollbar();
                //chartScrollbar.color = "#FFFFFF";


                // WRITE
                chart.write(thisObj.body.id);
                thisObj.chart = chart;
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }

            //console.debug(this);

        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            //console.log('resize');
            //console.log('adjWidth ==> ' + adjWidth);
            //console.log('adjHeight ==> ' + adjHeight);
            //console.log('rawWidth ==> ' + rawWidth);
            //console.log('rawHeight ==> ' + rawHeight);
            //console.debug(comp);

            Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tools = [
    ];

    Toc.TopEventsPanelCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TopEventsPanelCharts, Ext.Panel, {

    onEdit: function (record) {
        var event = record.data.event;

        var params = {
            label: this.label,
            servers_id: this.servers_id,
            databases_id: this.databases_id,
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host,
            db_port: this.port,
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_host: this.host,
            db_sid: this.sid,
            sid: this.sid,
            typ: this.typ
        };

        var dlg = this.owner.createDatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        var panels = [];
        panels[0] = 'sessions';

        dlg.show(params, path, this.owner, panels);
    },

    refreshData: function (scope) {
        if (scope && scope.started) {
            if(scope.reqs == 0)
            {
                console.log('reqs++');
                scope.reqs++;

                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'databases',
                        action: 'list_waits',
                        db_user: this.db_user,
                        db_pass: this.db_pass,
                        db_port: this.port,
                        db_host: this.host,
                        db_sid: this.sid,
                        databases_id: this.databases_id
                    },
                    callback: function (options, success, response) {
                        console.log('reqs--');
                        scope.reqs--;

                        var chart = this.chart;
                        var valueAxis = chart.valueAxes[0];

                        if (success) {
                            var json = Ext.decode(response.responseText);

                            //console.debug(json);
                            var data = json.records[0];

                            if (this.data.length > 50) {
                                this.data.shift();
                            }

                            chart.dataProvider = this.data;
                            if (chart.chartData.length > 50) {
                                chart.chartData.shift();
                            }

                            if (valueAxis) {
                                valueAxis.titleColor = "green";
                                valueAxis.labelsEnabled = true;
                                valueAxis.title = "Sessions";
                                if (data) {
                                    this.data.push(data);
                                }
                                else {
                                    valueAxis.titleColor = "red";
                                    valueAxis.title = "No Data";
                                }

                                if (data && data.comments) {
                                    valueAxis.titleColor = "red";
                                    valueAxis.title = data.comments;
                                }
                            }
                        }
                        else {
                            if (valueAxis) {
                                valueAxis.titleColor = "red";
                                valueAxis.title = "Timeout";
                            }
                        }

                        chart.validateData();

                        if(scope.count == 0)
                        {
                            var interval = setInterval(function(){
                                scope.refreshData(scope)
                            }, scope.freq || 5000);
                            //setTimeout(that.refreshData, that.freq || 10000);
                            scope.count++;
                            scope.interval = interval;
                        }
                        else
                        {
                            //console.log('that.count' + scope.count);
                        }
                    },
                    scope: this
                });
            }
        }
    },

    onRefresh: function () {
        this.getStore().reload();
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-search-record':
                this.onEdit(record);
                break;
        }
    },

    start: function () {
        this.started = true;
        this.refreshData(this);
    },

    stop: function () {
        this.started = false;
        this.refreshData(this);

        if(this.interval)
        {
            clearInterval(this.interval);
        }
        else
        {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle,"No job defined !!!");
        }
    }
});

Toc.AWRDialog = function(config) {
    config = config || {};

    config.title = 'Generer un Rapport AWR';
    config.layout = 'fit';
    config.width = 600;
    config.height = 100;
    config.resizable = false;
    config.minimizable = true,
    config.modal = true;
    config.iconCls = 'icon-reports-win';
    config.items = this.buildForm(config);

    config.buttons = [
        {
            text:'Executer',
            handler: function(){
                this.submitForm();
            },
            scope:this
        },
        {
            text: TocLanguage.btnClose,
            handler: function(){
                this.close();
            },
            scope:this
        }
    ];

    this.addEvents({'saveSuccess' : true});

    Toc.AWRDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AWRDialog, Ext.Window, {

    show: function() {
        var browser = new Toc.SnapshotBrowser({parent : this,capture : 'snap_id',db_user: this.db_user,
            db_pass: this.db_pass,
            db_port: this.db_port,
            db_host: this.db_host,
            db_sid: this.db_sid});

        this.tabreports.add(browser);
        this.tabreports.doLayout();

        Toc.AWRDialog.superclass.show.call(this);
    },
    buildParams : function (form, action,panel) {
        if (action.result.msg == '1') {
            var params = action.result.params;
            if (!this.pnlParameters.buildControls(params)) {
                this.tabreports.hideTabStripItem(0);
            }
        }
        else {
            if (action.result.msg != '') {
                Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.msg);
            }
        }

        if(panel)
        {
            panel.getEl().unmask();
        }
    },
    getContentPanel: function() {
        this.tabreports = new Ext.Panel({
            region: 'center',
            layout : 'form',
            border:false,
            defaults:{
                hideMode:'offsets'
            },
            deferredRender: false,
            items: [
            ]
        });

        return this.tabreports;
    },

    buildForm: function(config) {
        this.frmReport = new Ext.form.FormPanel({
            //fileUpload: true,
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                action: 'run_awr',
                module : 'databases',
                db_user: config.db_user,
                db_pass: config.db_pass,
                db_port: config.db_port,
                db_host: config.db_host,
                db_sid: config.db_sid
            },
            deferredRender: false,
            items: [this.getContentPanel()]
        });

        return this.frmReport;
    },
    downloadReport: function (request) {
        var status = request.status;
        var action = "get_jobStatus";

        switch (status) {
            case "run":
                action = "get_jobStatus";
                break;

            case "error":
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, request.comments);
                return;
                break;
            case "complete":
                action = "download_report";
                break;
            default:
                action = "get_jobStatus";
                break;
        }

        this.getEl().mask(request.comments);

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: action,
                task_id: request.task_id,
                comments : request.comments
            },
            callback: function (options, success, response) {
                if (response.responseText) {
                    result = Ext.decode(response.responseText);
                    switch (action) {
                        case 'download_report':
                            this.getEl().unmask();
                            url = result.file_name;
                            params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
                            window.open(url, "", params);
                            this.buttons[0].enable();
                            break;
                        default:
                            var req = result.records[0];
                            //console.debug(req);
                            if(req.task_id)
                            {
                                this.task_id = req.task_id;
                                this.getEl().unmask();
                                setTimeout(this.downloadReport(req), 2000);
                            }
                            else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task id specified !!!");
                            }

                            break;
                    }
                }
                else {
                    setTimeout(this.downloadReport(request), 2000);
                }
            },
            scope: this
        });
    },
    submitForm : function() {
        this.buttons[0].disable();

        this.frmReport.form.submit({
            waitMsg: 'Creation du job, veuillez patienter SVP ...',
            timeout:0,
            success: function(form, action){
                result = action.result;
                if (result.task_id) {
                    var request = {
                        status: "run",
                        task_id: result.task_id,
                        comment: result.comment
                    };

                    this.downloadReport(request);
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task_id !!!");
                }
            },
            failure: function(form, action) {
                Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
            },
            scope: this
        });
    }
});