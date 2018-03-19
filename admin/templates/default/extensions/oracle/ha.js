Toc.AvailabilityPanel = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.width = '25%';
    config.autoHeight = true;
    config.title = 'Disponibilite';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    var data = [];

    data[0] = ['Last Backup', config.last_backup || ''];
    data[1] = ['Status Backup', config.last_backup_status || ''];
    data[2] = ['Taille Backup', config.last_backup_size || ''];
    data[3] = ['Free FRA', config.percent_free_fra ? config.percent_free_fra + '%' : ''];
    data[4] = ['Flashback Time', config.flashback_time || ''];

    config.ds = new Ext.data.SimpleStore({
        fields: ['name', 'value'],
        data: data
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.cm = new Ext.grid.ColumnModel([
        {align: 'right', id: 'name', dataIndex: 'name', width: 90},
        {align: 'left', dataIndex: 'value', width: 170}
    ]);
    //config.autoExpandColumn = 'mount';

    var thisObj = this;

    config.tools = [];

    Toc.AvailabilityPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AvailabilityPanel, Ext.grid.GridPanel, {

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
        dlg.setTitle(record.get("name"));

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

Toc.DataguardPanel = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.width = '25%';
    config.autoHeight = true;
    config.title = 'Dataguard';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    var data = [];

    data[0] = ['Archive', config.archiver];
    data[1] = ['Log archivé', config.log_archived || ''];
    data[2] = ['Log appliqué', config.log_applied || ''];
    data[3] = ['Applied time', config.applied_time || ''];
    data[4] = ['Log Gap', config.log_gap || ''];

    config.ds = new Ext.data.SimpleStore({
        fields: ['name', 'value'],
        data: data
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.cm = new Ext.grid.ColumnModel([
        {align: 'right', id: 'name', dataIndex: 'name', width: 80},
        {align: 'left', dataIndex: 'value', width: 210}
    ]);
    //config.autoExpandColumn = 'mount';

    var thisObj = this;

    config.tools = [];

    Toc.DataguardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DataguardPanel, Ext.grid.GridPanel, {

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
        dlg.setTitle(record.get("name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(params, this.owner);
    },

    onRefresh: function () {
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
    }
});

Toc.DataGuardConfigPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.width = '25%';
    config.refreshed = false;
    config.autoHeight = true;
    config.loadMask = false;
    config.title = params.src_label + ' ====> ' + params.dest_label;
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords,forceFit:true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'watch_dg',
            config_id : params.id,
            src_home : params.src_home,
            src_db_user : params.src_db_user,
            src_db_pass : params.src_db_pass,
            src_db_host : params.src_db_host,
            src_db_sid : params.src_db_sid,
            dest_home : params.dest_home,
            dest_db_user : params.dest_db_user,
            dest_db_pass : params.dest_db_pass,
            dest_db_host : params.dest_db_host,
            dest_db_sid : params.dest_db_sid,
            src_os_user : params.src_os_user,
            src_os_pass : params.src_os_pass,
            dest_os_user : params.dest_os_user,
            dest_os_pass : params.dest_os_pass,
            oggdir_src : params.oggdir_src,
            extract_name : params.extract_name,
            datapump_name : params.datapump_name,
            oggdir_dest : params.oggdir_dest,
            replicat_name : params.replicat_name
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'var',
            'val'
        ]),
        autoLoad: true
    });

    config.listeners = {
        activate: function (panel) {
            console.log('activate');
            //this.onRefresh();
        },
        show: function (panel) {
            console.log('show');
            //this.onRefresh();
        },
        enable: function (panel) {
            console.log('enable');
            //this.onRefresh();
        },
        deactivate: function (panel) {
            console.log('deactivate');
            this.onStop();
        },
        scope: this
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        actions:[
            {iconCls: 'icon-detail-record', qtip: 'Details'},
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (val) {
        if (status == 'RUNNING') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'var', header: '', dataIndex: 'var',width:60},
        { id: 'val',header: '',dataIndex: 'var',renderer:render,width:30},
        config.rowActions
    ]);
    config.autoExpandColumn = 'var';

    var thisObj = this;

    config.task = {
        run: function () {
            thisObj.getStore().load();
        },
        interval: config.freq || 10000
    };

    config.runner = new Ext.util.TaskRunner();

    Toc.DataGuardConfigPanel.superclass.constructor.call(this, config);
    this.getView().scrollOffset = 0;
    this.start();
    this.start();
};

Ext.extend(Toc.DataGuardConfigPanel, Ext.grid.GridPanel, {
    onEdit: function (record) {
        //console.debug(params);
        var dlg = new Toc.CaptureDialog(this.params);
        dlg.setTitle('Processus de Capture sur le serveur  ' + this.params.src_db_host);

        dlg.on('saveSuccess', function () {
            this.start();
        }, this);

        //this.stop();
        //dlg.show(this.params, this.owner);
    },

    onRefresh: function() {
        this.getStore().reload();
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
    },

    start: function () {
        this.setTitle(this.params.src_label + ' ====> ' + this.params.dest_label + ' (monitoring)');
        this.runner.start(this.task);
    },

    stop: function () {
        this.setTitle(this.params.src_label + ' ====> ' + this.params.dest_label);
        this.runner.stop(this.task);
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
                var index = this.getStore().getAt(row).get('index');

                switch (action) {
                    case 'status-off':
                        switch(index)
                        {
                            case 0:
                                //this.stopCapture(index);
                                break;
                        }
                        break;
                    case 'status-on':
                        switch(index)
                        {
                            case 0:
                                //this.startCapture(index);
                                break;
                        }
                        break;
                        break;
                }
            }
        }
    },

    stopCapture: function (index) {
        this.getEl().mask('Arret du processus de capture ...');
        this.stop();
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'stop_capture',
                src_home : this.params.src_home,
                src_db_user : this.params.src_db_user,
                src_db_pass : this.params.src_db_pass,
                src_db_host : this.params.src_db_host,
                src_db_sid : this.params.src_db_sid,
                dest_home : this.params.dest_home,
                dest_db_user : this.params.dest_db_user,
                dest_db_pass : this.params.dest_db_pass,
                dest_db_host : this.params.dest_db_host,
                dest_db_sid : this.params.dest_db_sid,
                src_os_user : this.params.src_os_user,
                src_os_pass : this.params.src_os_pass,
                dest_os_user : this.params.dest_os_user,
                dest_os_pass : this.params.dest_os_pass,
                oggdir_src : this.params.oggdir_src,
                extract_name : this.params.extract_name,
                datapump_name : this.params.datapump_name,
                oggdir_dest : this.params.oggdir_dest,
                replicat_name : this.params.replicat_name
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                this.start();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(index).set('status', 'STOPPED');
                    store.commitChanges();
                    Ext.MessageBox.alert(TocLanguage.msgInfoTitle, result.feedback);
                }
                else
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            },
            scope: this
        });
    },

    startCapture: function (index) {
        this.getEl().mask('Demarrage du processus de capture ...');
        this.stop();
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'start_capture',
                src_home : this.params.src_home,
                src_db_user : this.params.src_db_user,
                src_db_pass : this.params.src_db_pass,
                src_db_host : this.params.src_db_host,
                src_db_sid : this.params.src_db_sid,
                dest_home : this.params.dest_home,
                dest_db_user : this.params.dest_db_user,
                dest_db_pass : this.params.dest_db_pass,
                dest_db_host : this.params.dest_db_host,
                dest_db_sid : this.params.dest_db_sid,
                src_os_user : this.params.src_os_user,
                src_os_pass : this.params.src_os_pass,
                dest_os_user : this.params.dest_os_user,
                dest_os_pass : this.params.dest_os_pass,
                oggdir_src : this.params.oggdir_src,
                extract_name : this.params.extract_name,
                datapump_name : this.params.datapump_name,
                oggdir_dest : this.params.oggdir_dest,
                replicat_name : this.params.replicat_name
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                this.start();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(index).set('status', 'RUNNING');
                    store.commitChanges();
                    Ext.MessageBox.alert(TocLanguage.msgInfoTitle, result.feedback);
                }
                else
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            },
            scope: this
        });
    }
});

Toc.DataguardDialog = function (params) {
    config = {};

    config.title = 'Capture';
    config.layout = 'fit';
    config.modal = true;
    config.width = 800;
    config.height = 600;
    config.iconCls = 'icon-tbs-win';
    config.items = this.getContentPanel(params);

    this.addEvents({'saveSuccess': true});

    Toc.DataguardDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DataguardDialog, Ext.Window, {

    show: function (config, owner) {
        this.owner = owner || null;
        if (config) {
            this.config = config;
        }

        Toc.DataguardDialog.superclass.show.call(this);

        this.pnlDetail.getEl().mask('Chargement details ...');

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'detail_capture',
                src_home : this.params.src_home,
                src_db_user : this.params.src_db_user,
                src_db_pass : this.params.src_db_pass,
                src_db_host : this.params.src_db_host,
                src_db_sid : this.params.src_db_sid,
                dest_home : this.params.dest_home,
                dest_db_user : this.params.dest_db_user,
                dest_db_pass : this.params.dest_db_pass,
                dest_db_host : this.params.dest_db_host,
                dest_db_sid : this.params.dest_db_sid,
                src_os_user : this.params.src_os_user,
                src_os_pass : this.params.src_os_pass,
                dest_os_user : this.params.dest_os_user,
                dest_os_pass : this.params.dest_os_pass,
                oggdir_src : this.params.oggdir_src,
                extract_name : this.params.extract_name,
                datapump_name : this.params.datapump_name,
                oggdir_dest : this.params.oggdir_dest,
                replicat_name : this.params.replicat_name
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                this.pnlDetail.getEl().unmask();

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(file_id).set('autoextensible', flag);
                    store.commitChanges();
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    },

    getContentPanel: function (params) {
        this.pnlDetail = new Ext.Panel({
            items: [
                {xtype:'textarea',name:'content'}
            ]
        });

        this.tabdatabases = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
                this.pnlDetail
            ]
        });

        return this.tabdatabases;
    }
});

Toc.DataGuardDashboardPanel = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.title = 'Dataguard';
    config.autoHeight = true;
    config.layout = 'column';
    config.loadMask = false;
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            //console.log('activate');
            //this.onRefresh();
        },
        show: function (panel) {
            //console.log('show');
            //this.onRefresh();
        },
        enable: function (panel) {
            //console.log('enable');
            //this.onRefresh();
        },
        deactivate: function (panel) {
            //console.log('deactivate');
            this.onStop();
        },
        scope: this
    };

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.combo_freq = Toc.content.ContentManager.getFrequenceCombo();
    //config.categoryCombo = Toc.content.ContentManager.getDatabasesCategoryCombo();

    config.tbar = [
        {
            text: TocLanguage.btnAdd,
            iconCls: 'add',
            handler: this.onAdd,
            scope: this
        },
        '-',
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
        },
        '-',
        config.combo_freq
    ];

    config.combo_freq.getStore().load();
    //config.categoryCombo.getStore().load();

    var thisObj = this;

    config.combo_freq.on('select', function (combo, record, index) {
        thisObj.onStop();
        //var category = thisObj.categoryCombo.getValue();
        var freq = thisObj.combo_freq.getValue();
        thisObj.buildItems(freq);
    });

    Toc.DataGuardDashboardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DataGuardDashboardPanel, Ext.Panel, {
    onAdd: function () {
        var dlg = new Toc.DataGuardConfigDialog();
        //var path = this.owner.getCategoryPath();
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show();
    },

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

        this.getEl().mask('Chargement ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'list_dgconfig'
            },
            callback: function (options, success, response) {
                this.getEl().unmask();
                var result = Ext.decode(response.responseText);

                if (result.total > 0) {
                    var i = 0;
                    while (i < result.total) {
                        ogg = result.records[i];
                        //console.debug(ogg);
                        ogg.owner = this.owner;
                        ogg.freq = frequence;

                        var panel = new Toc.DataGuardConfigPanel(ogg);
                        //var panel = new Toc.TopWaitClassPanel(db);
                        this.add(panel);
                        this.panels[i] = panel;
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

Toc.DataGuardConfigDialog = function(config) {

    config = config || {};

    config.id = 'dataguard_config_dialog-win';
    config.title = 'Editer une Configuration DataGuard';
    config.region = 'center';
    config.width = 600;
    config.height = 350;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.buildForm();

    config.buttons = [
        {
            text: 'Enregistrer',
            handler: function() {
                this.submitForm();
            },
            scope: this
        },
        {
            text: TocLanguage.btnClose,
            handler: function() {
                this.close();
            },
            scope: this
        }
    ];

    this.addEvents({'saveSuccess': true});

    Toc.DataGuardConfigDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DataGuardConfigDialog, Ext.Window, {

    show: function (databases_id,event) {
        //this.frmConfig.form.baseParams['databases_id'] = databases_id;
        //this.frmConfig.form.baseParams['event'] = event;
        Toc.DataGuardConfigDialog.superclass.show.call(this);

        var combo_arch_dest = Toc.content.ContentManager.getArchDestCombo();

        var fs_src = new Ext.form.FieldSet({
            autoScroll : true,
            title : 'Primaire',
            autoHeight:true,
            border : true,
            layout : 'form',
            items : [
                new Toc.content.ContentManager.getOracleConnexionsCombo({name:'src_database',valueField : 'id'}),
                combo_arch_dest,
                {xtype:'textfield', fieldLabel: 'Extract Name', name: 'extract_name', id: 'extract_name',allowBlank:false},
                {xtype:'textfield', fieldLabel: 'Datapump Name', name: 'datapump_name', id: 'datapump_name',allowBlank:false}
            ]
        });

        var fs_dest = new Ext.form.FieldSet({
            autoScroll : true,
            autoHeight:true,
            title : 'Destination',
            border : true,
            layout : 'form',
            items : [
                new Toc.content.ContentManager.getOracleConnexionsCombo({name:'dest_database',valueField : 'id'}),
                {xtype:'textfield', fieldLabel: 'OGG DIR', name: 'oggdir_dest', id: 'oggdir_dest',allowBlank:false,width:410},
                {xtype:'textfield', fieldLabel: 'Replicat Name', name: 'replicat_name', id: 'replicat_name',allowBlank:false}
            ]
        });

        fs_src.doLayout(true, true);
        fs_dest.doLayout(true, true);

        this.frmConfig.add(fs_src);
        this.frmConfig.doLayout(false, true);
        this.frmConfig.add(fs_dest);
        this.frmConfig.doLayout(false, true);

        this.center();
        this.doLayout(true, true);
    },

    buildForm: function() {
        this.frmConfig = new Ext.form.FormPanel({
            autoScroll: true,
            id : 'frmConfig',
            layout: 'form',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'databases',
                action : 'save_ggconfig'
            },
            deferredRender: false,
            items: [
            ]
        });

        return this.frmConfig;
    },

    submitForm: function() {
        this.frmConfig.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function(form, action){
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function(form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});

Toc.RmanConfigGrid = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.title = 'Configuration';
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords,forceFit:true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_rmanconfig',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'databases_id'
        }, [
            'databases_id',
            'name',
            'value'
        ]),
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

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'name', header: 'Nom', dataIndex: 'name', sortable: true,width : 49},
        { header: 'value', align: 'left', dataIndex: 'value',width : 49},
        config.rowActions
    ]);
    config.autoExpandColumn = 'name';

    config.tbar = [
        {
            text: TocLanguage.btnRefresh,
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->'
    ];

    var thisObj = this;

    Toc.RmanConfigGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.RmanConfigGrid, Ext.grid.GridPanel, {

    onEdit: function (record) {
        var dlg = new Toc.DatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(record.json, path, this.owner);
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

Toc.RmanBackupGrid = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.title = 'Backup';
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords,forceFit:true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_backup',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'key'
        }, [
            'key',
            'type',
            'status',
            'start_time',
            'end_time',
            'duree',
            'taille',
            'ratio'
        ]),
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

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { header: 'Type', align: 'center', dataIndex: 'type',width : 10},
        { id: 'start_time', align: 'center', header: 'Debut', dataIndex: 'start_time', sortable: true,width : 20},
        { header: 'Fin', align: 'center', dataIndex: 'end_time',width : 20},
        { header: 'Status', align: 'center', dataIndex: 'status',width : 20},
        { header: 'Duree', align: 'center', dataIndex: 'duree',width : 10},
        { header: 'Taille', align: 'center', dataIndex: 'taille',width : 10},
        { header: 'Ratio', align: 'center', dataIndex: 'ratio',width : 10},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'name';

    config.tbar = [
        {
            text: TocLanguage.btnRefresh,
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->'
    ];

    var thisObj = this;

    Toc.RmanBackupGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.RmanBackupGrid, Ext.grid.GridPanel, {

    onEdit: function (record) {
        var dlg = new Toc.DatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(record.json, path, this.owner);
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

Toc.databasesAvailabilityDashboard = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.title = 'Disponibilite';
    config.loadMask = true;
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            console.log('activate');
        },
        deactivate: function (panel) {
            console.log('deactivate');
            //this.onStop();
        },
        scope: this
    };

    if (!config.label) {
        config.txtSearch = new Ext.form.TextField({
            width: 100,
            hideLabel: true
        });

        config.categoryCombo = Toc.content.ContentManager.getDatabasesCategoryCombo();

        config.tbar = [
            {
                text: TocLanguage.btnAdd,
                iconCls: 'add',
                handler: this.onAdd,
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
            '-',
            {
                text: 'Collecter',
                iconCls: 'scan',
                handler: this.onCollect,
                scope: this
            },
            '->',
            config.categoryCombo
        ];

        config.categoryCombo.getStore().load();

        var thisObj = this;

        config.categoryCombo.on('select', function (combo, record, index) {
            var category = record.data.key;
            thisObj.buildItems(category);
        });
    }

    Toc.databasesAvailabilityDashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databasesAvailabilityDashboard, Ext.Panel, {

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

    onCollect: function () {
        Toc.runReport(this, 40, this.owner, this.owner);
    },

    buildItems: function (category) {
        if (this.items) {
            this.removeAll(true);
        }

        this.getEl().mask('Chargement');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'list_databases',
                category: category || 'prod'
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
                        var panel = new Toc.DatabasesDashboardPanel(db);
                        this.add(panel);
                        panel.buildItems(db);
                        this.doLayout();
                        i++;
                    }
                }
            },
            scope: this
        });
    }
});
