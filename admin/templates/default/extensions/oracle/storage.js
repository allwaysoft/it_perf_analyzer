Toc.TopTbsPanel = function (config) {
    this.params = config;
    this.owner = config.owner;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = false;
    config.width = this.params.width || '50%';
    //config.autoHeight = true;
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
    config.title = 'TBS';
    config.label = 'TBS';
    config.height = 110;
    config.hideHeaders = true;
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.ORACLE_URL,
        baseParams: {
            module: 'databases',
            action: 'list_toptbs',
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
            id: 'tbs'
        }, [
            'tbs',
            'pct_used',
            'qtip',
            'rest'
        ]),
        listeners: {
            exception: function (misc) {
                that.reqs--;
                that.try = 0;
                that.setTitle(that.label + (' error !!! ') + that.reqs);
            },
            load: function (store, records, opt) {
                that.reqs--;
                that.try = 0;
                that.setTitle(that.label);
            },
            beforeload: function (store, opt) {
                if (that.count == 0) {
                    var interval = setInterval(function () {
                        that.refreshData(that);
                    }, that.freq || 5000);
                    that.count++;
                    that.interval = interval;
                }

                return that.started;
            }, scope: that
        },
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-xxx-record', qtipIndex: 'qtip'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'name', header: 'TBS', dataIndex: 'tbs', width: 70, renderer: render},
        { id: 'pct_used', align: 'center', dataIndex: 'rest', renderer: Toc.content.ContentManager.renderFsProgress, sortable: true, width: 30},
        config.rowActions
    ]);

    var thisObj = this;

    config.tools = [
        /*{
         id: 'browse',
         qtip: 'Browse',
         handler: function (event, toolEl, panel) {

         }
         },*/
        {
            id: 'refresh',
            qtip: 'Refresh',
            handler: function (event, toolEl, panel) {
                thisObj.stop();
                thisObj.start();
            }
        }
    ];

    Toc.TopTbsPanel.superclass.constructor.call(this, config);
    this.getView().scrollOffset = 0;
};

Ext.extend(Toc.TopTbsPanel, Ext.grid.GridPanel, {
    refreshData: function (scope) {
        if (scope && scope.started) {
            scope.try++;
            scope.setTitle(scope.title + '.');

            if (scope.reqs == 0) {
                var store = this.getStore();
                scope.reqs++;
                store.load();
            }

            if (scope.try > 10) {
                scope.setTitle(scope.label);
                scope.try = 0;
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
            owner: this.owner,
            typ: this.typ,
            tbs: record.get("tbs")
        };

        //console.debug(params);
        var dlg = new Toc.TbsBrowser(params);
        dlg.setTitle(this.label + ' : ' + record.get("tbs"));

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
            case 'icon-xxx-record':
                this.onEdit(record);
                break;
        }
    },
    start: function () {
        this.started = true;
        this.count = 0;
        this.try = 0;
        this.reqs = 0;
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
    }
});

Toc.DatafileDialog = function (params) {
    config = {};

    config.title = 'Datafile ' + params.file_name;
    config.layout = 'fit';
    config.modal = true;
    config.iconCls = 'icon-tbs-win';
    config.items = this.getContentPanel(params);

    this.addEvents({'saveSuccess': true});

    Toc.DatafileDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatafileDialog, Ext.Window, {

    show: function (config, owner) {
        this.owner = owner || null;
        if (config) {
            this.config = config;
        }

        Toc.DatafileDialog.superclass.show.call(this);

        this.maximize();

        var params = {
            server_user: config.server_user,
            server_pass: config.server_pass,
            server_port: config.server_port,
            host: config.host,
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_host: config.host,
            db_sid: config.db_sid,
            tbs: config.tbs,
            file_id: config.file_id
        };
    },

    getContentPanel: function (params) {
        this.map = new Toc.TbsMap({file_id: params.file_id, tbs: params.tbs, sid: params.db_sid, host: params.db_host, db_port: params.db_port, db_pass: params.db_pass, db_user: params.db_user, owner: params.owner, mainPanel: this});

        this.tabdatabases = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
                this.map
            ]
        });

        return this.tabdatabases;
    }
});

Toc.RebuildIndexField = function (params) {
    config = {};
    config.params = params;
    config.autoScroll = true;
    config.title = 'INDEX ==> ' + params.index_name + ' (Tablespace actuel ===> ' + params.tablespace_name + ')';
    config.border = true;
    config.autoHeight = true;
    config.layout = 'column';
    config.listeners = {
        add: function (container, component, index) {
            //console.log('add');
        },
        added: function (component, ownerCt, index) {
            //console.log('added');
        },
        afterlayout: function (container, layout) {
            //console.log('afterlayout');
        },
        beforeadd: function (container, component, index) {
            //  console.log('beforeadd');
        },
        beforeshow: function (component) {
            //    console.log('beforeshow');
        },
        show: function (component) {
            //      console.log('show');
        },
        render: function (component) {
            //        console.log('render');
        },
        scope: this
    };

    Toc.RebuildIndexField.superclass.constructor.call(this, config);
    this.getDataPanel(params);
};

Ext.extend(Toc.RebuildIndexField, Ext.form.FieldSet, {
    getDataPanel: function (params) {
        this.pnlData = this;

        this.indexCombo = new Toc.content.ContentManager.getTbsCombo(params);
        this.add(this.indexCombo);

        this.chkLogging = new Ext.form.Checkbox({
            boxLabel: 'Logging',
            checked: true
        });
        this.add(this.chkLogging);

        this.chkParallel = new Ext.form.Checkbox({
            boxLabel: 'Parallel',
            checked: true
        });
        this.add(this.chkParallel);

        this.chkCompress = new Ext.form.Checkbox({
            boxLabel: 'Compress',
            checked: params.cols > 1,
            enabled: params.cols > 1
        });
        this.add(this.chkCompress);

        this.chkMonitoring = new Ext.form.Checkbox({
            boxLabel: 'Montoring',
            checked: true
        });
        this.add(this.chkMonitoring);

        this.doLayout(true, true);
    },
    setTbs: function (tbs) {
        if (this.indexCombo && tbs) {
            this.indexCombo.setValue(tbs);
        }
    },
    getDdl: function () {
        if (!this.params.owner) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible de determiner le proprietaire de l'index " + this.params.index_name + " !!!");
            return null;
        }

        var tbs = this.indexCombo.getValue();
        var ddl = this.params.isPart == 1 ? 'alter index ' + this.params.owner + '.' + this.params.index_name + ' rebuild partition ' + this.params.part + ' TABLESPACE ' + tbs + ' ' : 'alter index ' + this.params.owner + '.' + this.params.index_name + ' rebuild TABLESPACE ' + tbs + ' ';
        if (this.chkLogging.checked) {
            ddl = ddl + ' LOGGING ';
        }
        else {
            ddl = ddl + ' NOLOGGING ';
        }

        if (this.chkParallel.checked) {
            ddl = ddl + ' PARALLEL ';
        }
        else {
            ddl = ddl + ' NOPARALLEL ';
        }

        if (this.chkCompress.checked) {
            ddl = ddl + ' COMPRESS ';
        }
        else {
            ddl = ddl + ' NOCOMPRESS ';
        }

        ddl = ddl + ';';

        return ddl;
    }
});

Toc.RebuildTableField = function (params) {
    config = {};
    config.params = params;
    config.autoScroll = true;
    switch (params.segment_type) {
        case 'TABLE PARTITION':
            config.title = 'PARTITION ==> ' + params.segment_name + '.' + params.partition_name + ' (Tablespace actuel ===> ' + params.tablespace_name + ')';
            break;
        case 'TABLE SUBPARTITION':
            config.title = 'SUBPARTITION ==> ' + params.segment_name + '.' + params.partition_name + ' (Tablespace actuel ===> ' + params.tablespace_name + ')';
            break;
        case 'TABLE':
            config.title = 'TABLE ==> ' + params.segment_name + ' (Tablespace actuel ===> ' + params.tablespace_name + ')';
            break;
        default:
            break;
    }

    config.border = true;
    config.autoHeight = true;
    config.layout = 'column';

    Toc.RebuildTableField.superclass.constructor.call(this, config);
    this.getDataPanel(params);
};

Ext.extend(Toc.RebuildTableField, Ext.form.FieldSet, {
    getDataPanel: function (params) {
        this.pnlData = this;

        this.tableCombo = new Toc.content.ContentManager.getTbsCombo(params);
        this.add(this.tableCombo);

        if (this.params.segment_type == 'TABLE') {
            this.chkLogging = new Ext.form.Checkbox({
                boxLabel: 'Logging',
                checked: true
            });
            this.add(this.chkLogging);
        }

        this.chkParallel = new Ext.form.Checkbox({
            boxLabel: 'Parallel',
            checked: true
        });
        this.add(this.chkParallel);

        this.chkCompress = new Ext.form.Checkbox({
            boxLabel: 'Compress',
            checked: true
        });
        this.add(this.chkCompress);

        if (this.params.segment_type == 'TABLE') {
            this.chkMonitoring = new Ext.form.Checkbox({
                boxLabel: 'Montoring',
                checked: true
            });
            this.add(this.chkMonitoring);
        }

        this.doLayout(false, true);
    },
    getDdl: function () {
        if (!this.params.owner) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible de determiner le proprietaire de la table " + this.params.table_name + " !!!");
            return null;
        }

        var tbs = this.tableCombo.getValue();
        var ddl = 'alter table ' + this.params.owner + '.' + this.params.segment_name + ' move ';
        if (this.params.segment_type == 'TABLE PARTITION') {
            ddl = ddl + ' PARTITION ' + this.params.partition_name + ' ';
        }

        if (this.params.segment_type == 'TABLE SUBPARTITION') {
            ddl = ddl + ' SUBPARTITION ' + this.params.partition_name + ' ';
        }

        ddl = ddl + ' TABLESPACE ' + tbs + ' ';

        if (this.params.segment_type == 'TABLE') {
            if (this.chkLogging.checked) {
                ddl = ddl + ' LOGGING ';
            }
            else {
                ddl = ddl + ' NOLOGGING ';
            }
        }

        if (this.chkParallel.checked) {
            ddl = ddl + ' PARALLEL ';
        }
        else {
            ddl = ddl + ' NOPARALLEL ';
        }

        if (this.chkCompress.checked) {
            ddl = ddl + ' COMPRESS ';
        }
        else {
            ddl = ddl + ' NOCOMPRESS ';
        }

        return ddl;
    }
});

Toc.RebuildLobSegmentField = function (params) {
    config = {};
    config.params = params;
    config.autoScroll = true;
    config.title = 'LOBSEGMENT ==> ' + params.table_name + '.' + params.segment_name + ' (Tablespace actuel ===> ' + params.tablespace_name + ')';
    config.border = true;
    config.autoHeight = true;
    config.layout = 'column';

    Toc.RebuildLobSegmentField.superclass.constructor.call(this, config);
    this.getDataPanel(params);
};

Ext.extend(Toc.RebuildLobSegmentField, Ext.form.FieldSet, {
    getDataPanel: function (params) {
        this.pnlData = this;

        this.tableCombo = new Toc.content.ContentManager.getTbsCombo(params);
        this.add(this.tableCombo);

        this.chkDeduplicate = new Ext.form.Checkbox({
            boxLabel: 'Deduplicate',
            checked: true
        });
        this.add(this.chkDeduplicate);

        this.chkCompress = new Ext.form.Checkbox({
            boxLabel: 'Compress',
            checked: true
        });
        this.add(this.chkCompress);

        this.doLayout(false, true);
    },
    getDdl: function () {
        if (!this.params.owner) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible de determiner le proprietaire de la table " + this.params.table_name + " !!!");
            return null;
        }

        var tbs = this.tableCombo.getValue();
        var ddl = 'alter table ' + this.params.owner + '.' + this.params.table_name + ' MOVE LOB (' + this.params.column_name + ') STORE AS SECUREFILE  (';
        if (this.chkDeduplicate.checked) {
            ddl = ddl + ' DEDUPLICATE ';
        }

        if (this.chkCompress.checked) {
            ddl = ddl + ' COMPRESS HIGH ';
        }

        ddl = ddl + ' TABLESPACE ' + tbs + ')';

        return ddl;
    }
});

Toc.MoveSegmentDialog = function (params) {
    config = {};
    config.segment_type = params.segment_type;

    config.params = params;
    config.layout = 'fit';
    config.width = 800;
    config.height = 100;
    config.modal = true;
    config.iconCls = 'icon-resize-win';
    //config.items = this.getContentPanel(params);
    config.items = this.buildForm(params);

    config.buttons = [
        {
            text: 'Deplacer',
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

    config.listeners = {
        close: function (panel) {
            //panel.jobGrid.stop();
        },
        scope: this
    };

    params.panel = this;
    params.autoLoad = true;
    config.tbsCombo = new Toc.content.ContentManager.getTbsCombo(params);

    var thisObj = this;

    config.tbar = [
        config.tbsCombo
    ];

    config.tbsCombo.on('select', function (combo, record, ind) {
        var tbs = combo.getValue();

        var i = 0;

        while (i < thisObj.indexes.length) {
            var index = thisObj.indexes[i];
            index.setTbs(tbs);
            i++;
        }
    });

    this.addEvents({'finished': true});
    Toc.MoveSegmentDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MoveSegmentDialog, Ext.Window, {

    show: function (params, owner) {
        this.owner = owner || null;
        if (params) {
            this.config = params;
        }

        Toc.MoveSegmentDialog.superclass.show.call(this);

        this.pnlData = new Ext.Panel({
            layout: 'form',
            autoHeight: true,
            autoScroll: true
        });

        var h = 100;

        if (this.segment_type == 'TABLE') {
            var table = {
                db_user: this.params.db_user,
                db_pass: this.params.db_pass,
                db_port: this.params.db_port,
                db_host: this.params.db_host,
                db_sid: this.params.db_sid,
                panel: this,
                owner: this.params.owner,
                segment_name: this.params.segment_name,
                segment_type: this.params.segment_type,
                tablespace_name: this.params.tbs,
                autoLoad: true
            };

            this.table_field = new Toc.RebuildTableField(table);
            this.frmMoveTable.add(this.table_field);
            this.frmMoveTable.doLayout(false, true);
            h = h + 65;
        }

        if (this.segment_type == 'TABLE PARTITION' || this.segment_type == 'TABLE SUBPARTITION') {
            var part = {
                db_user: this.params.db_user,
                db_pass: this.params.db_pass,
                db_port: this.params.db_port,
                db_host: this.params.db_host,
                db_sid: this.params.db_sid,
                panel: this,
                owner: this.params.owner,
                segment_name: this.params.segment_name,
                partition_name: this.params.partition_name,
                segment_type: this.params.segment_type,
                tablespace_name: this.params.tbs,
                autoLoad: true
            };

            this.table_field = new Toc.RebuildTableField(part);
            this.frmMoveTable.add(this.table_field);
            this.frmMoveTable.doLayout(false, true);
            h = h + 65;
        }

        this.indexes = [];

        if (params.indexes) {
            var i = 0;
            var isPart = 0;
            var part = '';
            if (this.segment_type == 'TABLE PARTITION' || this.segment_type == 'TABLE SUBPARTITION') {
                isPart = 1;
                part = this.params.partition_name;
            }

            //console.log('isPart ==> ' + isPart);

            while (i < params.indexes.length) {
                var index = params.indexes[i];
                index.db_user = this.params.db_user;
                index.db_pass = this.params.db_pass;
                index.db_port = this.params.db_port;
                index.db_host = this.params.db_host;
                index.db_sid = this.params.db_sid;
                index.owner = this.params.owner;
                index.isPart = isPart;
                index.part = part;
                index.panel = this;
                index.autoLoad = true;

                var field = new Toc.RebuildIndexField(index);
                this.frmMoveTable.add(field);
                this.indexes[i] = field;
                if ((h + 65) < 600) {
                    h = h + 65;
                }

                i++;
            }
        }

        this.setHeight(h);
        this.center();
        this.doLayout(true, true);
    },

    getContentPanel: function (params) {
        var dummy_pane = new Ext.Panel();
        dummy_pane.setTitle('dummy');

        this.tabData = new Ext.TabPanel({
            activeTab: 0,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
            ]
        });

        return this.tabData;
    },

    buildForm: function (params) {
        this.frmMoveTable = new Ext.form.FormPanel({
            layout: 'form',
            autoScroll: true,
            id: 'frmMoveTable',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'databases',
                db_user: params.db_user,
                db_pass: params.db_pass,
                db_port: params.db_port,
                db_host: params.db_host,
                db_sid: params.db_sid,
                owner: params.owner
            },
            deferredRender: false,
            //items: [this.getContentPanel(params)]
            items: []
        });

        return this.frmMoveTable;
    },

    submitForm: function () {
        var action = 'move_table';
        var table_script = '';

        if (this.segment_type == 'TABLE') {
            //table_script = 'alter table ' + this.params.segment_name + ' move TABLESPACE ' + this.tableCombo.getValue();
            table_script = this.table_field.getDdl();

            if (!table_script) {
                return;
            }
        }
        else {
            action = 'move_index';
        }

        var indexes_script = '';

        var i = 0;

        while (i < this.indexes.length) {
            var index = this.indexes[i];
            indexes_script = indexes_script + index.getDdl();

            if (!indexes_script) {
                return;
            }

            i++;
        }

        var params = {
            table_script: table_script,
            indexes_script: indexes_script,
            action: action
        };

        this.frmMoveTable.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            params: params,
            success: function (form, action) {
                var result = action.result;

                if (result.success == true) {
                    var params = {
                        db_user: this.params.db_user,
                        db_pass: this.params.db_pass,
                        db_port: this.params.db_port,
                        db_host: this.params.db_host,
                        db_sid: this.params.db_sid,
                        job_name: result.job_name,
                        panel: this,
                        description: 'Deplacement de la Table ' + this.params.segment_name
                    };

                    this.proc_name = result.proc_name;
                    Toc.watchJob(params);
                    //this.fireEvent('saveSuccess', action.result.feedback);
                    //this.close();
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    },

    onRefresh: function () {
        this.getEl().mask('Finalisation job ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'drop_procedure',
                proc_name: this.proc_name,
                db_user: this.params.db_user,
                db_pass: this.params.db_pass,
                db_port: this.params.db_port,
                db_host: this.params.db_host,
                db_sid: this.params.db_sid
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    //this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                    this.close();
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
})
;

Toc.MoveLobSegmentDialog = function (params) {
    config = {};
    config.segment_type = params.segment_type;

    config.params = params;
    config.layout = 'fit';
    config.width = 800;
    config.height = 100;
    config.modal = true;
    config.iconCls = 'icon-resize-win';
    //config.items = this.getContentPanel(params);
    config.items = this.buildForm(params);

    config.buttons = [
        {
            text: 'Deplacer',
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

    config.listeners = {
        close: function (panel) {
            //panel.jobGrid.stop();
        },
        scope: this
    };

    var thisObj = this;

    this.addEvents({'finished': true});
    Toc.MoveSegmentDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MoveLobSegmentDialog, Ext.Window, {

    show: function (params, owner) {
        this.owner = owner || null;
        if (params) {
            this.config = params;
        }

        Toc.MoveLobSegmentDialog.superclass.show.call(this);
        //this.maximize();

        this.pnlData = new Ext.Panel({
            layout: 'form',
            autoHeight: true,
            autoScroll: true
        });

        var h = 100;

        var lobsegment = {
            db_user: this.params.db_user,
            db_pass: this.params.db_pass,
            db_port: this.params.db_port,
            db_host: this.params.db_host,
            db_sid: this.params.db_sid,
            panel: this,
            owner: this.params.owner,
            column_name: this.params.indexes[0].column_name,
            segment_name: this.params.segment_name,
            table_name: this.params.indexes[0].table_name,
            tablespace_name: this.params.tbs,
            autoLoad: true
        };

        this.lob_table = new Toc.RebuildLobSegmentField(lobsegment);
        this.frmMoveTable.add(this.lob_table);
        this.frmMoveTable.doLayout(false, true);
        h = h + 60;

        this.setHeight(h);
        this.center();
        this.doLayout(true, true);
    },

    getContentPanel: function (params) {
        var dummy_pane = new Ext.Panel();
        dummy_pane.setTitle('dummy');

        this.tabData = new Ext.TabPanel({
            activeTab: 0,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
            ]
        });

        return this.tabData;
    },

    buildForm: function (params) {
        this.frmMoveTable = new Ext.form.FormPanel({
            layout: 'form',
            autoScroll: true,
            id: 'frmMoveTable',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'databases',
                db_user: params.db_user,
                db_pass: params.db_pass,
                db_port: params.db_port,
                db_host: params.db_host,
                db_sid: params.db_sid,
                owner: params.owner
            },
            deferredRender: false,
            //items: [this.getContentPanel(params)]
            items: []
        });

        return this.frmMoveTable;
    },

    submitForm: function () {
        var action = 'move_lob';
        var lob_script = this.lob_table.getDdl();

        var params = {
            lob_script: lob_script,
            action: action
        };

        this.frmMoveTable.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            params: params,
            success: function (form, action) {
                var result = action.result;

                if (result.success == true) {
                    var params = {
                        db_user: this.params.db_user,
                        db_pass: this.params.db_pass,
                        db_port: this.params.db_port,
                        db_host: this.params.db_host,
                        db_sid: this.params.db_sid,
                        job_name: result.job_name,
                        panel: this,
                        description: 'Deplacement du LOBSEGMENT ' + this.params.segment_name
                    };

                    this.proc_name = result.proc_name;
                    Toc.watchJob(params);
                    //this.fireEvent('saveSuccess', action.result.feedback);
                    //this.close();
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    },

    onRefresh: function () {
        this.getEl().mask('Finalisation job ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'drop_procedure',
                proc_name: this.proc_name,
                db_user: this.params.db_user,
                db_pass: this.params.db_pass,
                db_port: this.params.db_port,
                db_host: this.params.db_host,
                db_sid: this.params.db_sid
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    //this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                    this.close();
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
})
;

Toc.MoveDatafileDialog = function (params) {
    config = {};

    config.params = params;
    config.layout = 'fit';
    config.width = 400;
    config.height = 180;
    config.modal = true;
    config.iconCls = 'icon-resize-win';
    //config.items = this.getContentPanel(params);
    config.items = this.buildForm(params);

    config.buttons = [
        {
            text: 'Deplacer',
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

    config.listeners = {
        close: function (panel) {
            //panel.jobGrid.stop();
        },
        scope: this
    };

    var thisObj = this;

    this.addEvents({'finished': true});
    Toc.MoveDatafileDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MoveDatafileDialog, Ext.Window, {
    show: function (params, owner) {
        this.owner = owner || null;
        if (params) {
            this.config = params;
        }

        Toc.MoveDatafileDialog.superclass.show.call(this);

        this.pnlData = new Ext.Panel({
            layout: 'form',
            autoHeight: true,
            autoScroll: true
        });

        this.browser = new Toc.DirSelectorField(params);
        this.frmMoveDatafile.add(this.browser);
        this.frmMoveDatafile.doLayout(false, true);

        this.txtFilename = new Ext.form.TextField({
            fieldLabel: 'Nom Fichier ',
            allowBlank: false,
            width: '95%'
        });
        this.frmMoveDatafile.add(this.txtFilename);
        this.frmMoveDatafile.doLayout(false, true);

        this.center();
        this.doLayout(true, true);
    },

    getContentPanel: function (params) {
        var dummy_pane = new Ext.Panel();
        dummy_pane.setTitle('dummy');

        this.tabData = new Ext.TabPanel({
            activeTab: 0,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
            ]
        });

        return this.tabData;
    },

    buildForm: function (params) {
        this.frmMoveDatafile = new Ext.form.FormPanel({
            layout: 'form',
            autoScroll: true,
            id: 'frmMoveTable',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'databases',
                db_user: params.db_user,
                db_pass: params.db_pass,
                db_port: params.db_port,
                db_host: params.db_host,
                db_sid: params.db_sid
            },
            deferredRender: false,
            //items: [this.getContentPanel(params)]
            items: []
        });

        return this.frmMoveDatafile;
    },

    submitForm: function () {
        var dir = this.browser.getValue();
        //console.log(dir);

        if (dir.length < 2) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Vous devez selectionner un repertoire destination");
            return;
        }

        var file = this.txtFilename.getValue();
        //console.log(file);

        if (file.length < 2) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Vous devez specifier un nom de fichier");
            return;
        }

        this.getEl().mask('Creation du job ... Veuillez patienter SVP !!!');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                action: 'setTbstatus',
                tbs: this.params.tablespace_name,
                flag: 'OFFLINE',
                module: 'databases',
                db_user: this.params.db_user,
                db_pass: this.params.db_pass,
                db_port: this.params.db_port,
                db_host: this.params.db_host,
                db_sid: this.params.db_sid
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);
                //console.debug(response);

                if (result.success == true) {
                    var params = {
                        db_user: this.params.db_user,
                        db_pass: this.params.db_pass,
                        db_port: this.params.db_port,
                        db_host: this.params.db_host,
                        db_sid: this.params.db_sid,
                        job_name: result.job_name,
                        proc_name: result.proc_name,
                        panel: this,
                        description: 'Mise hors ligne du Tablespace ' + this.params.tablespace_name
                    };

                    this.proc_name = result.proc_name;
                    var dlg = new Toc.JobDialog(params);
                    dlg.setTitle(params.description);

                    dlg.on('success', function () {
                        Toc.DropProcedure(this);

                        var dlg = new Toc.WatchFileMoveDialog();

                        var src = this.params.file_name;
                        var dest = dir + file;

                        dlg.setTitle('Deplacement du fichier ' + src);
                        dlg.on('saveSuccess', function () {
                            Toc.RenameDatafile(this, src, dest, this.params.tablespace_name);
                        }, this);

                        dlg.show(this.params, this.owner, src, dest);
                    }, this);

                    dlg.show(params);
                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    }
});

Toc.RenameDatafile = function (panel, src, dest, tbs) {
    if (panel) {
        panel.getEl().mask('Renommage du fichier de donnees ' + src);
    }

    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'databases',
            action: 'rename_datafile',
            proc_name: panel.proc_name,
            db_user: panel.params.db_user,
            db_pass: panel.params.db_pass,
            db_port: panel.params.db_port,
            db_host: panel.params.db_host,
            db_sid: panel.params.db_sid,
            src: src,
            dest: dest
        },
        callback: function (options, success, response) {
            if (panel) {
                panel.getEl().unmask();
            }

            var result = Ext.decode(response.responseText);

            if (result.success == true) {
                var pnl = panel;
                pnl.db_user = panel.params.db_user;
                pnl.db_pass = panel.params.db_pass;
                pnl.db_port = panel.params.db_port;
                pnl.host = panel.params.db_host;
                pnl.sid = panel.params.db_sid;

                Toc.setTbsStatus(pnl, 'ONLINE', tbs);
            }
            else
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible de renommer ce fichier de donnees : " + result.feedback);
        },
        scope: this
    });
};

Toc.setTbsStatus = function (panel, flag, tbs) {
    var msg = 'hors ligne';

    switch (flag) {
        case 'ONLINE':
            msg = 'en ligne';
            break;
        case 'OFFLINE':
            msg = 'hors ligne';
            break;
    }

    panel.getEl().mask('Creation du job ... Veuillez patienter SVP !!!');
    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'databases',
            action: 'setTbstatus',
            tbs: tbs,
            flag: flag,
            db_user: panel.db_user,
            db_pass: panel.db_pass,
            db_port: panel.db_port,
            db_host: panel.host,
            db_sid: panel.sid
        },
        callback: function (options, success, response) {
            panel.getEl().unmask();
            var result = Ext.decode(response.responseText);

            if (result.success == true) {
                var params = {
                    db_user: panel.db_user,
                    db_pass: panel.db_pass,
                    db_port: panel.db_port,
                    db_host: panel.host,
                    db_sid: panel.sid,
                    job_name: result.job_name,
                    panel: panel,
                    description: 'Mise ' + msg + ' du Tablespace ' + tbs
                };

                Toc.watchJob(params);
            } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            }
        },
        scope: this
    });
};

Toc.datafilesGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    config.title = 'Datafiles';
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.activated = true;
                this.getStore().load();
            }
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_datafiles',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid,
            tbs: config.tbs
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'file_id'
        }, [
            'file_id',
            'file_name',
            'tablespace_name',
            'status',
            'autoextensible',
            {name: 'size', type: 'int'},
            {name: 'blocks', type: 'int'},
            {name: 'maxsize', type: 'int'},
            {name: 'increment_by', type: 'int'},
            {name: 'maxblocks', type: 'int'},
            {name: 'total_pct_used', type: 'int'},
            {name: 'pct_used', type: 'int'},
            {name: 'frag_idx', type: 'int'},
            {name: 'free_mb', type: 'int'}
        ]),
        autoLoad: false
    });

    renderStatus = function (status) {
        if (status == 'AVAILABLE') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    renderAuto = function (status) {
        if (status == 'YES' || status == 'ON') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
            {iconCls: 'icon-move-record', qtip: 'Deplacer ce fichier de données'},
            {iconCls: 'icon-resize-record', qtip: 'Redimensionner ce fichier de données'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'file_id', header: 'ID', dataIndex: 'file_id', sortable: true, width: 2},
        { id: 'file_name', header: 'Nom', dataIndex: 'file_name', sortable: true, width: 28},
        { id: 'tablespace_name', header: 'Tablespace', dataIndex: 'tablespace_name', sortable: true, width: 10},
        { header: '% Used', align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true, width: 7},
        { header: '% Total', align: 'center', dataIndex: 'total_pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true, width: 7},
        { header: 'Size (MB)', align: 'center', dataIndex: 'size', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 9},
        { header: 'Free (MB)', align: 'center', dataIndex: 'free_mb', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 9},
        { header: 'Max (MB)', align: 'center', dataIndex: 'maxsize', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 9},
        { header: 'Inc (MB)', align: 'center', dataIndex: 'increment_by', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 9},
        { header: 'Auto Ext', align: 'center', dataIndex: 'autoextensible', sortable: true, renderer: renderAuto, width: 5},
        { id: 'status', header: 'Status', align: 'center', dataIndex: 'status', renderer: renderStatus, width: 5},
        config.rowActions
    ]);
    config.autoExpandColumn = 'file_name';
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
        '-',
        {
            text: '',
            iconCls: 'resize',
            handler: this.onBatchReclaimSpace,
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

    Toc.datafilesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.datafilesGrid, Ext.grid.GridPanel, {

    onAdd: function () {

    },

    onEdit: function (record) {
        var params = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            server_typ: this.typ,
            host: this.host,
            db_port: this.port,
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_host: this.host,
            db_sid: this.sid,
            tbs: record.get("tablespace_name"),
            file_id: record.get("file_id")
        };

        var dlg = new Toc.DatafileDialog(params);
        dlg.setTitle(this.host + ' : ' + record.get("file_name"));

        dlg.on('close', function () {
            this.onRefresh();
        }, this);

        dlg.show(params, this.owner);
    },

    onMove: function (record) {
        var params = {
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_port: this.db_port,
            db_host: this.host,
            db_sid: this.sid,
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            server_typ: this.typ,
            file_name: record.get("file_name"),
            tablespace_name: record.get("tablespace_name")
        };

        //console.debug(params);
        var dlg = new Toc.MoveDatafileDialog(params);
        dlg.setTitle("Move datafile " + record.get("file_name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(params, this.owner);
    },

    onRefresh: function () {
        this.getStore().reload();
    },

    onReclaimSpace: function (record) {
        if (record && record.get) {
            var file_id = record.get('file_id');

            if (file_id) {
                Ext.MessageBox.confirm(
                    TocLanguage.msgWarningTitle,
                    'Souhaitez vous vraiment redimensionner ce fichier de données',
                    function (btn) {
                        if (btn == 'yes') {
                            this.getEl().mask('Creation du Job ... veuillez patienter');
                            Ext.Ajax.request({
                                url: Toc.CONF.CONN_URL,
                                params: {
                                    module: 'databases',
                                    action: 'resize_datafile',
                                    file_id: file_id,
                                    db_user: this.db_user,
                                    db_pass: this.db_pass,
                                    db_port: this.db_port,
                                    db_host: this.host,
                                    db_sid: this.sid,
                                    panel: this
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
                                            description: 'Redimensionnement du fichier ' + record.get('file_name')
                                        };

                                        Toc.watchJob(params);
                                    } else {
                                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                                    }
                                },
                                scope: this
                            });
                        }
                    }, this);
            }
            else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Veuillez renseigner l'identifier du fichier de données");
            }
        }
    },

    onBatchReclaimSpace: function () {
        var keys = this.getSelectionModel().selections.keys;

        if (keys.length > 0) {
            var batch = keys.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                'Souhaitez vous vraiment redimensionner ces fichiers de données',
                function (btn) {
                    if (btn == 'yes') {
                        this.getEl().mask('Creation du Job ... veuillez patienter');
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'databases',
                                action: 'resize_datafile',
                                file_id: batch,
                                db_user: this.db_user,
                                db_pass: this.db_pass,
                                db_port: this.db_port,
                                db_host: this.host,
                                db_sid: this.sid,
                                panel: this
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
                                        description: 'Redimensionnement des fichiers ...'
                                    };

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
        } else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    refreshGrid: function (tablespace_name) {
        var store = this.getStore();

        store.baseParams['tbs'] = this.tbs;
        store.load();
    },

    onSearch: function () {
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-move-record':
                this.onMove(record);
                break;

            case 'icon-resize-record':
                this.onReclaimSpace(record);
                break;

            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
    },

    onClick: function (e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var action = false;

        if (row !== false) {
            var btn = e.getTarget(".img-button");

            if (btn) {
                action = btn.className.replace(/img-button btn-/, '').trim();
            }
            else {
                var sel = this.getStore().getAt(row);
            }

            if (action != 'img-button') {
                var file_id = this.getStore().getAt(row).get('file_id');
                var module = 'setDatafilestatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 'ON' : 'OFF';
                        this.onAction(module, file_id, flag);
                        break;
                }
            }
        }
    },

    onAction: function (action, file_id, flag) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: action,
                file_id: file_id,
                flag: flag,
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(file_id).set('autoextensible', flag);
                    store.commitChanges();

                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});

Toc.indexesGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    //config.autoHeight = true;
    config.title = 'Indexes';
    //config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (panel) {
            console.log('indexesGrid activate ...');
            that.schemasCombo.getStore().on('beforeload', function (store, records, options) {
                that.getEl().mask('Chargement Schemas ....');
            });

            that.tbsCombo.getStore().on('beforeload', function (store, records, options) {
                that.getEl().mask('Chargement Tablespaces ....');
            });

            that.schemasCombo.getStore().on('load', function (store, records, options) {
                if (records.length > 1) {
                    that.schema = 'all';
                    that.schemasCombo.setValue(thisObj.schema);
                }
                else {
                    that.schemasCombo.disable();
                    Ext.Msg.alert(TocLanguage.msgErrTitle, "Probleme chargement Schemas !!!");
                }

                that.getEl().unmask();
            });

            that.tbsCombo.getStore().on('load', function (store, records, options) {
                if (records.length > 1) {
                    that.tbs = 'all';
                    that.tbsCombo.setValue(thisObj.tbs);
                }
                else {
                    that.schemasCombo.disable();
                    Ext.Msg.alert(TocLanguage.msgErrTitle, "Probleme chargement tablespaces !!!");
                }

                that.getEl().unmask();
            });

            that.schemasCombo.on('select', function (combo, record, index) {
                var schema = record.data.username;
                that.schema = schema;
            });

            that.tbsCombo.on('select', function (combo, record, index) {
                var tbs = record.data.tablespace_name;
                panel.tbs = tbs;
            });

            that.tbsCombo.getStore().load();
            that.schemasCombo.getStore().load();

            if (!this.activated) {
                this.activated = true;
                //this.getStore().load();
            }
        },
        show: function (panel) {

        },
        'rowclick': this.onRowClick,
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_indexes',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            //limit: 100,
            db_sid: config.sid,
            tbs: config.tbs
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'segment_name'
        }, [
            'segment_name',
            'owner',
            'table_name',
            'tablespace_name',
            {name: 'initial_extent', type: 'int'},
            {name: 'next_extent', type: 'int'},
            {name: 'extents', type: 'int'},
            'size',
            {name: 'pct_increase', type: 'int'},
            'uniqueness',
            'clustering_factor',
            'table_blocks',
            'table_rows',
            'compression',
            'blevel',
            'leaf_blocks',
            'distinct_keys',
            'status',
            'last_analyzed',
            'logging'
        ]),
        autoLoad: false
    });

    renderStatus = function (status) {
        if (status == 'VALID') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    renderLogging = function (logging) {
        if (logging == 'YES') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    renderCompression = function (comp) {
        if (comp != 'DISABLED') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
            {iconCls: 'icon-gather-record', qtip: 'Collecter les Stats'},
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'owner', header: 'owner', dataIndex: 'owner', sortable: true, align: 'center', width: 5},
        { id: 'tablespace_name', header: 'Tablespace', dataIndex: 'tablespace_name', sortable: true, width: 5, align: 'center'},
        { id: 'table_name', header: 'Table', dataIndex: 'table_name', sortable: true, align: 'center', width: 10},
        { id: 'segment_name', header: 'Index', dataIndex: 'segment_name', sortable: true, align: 'center', width: 10},
        { id: 'table_blocks', header: 'Table Blocks', dataIndex: 'table_blocks', sortable: true, align: 'center', width: 7, renderer: Toc.content.ContentManager.FormatNumber},
        { id: 'clustering_factor', header: 'CFactor', dataIndex: 'clustering_factor', sortable: true, align: 'center', width: 5, renderer: Toc.content.ContentManager.FormatNumber},
        { id: 'table_rows', header: 'Table Rows', dataIndex: 'table_rows', sortable: true, align: 'center', width: 8, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Taille', align: 'center', dataIndex: 'size', sortable: true, width: 6},
        { header: 'Unique', dataIndex: 'uniqueness', sortable: true, align: 'center', width: 7},
        { header: 'Comp', dataIndex: 'compression', sortable: true, align: 'center', width: 5, renderer: renderCompression},
        { header: 'Status', dataIndex: 'status', sortable: true, align: 'center', width: 5, renderer: renderStatus},
        { header: 'Logging', dataIndex: 'logging', sortable: true, align: 'center', width: 5, renderer: renderLogging},
        { header: 'Blevel', dataIndex: 'blevel', sortable: true, align: 'center', width: 4},
        { header: 'Lblocks', dataIndex: 'leaf_blocks', sortable: true, align: 'center', width: 5, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Dkeys', dataIndex: 'distinct_keys', sortable: true, align: 'center', width: 5, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Analyzed', dataIndex: 'last_analyzed', sortable: true, align: 'center', width: 8},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'segment_name';
    config.stripeRows = true;

    config.chkUnique = new Ext.form.Checkbox({
        boxLabel: 'Unique',
        checked: true
    });

    config.chkNonUnique = new Ext.form.Checkbox({
        boxLabel: 'Non Unique',
        checked: true
    });

    config.chkComp = new Ext.form.Checkbox({
        boxLabel: 'Compresse',
        checked: true
    });

    config.chkNonComp = new Ext.form.Checkbox({
        boxLabel: 'Non Compresse',
        checked: true
    });

    config.chkMonitoring = new Ext.form.Checkbox({
        boxLabel: 'Monitoring',
        checked: true
    });

    config.chkNoMonitoring = new Ext.form.Checkbox({
        boxLabel: 'No Monitoring',
        checked: true
    });

    config.chkLogging = new Ext.form.Checkbox({
        boxLabel: 'Logging',
        checked: true
    });

    config.chkNoLogging = new Ext.form.Checkbox({
        boxLabel: 'No Logging',
        checked: true
    });

    config.chkVisible = new Ext.form.Checkbox({
        boxLabel: 'Visible',
        checked: true
    });

    config.chkInVisible = new Ext.form.Checkbox({
        boxLabel: 'Invisible',
        checked: true
    });

    config.txtSearch = new Ext.form.TextField({
        width: 60,
        hideLabel: true
    });

    config.autoLoad = false;
    config.db_host = config.host;
    config.db_sid = config.sid;

    config.tbsCombo = Toc.TbsCombo(config);
    config.schemasCombo = Toc.content.ContentManager.getDatabaseSchemasCombo(config);

    config.tbar = [
        {
            text: '',
            iconCls: 'remove',
            handler: this.onBatchDelete,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'move',
            handler: this.onBatchMove,
            scope: this
        },
        '-',
        config.chkUnique,
        '-',
        config.chkNonUnique,
        '-',
        config.chkComp,
        '-',
        config.chkNonComp,
        '-',
        config.chkMonitoring,
        '-',
        config.chkNoMonitoring,
        '-',
        config.chkLogging,
        '-',
        config.chkNoLogging,
        '-',
        config.chkVisible,
        '-',
        config.chkInVisible,
        '-',
        config.tbsCombo,
        '-',
        config.schemasCombo,
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
    Toc.indexesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.indexesGrid, Ext.grid.GridPanel, {
    onSearch: function () {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['schema'] = this.schema || 'all';
        store.baseParams['tbs'] = this.tbs || 'all';
        store.baseParams['unique'] = this.chkUnique.getValue();
        store.baseParams['non_unique'] = this.chkNonUnique.getValue();
        store.baseParams['comp'] = this.chkComp.getValue();
        store.baseParams['non_comp'] = this.chkNonComp.getValue();
        store.baseParams['monitoring'] = this.chkMonitoring.getValue();
        store.baseParams['no_monitoring'] = this.chkNoMonitoring.getValue();
        store.baseParams['logging'] = this.chkLogging.getValue();
        store.baseParams['visible'] = this.chkVisible.getValue();
        store.baseParams['invisible'] = this.chkInVisible.getValue();
        store.baseParams['search'] = filter;
        store.reload();
    },

    onGather: function (record) {
        var action = 'gather_indexstats';

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: action,
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid,
                owner: record.get('owner'),
                tbs: this.tbs,
                segment_type: 'INDEX',
                segment_name: record.get('segment_name')
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
                        description: 'Collecte des Stats sur index ' + record.get('segment_name')
                    };

                    this.proc_name = result.proc_name;
                    Toc.watchJob(params);
                    //this.fireEvent('saveSuccess', action.result.feedback);
                    //this.close();
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    },

    onAdd: function () {

    },

    onEdit: function (record) {

    },

    onDelete: function (record) {
        var DatabasesId = record.get('databases_id');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            TocLanguage.msgDeleteConfirm,
            function (btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'databases',
                            action: 'delete_database',
                            databases_id: DatabasesId
                        },
                        callback: function (options, success, response) {
                            var result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                                this.onRefresh();
                            } else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                            }
                        },
                        scope: this
                    });
                }
            }, this);
    },

    onBatchDelete: function () {
        var keys = this.getSelectionModel().selections.keys;

        if (keys.length > 0) {
            var batch = keys.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                TocLanguage.msgDeleteConfirm,
                function (btn) {
                    if (btn == 'yes') {
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'databases',
                                action: 'delete_databases',
                                batch: batch
                            },
                            callback: function (options, success, response) {
                                var result = Ext.decode(response.responseText);

                                if (result.success == true) {
                                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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

    onBatchMove: function () {
        console.debug(this.getSelectionModel().selections);

        Ext.MessageBox.alert(TocLanguage.msgErrTitle, 'Impossible de determiner le propietaire !!!');

        return;

        var keys = this.getSelectionModel().selections.keys;

        if (keys.length > 0) {
            this.getEl().mask('Chargement des metadonnées ... Veuillez patienter SVP');

            var i = 0;
            var cles = [];
            var len = 0;

            while (i < keys.length) {
                var key = keys[i];
                len = len + key.length;

                if (len < 4000) {
                    cles.push(key);
                }
                i++;
            }

            var batch = cles.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                'Voulez-vous vraiment deplacer ces indexes ?',
                function (btn) {
                    if (btn == 'yes') {
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'databases',
                                action: 'load_segment',
                                db_user: this.db_user,
                                db_pass: this.db_pass,
                                db_port: this.db_port,
                                db_host: this.host,
                                db_sid: this.sid,
                                owner: this.owner,
                                tbs: this.tbs,
                                segment_type: 'INDEX',
                                segment_name: batch
                            },
                            callback: function (options, success, response) {
                                this.getEl().unmask();
                                var result = Ext.decode(response.responseText);
                                //console.debug(response);

                                if (result.success == true) {
                                    var params = {
                                        db_user: this.db_user,
                                        db_pass: this.db_pass,
                                        db_port: this.db_port,
                                        db_host: this.host,
                                        db_sid: this.sid,
                                        segment_type: 'INDEX',
                                        panel: this,
                                        tbs: this.tbs,
                                        indexes: result.indexes,
                                        description: ''
                                    };

                                    var dlg = new Toc.MoveSegmentDialog(params);
                                    dlg.setTitle("Deplacement des indexes ... ");

                                    dlg.on('close', function () {
                                        this.onRefresh();
                                    }, this);

                                    dlg.show(params);

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

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-edit-record':
                this.onEdit(record);
                break;

            case 'icon-gather-record':
                this.onGather(record);
                break;
        }
    },

    setTbs: function (indexespace_name) {
        this.fireEvent('selectchange', indexespace_name);
    },

    onClick: function (e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var action = false;

        if (row !== false) {
            var btn = e.getTarget(".img-button");

            if (btn) {
                action = btn.className.replace(/img-button btn-/, '').trim();
            }
            else {
                var sel = this.getStore().getAt(row);
                this.setTbs(sel.json.indexespace_name);
            }

            if (action != 'img-button') {
                var serversId = this.getStore().getAt(row).get('servers_id');
                var module = 'setStatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, serversId, flag);

                        break;
                }
            }
        }
    },

    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
    },

    onAction: function (action, serversId, flag) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'servers',
                action: action,
                servers_id: serversId,
                flag: flag
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(serversId).set('content_status', flag);
                    store.commitChanges();

                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});

Toc.tablesGrid = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Tables';
    //config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            //console.log('tablesGrid activate');
            that.schemasCombo.getStore().on('beforeload', function (store, records, options) {
                that.getEl().mask('Chargement Schemas ....');
            });

            that.tbsCombo.getStore().on('beforeload', function (store, records, options) {
                that.getEl().mask('Chargement Tablespaces ....');
            });

            that.schemasCombo.getStore().on('load', function (store, records, options) {
                if (records.length > 1) {
                    that.schema = 'all';
                    that.schemasCombo.setValue(thisObj.schema);
                }
                else {
                    that.schemasCombo.disable();
                    Ext.Msg.alert(TocLanguage.msgErrTitle, "Probleme chargement Schemas !!!");
                }

                that.getEl().unmask();
            });

            that.tbsCombo.getStore().on('load', function (store, records, options) {
                if (records.length > 1) {
                    that.tbs = 'all';
                    that.tbsCombo.setValue(thisObj.tbs);
                }
                else {
                    that.schemasCombo.disable();
                    Ext.Msg.alert(TocLanguage.msgErrTitle, "Probleme chargement tablespaces !!!");
                }

                that.getEl().unmask();
            });

            that.schemasCombo.on('select', function (combo, record, index) {
                var schema = record.data.username;
                that.schema = schema;
            });

            that.tbsCombo.on('select', function (combo, record, index) {
                var tbs = record.data.tablespace_name;
                that.tbs = tbs;
            });

            that.tbsCombo.getStore().load();
            that.schemasCombo.getStore().load();

            if (!this.activated) {
                this.activated = true;
                if (this.schema) {
                    //this.getStore().load();
                }
            }
        },
        show: function (panel) {

        },
        'rowclick': this.onRowClick,
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_tables',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid,
            tbs: config.tbs
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'segment_name'
        }, [
            'compression',
            'monitoring',
            'last_analyzed',
            'logging',
            'segment_name',
            'partition_name',
            'segment_type',
            'owner',
            'tablespace_name',
            {name: 'avg_row_len', type: 'int'},
            {name: 'num_rows', type: 'int'},
            {name: 'blocks', type: 'int'},
            {name: 'chain_cnt', type: 'int'},
            {name: 'avg_space', type: 'int'},
            {name: 'empty_blocks', type: 'int'},
            {name: 'initial_extent', type: 'int'},
            {name: 'next_extent', type: 'int'},
            {name: 'extents', type: 'int'},
            {name: 'size', type: 'int'},
            {name: 'pct_increase', type: 'int'}
        ]),
        autoLoad: false
    });

    renderStatus = function (status) {
        switch (status) {
            case 'ONLINE':
                return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
            case 'ENABLED':
                return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
            case 'ON':
                return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
            case 'YES':
                return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
            default:
                return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-move-record', qtip: TocLanguage.tipMove},
            {iconCls: 'icon-gather-record', qtip: 'Collecter les Stats'},
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'owner', header: 'Owner', dataIndex: 'owner', sortable: true, align: 'center', width: 10},
        { id: 'tablespace_name', header: 'Tablespace', dataIndex: 'tablespace_name', sortable: true, align: 'center', width: 10},
        { id: 'segment_name', header: 'Table', dataIndex: 'segment_name', sortable: true, align: 'center', width: 15},
        { id: 'partition_name', header: 'Partition', dataIndex: 'partition_name', sortable: true, align: 'center', width: 15},
        { header: 'Size (MB)', align: 'center', dataIndex: 'size', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 10},
        { header: 'Blocks', align: 'center', dataIndex: 'blocks', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 5},
        { header: 'Rows', align: 'center', dataIndex: 'num_rows', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 5},
        { header: 'Chains', align: 'center', dataIndex: 'chain_cnt', sortable: true, renderer: Toc.content.ContentManager.FormatNumber, width: 5},
        { header: 'Compression', dataIndex: 'compression', renderer: renderStatus, align: 'center', sortable: true, width: 5},
        { header: 'Monitoring', dataIndex: 'monitoring', renderer: renderStatus, align: 'center', sortable: true, width: 5},
        { header: 'Logging', dataIndex: 'logging', renderer: renderStatus, align: 'center', sortable: true, width: 5},
        { id: 'last_analyzed', header: 'Last analyzed', dataIndex: 'last_analyzed', sortable: true, align: 'center', width: 10},
        config.rowActions
    ]);
    config.autoExpandColumn = 'segment_name';
    config.stripeRows = true;

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.pBar = new Ext.ProgressBar({
        hidden: true,
        width: 300,
        hideLabel: true
    });

    var thisObj = this;

    config.autoLoad = false;
    config.db_host = config.host;
    config.db_sid = config.sid;

    config.tbsCombo = Toc.TbsCombo(config);
    config.schemasCombo = Toc.content.ContentManager.getDatabaseSchemasCombo(config);

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onSearch,
            scope: this
        },
        {
            text: '',
            iconCls: 'remove',
            handler: this.onBatchDelete,
            scope: this
        },
        '->',
        config.pBar,
        '->',
        '-',
        config.tbsCombo,
        '-',
        config.schemasCombo
        ,
        '-',
        config.txtSearch,
        ' ',
        {
            text: '',
            iconCls: 'search',
            handler: this.onSearch,
            scope: this
        }
    ];

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
    Toc.tablesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tablesGrid, Ext.grid.GridPanel, {
    onSearch: function () {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['schema'] = this.schema || 'all';
        store.baseParams['tbs'] = this.tbs || 'all';
        store.baseParams['search'] = filter;
        store.reload();
    },

    onAdd: function () {

    },

    onEdit: function (record) {

    },

    onMove: function (record) {
        var action = 'load_segment';

        switch (record.get('segment_type')) {
            case 'TABLE':
                action = 'load_segment';
                break;

            case 'TABLE PARTITION':
                action = 'load_segment';
                break;

            case 'TABLE SUBPARTITION':
                action = 'load_segment';
                break;

            case 'INDEX':
                action = 'load_segment';
                break;

            case 'LOBSEGMENT':
                action = 'load_lob';
                break;

            default:
                break;
        }

        this.getEl().mask('Chargement des metadonnées ... veuillez patienter');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: action,
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid,
                owner: record.get('owner'),
                tbs: this.tbs,
                segment_type: 'TABLE',
                segment_name: record.get('segment_name'),
                partition_name: record.get('partition_name')
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
                        segment_name: record.get('segment_name'),
                        segment_type: record.get('segment_type'),
                        partition_name: record.get('partition_name'),
                        owner: record.get('owner'),
                        panel: this,
                        tbs: this.tbs,
                        indexes: result.indexes,
                        description: 'Deplacement du segment ' + record.get('segment_name')
                    };

                    switch (record.get('segment_type')) {
                        case 'TABLE':
                            var dlg = new Toc.MoveSegmentDialog(params);
                            dlg.setTitle('Deplacement de la Table ' + record.get('segment_name') + ' du Tablespace ' + this.tbs);

                            dlg.on('close', function () {
                                this.onRefresh();
                            }, this);

                            dlg.show(params);
                            break;

                        case 'TABLE PARTITION':
                            var dlg = new Toc.MoveSegmentDialog(params);
                            dlg.setTitle('Deplacement de la Partition ' + record.get('partition_name') + ' du Tablespace ' + this.tbs);

                            dlg.on('close', function () {
                                this.onRefresh();
                            }, this);

                            dlg.show(params);
                            break;

                        case 'TABLE SUBPARTITION':
                            var dlg = new Toc.MoveSegmentDialog(params);
                            dlg.setTitle('Deplacement de la Sous Partition ' + record.get('partition_name') + ' du Tablespace ' + this.tbs);

                            dlg.on('close', function () {
                                this.onRefresh();
                            }, this);

                            dlg.show(params);
                            break;

                        case 'INDEX':
                            var dlg = new Toc.MoveSegmentDialog(params);
                            dlg.setTitle("Deplacement de l'index " + record.get('segment_name') + " du Tablespace " + this.tbs);

                            dlg.on('close', function () {
                                this.onRefresh();
                            }, this);

                            dlg.show(params);
                            break;

                        case 'LOBSEGMENT':
                            var dlg = new Toc.MoveLobSegmentDialog(params);
                            dlg.setTitle("Deplacement du LOGSEGMENT " + record.get('segment_name') + " du Tablespace " + this.tbs);

                            dlg.on('close', function () {
                                this.onRefresh();
                            }, this);

                            dlg.show(params);
                            break;

                        default:
                            break;
                    }

                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    },

    onGather: function (record) {
        var action = 'gather_tablestats';

        switch (record.get('segment_type')) {
            case 'TABLE':
                action = 'gather_tablestats';
                break;

            case 'TABLE PARTITION':
                action = 'gather_tablestats';
                break;

            case 'TABLE SUBPARTITION':
                action = 'gather_tablestats';
                break;

            case 'INDEX':
                action = 'gather_tablestats';
                break;

            case 'LOBSEGMENT':
                action = 'gather_tablestats';
                break;

            default:
                break;
        }

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: action,
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid,
                owner: record.get('owner'),
                tbs: this.tbs,
                segment_type: 'TABLE',
                segment_name: record.get('segment_name'),
                partition_name: record.get('partition_name')
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
                        description: 'Collecte des Stats de la Table ' + record.get('segment_name')
                    };

                    this.proc_name = result.proc_name;
                    Toc.watchJob(params);
                    //this.fireEvent('saveSuccess', action.result.feedback);
                    //this.close();
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    },

    onDelete: function (record) {
        var msg = 'Voulez vous vraiment supprimer cette Table ?';

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            msg,
            function (btn) {
                if (btn == 'yes') {
                    record.step = 1;
                    record.max = 1;
                    this.dropTable(record);
                }
            }, this);
    },

    dropTable: function (record) {
        var action = 'drop_table';
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: action,
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.db_port,
                db_host: this.host,
                db_sid: this.sid,
                owner: record.get('owner'),
                segment_type: record.get('segment_type'),
                segment_name: record.get('segment_name')
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    this.pBar.val = this.pBar.val + record.step;
                    this.pBar.count = this.pBar.count + 1;
                    this.pBar.updateProgress(this.pBar.val, record.get('segment_name') + " ...", true);

                    if (this.pBar.count >= record.max) {
                        this.pBar.reset();
                        this.pBar.hide();
                    }

                    if (result.success == true) {
                        var store = this.getStore();
                        store.remove(record);
                        store.commitChanges();
                    }
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                    //Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, result.feedback);
                    //this.onGenerate();
                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    },

    onBatchDelete: function () {
        var count = this.selModel.getCount();
        if (count > 0) {
            this.pBar.reset();
            this.pBar.updateProgress(0, "", true);
            this.pBar.val = 0;
            this.pBar.count = 0;
            this.pBar.show();
            var step = 1 / count;

            for (var i = 0; i < count; i++) {
                var record = this.selModel.selections.items[i];
                record.step = step;
                record.max = count;

                this.dropTable(record);
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    onRefresh: function () {
        if (this.schema) {
            this.refreshGrid(this.schema);
        }
    },

    refreshGrid: function (schema) {
        var store = this.getStore();

        store.baseParams['schema'] = schema;
        store.reload();
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-move-record':
                this.onMove(record);
                break;

            case 'icon-edit-record':
                this.onEdit(record);
                break;

            case 'icon-gather-record':
                this.onGather(record);
                break;
        }
    },

    setTbs: function (tablespace_name) {
        this.fireEvent('selectchange', tablespace_name);
    },

    onClick: function (e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var action = false;

        if (row !== false) {
            var btn = e.getTarget(".img-button");

            if (btn) {
                action = btn.className.replace(/img-button btn-/, '').trim();
            }
            else {
                console.debug(sel);
                var sel = this.getStore().getAt(row);
                this.setTbs(sel.json.tablespace_name);
            }

            if (action != 'img-button') {
                var serversId = this.getStore().getAt(row).get('servers_id');
                var module = 'setStatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, serversId, flag);

                        break;
                }
            }
        }
    },

    onRowClick: function (grid, index, obj) {
        console.log(index);
        console.debug(obj);
        var item = grid.getStore().getAt(index);
        console.debug(item);
        this.fireEvent('selectchange', item);
    },

    onAction: function (action, serversId, flag) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'servers',
                action: action,
                servers_id: serversId,
                flag: flag
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(serversId).set('content_status', flag);
                    store.commitChanges();

                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});

Toc.TbsMap = function (config) {
    var that = this;
    var params = config;
    config = config || {};
    config.loadMask = true;
    config.title = params.file_id != -1 ? 'Datafile Map' : 'Tbs Map';
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.activated = true;
                //this.onGenerate();
            }
        },
        'rowclick': this.onRowClick,
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_tbsmap',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid,
            tbs: config.tbs,
            file_id: config.file_id || -1
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'file_id',
            'block_id',
            'end_block',
            'size',
            'owner',
            'segment_name',
            'partition_name',
            'segment_type'
        ]),
        autoLoad: false
    });

    render = function (segment) {
        if (segment == 'free') {
            return '<span style="color:#ffffff;"><span style="background-color:#006400;">' + segment + '</span></span>';
        } else {
            return '<span style="color:#ffffff;"><span style="background-color:#ff0000;">' + segment + '</span></span>';
        }
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-move-record', qtip: TocLanguage.tipMove},
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'file_id', header: 'Fichier', dataIndex: 'file_id', align: 'center', width: 3},
        { id: 'owner', header: 'Proprietaire', dataIndex: 'owner', align: 'center', width: 7},
        { id: 'segment_name', header: 'Nom Segment', dataIndex: 'segment_name', width: 25, align: 'center', renderer: render},
        { header: 'Nom Partition', dataIndex: 'partition_name', width: 20, align: 'center'},
        { id: 'segment_type', header: 'Type Segment', dataIndex: 'segment_type', width: 10, align: 'center'},
        { id: 'block_id', header: 'Block ID', dataIndex: 'block_id', width: 10, align: 'center'},
        { id: 'end_block', header: 'End Block', dataIndex: 'end_block', width: 10, align: 'center'},
        { header: 'Taille', align: 'center', dataIndex: 'size', width: 15},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'segment_name';
    config.stripeRows = true;

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.tbar = [
        {
            text: '',
            iconCls: 'remove',
            handler: this.onBatchDelete,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onGenerate,
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

    this.addEvents({'selectchange': true});

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

    Toc.TbsMap.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TbsMap, Ext.grid.GridPanel, {

    onAdd: function () {

    },

    onEdit: function (record) {

    },

    onGenerate: function () {
        var msg = 'Souhaitez vous vraiment generer le Map de ce Tablespace ? cette action peut prendre un peu de temps';

        if (this.file_id != -1) {
            msg = 'Souhaitez vous vraiment generer le Map de ce Datafile ? cette action peut prendre un peu de temps';
        }

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            msg,
            function (btn) {
                if (btn == 'yes') {
                    this.getEl().mask('Creation du Job ... veuillez patienter');
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'databases',
                            action: 'tbs_map',
                            db_user: this.db_user,
                            db_pass: this.db_pass,
                            db_port: this.db_port,
                            db_host: this.host,
                            db_sid: this.sid,
                            tbs: this.tbs,
                            file_id: this.file_id
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
                                    table_name: result.table_name,
                                    panel: this,
                                    description: this.file_id != -1 ? 'Generation du Map du Datafile ' + this.file_id : 'Generation du Map du Tablespace ' + this.tbs
                                };

                                var store = this.getStore();
                                store.baseParams['table_name'] = result.table_name;

                                Toc.watchJob(params);
                            } else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                            }
                        },
                        scope: this
                    });
                }
            }, this);
    },

    onMove: function (record) {
        var action = 'load_segment';
        console.log(record.get('segment_type'));

        switch (record.get('segment_type')) {
            case 'TABLE':
                action = 'load_segment';
                break;

            case 'TABLE PARTITION':
                action = 'load_segment';
                break;

            case 'TABLE SUBPARTITION':
                action = 'load_segment';
                break;

            case 'INDEX':
                action = 'load_segment';
                break;

            case 'LOBSEGMENT':
                action = 'load_lob';
                break;

            default:
                break;
        }

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            'Souhaitez vous vraiment deplacer ce segment ? cette action peut prendre un peu de temps',
            function (btn) {
                if (btn == 'yes') {
                    this.getEl().mask('Chargement des metadonnées ... veuillez patienter');
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'databases',
                            action: action,
                            db_user: this.db_user,
                            db_pass: this.db_pass,
                            db_port: this.db_port,
                            db_host: this.host,
                            db_sid: this.sid,
                            owner: record.get('owner'),
                            tbs: this.tbs,
                            segment_type: record.get('segment_type'),
                            segment_name: record.get('segment_name'),
                            partition_name: record.get('partition_name')
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
                                    segment_name: record.get('segment_name'),
                                    segment_type: record.get('segment_type'),
                                    partition_name: record.get('partition_name'),
                                    owner: record.get('owner'),
                                    panel: this,
                                    tbs: this.tbs,
                                    indexes: result.indexes,
                                    description: 'Deplacement du segment ' + record.get('segment_name')
                                };

                                switch (record.get('segment_type')) {
                                    case 'TABLE':
                                        var dlg = new Toc.MoveSegmentDialog(params);
                                        dlg.setTitle('Deplacement de la Table ' + record.get('segment_name') + ' du Tablespace ' + this.tbs);

                                        dlg.on('close', function () {
                                            this.onGenerate;
                                        }, this);

                                        dlg.show(params);
                                        break;

                                    case 'TABLE PARTITION':
                                        var dlg = new Toc.MoveSegmentDialog(params);
                                        dlg.setTitle('Deplacement de la Partition ' + record.get('partition_name') + ' du Tablespace ' + this.tbs);

                                        dlg.on('close', function () {
                                            this.onGenerate;
                                        }, this);

                                        dlg.show(params);
                                        break;

                                    case 'TABLE SUBPARTITION':
                                        var dlg = new Toc.MoveSegmentDialog(params);
                                        dlg.setTitle('Deplacement de la Sous Partition ' + record.get('partition_name') + ' du Tablespace ' + this.tbs);

                                        dlg.on('close', function () {
                                            this.onGenerate;
                                        }, this);

                                        dlg.show(params);
                                        break;

                                    case 'INDEX':
                                        var dlg = new Toc.MoveSegmentDialog(params);
                                        dlg.setTitle("Deplacement de l'index " + record.get('segment_name') + " du Tablespace " + this.tbs);

                                        dlg.on('close', function () {
                                            this.onGenerate;
                                        }, this);

                                        dlg.show(params);
                                        break;

                                    case 'LOBSEGMENT':
                                        var dlg = new Toc.MoveLobSegmentDialog(params);
                                        dlg.setTitle("Deplacement du LOGSEGMENT " + record.get('segment_name') + " du Tablespace " + this.tbs);

                                        dlg.on('close', function () {
                                            this.onGenerate;
                                        }, this);

                                        dlg.show(params);
                                        break;

                                    default:
                                        break;
                                }

                            } else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                            }
                        },
                        scope: this
                    });
                }
            }, this);
    },

    onDelete: function (record) {
        var action = 'drop_table';
        var msg = 'Voulez vous vraiment supprimer cette Table ?';
        switch (record.get('segment_type')) {
            case 'TABLE':
                action = 'drop_table';
                msg = 'Voulez-vous vraiment supprimer cette Table ?';
                break;
            case 'INDEX':
                action = 'drop_index';
                msg = 'Voulez-vous vraiment supprimer cet Index ?';
                break;
            default:
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, 'Type de segment pas pris en charge');
                return;
        }

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            msg,
            function (btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'databases',
                            action: action,
                            db_user: this.db_user,
                            db_pass: this.db_pass,
                            db_port: this.db_port,
                            db_host: this.host,
                            db_sid: this.sid,
                            owner: record.get('owner'),
                            segment_type: record.get('segment_type'),
                            segment_name: record.get('segment_name')
                        },
                        callback: function (options, success, response) {
                            var result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                //this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                                Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, result.feedback);
                                this.onGenerate();
                            } else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                            }
                        },
                        scope: this
                    });
                }
            }, this);
    },

    onBatchDelete: function () {
        var keys = this.getSelectionModel().selections.keys;

        if (keys.length > 0) {
            var batch = keys.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                TocLanguage.msgDeleteConfirm,
                function (btn) {
                    if (btn == 'yes') {
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'databases',
                                action: 'delete_databases',
                                batch: batch
                            },
                            callback: function (options, success, response) {
                                var result = Ext.decode(response.responseText);

                                if (result.success == true) {
                                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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

    onRefresh: function () {
        var store = this.getStore();

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

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-move-record':
                this.onMove(record);
                break;
        }
    },

    setTbs: function (tablespace_name) {
        this.fireEvent('selectchange', tablespace_name);
    },

    onClick: function (e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var action = false;

        if (row !== false) {
            var btn = e.getTarget(".img-button");

            if (btn) {
                action = btn.className.replace(/img-button btn-/, '').trim();
            }
            else {
                console.debug(sel);
                var sel = this.getStore().getAt(row);
                this.setTbs(sel.json.tablespace_name);
            }

            if (action != 'img-button') {
                var serversId = this.getStore().getAt(row).get('servers_id');
                var module = 'setStatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, serversId, flag);

                        break;
                }
            }
        }
    },

    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
    },

    onAction: function (action, serversId, flag) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'servers',
                action: action,
                servers_id: serversId,
                flag: flag
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(serversId).set('content_status', flag);
                    store.commitChanges();

                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});

Toc.tbsGrid = function (config) {
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.loadMask = true;
    config.title = 'Tablespaces';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.activated = true;
                this.getStore().load();
            }
        },
        'rowclick': this.onRowClick,
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_tbs',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'tablespace_name'
        }, [
            'tablespace_name',
            'status',
            'contents',
            'extent_management',
            'bigfile',
            {name: 'megs_alloc', type: 'int'},
            {name: 'free', type: 'int'},
            {name: 'megs_used', type: 'int'},
            {name: 'pct_free', type: 'int'},
            {name: 'pct_used', type: 'int'},
            {name: 'total_pct_used', type: 'int'},
            {name: 'max', type: 'int'}
        ]),
        autoLoad: false
    });

    renderStatus = function (status) {
        if (status == 'ONLINE') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
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
        { id: 'tablespace_name', header: 'Nom', dataIndex: 'tablespace_name', sortable: true},
        { header: '% Utilisation', align: 'center', dataIndex: 'total_pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true},
        { header: 'Taille (MB)', align: 'center', dataIndex: 'max', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Libre (MB)', align: 'center', dataIndex: 'free', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Utilise (MB)', align: 'center', dataIndex: 'megs_used', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { id: 'status', header: 'Status', align: 'center', dataIndex: 'status', renderer: renderStatus},
        config.rowActions
    ]);
    config.autoExpandColumn = 'tablespace_name';
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

    this.addEvents({'selectchange': true});
    Toc.tbsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tbsGrid, Ext.grid.GridPanel, {

    onAdd: function () {

    },

    onDelete: function (record) {
        var tbs = record.get('tablespace_name');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            'Voulez-vous vraiment supprimer cet espace logique ? Cette action est irreversible !!!',
            function (btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'databases',
                            action: 'drop_tablespace',
                            db_user: this.db_user,
                            db_pass: this.db_pass,
                            db_port: this.db_port,
                            db_host: this.host,
                            db_sid: this.sid,
                            tablespace_name: tbs
                        },
                        callback: function (options, success, response) {
                            var result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                                this.onRefresh();
                            } else {
                                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                            }
                        },
                        scope: this
                    });
                }
            }, this);
    },

    setPermissions: function (permissions) {
        this.bottomToolbar.items.items[0].disable();
        this.bottomToolbar.items.items[2].disable();

        this.topToolbar.items.items[0].disable();
        this.topToolbar.items.items[2].disable();
        if (permissions) {
            if (permissions.can_write == 1 || permissions.can_modify == '') {
                this.bottomToolbar.items.items[0].enable();
                this.topToolbar.items.items[0].enable();
            }
            if (permissions.can_modify == '') {
                this.bottomToolbar.items.items[2].enable();
                this.topToolbar.items.items[2].enable();
            }
        }
    },

    onEdit: function (record) {
        var params = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            server_typ: this.server_typ,
            host: this.host,
            db_port: this.port,
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_host: this.host,
            db_sid: this.sid,
            file_id: -1,
            tbs: record.get("tablespace_name")
        };

        var dlg = new Toc.TbsBrowser(params);
        dlg.setTitle(this.label + ' : ' + record.get("tablespace_name"));

        dlg.on('close', function () {
            this.onRefresh();
        }, this);

        dlg.show(params, this.owner);
    },

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

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {

            case 'icon-edit-record':
                this.onEdit(record);
                break;
            case 'icon-delete-record':
                this.onDelete(record);
                break;
        }
    },

    setTbs: function (tablespace_name) {
        this.fireEvent('selectchange', tablespace_name);
    },

    onClick: function (e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var action = false;

        if (row !== false) {
            var btn = e.getTarget(".img-button");

            if (btn) {
                action = btn.className.replace(/img-button btn-/, '').trim();
            }
            else {
                var sel = this.getStore().getAt(row);
                this.setTbs(sel.json.tablespace_name);
            }

            if (action != 'img-button') {
                var tbs = this.getStore().getAt(row).get('tablespace_name');
                var module = 'setTbstatus';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 'ONLINE' : 'OFFLINE';
                        this.onAction(module, tbs, flag);
                        break;
                }
            }
        }
    },

    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
    },

    onAction: function (module, tbs, flag) {
        Toc.setTbsStatus(this, flag, tbs);
    }
});

