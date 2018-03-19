Toc.SnapshotsGrid = function (config) {
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.loadMask = true;
    config.header = false;
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.listeners = {
        'rowclick': this.onRowClick
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_snapshots'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'snap_id'
        }, [
            'snap_id',
            'begin_interval_time',
            'end_interval_time',
            'startup_time'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'snap_id', header: 'Snap_id', dataIndex: 'snap_id', width: 60, align: 'center'},
        { id: 'begin_interval_time', header: 'begin_interval_time', dataIndex: 'begin_interval_time', width: 205, align: 'center'},
        { id: 'end_interval_time', header: 'end_interval_time', dataIndex: 'end_interval_time', width: 205, align: 'center'},
        { id: 'startup_time', header: 'startup_time', dataIndex: 'startup_time', width: 205, align: 'center'}
    ]);
    config.stripeRows = true;

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.tbar = [
        {
            text: '',
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
        beforePageText: TocLanguage.beforePageText,
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

    this.addEvents({'selectchange': true});
    Toc.SnapshotsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SnapshotsGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        this.getStore().reload();
    },

    refreshGrid: function (categoriesId) {
        var permissions = this.mainPanel.getCategoryPermissions();
        var store = this.getStore();

        store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;
        store.baseParams['categories_id'] = categoriesId;
        this.categoriesId = categoriesId;
        store.reload();
    },

    onSearch: function () {
        var categoriesId = this.cboCategories.getValue() || null;
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['current_category_id'] = categoriesId;
        store.baseParams['search'] = filter;
        store.reload();
    },

    onClick: function (e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);

        if (row !== false) {
            this.parent.snap_id = this.getStore().getAt(row).get('snap_id');
            this.parent.time = this.getStore().getAt(row).get('begin_interval_time');
        }
    },
    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
    }
});

Toc.SnaphotsDialog = function (config) {

    config = config || {};

    config.id = 'databases-snapshots-dialog-win';
    config.title = 'Snaphots browser';
    config.layout = 'fit';
    config.width = 690;
    config.height = 400;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.getContentPanel();

    config.buttons = [
        {
            text: 'Selectionner',
            handler: function () {
                this.selectSnap();
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

    Toc.SnaphotsDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SnaphotsDialog, Ext.Window, {
    show: function (start_or_end, start_field, end_field, params) {
        this.config = params;
        this.start_or_end = start_or_end;
        this.start_field = start_field;
        this.end_field = end_field;

        Toc.SnaphotsDialog.superclass.show.call(this);
        this.listSnaps(this.config);
    },
    getContentPanel: function () {
        this.pnlSnapshots = new Toc.SnapshotsGrid({parent: this});

        return this.pnlSnapshots;
    },
    listSnaps: function (config) {
        config.module = 'databases';
        config.action = 'list_snapshots';
        this.pnlSnapshots.getStore().baseParams = config;
        this.pnlSnapshots.getStore().load();
    },
    selectSnap: function (capture, value) {
        if (this.snap_id) {
            switch (this.start_or_end) {
                case 'start':
                    this.start_field.setValue(this.config.capture == 'snap_id' ? this.snap_id : this.time);
                    this.start_field.snap_id = this.snap_id;
                    break;
                case 'end':
                    this.end_field.setValue(this.config.capture == 'snap_id' ? this.snap_id : this.time);
                    break;
            }
            this.close();
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, 'Veuillez selectionner une capture !!!');
        }
    }
});

Toc.SnapshotBrowser = function (config) {
    config = config || {};

    config.deferredRender = false;
    config.header = false;
    config.border = false;
    config.items = this.getDataPanel(config);

    Toc.SnapshotBrowser.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SnapshotBrowser, Ext.Panel, {
    getDataPanel: function (config) {
        var that = this;

        var showDialog = function (start_or_end, start_field, end_field, start_id) {
            var dlg = new Toc.SnaphotsDialog();
            var params = {
                capture: config.capture,
                db_user: config.db_user,
                db_pass: config.db_pass,
                db_port: config.db_port,
                db_host: config.db_host,
                db_sid: config.db_sid
            };

            params.start_id = start_id;

            dlg.show(start_or_end, start_field, end_field, params);
        };

        var txtStartId = '';
        var txtEndId = '';

        switch (config.capture) {
            case 'snap_id':
                txtStartId = new Ext.form.NumberField({fieldLabel: 'Start Snap', allowBlank: false, name: 'PARAM_START_SNAP', width: 405});
                txtEndId = new Ext.form.NumberField({fieldLabel: 'End Snap', allowBlank: false, name: 'PARAM_END_SNAP', width: 405});
                break;

            case 'time':
                txtStartId = new Ext.form.TextField({fieldLabel: 'Start Snap', allowBlank: false, name: 'PARAM_START_SNAP_TIME', width: 405});
                txtEndId = new Ext.form.TextField({fieldLabel: 'End Snap', allowBlank: false, name: 'PARAM_END_SNAP_TIME', width: 405});
                break;

            default:
                Ext.Msg.alert(TocLanguage.msgErrTitle, 'Vous devez selectionner une capture de base !!!');
                return null;
                break;
        }

        var btnStartId = new Ext.Button(
            {
                text: '...',
                handler: function () {
                    showDialog('start', txtStartId, txtEndId);
                },
                scope: this
            });

        var btnEndId = new Ext.Button(
            {
                text: '...',
                handler: function () {
                    var start_id = txtStartId.snap_id;
                    if (start_id) {
                        showDialog('end', txtStartId, txtEndId, start_id);
                    }
                    else {
                        Ext.Msg.alert(TocLanguage.msgErrTitle, 'Vous devez selectionner une capture de base !!!');
                    }
                },
                scope: this
            });

        return {
            xtype: 'fieldset',
            autoHeight: true,
            baseCls: 'x-fieldset1',
            border: 'false',
            hideLabel: true,
            hideBorders: true,
            layout: 'form',
            items: [
                {
                    layout: 'column',
                    items: [
                        {
                            //columnWidth: .90,
                            layout: 'form',
                            style: {
                                'border-color': 'white'
                            },
                            border: false,
                            items: [txtStartId]
                        },
                        {
                            //columnWidth: .1,
                            layout: 'form',
                            border: false,
                            items: [btnStartId]
                        }
                    ]
                },
                {
                    layout: 'column',
                    items: [
                        {
                            //columnWidth: .90,
                            layout: 'form',
                            border: false,
                            items: [txtEndId]
                        },
                        {
                            //columnWidth: .1,
                            layout: 'form',
                            border: false,
                            items: [btnEndId]
                        }
                    ]
                }
            ]
        };
    }
});

Toc.AshPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.border = true;
    config.activated = false;
    config.layout = 'column';
    config.cls = params.classs;
    config.title = params.label;
    config.started = false;
    config.listeners = {
        show: function (comp) {
        },
        added: function (index) {
        },
        enable: function (comp) {
        },
        render: function (comp) {
            //console.log('ash render');
        },
        afterrender: function (comp) {
            //console.log('afterrender');
        },
        activate: function (panel) {
            //console.log('ash activate');
            if (!that.activated) {
                //that.buildItems(that.params);
            }

            that.buildItems(that.params);

            that.dbperf.setHeight(that.getInnerHeight()/2);
            that.pnlEvents.setHeight(that.getInnerHeight()/2);
            that.pnlSessions.setHeight(that.getInnerHeight()/2);
            that.doLayout(true,true);
            that.activated = true;

            panel.start();
        },
        deactivate: function (panel) {
            panel.stop();
            that.activated = false;
        },
        destroy: function (panel) {
            panel.stop();
            that.activated = false;
        },
        disable: function (panel) {
            panel.stop();
            that.activated = false;
        },
        remove: function (container, panel) {
            //console.log('remove');
            that.stop();
            that.activated = false;
        },
        removed: function (container, panel) {
            //console.log('removed');
            that.stop();
            that.activated = false;
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            if(Component.dbperf && Component.pnlEvents)
            {
                Component.dbperf.setHeight(Component.getInnerHeight()/2);
                Component.pnlEvents.setHeight(Component.getInnerHeight()/2);
                Component.pnlSessions.setHeight(Component.getInnerHeight()/2);
                Component.doLayout(true, true);
            }
        },
        scope: this
    };

    config.tbar = [
        {
            text: '',
            iconCls: this.started ? 'stop' : 'play',
            handler: this.started ? that.stop : that.start,
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
        }
    ];

    Toc.AshPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AshPanel, Ext.Panel, {
    zoom : function(start,end){
        this.dbperf.stop();
        var store = this.pnlEvents.getStore();
        store.baseParams['start_sample_id'] = start;
        store.baseParams['end_sample_id'] = end;
        store.reload();

        store = this.pnlSessions.getStore();
        store.baseParams['start_sample_id'] = start;
        store.baseParams['end_sample_id'] = end;
        store.reload();
    },
    buildItems: function (params) {
        //console.log('buildItems');
        //console.debug(this);
        this.removeAll(true);
        params.owner = this.owner;

        var conf = {
            //width: '100%',
            columnWidth : 1,
            action: 'ash_waits',
            label: 'ASH',
            freq: params.freq,
            mainPanel: this,
            sample_time : '',
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
            sid: params.sid,
            showLegend: true
        };

        this.dbperf = new Toc.TopEventsPanelCharts(conf);

        var node = params.node;

        this.pnlEvents = new Toc.TopEventAsh({columnWidth : 0.3, label: 'Top Events', title: 'Top Events', databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner,mainPanel: this});

        this.pnlSessions = new Toc.ActiveSessionsGrid({columnWidth : 0.7,label: 'Active Sessions', databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner,mainPanel: this});

        this.add(this.dbperf);
        this.add(this.pnlEvents);
        this.add(this.pnlSessions);
        this.doLayout(true, true);

        var that = this;

        var mem = {
            width: '20%',
            label: 'Swap',
            body_height: '75px',
            //body_height: this.getInnerHeight()/5 + 'px',
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

        //this.mem_usage = new Toc.MemCharts(mem);
        //this.add(this.mem_usage);

        var cpu = {
            width: '40%',
            label: 'CPU',
            body_height: '75px',
            //body_height: this.getInnerHeight()/5 + 'px',
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

        //this.cpu_usage = new Toc.CpuCharts(cpu);
        //this.add(this.cpu_usage);

        var disk = {
            width: '40%',
            label: 'Disks (%)',
            body_height: '75px',
            //body_height: this.getInnerHeight()/5 + 'px',
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

        //this.disk_usage = new Toc.DiskCharts(disk);
        //this.add(this.disk_usage);

        var net = {
            width: '40%',
            label: 'Net (MB/s)',
            body_height: '75px',
            //body_height: this.getInnerHeight()/5 + 'px',
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

        //this.net_usage = new Toc.NetCharts(net);
        //this.add(this.net_usage);

        params.freq = params.freq * 5;
        params.width = '30%';
        params.body_height = '75px';
    },

    start: function () {
        //console.log('starting ...');
        this.dbperf.start();
        this.topToolbar.items.items[0].setHandler(this.stop, this);
        this.topToolbar.items.items[0].setIconClass('stop');
        this.started = true;
    },

    stop: function () {
        //console.log('stopping ...');
        this.dbperf.stop();
        this.topToolbar.items.items[0].setHandler(this.start, this);
        this.topToolbar.items.items[0].setIconClass('play');
        this.started = false;
    },
    onAsh: function () {
        this.getEl().mask('Creation du job ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'oracle_perf',
                action: 'run_ash',
                db_user: this.params.db_user,
                db_pass: this.params.db_pass,
                db_port: this.params.db_port,
                db_host: this.params.host,
                startValue: this.dbperf.startValue,
                endValue: this.dbperf.endValue,
                db_sid: this.params.sid
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
                module: 'oracle_perf',
                action: 'run_addm',
                db_user: this.params.db_user,
                db_pass: this.params.db_pass,
                db_port: this.params.db_port,
                db_host: this.params.host,
                db_sid: this.params.sid
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
            db_user: this.params.db_user,
            db_pass: this.params.db_pass,
            db_port: this.params.db_port,
            db_host: this.params.host,
            db_sid: this.params.sid
        };

        var dialog = new Toc.AWRDialog(params);
        dialog.show();
    }
});

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
            panel.onStop();
        },
        destroy: function (panel) {
            panel.onStop();
        },
        disable: function (panel) {
            panel.onStop();
        },
        remove: function (container, panel) {
            that.onStop();
        },
        removed: function (container, panel) {
            that.onStop();
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

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'username', header: 'DB User', dataIndex: 'username', width: 8},
        { id: 'osuser', header: 'OS User', dataIndex: 'osuser', width: 7},
        { id: 'machine', header: 'Machine', dataIndex: 'machine', width: 10},
        { id: 'sql', header: 'SQL', dataIndex: 'sql_text', width: 25, renderer: Toc.content.ContentManager.renderNewLine},
        { id: 'client_info', header: 'Info', dataIndex: 'client_info', width: 20, renderer: Toc.content.ContentManager.renderNewLine},
        { id: 'state', header: 'Status', dataIndex: 'state', width: 8, renderer: Toc.content.ContentManager.renderNewLine},
        { id: 'event', header: 'Event', dataIndex: 'event', width: 10, renderer: Toc.content.ContentManager.renderNewLine},
        { id: 'seconds_in_wait', header: 'Duree (S)', dataIndex: 'seconds_in_wait', width: 4, align: 'center'},
        { id: 'pct', header: '%', dataIndex: 'pct', width: 8, renderer: Toc.content.ContentManager.renderOsProgress, align: 'center'},
        config.rowActions
    ]);

    var thisObj = this;

    if (!config.inAshPanel) {
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
        if(scope.isVisible())
        {
            if (scope && scope.started) {
                if (scope.reqs == 0) {
                    var store = this.getStore();
                    scope.reqs++;
                    store.load();
                }
            }
        }
        else
        {
            console.log('session grid not visible ...');
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
    onStart: function (start_sample,end_sample) {
        this.started = true;
        this.count = 0;
        this.reqs = 0;
        var store = this.getStore();
        store.baseParams['start_sample'] = start_sample || null;
        store.baseParams['end_sample'] = end_sample || null;
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
    this.params = config;
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.width = this.params.width || '25%';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
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
        deactivate: function (panel) {
            panel.stop();
        },
        destroy: function (panel) {
            panel.stop();
        },
        disable: function (panel) {
            panel.stop();
        },
        remove: function (container, panel) {
            //console.log('remove');
            that.stop();
        },
        removed: function (container, panel) {
            //console.log('removed');
            that.stop();
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

    Toc.TopEventsPanelCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TopEventsPanelCharts, Ext.Panel, {
    onZoomed: function (chart)  {
        //console.log('onZoomed');
        chart.chart.panel.startValue = chart.startValue;
        chart.chart.panel.endValue = chart.endValue;
        console.debug(chart);

        if(!chart.chart.first_zoom)
        {
            console.log('first zoom');
            chart.chart.first_zoom = true;
        }
        else
        {
            console.log('another zoom');
            if(!chart.chart.panel.started)
            {
                var startSample = chart.chart.dataProvider[chart.startIndex].sample_id;
                var endSample = chart.chart.dataProvider[chart.endIndex].sample_id;

                if(startSample && endSample)
                {
                    if(chart.startIndex == 0)
                    {
                        //console.log('plage trop grande ...');
                        console.log('auto zoom');
                        //Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task_id !!!");
                    }
                    else
                    {
                        console.log('manual zoom');
                        if(chart.chart.panel.mainPanel && chart.chart.panel.mainPanel.zoom)
                        {
                            chart.chart.panel.mainPanel.zoom(startSample,endSample);
                        }
                        else
                        {
                            console.log('no mainPanel');
                        }
                    }
                }
                else
                {
                    console.log('no samples available');
                }
            }
            else
            {
                console.log('stop first ...');
            }
        }

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
                                        //chart.dataProvider.push(data);
                                        for (var i = 0; i < json.records.length; i++) {
                                            //console.debug(json.records[i]);
                                            chart.dataProvider.push(json.records[i]);
                                        }
                                    }

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

                            console.log('validateNow ...');
                            chart.validateNow();

                            scope.sample_time = json.sample_time;

                            console.log('refreshGrid ...');
                            console.log('sample_time ...' + scope.sample_time);

                            if(scope.mainPanel)
                            {
                                if(scope.sample_id)
                                {
                                    console.log('scope sample_id ...' + scope.sample_id);
                                    scope.mainPanel.zoom(scope.sample_id,json.sample_id);
                                }
                                else
                                {
                                    scope.mainPanel.zoom(json.sample_id - 15,json.sample_id);
                                }
                            }

                            console.log('json sample_id ...' + json.sample_id);
                            scope.sample_id = json.sample_id;

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
                module: 'oracle_perf',
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
                module: 'oracle_perf',
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
            {iconCls: 'icon-detail-record', qtip: 'Details'},
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

            case 'icon-detail-record':
                //this.onSqlReport(record);
                Toc.SqlDetail(record, this);
                break;
        }
    }
});

Toc.TopEventAsh = function (config) {
    this.params = config;
    this.owner = config.owner;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    //config.width = this.params.width || '50%';
    //config.autoHeight = true;
    config.hideHeaders = true;
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.ORACLE_URL,
        baseParams: {
            module: 'databases',
            action: 'list_topeventash',
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
            id: 'event'
        }, [
            'event',
            'pct'
        ]),
        listeners: {
            load: function (store, records, opt) {
                that.finished = true;
                if(that.mainPanel)
                {
                    if(that.isVisible())
                    {
                        if(that.mainPanel.started)
                        {
                            console.log('start ...');
                            if(that.mainPanel.pnlSessions.finished)
                            {
                                that.mainPanel.dbperf.start();
                            }
                            else
                            {
                                console.log('pnlSessions not finished ...');
                            }
                        }
                        else
                        {
                            console.log('mainPanel not started ...');
                        }
                    }
                    else
                    {
                        console.log('session grid not visible ...');
                    }
                }
                else
                {
                    console.log('no mainPanel ...');
                }
            },
            beforeload: function (store, opt) {
                that.finished = false;
                return true;
            },
            scope: that
        },
        autoLoad: false
    });

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'event', header: 'Event', dataIndex: 'event', width: 70, renderer: render,align : 'left'},
        { id: 'pct', align: 'center', dataIndex: 'pct', sortable: true, width: 30}
    ]);

    var thisObj = this;

    Toc.TopEventAsh.superclass.constructor.call(this, config);
    //this.getView().scrollOffset = 0;
};

Ext.extend(Toc.TopEventAsh, Ext.grid.GridPanel, {
});

Toc.ActiveSessionsGrid = function (config) {
    var that = this;
    config = config || {};
    config.started = false;
    //config.region = 'center';
    config.loadMask = true;
    config.border = true;
    config.activated = false;
    config.title = 'Active Sessions';
    config.count = 0;
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
            action: 'list_active_sessions',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'sql_text',
            'sample_id',
            'event',
            'sql_id',
            'machine',
            'username'
        ]),
        listeners: {
            load: function (store, records, opt) {
                that.finished = true;
                that.setTitle(that.label + ' (' + store.totalLength + ')');

                if(that.mainPanel)
                {
                    if(that.isVisible())
                    {
                        if(that.mainPanel.started)
                        {
                            console.log('start ...');
                            if(that.mainPanel.pnlEvents.finished)
                            {
                                that.mainPanel.dbperf.start();
                            }
                            else
                            {
                                console.log('pnlEvents not finished ...');
                            }
                        }
                        else
                        {
                            console.log('mainPanel not started ...');
                        }
                    }
                    else
                    {
                        console.log('session grid not visible ...');
                    }
                }
                else
                {
                    console.log('no mainPanel ...');
                }
            },
            beforeload: function (store, opt) {
                that.finished = false;
                return true;
            },
            scope: that
        },
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-detail-record', qtip: 'Details'},
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
        { id: 'username', header: 'DB User', dataIndex: 'username', width: 15},
        { id: 'machine', header: 'Machine', dataIndex: 'machine', width: 15},
        { id: 'sql', header: 'SQL', dataIndex: 'sql_text', width: 49, renderer: render},
        { id: 'event', header: 'Event', dataIndex: 'event', width: 20, renderer: render},
        config.rowActions
    ]);

    config.bbar = new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: config.ds,
        steps: Toc.CONF.GRID_STEPS,
        beforePageText: TocLanguage.beforePageText,
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

    Toc.ActiveSessionsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ActiveSessionsGrid, Ext.grid.GridPanel, {
    onRowAction: function (grid, record, action, row, col) {
    switch (action) {
        case 'icon-detail-record':
            //this.onSqlReport(record);
            Toc.SqlDetail(record, this);
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

Toc.SqlDialog = function (config) {

    config = config || {};

    config.id = 'databases-snapshots-dialog-win';
    config.title = 'SQL Infos';
    config.layout = 'fit';
    config.width = 800;
    config.height = 500;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.getContentPanel(config);

    this.addEvents({'saveSuccess': true});

    Toc.SqlDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlDialog, Ext.Window, {
    show: function (scope) {

        Toc.SqlDialog.superclass.show.call(this);
        //this.tab.activate(this.pnlQuery);
        this.pnlQuery.getQuery(scope);
    },
    getContentPanel: function (config) {
        this.pnlQuery = new Toc.SqlQueryPanel({sql_id : config.sql_id,parent: this});
        this.pnlOpt = new Toc.SqlOptimizerParameterGrid({sql_id : config.sql_id,parent: this,db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid});

        this.pnlTabStats = new Toc.SqlTableStatsGrid({sql_id : config.sql_id,parent: this,db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid});

        this.pnlIndStats = new Toc.SqlIndexStatsGrid({sql_id : config.sql_id,parent: this,db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid});

        this.pnlSqlCboRecos = new Toc.SqlCboRecos({sql_id : config.sql_id,parent: this,db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid});

        var tab = new Ext.TabPanel({
            activeTab: 0,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: true,
            items : [this.pnlQuery,this.pnlOpt,this.pnlTabStats,this.pnlIndStats,this.pnlSqlCboRecos]
        });

        return tab;
    }
});

Toc.SqlQueryPanel = function (config) {
    var that = this;
    config = config || {};

    config.layout = 'fit';
    config.border = false;
    config.title = 'Query';
    config.items = this.getContentPanel();
    config.listeners = {
        activate: function (panel) {
            console.log('active sql infos ...');
        },
        scope: this
    };

    Toc.SqlQueryPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlQueryPanel, Ext.Panel, {
    getContentPanel: function () {
        this.txtArea = new Ext.form.TextArea();

        return this.txtArea;
    },
    getQuery : function(mainPanel){
        var that = this;
        mainPanel.getEl().mask('Chargement Query ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'oracle_perf',
                action: 'get_sqltext',
                db_user: mainPanel.db_user,
                db_pass: mainPanel.db_pass,
                db_host: mainPanel.host,
                db_sid: mainPanel.sid,
                sql_id: this.sql_id
            },
            callback: function (options, success, response) {
                mainPanel.getEl().unmask();
                var result = Ext.decode(response.responseText);
                if(result.success)
                {
                    that.txtArea.setValue(result.sql_text);
                }
            },
            scope: this
        });
    }
});

Toc.SqlOptimizerParameterGrid = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Optimizer Env';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.getStore().load();
                this.activated = true;
            }
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_sqloptenv',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid,
            sql_id: config.sql_id
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'id'
        }, [
            'id',
            'isdefault',
            'name',
            'value'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'isdefault', header: 'Default', dataIndex: 'isdefault', sortable: true, align: 'left', width: 10},
        { id: 'name', header: 'Name', dataIndex: 'name', sortable: true, align: 'left', width: 40},
        { id: 'value', header: 'Value', dataIndex: 'value', sortable: false, align: 'left', width: 50}
    ]);
    config.stripeRows = true;

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    Toc.SqlOptimizerParameterGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlOptimizerParameterGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        this.refreshGrid();
    },

    refreshGrid: function () {
        var store = this.getStore();
        store.reload();
    }
});

Toc.SqlTableStatsGrid = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Tables Stats';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.getStore().load();
                this.activated = true;
            }
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_sqltablestats',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid,
            sql_id: config.sql_id
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'table_name'
        }, [
            'table_name',
            'owner',
            'num_rows',
            'blocks',
            'empty_blocks',
            'avg_free_space',
            'chain_cnt',
            'avg_row_len',
            'sample_size',
            'last_analyzed',
            'stale_stats'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'owner', header: 'Owner', dataIndex: 'owner', sortable: true, align: 'center', width: 10},
        { id: 'table_name', header: 'Name', dataIndex: 'table_name', sortable: true, align: 'center', width: 10},
        { id: 'num_rows', header: 'Num Rows', dataIndex: 'num_rows', sortable: false, align: 'center', width: 10},
        { id: 'blocks', header: 'Blocks', dataIndex: 'blocks', sortable: false, align: 'center', width: 10},
        { id: 'empty_blocks', header: 'Empty Blocks', dataIndex: 'empty_blocks', sortable: false, align: 'center', width: 5},
        { id: 'avg_free_space', header: 'AVG Free Space', dataIndex: 'avg_free_space', sortable: false, align: 'center', width: 10},
        { id: 'chain_cnt', header: 'Chain Cnt', dataIndex: 'chain_cnt', sortable: false, align: 'center', width: 10},
        { id: 'avg_row_len', header: 'AVG Row Len', dataIndex: 'avg_row_len', sortable: false, align: 'center', width: 10},
        { id: 'sample_size', header: 'Sample Size', dataIndex: 'sample_size', sortable: false, align: 'center', width: 10},
        { id: 'last_analyzed', header: 'Last Analyzed', dataIndex: 'last_analyzed', sortable: false, align: 'center', width: 10},
        { id: 'stale_stats', header: 'Stale ?', dataIndex: 'stale_stats', sortable: false, align: 'center', width: 5}
    ]);
    config.stripeRows = true;

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    Toc.SqlTableStatsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlTableStatsGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    }
});

Toc.SqlIndexStatsGrid = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Index Stats';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.getStore().load();
                this.activated = true;
            }
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_sqlindexestats',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid,
            sql_id: config.sql_id
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index_name'
        }, [
            'index_name',
            'owner',
            'index_type',
            'uniqueness',
            'compression',
            'pct_free',
            'blevel',
            'clustering_factor',
            'last_analyzed'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'owner', header: 'Owner', dataIndex: 'owner', sortable: true, align: 'center', width: 10},
        { id: 'table_name', header: 'Name', dataIndex: 'table_name', sortable: true, align: 'center', width: 10},
        { id: 'num_rows', header: 'Num Rows', dataIndex: 'num_rows', sortable: false, align: 'center', width: 10},
        { id: 'blocks', header: 'Blocks', dataIndex: 'blocks', sortable: false, align: 'center', width: 10},
        { id: 'empty_blocks', header: 'Empty Blocks', dataIndex: 'empty_blocks', sortable: false, align: 'center', width: 5},
        { id: 'avg_free_space', header: 'AVG Free Space', dataIndex: 'avg_free_space', sortable: false, align: 'center', width: 10},
        { id: 'chain_cnt', header: 'Chain Cnt', dataIndex: 'chain_cnt', sortable: false, align: 'center', width: 10},
        { id: 'avg_row_len', header: 'AVG Row Len', dataIndex: 'avg_row_len', sortable: false, align: 'center', width: 10},
        { id: 'sample_size', header: 'Sample Size', dataIndex: 'sample_size', sortable: false, align: 'center', width: 10},
        { id: 'last_analyzed', header: 'Last Analyzed', dataIndex: 'last_analyzed', sortable: false, align: 'center', width: 10},
        { id: 'stale_stats', header: 'Stale ?', dataIndex: 'stale_stats', sortable: false, align: 'center', width: 5}
    ]);
    config.stripeRows = true;

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    Toc.SqlIndexStatsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlIndexStatsGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    }
});

Toc.SqlCboRecos = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'CBO Recos';
    config.border = true;
    config.hideHeader = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.getStore().load();
                this.activated = true;
            }
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'cbo_recos',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid,
            sql_id: config.sql_id
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'reco'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'reco', header: 'Reco', dataIndex: 'reco', sortable: true, align: 'left', width: 100,renderer : Toc.content.ContentManager.renderNewLine}
    ]);
    config.stripeRows = true;

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    Toc.SqlCboRecos.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SqlCboRecos, Ext.grid.GridPanel, {
    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    }
});

Toc.PxSessionsGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    config.title = 'Px Sessions';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_pxsessions',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            scope: config.scope,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'degree',
            'req_degree',
            'no_of_processes',
            'sql_text',
            'index',
            'username'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'username', header: 'User', dataIndex: 'username', sortable: false, align: 'left', width: 20},
        { id: 'sql_text', header: 'sql_text', dataIndex: 'sql_text', sortable: false, align: 'left', width: 50},
        { id: 'degree', header: 'degree', dataIndex: 'degree', sortable: false, align: 'left', width: 10},
        { id: 'req_degree', header: 'req_degree', dataIndex: 'req_degree', sortable: false, align: 'left', width: 10},
        { id: 'no_of_processes', header: '# Procs', dataIndex: 'no_of_processes', sortable: false, align: 'left', width: 10}
    ]);

    Toc.PxSessionsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PxSessionsGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    }
});

Toc.PxPanel = function (params) {
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
        activate: function (panel) {
            //console.log('pnlSqlAreaUsage activate');
            if (!that.activated) {
                that.buildItems(that.params);
            }

            that.activated = true;

            that.pnlBufferAdvice.setHeight(that.getInnerHeight()/2);
            that.pnlPxParameters.setHeight(that.getInnerHeight()/2);
            that.pnlPxSessions.setHeight(that.getInnerHeight()/2);
            that.pnlPxStats.setHeight(that.getInnerHeight()/2);
            that.pnlPxSessStats.setHeight(that.getInnerHeight()/2);
            //that.pnlPxProcessesGauge.setHeight(that.getInnerHeight()/2);
            that.doLayout(true, true);

            that.refresh();
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            if(that.pnlBufferAdvice && that.pnlPxSessions)
            {
                that.pnlBufferAdvice.setHeight(that.getInnerHeight()/2);
                that.pnlPxSessions.setHeight(that.getInnerHeight()/2);
                that.pnlPxParameters.setHeight(that.getInnerHeight()/2);
              //  that.pnlPxProcessesGauge.setHeight(that.getInnerHeight()/2);
                that.pnlPxStats.setHeight(that.getInnerHeight()/2);
                that.pnlPxSessStats.setHeight(that.getInnerHeight()/2);

                that.doLayout(true, true);
            }
        },
        scope: this
    };

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.refresh,
            scope: this
        }
    ];

    Toc.PxPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PxPanel, Ext.Panel, {
    refresh : function(){
        this.pnlBufferAdvice.onRefresh();
        this.pnlPxSessions.onRefresh();
        this.pnlPxParameters.onRefresh();
        this.pnlPxStats.onRefresh();
        this.pnlPxSessStats.onRefresh();
        //this.pnlPxProcessesGauge.onRefresh();
    },
    buildItems: function (params) {
        //console.log('pnlSqlAreaUsage buildItems');
        var that = this;

        var node = params.node;

        that.pnlPxParameters = new Toc.ParameterGrid({columnWidth : 0.4,scope : 'px',sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
        that.pnlPxSessions = new Toc.PxSessionsGrid({columnWidth : 0.6,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
        that.pnlBufferAdvice = new Toc.PxBufferAdviceGrid({columnWidth : 0.3,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
        that.pnlPxStats = new Toc.PxStats({columnWidth : 0.3,label: "Stats", databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
        that.pnlPxSessStats = new Toc.PxSessStats({columnWidth : 0.4,label: "Stats", databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
        //that.pnlPxProcessesGauge = new Toc.PxProcessesGauge({columnWidth : 0.3,label: "PX Usage", databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

        that.add(that.pnlPxParameters);
        that.add(that.pnlPxSessions);
        that.add(that.pnlBufferAdvice);
        that.add(that.pnlPxStats);
        that.add(that.pnlPxSessStats);
        //that.add(that.pnlPxProcessesGauge);
        that.doLayout(true, true);
    }
});

Toc.PxStats = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Stats';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_pxstats',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            scope: config.scope,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'statistic'
        }, [
            'value',
            'statistic'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'statistic', header: 'statistic', dataIndex: 'statistic', sortable: false, align: 'left', width: 65},
        { id: 'value', header: 'Value', dataIndex: 'value', sortable: false, align: 'left', width: 35}
    ]);

    Toc.PxStats.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PxStats, Ext.grid.GridPanel, {
    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    }
});

Toc.PxSessStats = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Stats';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_pxsessstats',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            scope: config.scope,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'statistic'
        }, [
            'last_query',
            'session_total',
            'statistic'
        ]),
        autoLoad: false
    });

    config.cm = new Ext.grid.ColumnModel([
        { id: 'statistic', header: 'statistic', dataIndex: 'statistic', sortable: false, align: 'left', width: 80},
        { id: 'last_query', header: 'Last Query', dataIndex: 'last_query', sortable: false, align: 'center', width: 10},
        { id: 'session_total', header: 'Session Total', dataIndex: 'session_total', sortable: false, align: 'center', width: 10}
    ]);

    Toc.PxSessStats.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PxSessStats, Ext.grid.GridPanel, {
    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    }
});

Toc.SegmentStatsChart = function (config) {
    this.params = config;
    var that = this;
    config = config || {};
    config.count = 0;
    config.reqs = 0;
    config.try = 0;

    var thisObj = this;

    config.listeners = {
        render: function (comp) {

            var configChart = function () {
                thisObj.data = [];

                var chart;
                chart = new AmCharts.AmSerialChart();
                chart.width = '100%';
                chart.gridAboveGraphs = true;
                chart.startDuration = 1;

                chart.dataProvider = thisObj.data;

                chart.marginBottom = 1;
                chart.marginLeft = 1;
                chart.marginRight = 1;
                chart.marginTop = 1;

                chart.valueAxes = [ {
                    "gridColor": "#FFFFFF",
                    "gridAlpha": 0.2,
                    "dashLength": 0
                } ];

                chart.categoryField = "last_analyzed";
                chart.categoryAxis = {
                    "gridPosition": "start",
                    "gridAlpha": 0,
                    "tickPosition": "start",
                    "tickLength": 20
                };

                // GRAPHS
                var graph = new AmCharts.AmGraph();
                graph.type = "column";
                graph.balloonText = "[[category]]: <b>[[value]]</b>";
                graph.lineAlpha = 0.2;
                graph.fillAlphas = 0.8;
                graph.valueField = "nbre";
                chart.addGraph(graph);

                chart.write(comp.body.id);
                chart.panel = comp;
                comp.chart = chart;
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        activate: function (panel) {
            panel.onRefresh();
        },
        deactivate: function (panel) {
            panel.stop();
        },
        destroy: function (panel) {
            panel.stop();
        },
        disable: function (panel) {
            panel.stop();
        },
        remove: function (container, panel) {
            //console.log('remove');
            that.stop();
        },
        removed: function (container, panel) {
            //console.log('removed');
            that.stop();
        },
        scope: this
    };

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    Toc.SegmentStatsChart.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SegmentStatsChart, Ext.Panel, {
    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                scope.reqs++;

                scope.transactionId = Ext.Ajax.request({
                    url: Toc.CONF.ORACLE_URL,
                    params: {
                        action: 'segmentstats_chart',
                        db_user: this.db_user,
                        db_pass: this.db_pass,
                        db_port: this.port,
                        db_host: this.host,
                        db_sid: this.sid,
                        segment_type: this.segment_type,
                        databases_id: this.databases_id
                    },
                    callback: function (options, success, response) {
                        scope.reqs--;
                        scope.try = 0;
                        scope.setTitle(scope.label);

                        if (scope.chart) {
                            var chart = scope.chart;

                            if (success) {
                                var json = Ext.decode(response.responseText);
                                var data = null;

                                data = json ? json.records : [];

                                chart.dataProvider = data;
                            }
                            else {
                                scope.setTitle(response.responseText);
                            }

                            chart.validateNow();
                            chart.validateData();
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
                }, scope.freq || 50000);
                scope.count++;
                scope.interval = interval;
            }
        }
    },

    onRefresh: function () {
        this.start();
        this.stop();
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