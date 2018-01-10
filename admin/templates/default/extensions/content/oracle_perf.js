Toc.SessionsGrid = function (config) {
    var that = this;
    config = config || {};
    config.started = false;
    //config.region = 'center';
    config.loadMask = true;
    config.border = true;
    config.title = 'Sessions';
    config.count = 0;
    //config.height = '100%';
    config.reqs = 0;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (comp) {
        },
        show: function (comp) {
        },
        enable: function (panel) {
        },
        deactivate: function (panel) {
            this.onStop();
        },
        destroy: function (panel) {
            this.onStop();
        },
        disable: function (panel) {
            this.onStop();
        },
        remove: function (container, panel) {
            this.onStop();
        },
        removed: function (container, panel) {
            this.onStop();
        },
        render: function (comp) {
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.ORACLE_URL,
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
            'sql_text',
            'sql_id',
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
            'action',
            'pct'
        ]),
        listeners: {
            load: function (store, records, opt) {
                if (that.inAshPanel) {
                    that.setTitle('Sessions (' + store.data.length + ' active(s) ... )');
                }
                else {
                    that.lblInfos.setText(store.data.length + ' Sessions actives ...');
                }

                that.reqs--;

                if (that.count == 0) {
                    var interval = setInterval(function () {
                        that.refreshData(that);
                    }, that.freq || 2000);
                    that.count++;
                    that.interval = interval;
                }
            },
            beforeload: function (store, opt) {
                return that.started;
            }, scope: that
        },
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-delete-record', qtip: 'Deconnecter'},
            {iconCls: 'icon-tune-record', qtip: 'Recommendations'},
            {iconCls: 'icon-gather-record', qtip: 'Rapport SQL'}
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
        { id: 'osuser', header: 'OS User', dataIndex: 'osuser', width: 7},
        { id: 'machine', header: 'Machine', dataIndex: 'machine', width: 10},
        { id: 'sql', header: 'SQL', dataIndex: 'sql_text', width: 25, renderer: render},
        { id: 'client_info', header: 'Info', dataIndex: 'client_info', width: 20, renderer: render},
        { id: 'state', header: 'Status', dataIndex: 'state', width: 8, renderer: render},
        { id: 'event', header: 'Event', dataIndex: 'event', width: 10, renderer: render},
        { id: 'seconds_in_wait', header: 'Duree (S)', dataIndex: 'seconds_in_wait', width: 4, align: 'center'},
        { id: 'pct', header: '%', dataIndex: 'pct', width: 8, renderer: Toc.content.ContentManager.renderProgress, align: 'center'},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'username';

    var thisObj = this;

    if (that.inAshPanel) {
        config.txtSearch = new Ext.form.TextField({
            width: 100,
            hideLabel: true
        });

        config.lblInfos = new Ext.form.Label({
            width: 200,
            text: 'O Sessions actives',
            autoShow: true
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
                text: 'ADDM',
                iconCls: 'report',
                handler: this.onAddm,
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
    }

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

                        Toc.downloadJobReport(request, this);

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
    onAddm: function () {
        this.getEl().mask('Creation du job ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'run_addm',
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

                        Toc.downloadJobReport(request, this);

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
    refreshData: function (scope) {
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                var store = this.getStore();
                scope.reqs++;
                store.load();
            }
        }
    },
    killUser: function (sid, serial, pbar, step, max) {
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
                pbar.updateProgress(pbar.val, sid + " deconnecte ...", true);

                if (pbar.count >= max) {
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
    onBatchKill: function () {
        var count = this.selModel.getCount();
        if (count > 0) {
            this.pBar.reset();
            this.pBar.updateProgress(0, "", true);
            this.pBar.val = 0;
            this.pBar.count = 0;
            this.pBar.show();
            var step = 1 / count;

            for (var i = 0; i < count; i++) {
                var sid = this.selModel.selections.items[i].data.sid;
                var serial = this.selModel.selections.items[i].data.serial;

                this.killUser(sid, serial, this.pBar, step, count);
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },
    onKill: function (record) {
        Toc.KillUser(record, this);
    },
    onStart: function () {
        this.started = true;
        this.count = 0;
        this.reqs = 0;
        var store = this.getStore();
        store.baseParams['start_time'] = null;
        store.baseParams['end_time'] = null;
        this.refreshData(this);
        if (!this.inAshPanel) {
            this.topToolbar.items.items[4].setHandler(this.onStop, this);
            this.topToolbar.items.items[4].setIconClass('stop');
        }
    },
    onStop: function () {
        this.started = false;
        this.count = 10;
        this.reqs = 10;
        this.refreshData(this);

        if (this.interval) {
            clearInterval(this.interval);
        }

        if (!this.inAshPanel) {
            this.topToolbar.items.items[4].setHandler(this.onStart, this);
            this.topToolbar.items.items[4].setIconClass('play');
        }
    },
    onRefresh: function () {
        this.onStart();
        this.onStop();
    },
    onSearch: function () {
        var filter;
        this.onStop();

        filter = this.txtSearch.getValue() || null;
        var store = this.getStore();
        store.baseParams['search'] = filter;

        this.onStart();
    },
    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onKill(record);
                break;

            case 'icon-gather-record':
                //this.onSqlReport(record);
                Toc.SqlReport(record, this);
                break;

            case 'icon-tune-record':
                //this.onSqlReport(record);
                Toc.SqlTune(record, this);
                break;
        }
    }
});

Toc.LockedObjGrid = function (config) {

    var that = this;
    config = config || {};
    config.started = false;
    config.region = 'center';
    config.loadMask = true;
    config.count = 0;
    config.reqs = 0;
    config.title = 'Verrous Objets';

    //config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (comp) {
            comp.activated = true;
        },
        show: function (comp) {
        },
        enable: function (panel) {
        },
        deactivate: function (panel) {
            if (panel.activated) {
                panel.onStop();
            }
        },
        destroy: function (panel) {
            if (panel.activated) {
                panel.onStop();
            }
        },
        disable: function (panel) {
            if (panel.activated) {
                panel.onStop();
            }
        },
        remove: function (container, panel) {
            if (panel.activated) {
                panel.onStop();
            }
        },
        removed: function (container, panel) {
            if (container.activated) {
                container.onStop();
            }
        },
        render: function (comp) {
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'lock_obj',
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
            'serial',
            'username',
            'osuser',
            'duree',
            'locked_object',
            'status',
            'description'
        ]),
        listeners: {
            load: function (store, records, opt) {
                this.lblInfos.setText(store.data.length + ' Verrous ...');
                that.reqs--;

                if (that.count == 0) {
                    var interval = setInterval(function () {
                        that.refreshData(that);
                    }, that.freq || 5000);
                    that.count++;
                    that.interval = interval;
                }
            },
            beforeload: function (store, opt) {
                return that.started;
            }, scope: that
        },
        autoLoad: false
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
        { id: 'username', header: 'DB User', dataIndex: 'username', width: 15},
        { id: 'osuser', header: 'OS User', dataIndex: 'osuser', width: 10},
        { id: 'duree', header: 'Duree (Min)', dataIndex: 'duree', width: 10},
        { id: 'locked_object', header: 'Objet', dataIndex: 'locked_object', width: 20},
        { id: 'description', header: 'SQL', dataIndex: 'description', width: 45, renderer: render},
        config.rowActions
    ]);
    config.autoExpandColumn = 'username';

    var thisObj = this;

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.lblInfos = new Ext.form.Label({
        width: 200,
        text: 'O Verrous',
        autoShow: true
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

    Toc.LockedObjGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.LockedObjGrid, Ext.grid.GridPanel, {

    refreshData: function (scope) {
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                var store = this.getStore();
                scope.reqs++;
                store.load();
            }
        }
    },
    killUser: function (sid, serial, pbar, step, max) {
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
                pbar.updateProgress(pbar.val, sid + " deconnecte ...", true);

                if (pbar.count >= max) {
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
    onBatchKill: function () {
        var count = this.selModel.getCount();
        if (count > 0) {
            this.pBar.reset();
            this.pBar.updateProgress(0, "", true);
            this.pBar.val = 0;
            this.pBar.count = 0;
            this.pBar.show();
            var step = 1 / count;

            for (var i = 0; i < count; i++) {
                var sid = this.selModel.selections.items[i].data.sid;
                var serial = this.selModel.selections.items[i].data.serial;

                this.killUser(sid, serial, this.pBar, step, count);
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },
    onKill: function (record) {
        Toc.KillUser(record, this);
    },
    onStart: function () {
        var that = this;

        this.started = true;
        this.count = 0;
        this.reqs = 0;
        this.refreshData(this);
        this.topToolbar.items.items[4].setHandler(this.onStop, this);
        this.topToolbar.items.items[4].setIconClass('stop');
    },
    onStop: function () {
        this.started = false;
        this.count = 10;
        this.reqs = 10;
        this.refreshData(this);
        if (this.interval) {
            clearInterval(this.interval);
        }

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
    },
    onSearch: function () {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['search'] = filter;
        store.reload();
    }
});

Toc.LockTreeGrid = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.started = false;
    config.region = 'center';
    config.loadMask = true;
    //config.width = '25%';
    //config.border = true;
    config.autoHeight = true;
    config.count = 0;
    config.reqs = 0;
    config.title = 'Verrous Sessions';
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
                that.reqs--;

                if (that.count == 0) {
                    var interval = setInterval(function () {
                        that.refreshData(that);
                    }, that.freq || 5000);
                    that.count++;
                    that.interval = interval;
                }
            },
            beforeload: function (store, opt) {
                return that.started;
            }, scope: that
        },
        autoLoad: false
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
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                var store = this.getStore();
                scope.reqs++;
                store.load();
            }
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
        Toc.KillUser(record, this);
    },
    onStart: function () {
        this.started = true;
        this.count = 0;
        this.reqs = 0;
        this.refreshData(this);
        this.topToolbar.items.items[4].setHandler(this.onStop, this);
        this.topToolbar.items.items[4].setIconClass('stop');
    },
    onStop: function () {
        this.started = false;
        this.count = 10;
        this.reqs = 10;
        this.refreshData(this);
        if (this.interval) {
            clearInterval(this.interval);
        }

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
    config.width = '100%';
    //config.border = true;
    //config.autoHeight = true;
    config.title = 'Library Cache';
    //config.columnLines = false;
    //config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (panel) {
            panel.getStore().load();
        }
    };

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
            'gets',
            'gethits',
            'pins',
            'pinhits',
            'invalidations',
            'get',
            'pin'
        ]),
        listeners: {
        },
        autoLoad: true
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
        { id: 'namespace', header: 'Namespace', dataIndex: 'namespace', width: 25, align: 'left'},
        { id: 'reloads', header: 'Reloads', dataIndex: 'reloads', width: 5, align: 'center'},
        { id: 'gets', header: 'Gets', dataIndex: 'gets', width: 5, align: 'center'},
        { id: 'gethits', header: 'Gethits', dataIndex: 'gethits', width: 5, align: 'center'},
        { id: 'pins', header: 'Pins', dataIndex: 'pins', width: 5, align: 'center'},
        { id: 'pinhits', header: 'Pinhits', dataIndex: 'pinhits', width: 5, align: 'center'},
        { id: 'invalidations', header: 'Invalidations', dataIndex: 'invalidations', width: 10, align: 'center'},
        { id: 'get', header: '% Gets', dataIndex: 'get', width: 15, renderer: Toc.content.ContentManager.renderPct, align: 'center'},
        { id: 'pin', header: '% Pin', dataIndex: 'pin', width: 15, renderer: Toc.content.ContentManager.renderPct, align: 'center'}
    ]);

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: function (panel) {
                that.getStore().reload();
            },
            scope: this
        }
    ];

    Toc.LibrayCachePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.LibrayCachePanel, Ext.grid.GridPanel, {
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
                        this.add(panel);
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

Toc.MemoryDashboardPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.title = 'Memory';
    config.autoHeight = true;
    config.layout = 'column';
    config.loadMask = false;
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            this.onRefresh();
        },
        deactivate: function (panel) {
            this.onStop();
        },
        scope: this
    };

    config.tbar = [
        {
            text: TocLanguage.btnRefresh,
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '-',
        {
            text: 'Start',
            iconCls: 'play',
            handler: this.onStart,
            scope: this
        },
        '-',
        {
            text: 'Stop',
            iconCls: 'stop',
            handler: this.onStop,
            scope: this
        }
    ];

    Toc.MemoryDashboardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MemoryDashboardPanel, Ext.Panel, {
    onRefresh: function () {
        //var category = this.categoryCombo.getValue();
        this.buildItems();
    },

    buildItems: function (freq) {
        if (this.items) {
            this.removeAll(true);
        }

        this.panels = [];

        var frequence = freq || 15000;

        var panel = new Toc.SgaResizePanel(this.params);
        panel.frequence = frequence;
        this.add(panel);
        this.panels[0] = panel;
        //panel.buildItems(db);
        this.doLayout();
    },

    onStop: function () {
        if (this.panels) {
            var i = 0;
            while (i < this.panels.length) {
                var panel = this.panels[i];
                //console.debug(panel);
                if (panel && panel.stop) {
                    panel.stop();
                }
                i++;
            }
        }
    },

    onStart: function () {
        if (this.panels) {
            var i = 0;
            while (i < this.panels.length) {
                var panel = this.panels[i];
                //console.debug(panel);
                if (panel && panel.start) {
                    panel.start();
                }
                i++;
            }
        }
    }
});

Toc.DatabasePerfDashboard = function (config) {
    var that = this;
    config = config || {};
    //console.log(config.isProduction);
    config.region = 'center';
    config.started = false;
    config.layout = 'Column';
    config.loadMask = false;
    //config.body_height = '100px';
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
        },
        add: function (container, component, index) {
            //console.log('add databaseSpaceDashboard');
            if (!thisObj.isProduction) {
                //thisObj.buildItems(null, 15000);
            }
            //this.onRefresh();
        },
        added: function (component, ownerCt, index) {
            //console.log('added databaseSpaceDashboard');
            if (!thisObj.isProduction) {
                //thisObj.buildItems(null, 15000);
            }
            //this.onRefresh();
        },
        afterlayout: function (container, layout) {
            //console.log('afterlayout databaseSpaceDashboard');
            if (!thisObj.isProduction) {
                //thisObj.buildItems(null, 15000);
            }
            //this.onRefresh();
        },
        afterrender: function (panel) {
            //console.log('afterrender databaseSpaceDashboard');
            if (!thisObj.isProduction) {
                //thisObj.buildItems(null, 15000);
            }
            //this.onRefresh();
        },
        enable: function (panel) {
            //console.log('enable databaseSpaceDashboard');
            if (!thisObj.isProduction) {
                //thisObj.buildItems(null, 15000);
            }
            //this.onRefresh();
        },
        render: function (panel) {
            //console.log('render databaseSpaceDashboard');
            //console.debug(panel);
            if (this.isProduction) {
                this.buildItems('all', 5000);
            }
            //this.onRefresh();
        },
        show: function (panel) {
            //console.log('show databaseSpaceDashboard');
            if (!thisObj.isProduction) {
                //thisObj.buildItems(null, 15000);
            }
            //this.onRefresh();
        },
        deactivate: function (panel) {
            //console.log('deactivate');
            this.onStop();
        },
        scope: this
    };

    var thisObj = this;

    if (!config.label) {

        if (!config.isProduction) {
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

            config.categoryCombo.on('select', function (combo, record, index) {
                thisObj.onStop();
                thisObj.combo_freq.enable();
                var category = record.data.group_id;
                var freq = thisObj.combo_freq.getValue();
                thisObj.buildItems(category, freq);
            });

            config.combo_freq.on('select', function (combo, record, index) {
                if (thisObj.started)
                    thisObj.onStop();
                var category = thisObj.categoryCombo.getValue();
                var freq = thisObj.combo_freq.getValue();
                thisObj.buildItems(category, freq);
            });
        }
        else {
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
                }
            ];
        }
    }

    Toc.DatabasePerfDashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabasePerfDashboard, Ext.Panel, {
    onAdd: function () {
    },

    onRefresh: function () {
        var category = this.isProduction ? 'all' : this.categoryCombo.getValue();
        this.buildItems(category);
    },

    buildItems: function (category, freq) {
        if (this.items) {
            this.removeAll(true);
        }

        this.panels = [];

        var frequence = freq || 5000;

        this.getEl().mask('Chargement');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'list_databasesperf',
                category: category || 'all',
                where: this.isProduction ? "a.databases_id in (45,44,23,22,10,54)" : ''
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);

                if (result.total > 0) {
                    var i = 0;
                    while (i < result.total) {
                        db = result.records[i];
                        //console.debug(db);
                        db.owner = this.owner;
                        db.freq = frequence;
                        db.width = result.total <= 6 ? '100%' : '50%';
                        db.classs = (i % 2 == 0) ? 'blue' : 'gray';

                        var panel = new Toc.DatabasePerfPanel(db);
                        //var panel = new Toc.TopWaitClassPanel(db);
                        this.add(panel);
                        this.panels[i] = panel;
                        //panel.buildItems(db);
                        this.doLayout();
                        //panel.start();
                        i++;
                    }
                }
            },
            scope: this
        });
    },

    onStop: function () {
        if (this.panels) {
            var i = 0;
            while (i < this.panels.length) {
                var panel = this.panels[i];
                //console.debug(panel);
                if (panel && panel.stop) {
                    panel.stop();
                }
                i++;
            }
        }

        this.started = false;
        this.topToolbar.items.items[4].setHandler(this.onStart, this);
        this.topToolbar.items.items[4].setIconClass('play');
    },

    onStart: function () {
        if (this.panels) {
            var i = 0;
            while (i < this.panels.length) {
                var panel = this.panels[i];
                //console.debug(panel);
                if (panel && panel.start) {
                    panel.start();
                }
                i++;
            }
        }

        this.started = true;
        this.topToolbar.items.items[4].setHandler(this.onStop, this);
        this.topToolbar.items.items[4].setIconClass('stop');
    }
});

Toc.DatabasePerfPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.border = true;
    config.width = config.params.width || '100%';
    config.layout = 'column';
    config.cls = params.classs;
    config.title = params.label;
    //config.header = false;
    //config.autoHeight = true;
    config.listeners = {
        show: function (comp) {
        },
        added: function (index) {
        },
        enable: function (comp) {
        },
        render: function (comp) {
            this.buildItems(this.params);
        },
        afterrender: function (comp) {
        },
        scope: this
    };

    Toc.DatabasePerfPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabasePerfPanel, Ext.Panel, {
    buildItems: function (params) {
        params.owner = this.owner;
        //params.width = '14%';

        var conf = {
            status: 'up',
            width: '20%',
            autoExpandColumn: 'event',
            label: 'Waits',
            body_height: '75px',
            freq: params.freq,
            //hideHeaders: true,
            databases_id: params.databases_id,
            server_user: params.server_user,
            server_pass: params.server_pass,
            server_port: params.server_port,
            servers_id: params.servers_id,
            db_user: params.db_user,
            db_pass: params.db_pass,
            db_port: params.port,
            db_host: params.host,
            db_sid: params.sid,
            port: params.port,
            host: params.host,
            sid: params.sid
        };

        this.dbperf = new Toc.TopEventsPanelCharts(conf);
        this.add(this.dbperf);

        var mem = {
            width: '10%',
            label: 'Swap',
            body_height: '75px',
            freq: params.freq * 5,
            //hideHeaders:true,
            databases_id: params.databases_id,
            server_user: params.server_user,
            server_pass: params.server_pass,
            server_port: params.server_port,
            servers_id: params.servers_id,
            db_user: params.db_user,
            db_pass: params.db_pass,
            db_port: params.port,
            db_host: params.host,
            db_sid: params.sid,
            port: params.port,
            host: params.host,
            sid: params.sid
        };

        this.mem_usage = new Toc.MemCharts(mem);
        this.add(this.mem_usage);

        var cpu = {
            width: '12%',
            label: 'CPU',
            body_height: '75px',
            freq: params.freq,
            //hideHeaders:true,
            databases_id: params.databases_id,
            server_user: params.server_user,
            server_pass: params.server_pass,
            server_port: params.server_port,
            servers_id: params.servers_id,
            db_user: params.db_user,
            db_pass: params.db_pass,
            db_port: params.port,
            db_host: params.host,
            db_sid: params.sid,
            port: params.port,
            host: params.host,
            sid: params.sid
        };

        this.cpu_usage = new Toc.CpuCharts(cpu);
        this.add(this.cpu_usage);

        var disk = {
            width: '12%',
            label: 'Disks',
            body_height: '75px',
            freq: params.freq,
            //hideHeaders:true,
            databases_id: params.databases_id,
            server_user: params.server_user,
            server_pass: params.server_pass,
            server_port: params.server_port,
            servers_id: params.servers_id,
            db_user: params.db_user,
            db_pass: params.db_pass,
            db_port: params.port,
            db_host: params.host,
            db_sid: params.sid,
            port: params.port,
            host: params.host,
            sid: params.sid
        };

        this.disk_usage = new Toc.DiskCharts(disk);
        this.add(this.disk_usage);

        var net = {
            width: '16%',
            label: 'Net (MB/s)',
            body_height: '75px',
            freq: params.freq,
            //hideHeaders:true,
            databases_id: params.databases_id,
            server_user: params.server_user,
            server_pass: params.server_pass,
            server_port: params.server_port,
            servers_id: params.servers_id,
            db_user: params.db_user,
            db_pass: params.db_pass,
            db_port: params.port,
            db_host: params.host,
            db_sid: params.sid,
            port: params.port,
            host: params.host,
            sid: params.sid
        };

        this.net_usage = new Toc.NetCharts(net);
        this.add(this.net_usage);

        params.freq = params.freq * 5;
        params.width = '15%';
        params.body_height = '75px';
        this.tbs = new Toc.TopTbsPanel(params);
        this.add(this.tbs);

        this.fs = new Toc.TopFsPanel(params);
        this.add(this.fs);
    },

    start: function () {
        this.dbperf.start();
        this.tbs.start();
        this.fs.start();
        this.mem_usage.start();
        this.cpu_usage.start();
        this.disk_usage.start();
        this.net_usage.start();
    },

    stop: function () {
        this.dbperf.stop();
        this.tbs.stop();
        this.fs.stop();
        this.mem_usage.stop();
        this.cpu_usage.stop();
        this.disk_usage.stop();
        this.net_usage.stop();
    }
});

Toc.TopEventsPanelCharts = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.width = this.params.width || '25%';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
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
                //chart.height = '100%';
                //chart.autoResize = true;
                //chart.autoDisplay = true;

                var type = "line";
                chart.dataProvider = thisObj.data;

                chart.marginBottom = 1;
                chart.marginLeft = 1;
                chart.marginRight = 1;
                chart.marginTop = 1;
                chart.categoryField = "date";

                // AXES
                // Category
                var categoryAxis = chart.categoryAxis;
                categoryAxis.gridAlpha = 0.07;
                categoryAxis.axisColor = "#DADADA";
                categoryAxis.labelsEnabled = config.showLegend;

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
                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "CPU";
                graph.valueField = "cpu";
                graph.lineColor = "green";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Queueing : [[value]]";
                }
                chart.addGraph(graph);

                // second graph
                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Application";
                graph.valueField = "application";
                graph.lineAlpha = 0;
                graph.lineColor = "#C02800";
                graph.fillAlphas = 0.6;
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Application : [[value]]";
                }
                chart.addGraph(graph);

                // third graph
                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Configuration";
                graph.valueField = "configuration";
                graph.lineAlpha = 0;
                graph.lineColor = "#5C440B";
                graph.fillAlphas = 0.6;
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Configuration : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Administrative";
                graph.valueField = "administrative";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#717354";
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Administrative : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Concurrency";
                graph.valueField = "concurrency";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#8B1A00";
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Concurrency : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Commit";
                graph.valueField = "commit";
                graph.lineColor = "#E46800";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Commit : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "User I/O";
                graph.valueField = "userio";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#004AE7";
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "User I/O : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "System I/O";
                graph.valueField = "systemio";
                graph.lineColor = "#0094E7";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "System I/O : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Network";
                graph.valueField = "network";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.lineColor = "#9F9371";
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Network : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Other";
                graph.valueField = "other";
                graph.lineColor = "#F06EAA";
                graph.lineAlpha = 0;
                graph.showBalloon = false;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                if (config.showLegend) {
                    //graph.balloonText = "Other : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Scheduler";
                graph.valueField = "scheduler";
                graph.lineColor = "#86FF86";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Scheduler : [[value]]";
                }
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type;
                graph.title = "Queueing";
                graph.valueField = "queueing";
                graph.lineColor = "#C2B79B";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6;
                graph.showBalloon = false;
                if (config.showLegend) {
                    //graph.balloonText = "Queueing : [[value]]";
                }
                chart.addGraph(graph);

                // CURSOR
                if (config.showLegend) {
                    var legend = new AmCharts.AmLegend();
                    legend.position = "right";
                    //legend.periodValueText = "total: [[value.sum]]"; // this is displayed when mouse is not over the chart.
                    chart.addLegend(legend);

                    var chartCursor = new AmCharts.ChartCursor();
                    chartCursor.cursorAlpha = 0;
                    chart.addChartCursor(chartCursor);

                    // SCROLLBAR
                    chart.chartScrollbar = new AmCharts.ChartScrollbar();
                    //chartScrollbar.color = "#FFFFFF";

                    chart.listeners = [
                        {"event": "zoomed", "method": thisObj.onZoomed}
                    ];
                }

                // WRITE
                chart.write(comp.body.id);
                chart.panel = comp;
                comp.chart = chart;
                //console.log('configChart');
                //console.debug(comp);
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
            if(config.body_height)
            {
                Component.body.dom.style.height = config.body_height;
            }
        },
        scope: this
    };

    config.tools = [
        {
            id: 'refresh',
            qtip: 'Refresh',
            handler: function (event, toolEl, panel) {
                thisObj.stop();
                thisObj.start();
            }
        }
    ];

    this.addEvents('zoomed');

    Toc.TopEventsPanelCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TopEventsPanelCharts, Ext.Panel, {
    onZoomed: function (chart)  {
        //console.log('onZoomed');
        chart.chart.panel.startValue = chart.startValue;
        chart.chart.panel.endValue = chart.endValue;
        //console.debug(chart);
        this.fireEvent('zoomed',chart.startValue,chart.endValue);

        //console.log('endIndex ===> ' + chart.endIndex);
        //console.log('endValue ===> ' + chart.endValue);
        //console.log('startIndex ===> ' + chart.startIndex);
        //console.log('startValue ===> ' + chart.startValue);
        //console.log('type ===> ' + chart.type);
    },
    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                scope.reqs++;

                scope.transactionId = Ext.Ajax.request({
                    url: Toc.CONF.ORACLE_URL,
                    params: {
                        module: 'databases',
                        action: this.action || 'list_waits',
                        db_user: this.db_user,
                        db_pass: this.db_pass,
                        db_port: this.port,
                        sample_time: this.sample_time,
                        db_host: this.host,
                        db_sid: this.sid,
                        databases_id: this.databases_id
                    },
                    callback: function (options, success, response) {
                        scope.reqs--;
                        scope.try = 0;
                        scope.setTitle(scope.label);

                        if (scope.chart) {
                            var chart = scope.chart;
                            var valueAxis = chart.valueAxes[0];

                            if (success) {
                                var json = Ext.decode(response.responseText);
                                var data = null;
                                //console.debug(json);
                                if (!this.showLegend) {
                                    data = json ? json.records[0] : [];
                                    //var data = json ? json.records : [];

                                    if (this.data.length > 200) {
                                        this.data.shift();
                                    }

                                    chart.dataProvider = this.data;
                                    if (chart.chartData.length > 200) {
                                        chart.chartData.shift();
                                    }

                                    if (valueAxis) {
                                        valueAxis.titleColor = "green";
                                        valueAxis.labelsEnabled = true;
                                        valueAxis.title = "";
                                        if (data) {
                                            this.data.push(data);
                                        }
                                        else {
                                            valueAxis.labelsEnabled = false;
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = "No Data";
                                        }

                                        if (data && data.comments) {
                                            valueAxis.labelsEnabled = false;
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = data.comments;
                                        }
                                    }
                                }
                                else {
                                    //var data = json ? json.records[0] : [];
                                    data = json ? json.records : [];

                                    scope.setTitle(scope.label + ' (' + data.length + ')');

                                    if (this.data.length > 200) {
                                        //this.data.shift();
                                    }

                                    //chart.dataProvider = this.data;
                                    if(!scope.sample_time)
                                    {
                                        chart.dataProvider = data;
                                    }
                                    else
                                    {
                                        chart.dataProvider.push(data);
                                    }

                                    scope.sample_time = json.sample_time;

                                    if (chart.chartData.length > 200) {
                                        //chart.chartData.shift();
                                    }

                                    if (valueAxis) {
                                        valueAxis.titleColor = "green";
                                        valueAxis.labelsEnabled = true;
                                        valueAxis.title = "";
                                        if (data) {
                                            //this.data.push(data);
                                        }
                                        else {
                                            valueAxis.labelsEnabled = false;
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = "No Data";
                                        }

                                        if (data && data.comments) {
                                            valueAxis.labelsEnabled = false;
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = data.comments;
                                        }
                                    }
                                }
                            }
                            else {
                                if (valueAxis) {
                                    valueAxis.labelsEnabled = false;
                                    valueAxis.titleColor = "red";
                                    //valueAxis.title = "Timeout";
                                    valueAxis.title = response.responseText;
                                }
                            }

                            chart.validateNow();

                            if (json && json.records) {
                                if (json.records.length > 0) {
                                    chart.validateData();
                                }
                            }
                        }
                    },
                    scope: this
                });
            }
            else {
                scope.setTitle(scope.title + '.');
            }

            if (scope.try > 10) {
                scope.setTitle(scope.label);
                scope.try = 0;
            }

            if (scope.count == 0) {
                var interval = setInterval(function () {
                    scope.refreshData(scope)
                }, scope.freq || 5000);
                scope.count++;
                scope.interval = interval;
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
        this.count = 0;
        this.reqs = 0;
        this.try = 0;
        this.refreshData(this);
    },
    stop: function () {
        this.started = false;
        this.count = 10;
        this.reqs = 10;
        this.refreshData(this);

        if (this.interval) {
            clearInterval(this.interval);
        }

        this.interval = null;
    }
});

Toc.AWRDialog = function (config) {
    config = config || {};

    config.title = 'Generer un Rapport AWR';
    config.layout = 'fit';
    config.width = 600;
    config.height = 200;
    config.resizable = false;
    config.minimizable = true,
        config.modal = true;
    config.iconCls = 'icon-reports-win';
    config.items = this.buildForm(config);

    config.buttons = [
        {
            text: 'Executer',
            handler: function () {
                this.submitForm();
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

    Toc.AWRDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AWRDialog, Ext.Window, {

    show: function () {
        var browser = new Toc.SnapshotBrowser({parent: this, capture: 'snap_id', db_user: this.db_user,
            db_pass: this.db_pass,
            db_port: this.db_port,
            db_host: this.db_host,
            db_sid: this.db_sid});

        this.tabreports.add(browser);
        this.tabreports.doLayout();

        Toc.AWRDialog.superclass.show.call(this);
    },
    buildParams: function (form, action, panel) {
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

        if (panel) {
            panel.getEl().unmask();
        }
    },
    getContentPanel: function () {
        this.tabreports = new Ext.Panel({
            region: 'center',
            layout: 'form',
            border: false,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
            ]
        });

        return this.tabreports;
    },

    buildForm: function (config) {
        this.frmReport = new Ext.form.FormPanel({
            //fileUpload: true,
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                action: 'run_awr',
                module: 'databases',
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
    submitForm: function () {
        this.buttons[0].disable();

        this.frmReport.form.submit({
            waitMsg: 'Creation du job, veuillez patienter SVP ...',
            timeout: 0,
            success: function (form, action) {
                result = action.result;
                if (result.task_id) {
                    var request = {
                        status: "run",
                        task_id: result.task_id,
                        comment: result.comment
                    };

                    Toc.downloadJobReport(request, this);
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task_id !!!");
                }
            },
            failure: function (form, action) {
                Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
            },
            scope: this
        });
    }
});

Toc.SgaResizePanel = function (config) {
    config = config || {};
    config.loadMask = true;
    config.border = true;
    config.title = 'SGA resize OPS';
    //config.autoHeight = true;
    config.viewConfig = {
        emptyText: TocLanguage.gridNoRecords, forceFit: true
    };

    config.listeners = {
        activate: function (panel) {
            panel.getStore().load();
        }
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'sga_resize',
            db_port: config.port,
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'icon',
            'component',
            'parameter',
            'status',
            'initial_size',
            'target_size',
            'final_size',
            'start_time',
            'end_time',
            'duree'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        {header: '', dataIndex: 'icon', width: 5},
        {id: 'component', header: 'Component', dataIndex: 'component', width: 25},
        {header: 'Parameter', dataIndex: 'parameter', width: 10},
        {header: 'Status', align: 'center', dataIndex: 'status', width: 5},
        {header: 'Initial Size', align: 'center', dataIndex: 'initial_size', width: 10},
        {header: 'Target Size', align: 'center', dataIndex: 'target_size', width: 10},
        {header: 'Final Size', align: 'center', dataIndex: 'final_size', width: 10},
        {header: 'Start Time', align: 'center', dataIndex: 'start_time', width: 10},
        {header: 'End Time', align: 'center', dataIndex: 'end_time', width: 10},
        {header: 'Duree', align: 'center', dataIndex: 'duree', width: 5}
    ]);
    //config.autoExpandColumn = 'component';

    Toc.SgaResizePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SgaResizePanel, Ext.grid.GridPanel, {
});

Toc.exploreDatabase = function (node, panel) {
    panel.removeAll();

    if (node.id == 0) {
        panel.removeAll();
    }
    else {
        if (node) {

            if (node) {
                panel.node = node;
            }
            else {
                Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun element selectionne !!!");
                return false;
            }

            var pnlAsh = new Toc.AshPanel({node: node, label: "ASH", databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

            var tab = new Ext.TabPanel({
                //activeTab: 0,
                defaults: {
                    hideMode: 'offsets'
                },
                deferredRender: true,
                listeners: {
                    beforetabchange: function (tabPanel, newTab, currentTab) {
                        //console.log('tab beforetabchange');
                    },
                    tabchange: function (tabPanel, tab) {
                        //console.log('tab tabchange');
                    },
                    beforeshow: function (comp) {
                        //console.log('tab beforeshow');
                    },
                    show: function (comp) {
                        //console.log('tab show');
                    },
                    added: function (index) {
                        //console.log('tab added');
                    },
                    add: function (container, component, index) {
                        //console.log('tab add');
                    },
                    beforeadd: function (container, component, index) {
                        //console.log('tab beforeadd');
                        //console.debug(component);
                        //panel.getEl().mask('adding ' + component.title + ' ...');
                    },
                    enable: function (comp) {
                        //console.log('tab enable');
                    },
                    beforerender: function (comp) {
                        //console.log('tab beforerender');
                        //panel.getEl().mask('rendering ...');
                    },
                    render: function (comp) {
                        //console.log('tab render');
                        //panel.getEl().unmask();
                    },
                    afterrender: function (comp) {
                        //console.log('tab afterrender');
                    },
                    afterlayout: function (container, layout) {
                        //console.log('tab afterlayout');
                    },
                    activate: function (panel) {
                        //console.log('tab activate');
                    },
                    deactivate: function (panel) {
                        //console.log('tab deactivate');
                    },
                    destroy: function (panel) {
                        //console.log('tab destroy');
                    },
                    disable: function (panel) {
                        //console.log('tab disable');
                    },
                    remove: function (container, panel) {
                        //console.log('tab remove');
                    },
                    removed: function (container, panel) {
                        //console.log('tab removed');
                    },
                    scope: this
                }
            });

            panel.add(tab);

            tab.add(pnlAsh);

            var tab_perf = new Ext.TabPanel({
                activeTab: 0,
                hideParent: false,
                title: 'Perf',
                region: 'center',
                defaults: {
                    hideMode: 'offsets'
                },
                deferredRender: true,
                listeners: {
                    activate: function (pnl) {
                        //console.log('tab_perf activate');
                        pnl.removeAll();
                        //var pnlSessions = new Toc.SessionsGrid({label: node.attributes.label, databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlSql = new Toc.SqlGrid({label: node.attributes.label, databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlVerrous = new Toc.LockTreeGrid({label: node.attributes.label, databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlLockedObj = new Toc.LockedObjGrid({label: node.attributes.label, databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

                        //var pnlMemory = new Toc.MemoryDashboardPanel({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

                        var pnlUserIO = new Toc.DatabaseHistogramPanel({lineColor: '#004AE7', class: 'User I/O', label: 'User I/O', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlSystemIO = new Toc.DatabaseHistogramPanel({lineColor: '#0094E7', class: 'System I/O', label: 'System I/O', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlConcurrency = new Toc.DatabaseHistogramPanel({lineColor: '#8B1A00', class: 'Concurrency', label: 'Concurrency', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlAdministrative = new Toc.DatabaseHistogramPanel({lineColor: '#717354', class: 'Administrative', label: 'Administrative', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlApplication = new Toc.DatabaseHistogramPanel({lineColor: '#C02800', class: 'Application', label: 'Application', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlCluster = new Toc.DatabaseHistogramPanel({lineColor: '#C9C2AF', class: 'Cluster', label: 'Cluster', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlCommit = new Toc.DatabaseHistogramPanel({lineColor: '#E46800', class: 'Commit', label: 'Commit', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlConfiguration = new Toc.DatabaseHistogramPanel({lineColor: '#5C440B', class: 'Configuration', label: 'Configuration', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlNetwork = new Toc.DatabaseHistogramPanel({lineColor: '#9F9371', class: 'Network', label: 'Network', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlOther = new Toc.DatabaseHistogramPanel({lineColor: '#F06EAA', class: 'Other', label: 'Other', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

                        //pnl.add(pnlSessions);
                        pnl.add(pnlSql);
                        pnl.doLayout(true, true);
                        pnl.add(pnlVerrous);
                        pnl.doLayout(true, true);
                        pnl.add(pnlLockedObj);
                        pnl.doLayout(true, true);
                        pnl.add(pnlUserIO);
                        pnl.doLayout(true, true);
                        pnl.add(pnlSystemIO);
                        pnl.doLayout(true, true);
                        pnl.add(pnlCommit);
                        pnl.doLayout(true, true);
                        pnl.add(pnlConcurrency);
                        pnl.doLayout(true, true);
                        pnl.add(pnlAdministrative);
                        pnl.doLayout(true, true);
                        pnl.add(pnlApplication);
                        pnl.doLayout(true, true);
                        pnl.add(pnlCluster);
                        pnl.doLayout(true, true);
                        pnl.add(pnlConfiguration);
                        pnl.doLayout(true, true);
                        pnl.add(pnlNetwork);
                        pnl.doLayout(true, true);
                        pnl.add(pnlOther);
                        pnl.doLayout(true, true);

                        pnl.activate(pnlSql);
                    },
                    deactivate: function (pnl) {
                        //console.log('tab_perf deactivate');
                        //pnl.removeAll();
                    }
                },
                items: []
            });

            tab.add(tab_perf);
            //tab.doLayout(true,true);

            var tabMemory = new Ext.TabPanel({
                activeTab: 0,
                hideParent: false,
                title: 'Memory',
                region: 'center',
                defaults: {
                    hideMode: 'offsets'
                },
                deferredRender: true,
                listeners: {
                    activate: function (pnl) {
                        //console.log('Memory activate');
                        pnl.removeAll();
                        var pnlMemoryResize = new Toc.MemoryResizePanel({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlLibrayCache = new Toc.LibrayCachePanel({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlLatch = new Toc.LatchPanel({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlPga = new Toc.PgaStatGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlMemoryAllocation = new Toc.MemoryAllocationPanel({label : 'Allocation',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlSharedPool = new Toc.SharedPoolPanel({label : 'Shared Pool',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

                        pnl.add(pnlMemoryResize);
                        pnl.add(pnlMemoryAllocation);
                        pnl.add(pnlPga);
                        pnl.add(pnlLatch);
                        pnl.add(pnlLibrayCache);
                        pnl.add(pnlSharedPool);
                        pnl.doLayout(true, true);
                        pnl.activate(pnlMemoryResize);
                    },
                    deactivate: function (pnl) {
                        //console.log('Memory deactivate');
                        pnl.removeAll();
                    }
                },
                items: []
            });

            tab.add(tabMemory);

            var pnlUsers = new Toc.usersGrid({label: node.attributes.label, databases_id: node.attributes.databasesId, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

            tab.add(pnlUsers);
            //tab.doLayout(true,true);

            var tab_storage = new Ext.TabPanel({
                activeTab: 0,
                hideParent: false,
                title: 'Storage',
                region: 'center',
                defaults: {
                    hideMode: 'offsets'
                },
                deferredRender: true,
                listeners: {
                    activate: function (pnl) {
                        console.log('tab_storage activate');
                        pnl.removeAll();
                        var pnlTbs = new Toc.tbsGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlDatafiles = new Toc.datafilesGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: node.attributes.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlFS = new Toc.fsGrid({host: node.attributes.host, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, owner: this.owner, typ: node.attributes.typ});

                        pnl.add(pnlTbs);
                        pnl.add(pnlDatafiles);
                        pnl.add(pnlFS);

                        pnl.doLayout(true, true);
                        pnl.activate(pnlTbs);
                    },
                    deactivate: function (pnl) {
                        console.log('tab_storage deactivate');
                        pnl.removeAll();
                    }
                },
                items: []
            });

            tab.add(tab_storage);
            //tab.doLayout(true,true);

            var tab_schema = new Ext.TabPanel({
                activeTab: 0,
                hideParent: false,
                title: 'Schema',
                region: 'center',
                defaults: {
                    hideMode: 'offsets'
                },
                deferredRender: true,
                listeners: {
                    activate: function (pnl) {
                        console.log('tab_schema activate');
                        pnl.removeAll();
                        var pnlTables = new Toc.tablesGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                        var pnlIndexes = new Toc.indexesGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

                        pnl.add(pnlTables);
                        pnl.add(pnlIndexes);

                        pnl.doLayout(true, true);
                        pnl.activate(pnlTables);
                    },
                    deactivate: function (pnl) {
                        console.log('tab_schema deactivate');
                        pnl.removeAll();
                    }
                },
                items: []
            });

            tab.add(tab_schema);
            //tab.doLayout(true,true);

            //this.pnlDocuments = new Toc.content.DocumentsPanel({content_id: this.databasesId, content_type: 'databases', owner: this.owner});
            //this.pnlLinks = new Toc.content.LinksPanel({content_id: this.databasesId, content_type: 'databases', owner: this.owner});
            //this.pnlComments = new Toc.content.CommentsPanel({content_id: this.databasesId, content_type: 'databases', owner: this.owner});

            var tab_rman = new Ext.TabPanel({
                activeTab: 0,
                hideParent: false,
                title: 'Rman',
                region: 'center',
                defaults: {
                    hideMode: 'offsets'
                },
                deferredRender: true,
                listeners: {
                    activate: function (pnl) {
                        console.log('tab_rman activate');
                        pnl.removeAll();
                        var pnlRmanConfig = new Toc.RmanConfigGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
                        var pnlRmanBackup = new Toc.RmanBackupGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

                        pnl.add(pnlRmanConfig);
                        pnl.add(pnlRmanBackup);

                        pnl.doLayout(true, true);
                        pnl.activate(pnlRmanConfig);
                    },
                    deactivate: function (pnl) {
                        console.log('tab_rman deactivate');
                        pnl.removeAll();
                    }
                },
                items: []
            });

            tab.add(tab_rman);
            //tab.doLayout(true,true);

            var pnlLogs = new Toc.logPanel({host: node.attributes.host, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, content_id: node.attributes.databases_id, content_type: 'databases', owner: this.owner});
            tab.add(pnlLogs);
            //tab.doLayout(true,true);

            var pnlNotifications = new Toc.notificationsGrid({databases_id: node.attributes.databasesId, owner: this.owner});
            tab.add(pnlNotifications);
            //tab.doLayout(true,true);

            var pnlParameters = new Toc.ParameterGrid({scope : 'instance',sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
            tab.add(pnlParameters);
            //tab.doLayout(true,true);

            var pnlAlertLog = new Toc.AlertLogPanel({host: node.attributes.host, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, owner: this.owner, typ: node.attributes.typ, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
            tab.add(pnlAlertLog);

            tab.doLayout(true, true);
            panel.doLayout(true, true);

            tab.activate(pnlAsh);
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucune Database selectionnee !!!");
        }
    }

    panel.mainPanel.doLayout();

    return true;
};

Toc.KillUser = function (record, scope) {
    var sid = record.get('sid');
    var serial = record.get('serial');

    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        'Voulez-vous vraiment deconnecter cet utilisateur ?',
        function (btn) {
            if (btn == 'yes') {
                scope.getEl().mask('Creation du Job ...');
                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'databases',
                        action: 'kill_session',
                        sid: sid,
                        db_user: scope.db_user,
                        db_pass: scope.db_pass,
                        db_port: scope.db_port,
                        db_host: scope.host,
                        db_sid: scope.sid,
                        serial: serial
                    },
                    callback: function (options, success, response) {
                        scope.getEl().unmask();
                        var result = Ext.decode(response.responseText);

                        if (result.success == true) {
                            var params = {
                                db_user: scope.db_user,
                                db_pass: scope.db_pass,
                                db_port: scope.db_port,
                                db_host: scope.host,
                                db_sid: scope.sid,
                                job_name: result.job_name,
                                panel: scope,
                                description: 'Deconnexion de la session ' + sid + ':' + serial
                            };

                            scope.proc_name = result.proc_name;
                            Toc.watchJob(params);
                        } else {
                            Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                        }
                    },
                    scope: scope
                });
            }
        },
        scope
    );
};

Toc.SqlReport = function (record, scope) {
    scope.getEl().mask('Creation du job ...');
    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'databases',
            action: 'sql_report',
            db_user: scope.db_user,
            db_pass: scope.db_pass,
            db_port: scope.db_port,
            db_host: scope.host,
            db_sid: scope.sid,
            sql_id: record.get('sql_id')
        },
        callback: function (options, success, response) {
            scope.getEl().unmask();
            var result = Ext.decode(response.responseText);

            if (result.success == true) {
                if (result.task_id) {
                    var request = {
                        status: "run",
                        task_id: result.task_id,
                        comment: result.comment
                    };

                    Toc.downloadJobReport(request, scope);
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task_id !!!");
                }
            }
            else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            }
        },
        scope: scope
    });
};

Toc.SqlTune = function (record, scope) {
    scope.getEl().mask('Creation du job ...');
    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'databases',
            action: 'sql_tune',
            db_user: scope.db_user,
            db_pass: scope.db_pass,
            db_port: scope.db_port,
            db_host: scope.host,
            db_sid: scope.sid,
            sql_id: record.get('sql_id')
        },
        callback: function (options, success, response) {
            scope.getEl().unmask();
            var result = Ext.decode(response.responseText);

            if (result.success == true) {
                if (result.task_id) {
                    var request = {
                        status: "run",
                        task_id: result.task_id,
                        comment: result.comment
                    };

                    Toc.downloadJobReport(request, scope);
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
};

Toc.EventHistoChart = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.width = this.params.width || '25%';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
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

                chart.marginBottom = 1;
                chart.marginLeft = 1;
                chart.marginRight = 1;
                chart.marginTop = 1;
                //chart.categoryField = "date";
                chart.categoryField = "wait_count";

                // AXES
                // Category
                var categoryAxis = chart.categoryAxis;
                categoryAxis.gridAlpha = 0.07;
                categoryAxis.axisColor = "#DADADA";
                categoryAxis.labelsEnabled = true;
                //categoryAxis.labelRotation = 90;
                categoryAxis.startOnAxis = true;

                // Value
                var valueAxis = new AmCharts.ValueAxis();
                //valueAxis.stackType = "regular"; // this line makes the chart "stacked"
                //valueAxis.gridAlpha = 0.07;
                valueAxis.title = "Time";
                //valueAxis.titleColor = "green";
                valueAxis.labelsEnabled = true;
                chart.addValueAxis(valueAxis);

                // GRAPHS
                // first graph
                var graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = config.event;
                graph.bullet = "round";
                graph.valueField = "wait_time";
                graph.lineColor = config.lineColor;
                //graph.lineAlpha = 0;
                //graph.bulletSizeField = 'wait_count';
                //graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<b>[[category]] waits de [[value]] secondes ==> [[last_update_time]]</b>";
                chart.addGraph(graph);

                // WRITE
                chart.write(thisObj.body.id);
                thisObj.chart = chart;
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tools = [
        {
            id: 'refresh',
            qtip: 'Refresh',
            handler: function (event, toolEl, panel) {
                thisObj.stop();
                thisObj.start();
            }
        }
    ];

    Toc.EventHistoChart.superclass.constructor.call(this, config);
};

Ext.extend(Toc.EventHistoChart, Ext.Panel, {

    onEdit: function (record) {
    },

    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                scope.reqs++;

                scope.transactionId = Ext.Ajax.request({
                    url: Toc.CONF.ORACLE_URL,
                    params: {
                        module: 'databases',
                        action: 'list_histo',
                        db_user: this.db_user,
                        db_pass: this.db_pass,
                        db_port: this.port,
                        db_host: this.host,
                        db_sid: this.sid,
                        databases_id: this.databases_id,
                        event: this.event
                    },
                    callback: function (options, success, response) {
                        scope.reqs--;
                        scope.try = 0;
                        scope.setTitle(scope.label);

                        if (scope.chart) {
                            var chart = scope.chart;
                            var valueAxis = chart.valueAxes[0];

                            if (success) {
                                var json = Ext.decode(response.responseText);

                                this.data = json ? json.records : [];

                                if (valueAxis) {
                                    valueAxis.titleColor = "green";
                                    valueAxis.title = "";
                                    valueAxis.labelsEnabled = true;
                                    if (!json) {
                                        valueAxis.titleColor = "red";
                                        valueAxis.title = "No Data";
                                    }
                                    else {
                                        chart.dataProvider = this.data;

                                        if (json.comment || !json.records) {
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = json.comment;
                                            valueAxis.labelsEnabled = false;
                                        }
                                    }

                                    chart.validateNow();
                                }

                                if (json && json.records) {
                                    if (json.records.length > 0) {
                                        chart.validateData();
                                    }
                                }
                            }
                            else {
                                if (valueAxis) {
                                    valueAxis.labelsEnabled = false;
                                    valueAxis.titleColor = "red";
                                    valueAxis.title = "Time out";

                                    chart.validateNow();
                                }
                            }
                        }
                    },
                    scope: this
                });
            }
            else {
                scope.setTitle(scope.title + '.');
            }

            if (scope.try > 10) {
                scope.setTitle(scope.label);
                scope.try = 0;
            }

            if (scope.count == 0) {
                var interval = setInterval(function () {
                    scope.refreshData(scope)
                }, scope.freq || 5000);
                scope.count++;
                scope.interval = interval;
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
        this.count = 0;
        this.reqs = 0;
        this.try = 0;
        this.refreshData(this);
    },
    stop: function () {
        this.started = false;
        this.count = 10;
        this.reqs = 10;
        this.refreshData(this);

        if (this.interval) {
            clearInterval(this.interval);
        }

        this.interval = null;
    }
});

Toc.DatabaseHistogramPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.border = true;
    config.width = config.params.width || '50%';
    config.layout = 'column';
    //config.cls = params.classs;
    config.title = params.label;
    //config.header = false;
    //config.autoHeight = true;
    config.listeners = {
        show: function (comp) {
        },
        added: function (index) {
        },
        enable: function (comp) {
        },
        render: function (comp) {
            console.log('render');
        },
        afterrender: function (comp) {
            //console.log('afterrender');
        },
        activate: function (panel) {
            //console.log('activate');
            panel.buildItems(this.params);
            //panel.start();
        },
        deactivate: function (panel) {
            //console.log('deactivate');
            panel.stop();
            panel.removeAll(true);
        },
        destroy: function (panel) {
            console.log('destroy');
            panel.stop();
        },
        disable: function (panel) {
            console.log('disable');
            panel.stop();
        },
        remove: function (container, panel) {
            console.log('remove');
            panel.stop();
        },
        removed: function (container, panel) {
            console.log('removed');
            panel.stop();
        },
        scope: this
    };

    Toc.DatabaseHistogramPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabaseHistogramPanel, Ext.Panel, {
    buildItems: function (params) {
        this.panels = [];

        var frequence = 60000;

        this.getEl().mask('Chargement');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'list_ioevents',
                class: params.class,
                databases_id: params.databases_id,
                server_user: params.server_user,
                server_pass: params.server_pass,
                server_port: params.server_port,
                servers_id: params.servers_id,
                db_user: params.db_user,
                db_pass: params.db_pass,
                db_port: params.db_port,
                db_host: params.host,
                db_sid: params.sid,
                port: params.port,
                host: params.host,
                sid: params.sid
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);

                if (result.total > 0) {
                    var i = 0;
                    while (i < result.total) {
                        var conf = result.records[i];
                        conf.owner = this.owner;
                        conf.freq = frequence;
                        conf.width = result.total <= 6 ? '100%' : '25%';
                        conf.classs = (i % 2 == 0) ? 'blue' : 'gray';
                        conf.label = conf.event;
                        conf.body_height = '100px';
                        conf.freq = params.freq * 10;

                        conf.databases_id = params.databases_id;
                        conf.lineColor = params.lineColor;
                        conf.server_user = params.server_user;
                        conf.server_pass = params.server_pass;
                        conf.server_port = params.server_port;
                        conf.servers_id = params.servers_id;
                        conf.db_user = params.db_user;
                        conf.db_pass = params.db_pass;
                        conf.db_port = params.port;
                        conf.db_host = params.host;
                        conf.db_sid = params.sid;
                        conf.port = params.port;
                        conf.host = params.host;
                        conf.sid = params.sid;

                        var panel = new Toc.EventHistoChart(conf);
                        this.add(panel);
                        this.panels[i] = panel;
                        //panel.buildItems(db);
                        this.doLayout();
                        //panel.start();
                        i++;
                    }

                    this.start();
                }
            },
            scope: this
        });
    },

    stop: function () {
        if (this.panels) {
            var i = 0;
            while (i < this.panels.length) {
                var panel = this.panels[i];
                //console.debug(panel);
                if (panel && panel.stop) {
                    panel.stop();
                }
                i++;
            }
        }

        this.started = false;
    },

    start: function () {
        if (this.panels) {
            var i = 0;
            while (i < this.panels.length) {
                var panel = this.panels[i];
                //console.debug(panel);
                if (panel && panel.start) {
                    panel.start();
                }
                i++;
            }
        }

        this.started = true;
    }
});

Toc.SqlGrid = function (config) {
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.border = true;
    config.loadMask = true;
    config.title = 'SQL';
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (comp) {
            comp.activated = true;
        },
        show: function (comp) {
        },
        enable: function (panel) {
        },
        deactivate: function (panel) {
        },
        destroy: function (panel) {
        },
        disable: function (panel) {
        },
        remove: function (container, panel) {
        },
        removed: function (container, panel) {
        },
        render: function (comp) {
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.ORACLE_URL,
        baseParams: {
            module: 'databases',
            action: 'list_sql',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid,
            user_id: config.user_id || '',
            tbs: config.tbs
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'sql_id'
        }, [
            {name: 'buffer_gets', type: 'float'},
            {name: 'sorts', type: 'float'},
            {name: 'elapsed_time', type: 'float'},
            {name: 'cpu_time', type: 'float'},
            {name: 'version_count', type: 'float'},
            {name: 'parse_calls', type: 'float'},
            {name: 'rows_exec', type: 'float'},
            {name: 'rows_processed', type: 'float'},
            {name: 'gets_exec', type: 'float'},
            {name: 'reads_exec', type: 'float'},
            {name: 'disk_reads', type: 'float'},
            {name: 'executions', type: 'float'},
            'sql_text',
            'sql_id'
        ]),
        listeners: {
            load: function (store, records, opt) {
                this.lblInfos.setText(store.data.length + ' Requetes ...');
            },
            scope: that
        },
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-tune-record', qtip: 'Recommendations'},
            {iconCls: 'icon-gather-record', qtip: 'Rapport SQL'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'sql', header: 'SQL', dataIndex: 'sql_text', width: 25, renderer: render, align: 'left'},
        { id: 'buffer_gets', header: 'Buffer Gets', dataIndex: 'buffer_gets', width: 8, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'gets_exec', header: 'Gets/Exec', dataIndex: 'gets_exec', width: 6, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'executions', header: 'Execs', dataIndex: 'executions', width: 4, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'disk_reads', header: 'Disk Reads', dataIndex: 'disk_reads', width: 7, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'reads_exec', header: 'Reads/Exec', dataIndex: 'reads_exec', width: 8, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'rows_processed', header: 'Rows', dataIndex: 'rows_processed', width: 5, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'rows_exec', header: 'Rows/Exec', dataIndex: 'rows_exec', width: 4, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'parse_calls', header: 'Parses', dataIndex: 'parse_calls', width: 5, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'version_count', header: 'Versions', dataIndex: 'version_count', width: 5, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'cpu_time', header: 'CPU Time', dataIndex: 'cpu_time', width: 6, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'elapsed_time', header: 'Elapsed Time', dataIndex: 'elapsed_time', width: 8, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'sorts', header: 'Sorts', dataIndex: 'sorts', width: 4, align: 'center',sortable : true,xtype : 'numbercolumn'},
        config.rowActions
    ]);

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.lblInfos = new Ext.form.Label({
        width: 200,
        text: 'O Requetes',
        autoShow: true
    });

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '-',
        config.lblInfos,
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

    Toc.SqlGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlGrid, Ext.grid.GridPanel, {
    onSearch: function () {
        var filter;

        filter = this.txtSearch.getValue() || null;
        var store = this.getStore();
        store.baseParams['search'] = filter;

        store.reload();
    },
    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-gather-record':
                Toc.SqlReport(record, this);
                break;

            case 'icon-tune-record':
                Toc.SqlTune(record, this);
                break;
        }
    }
});

Toc.PgaStatGrid = function (config) {
    config = config || {};
    config.loadMask = false;
    config.border = true;
    config.title = 'PGA Stats';
    config.autoHeight = true;
    config.viewConfig = {
        emptyText: TocLanguage.gridNoRecords, forceFit: true
    };

    config.listeners = {
        activate: function (panel) {
            panel.getStore().load();
        }
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'pga_stats',
            db_port: config.port,
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'name',
            'val'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        {id: 'name', header: 'Component', dataIndex: 'name', width: 80},
        {header: 'Valeur', dataIndex: 'val', width: 20}
    ]);
    //config.autoExpandColumn = 'component';

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: function (panel) {
                panel.getStore().load();
            },
            scope: this
        }
    ];

    Toc.PgaStatGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PgaStatGrid, Ext.grid.GridPanel, {
});

Toc.MemoryAllocationChart = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    //config.width = this.params.width;
    config.header = true;
    config.layout = 'fit';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        activate: function (panel) {
            //console.debug('MemoryAllocationChart activate');
            panel.refreshData();
        },
        render: function (comp) {
            //console.debug('MemoryAllocationChart render');
            var configChart = function () {
                thisObj.data = [];

                var chart = AmCharts.makeChart(thisObj.body.id, {
                    "type": "serial",
                    "theme": "light",
                    marginTop: 5,
                    marginBottom: 5,
                    marginLeft: 5,
                    marginRight: 5,
                    "legend": {
                        "useGraphSettings": true,
                        //"markerType": "circle",
                        //marginLeft: 10,
                        //marginRight: 10,
                        //spacing: 100,
                        //valueWidth: 50,
                        "position": "right"
                    },
                    "dataProvider": [],
                    "synchronizeGrid": true,
                    //dataDateFormat: "DD/MM/YY HH:NN:SS",
                    "valueAxes": [
                        {
                            "id": "v_small",
                            "axisColor": "#FF6600",
                            "axisThickness": 2,
                            "axisAlpha": 1,
                            "position": "left"
                        }
                    ],
                    "graphs": [
                        {
                            "valueAxis": "v_small",
                            "bullet": "none",
                            bulletSize: 0,
                            "bulletBorderThickness": 0,
                            balloonText: 'SGA : ' + "[[value]]",
                            "title": "SGA",
                            type: "line",
                            "valueField": "sga",
                            "fillAlphas": 0.6
                        },
                        {
                            "valueAxis": "v_small",
                            "bullet": "none",
                            bulletSize: 0,
                            "bulletBorderThickness": 1,
                            balloonText: 'PGA : ' + "[[value]]",
                            "title": "PGA",
                            type: "line",
                            "valueField": "pga",
                            "fillAlphas": 0.6
                        }
                    ],
                    "chartScrollbar": {},
                    "chartCursor": {
                        "cursorPosition": "mouse",
                        scrollbarHeight: 10
                    },
                    "categoryField": "time",
                    "categoryAxis": {
                        //"parseDates": true,
                        //minPeriod: "mm",
                        "axisColor": "#DADADA",
                        "minorGridEnabled": true
                    },
                    "export": {
                        "enabled": false,
                        "position": "bottom-right"
                    }
                });

                thisObj.chart = chart;

                chart.validateData();

                thisObj.getEl().unmask();
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            //console.debug('MemoryAllocationChart resize');
            //Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tools = [
    ];

    Toc.MemoryAllocationChart.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MemoryAllocationChart, Ext.Panel, {

    refreshData: function () {
        var chart = this.chart;
        this.getEl().mask('Chargement donnees');

        Ext.Ajax.request({
            url: Toc.CONF.ORACLE_URL,
            params: {
                module: 'databases',
                action: 'list_memhisto',
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.port,
                db_host: this.host,
                db_sid: this.sid,
                databases_id: this.databases_id
            },
            callback: function (options, success, response) {

                var valueAxis = chart.valueAxes[0];

                if (success) {
                    var json = Ext.decode(response.responseText);

                    this.data = json ? json.records : [];

                    if (valueAxis) {
                        valueAxis.titleColor = "green";
                        valueAxis.title = "";
                        valueAxis.labelsEnabled = true;
                        if (!json) {
                            valueAxis.titleColor = "red";
                            valueAxis.title = "No Data";
                        }
                        else {
                            chart.dataProvider = this.data;

                            if (json.comment || !json.records) {
                                valueAxis.titleColor = "red";
                                valueAxis.title = json.comment;
                                valueAxis.labelsEnabled = false;
                            }
                        }

                        chart.validateNow();
                    }

                    if (json && json.records) {
                        if (json.records.length > 0) {
                            chart.validateData();
                        }
                    }
                }
                else {
                    if (valueAxis) {
                        valueAxis.labelsEnabled = false;
                        valueAxis.titleColor = "red";
                        valueAxis.title = "Time out";

                        chart.validateNow();
                    }
                }

                this.getEl().unmask();
            },
            scope: this
        });
    },

    onRefresh: function () {
        this.refreshData();
    }
});

Toc.SgaAllocationChart = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    //config.region = 'center';
    //config.width = this.params.width;
    config.header = true;
    //config.layout = 'fit';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        activate: function (panel) {
            panel.refreshData();
        },
        render: function (comp) {
            var configChart = function () {
                thisObj.data = [];

                var chart = AmCharts.makeChart(thisObj.body.id, {
                    "type": "serial",
                    "theme": "light",
                    marginTop: 5,
                    marginBottom: 5,
                    marginLeft: 5,
                    marginRight: 5,
                    "legend": {
                        "useGraphSettings": true,
                        "position": "right"
                    },
                    "dataProvider": [],
                    "synchronizeGrid": true,
                    "valueAxes": [
                        {
                            "id": "v_small",
                            "axisColor": "#FF6600",
                            "axisThickness": 2,
                            "axisAlpha": 1,
                            "position": "left"
                        }
                    ],
                    "graphs": [
                        {
                            "valueAxis": "v_small",
                            "bullet": "none",
                            bulletSize: 0,
                            "bulletBorderThickness": 0,
                            balloonText: 'Buffer Cache : ' + "[[value]]",
                            "title": "Buffer Cache",
                            type: "line",
                            "valueField": "other",
                            "fillAlphas": 0.6
                        },
                        {
                            "valueAxis": "v_small",
                            "bullet": "none",
                            bulletSize: 0,
                            "bulletBorderThickness": 1,
                            balloonText: 'Java Pool : ' + "[[value]]",
                            "title": "Java Pool",
                            type: "line",
                            "valueField": "java",
                            "fillAlphas": 0.6
                        },
                        {
                            "valueAxis": "v_small",
                            "bullet": "none",
                            bulletSize: 0,
                            "bulletBorderThickness": 1,
                            balloonText: 'Streams Pool : ' + "[[value]]",
                            "title": "Streams Pool",
                            type: "line",
                            "valueField": "streams",
                            "fillAlphas": 0.6
                        },
                        {
                            "valueAxis": "v_small",
                            "bullet": "none",
                            bulletSize: 0,
                            "bulletBorderThickness": 1,
                            balloonText: 'Shared Pool : ' + "[[value]]",
                            "title": "Shared Pool",
                            type: "line",
                            "valueField": "shared",
                            "fillAlphas": 0.6
                        },
                        {
                            "valueAxis": "v_small",
                            "bullet": "none",
                            bulletSize: 0,
                            "bulletBorderThickness": 1,
                            balloonText: 'Large Pool : ' + "[[value]]",
                            "title": "Large Pool",
                            type: "line",
                            "valueField": "large",
                            "fillAlphas": 0.6
                        }
                    ],
                    "chartScrollbar": {},
                    "chartCursor": {
                        "cursorPosition": "mouse",
                        scrollbarHeight: 10
                    },
                    "categoryField": "time",
                    "categoryAxis": {
                        //"parseDates": true,
                        //minPeriod: "mm",
                        "axisColor": "#DADADA",
                        "minorGridEnabled": true
                    },
                    "export": {
                        "enabled": false,
                        "position": "bottom-right"
                    }
                });

                thisObj.chart = chart;

                chart.validateData();

                thisObj.getEl().unmask();
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
    //        Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tools = [
    ];

    Toc.SgaAllocationChart.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SgaAllocationChart, Ext.Panel, {

    refreshData: function () {
        var chart = this.chart;
        this.getEl().mask('Chargement donnees');

        Ext.Ajax.request({
            url: Toc.CONF.ORACLE_URL,
            params: {
                module: 'databases',
                action: 'list_sgahisto',
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.port,
                db_host: this.host,
                db_sid: this.sid,
                databases_id: this.databases_id
            },
            callback: function (options, success, response) {

                var valueAxis = chart.valueAxes[0];

                if (success) {
                    var json = Ext.decode(response.responseText);

                    this.data = json ? json.records : [];

                    if (valueAxis) {
                        valueAxis.titleColor = "green";
                        valueAxis.title = "";
                        valueAxis.labelsEnabled = true;
                        if (!json) {
                            valueAxis.titleColor = "red";
                            valueAxis.title = "No Data";
                        }
                        else {
                            chart.dataProvider = this.data;

                            if (json.comment || !json.records) {
                                valueAxis.titleColor = "red";
                                valueAxis.title = json.comment;
                                valueAxis.labelsEnabled = false;
                            }
                        }

                        chart.validateNow();
                    }

                    if (json && json.records) {
                        if (json.records.length > 0) {
                            chart.validateData();
                        }
                    }
                }
                else {
                    if (valueAxis) {
                        valueAxis.labelsEnabled = false;
                        valueAxis.titleColor = "red";
                        valueAxis.title = "Time out";

                        chart.validateNow();
                    }
                }

                this.getEl().unmask();
            },
            scope: this
        });
    },

    onRefresh: function () {
        this.refreshData();
    }
});

Toc.MemoryResizePanel = function (config) {
    config = config || {};
    config.loadMask = true;
    config.border = true;
    config.title = 'Memory resize OPS';
    //config.autoHeight = true;
    config.viewConfig = {
        emptyText: TocLanguage.gridNoRecords, forceFit: true
    };

    config.listeners = {
        activate: function (panel) {
            panel.getStore().load();
        }
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'memory_resize',
            db_port: config.port,
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'icon',
            'component',
            'parameter',
            'status',
            'initial_size',
            'target_size',
            'final_size',
            'start_time',
            'end_time',
            'duree'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        {header: '', dataIndex: 'icon', width: 5},
        {id: 'component', header: 'Component', dataIndex: 'component', width: 25},
        {header: 'Parameter', dataIndex: 'parameter', width: 10},
        {header: 'Status', align: 'center', dataIndex: 'status', width: 5},
        {header: 'Initial Size', align: 'center', dataIndex: 'initial_size', width: 10},
        {header: 'Target Size', align: 'center', dataIndex: 'target_size', width: 10},
        {header: 'Final Size', align: 'center', dataIndex: 'final_size', width: 10},
        {header: 'Start Time', align: 'center', dataIndex: 'start_time', width: 10},
        {header: 'End Time', align: 'center', dataIndex: 'end_time', width: 10},
        {header: 'Duree', align: 'center', dataIndex: 'duree', width: 5}
    ]);

    Toc.MemoryResizePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MemoryResizePanel, Ext.grid.GridPanel, {
});

Toc.LatchPanel = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.started = false;
    config.region = 'center';
    config.loadMask = true;
    config.width = '100%';
    //config.border = true;
    //config.autoHeight = true;
    config.title = 'Latch';
    //config.columnLines = false;
    //config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (panel) {
            panel.getStore().load();
        }
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_latch',
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
            id: 'ind'
        }, [
            'ind',
            'name',
            'gets',
            'wait_time',
            'misses',
            'immediate_gets',
            'immediate_misses',
            'misses_ratio'
        ]),
        listeners: {
        },
        autoLoad: true
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'ind', header: 'Latch #', dataIndex: 'ind', width: 5, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'name', header: 'Name', dataIndex: 'name', width: 30, align: 'left'},
        { id: 'gets', header: 'Gets', dataIndex: 'gets', width: 10, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'misses', header: 'Misses', dataIndex: 'misses', width: 10, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'immediate_gets', header: 'Immediate Gets', dataIndex: 'immediate_gets', width: 10, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'immediate_misses', header: 'Immediate Misses', dataIndex: 'immediate_misses', width: 10, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'wait_time', header: 'Wait Time', dataIndex: 'wait_time', width: 10, align: 'center',sortable : true,xtype : 'numbercolumn'},
        { id: 'misses_ratio', header: '% Misses', dataIndex: 'misses_ratio', width: 15, renderer: Toc.content.ContentManager.renderPct, align: 'center',sortable : true,xtype : 'numbercolumn'}
    ]);

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: function (panel) {
                that.getStore().reload();
            },
            scope: this
        }
    ];

    Toc.LatchPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.LatchPanel, Ext.grid.GridPanel, {
});

Toc.SqlAreaUsageGrid = function (config) {
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.loadMask = true;
    config.title = 'SqlArea Usage';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            that.getStore().load();
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'sqlarea_usage',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'user_id'
        }, [
            {name: 'sharable', type: 'float'},
            {name: 'persistent', type: 'float'},
            {name: 'runtime', type: 'float'},
            {name: 'areas', type: 'float'},
            {name: 'mem_sum', type: 'float'},
            'user_id',
            'username'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'username', header: 'Username', dataIndex: 'username', sortable: true, align: 'left', width: 40},
        { id: 'sharable', header: 'Sharable', dataIndex: 'sharable', sortable: true, align: 'center', width: 10,xtype : 'numbercolumn'},
        { id: 'persistent', header: 'Persistent', dataIndex: 'persistent', align: 'center', width: 10, sortable: true,xtype : 'numbercolumn'},
        { id: 'runtime', header: 'Runtime', dataIndex: 'runtime', align: 'center', width: 10, sortable: true,xtype : 'numbercolumn'},
        { id: 'areas', header: 'Areas', dataIndex: 'areas', align: 'center', width: 10, sortable: true,xtype : 'numbercolumn'},
        { id: 'mem_sum', header: 'Total', dataIndex: 'mem_sum', align: 'center', width: 10, sortable: true,xtype : 'numbercolumn'}
    ]);

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    var thisObj = this;

    this.addEvents({'selectchange': true});
    Toc.SqlAreaUsageGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlAreaUsageGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        this.getStore().load();
    }
});

Toc.MemoryAllocationPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.border = true;
    config.layout = 'column';
    config.title = params.label;
    config.started = false;
    //config.header = false;
    //config.autoHeight = true;
    config.listeners = {
        show: function (comp) {
            //console.log('pnlSqlAreaUsage show');
        },
        added: function (index) {
        },
        enable: function (comp) {
            //console.log('pnlSqlAreaUsage enable');
        },
        render: function (comp) {
            //console.log('pnlSqlAreaUsage render');
        },
        afterrender: function (comp) {
            //console.log('afterrender');
            //console.log('pnlSqlAreaUsage afterrender');
        },
        activate: function (panel) {
            //console.log('pnlSqlAreaUsage activate');
            if (!that.activated) {
                that.buildItems(that.params);
            }

            that.activated = true;
        },
        deactivate: function (panel) {
            //console.log('ash deactivate');
            //panel.removeAll(true);
        },
        destroy: function (panel) {
            //console.log('destroy');
        },
        disable: function (panel) {
            //console.log('disable');
        },
        remove: function (container, panel) {
            //console.log('remove');
        },
        removed: function (container, panel) {
            //console.log('removed');
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            if(that.pnlAlloc && that.pnlSgaAlloc)
            {
                that.pnlAlloc.setHeight(that.getInnerHeight()/2);
                that.doLayout(true, true);
                that.pnlSgaAlloc.setHeight(that.getInnerHeight()/2);
                that.doLayout(true, true);
            }
        },
        scope: this
    };

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: function (panel) {
                that.pnlAlloc.refreshData();
                that.pnlSgaAlloc.refreshData();
            },
            scope: this
        }
    ];

    Toc.MemoryAllocationPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MemoryAllocationPanel, Ext.Panel, {
    buildItems: function (params) {
        var that = this;

        var node = params.node;

        that.pnlAlloc = new Toc.MemoryAllocationChart({columnWidth: 1,label: 'Memory Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
        that.pnlSgaAlloc = new Toc.SgaAllocationChart({columnWidth: 1,label: 'SGA Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
        //that.pnlSgaResize = new Toc.SgaResizePanel({width : '100%',sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
        //that.pnlMemoryResize = new Toc.MemoryResizePanel({width : '100%',sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

        that.add(that.pnlAlloc);
        that.pnlAlloc.setHeight(that.getInnerHeight()/2);
        that.doLayout(true, true);

        that.add(that.pnlSgaAlloc);
        that.pnlSgaAlloc.setHeight(that.getInnerHeight()/2);
        that.doLayout(true, true);

        that.pnlAlloc.refreshData();
        that.pnlSgaAlloc.refreshData();
    }
});