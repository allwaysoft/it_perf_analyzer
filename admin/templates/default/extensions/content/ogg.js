Toc.GoldenGateConfigPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.width = '50%';
    config.count = 0;
    config.reqs = 0;
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
            action: 'mon_ogg',
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
            'process',
            'latence',
            'status',
            'pct',
            'seqno',
            'rba',
            'network'
        ]),
        listeners: {
            load: function (store, records, opt) {
                //console.log('load');
                that.reqs--;
                if(that && that.started)
                {
                    if(that.count == 0)
                    {
                        var interval = setInterval(function(){
                            store.load();
                        }, that.freq || 5000);

                        //setTimeout(that.refreshData, that.freq || 10000);
                        that.count++;
                        that.interval = interval;
                    }
                    else
                    {
                        //console.log('that.count' + that.count);
                    }
                }
            },
            beforeload: function (store, opt) {
                //console.log('beforeload');
                //console.log('reqs ==> ' + that.reqs);
                //console.log('that.started ? ' + that.started);
                if(that.reqs == 0)
                {
                    if(that.started)
                    {
                        that.reqs++;
                    }
                }
                else
                {
                    return false;
                }
                return that.started;
            }, scope: this
        },
        autoLoad: false
    });

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
            this.stop();
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

    renderStatus = function (status) {
        if (status == 'RUNNING') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    renderFS = function (percent) {
        var pct = percent.split("%")[0];

        if (pct == 0) {
            return '';
        }

        if (pct >= 90) {
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + ";' class='item-bar red'></div></div>";
        }
        else {
            if (pct < 90 && pct >= 80) {
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + ";' class='item-bar yellow'></div></div>";
            }
            else {
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + ";' class='item-bar green'></div></div>";
            }
        }
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'process', header: 'Processus', dataIndex: 'process',width:13},
        { id: 'seqno', header: 'Seq No',align: 'center', dataIndex: 'seqno',width:10},
        { id: 'rba', header: 'RBA',align: 'center', dataIndex: 'rba',width:15},
        { id: 'fs', header: 'FS',align: 'center', dataIndex: 'pct',renderer:renderFS,width:12},
        { id: 'status',header: 'Status', align: 'center', dataIndex: 'status',renderer:renderStatus,width:10},
        { id: 'latence',header: 'Latence', align: 'center', dataIndex: 'latence',width:15},
        { id: 'network',header: 'Network', align: 'center', dataIndex: 'network',width:20},
        config.rowActions
    ]);
    config.autoExpandColumn = 'status';

    var thisObj = this;

    Toc.GoldenGateConfigPanel.superclass.constructor.call(this, config);
    this.getView().scrollOffset = 0;
    //this.start();
};

Ext.extend(Toc.GoldenGateConfigPanel, Ext.grid.GridPanel, {
    refreshData: function () {
        var store = this.getStore();
        store.load();
    },
    onEdit: function (record) {
        //console.debug(params);
        var dlg = new Toc.CaptureDialog(this.params);
        dlg.setTitle('Processus de Capture sur le serveur  ' + this.params.src_db_host);

        dlg.on('saveSuccess', function () {
            this.start();
        }, this);

        this.stop();
        dlg.show(this.params, this.owner);
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
        this.started = true;
        this.refreshData();

    },
    stop: function () {
        this.started = false;
        this.refreshData();
        if(this.interval)
        {
            clearInterval(this.interval);
        }
        else
        {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle,"No job defined !!!");
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
                var index = this.getStore().getAt(row).get('index');

                switch (action) {
                    case 'status-off':
                        switch(index)
                        {
                            case 0:
                                this.stopCapture(index);
                                break;
                            case 1:
                                this.stopDatapump(index);
                                break;
                            case 2:
                                this.stopReplicat(index);
                                break;
                        }
                        break;
                    case 'status-on':
                        switch(index)
                        {
                            case 0:
                                this.startCapture(index);
                                break;
                            case 1:
                                this.startDatapump(index);
                                break;
                            case 2:
                                this.startReplicat(index);
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

    stopDatapump: function (index) {
        this.getEl().mask('Arret du processus de capture ...');
        this.stop();
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'stop_datapump',
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
    },
    startDatapump: function (index) {
        this.getEl().mask('Demarrage du processus de propagation ...');
        this.stop();
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'start_datapump',
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
    },

    startReplicat: function (index) {
        this.getEl().mask('Demarrage du processus de replication ...');
        this.stop();
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'start_replicat',
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
    },

    stopReplicat: function (index) {
        this.getEl().mask('Arret du processus de replication ...');
        this.stop();
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'stop_replicat',
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

Toc.CaptureDialog = function (params) {
    config = {};

    config.title = 'Capture';
    config.layout = 'fit';
    config.modal = true;
    config.width = 800;
    config.height = 600;
    config.iconCls = 'icon-tbs-win';
    config.items = this.getContentPanel(params);

    this.addEvents({'saveSuccess': true});

    Toc.CaptureDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.CaptureDialog, Ext.Window, {

    show: function (config, owner) {
        this.owner = owner || null;
        if (config) {
            this.config = config;
        }

        Toc.CaptureDialog.superclass.show.call(this);

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

Toc.GoldenGateDashboardPanel = function (config) {
    var that = this;
    config = config || {};
    //console.log(config.isProduction);
    config.region = 'center';
    config.started = false;
    config.title = 'GoldenGate';
    config.autoHeight = true;
    config.layout = 'column';
    config.loadMask = false;
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            //this.onRefresh();
        },
        show: function (panel) {
            this.buildItems(10000);
            //this.onRefresh();
        },
        render: function (panel) {
            this.buildItems(10000);
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

    if(!config.isProduction)
    {
        config.combo_freq = Toc.content.ContentManager.getFrequenceCombo();

        config.combo_freq.enable();

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
            config.combo_freq
        ];
    }
    else
    {
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


    if(!config.isProduction)
    {
        config.combo_freq.getStore().load();
        //config.categoryCombo.getStore().load();
    }

    var thisObj = this;

    if(!config.isProduction)
    {
        config.combo_freq.on('select', function (combo, record, index) {
            if(thisObj.started)
                thisObj.onStop();
            //var category = thisObj.categoryCombo.getValue();
            var freq = thisObj.combo_freq.getValue();
            thisObj.buildItems(freq);
        });
    }

    Toc.GoldenGateDashboardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.GoldenGateDashboardPanel, Ext.Panel, {
    onAdd: function () {
        var dlg = new Toc.GoldenGateConfigDialog();
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

        var frequence = freq || 5000;

        this.getEl().mask('Chargement ...');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'databases',
                action: 'list_oggconfig',
                where: this.isProduction ? "g.id in (2,3)" : ''
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

                        var panel = new Toc.GoldenGateConfigPanel(ogg);
                        //var panel = new Toc.TopWaitClassPanel(db);
                        this.add(panel);
                        this.panels[i] = panel;
                        //panel.buildItems(db);
                        this.doLayout();
                        i++;
                    }

                    this.combo_freq.getStore().load();
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
        this.topToolbar.items.items[4].setHandler(this.onStart,this);
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
        this.topToolbar.items.items[4].setHandler(this.onStop,this);
        this.topToolbar.items.items[4].setIconClass('stop');
    }
});

Toc.GoldenGateConfigDialog = function(config) {

    config = config || {};

    config.id = 'ggsconfig_dialog-win';
    config.title = 'Editer une Configuration Goldengate';
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

    Toc.GoldenGateConfigDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.GoldenGateConfigDialog, Ext.Window, {

    show: function (databases_id,event) {
        //this.frmConfig.form.baseParams['databases_id'] = databases_id;
        //this.frmConfig.form.baseParams['event'] = event;
        Toc.GoldenGateConfigDialog.superclass.show.call(this);

        var fs_src = new Ext.form.FieldSet({
            autoScroll : true,
            title : 'Source',
            autoHeight:true,
            border : true,
            layout : 'form',
            items : [
                new Toc.content.ContentManager.getOracleConnexionsCombo({name:'src_database',valueField : 'id'}),
                {xtype:'textfield', fieldLabel: 'OGG DIR', name: 'oggdir_src', id: 'oggdir_src',allowBlank:false,width:410},
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