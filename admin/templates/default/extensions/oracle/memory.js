Toc.SharedPoolPanel = function (params) {
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

            that.pnlSqlAreaUsage.setHeight(that.getInnerHeight()/2);
            that.pnlParameters.setHeight(that.getInnerHeight()/2);
            that.pnlSql.setHeight(that.getInnerHeight()/2);
            that.doLayout(true, true);

            that.pnlSqlAreaUsage.onRefresh();
            that.pnlParameters.getStore().load();
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
            if(that.pnlSql && that.pnlSqlAreaUsage)
            {
                that.pnlSqlAreaUsage.setHeight(that.getInnerHeight()/2);
                that.pnlParameters.setHeight(that.getInnerHeight()/2);
                that.pnlSql.setHeight(that.getInnerHeight()/2);

                that.doLayout(true, true);
            }
        },
        scope: this
    };

    //config.items = [config.pnlSqlAreaUsage,config.pnlSql];

    Toc.SharedPoolPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SharedPoolPanel, Ext.Panel, {
    buildItems: function (params) {
        //console.log('pnlSqlAreaUsage buildItems');
        var that = this;

        var node = params.node;

        that.pnlParameters = new Toc.ParameterGrid({columnWidth : 0.4,scope : 'shared_pool',sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
        that.pnlSqlAreaUsage = new Toc.SqlAreaUsageGrid({columnWidth : 0.6,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
        that.pnlSql = new Toc.SqlGrid({columnWidth : 1,label: node.attributes.label, databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

        that.pnlSqlAreaUsage.on('rowclick', function (grid, rowIndex, eventObject) {

                var record = grid.getStore().getAt(rowIndex);
                //console.debug(record);
                var store = that.pnlSql.getStore();
                store.baseParams['user_id'] = record.data.user_id;
                that.add(that.pnlSql);
                //that.pnlSqlAreaUsage.setHeight(that.getInnerHeight()/2);
                //that.pnlParameters.setHeight(that.getInnerHeight()/2);
                //that.pnlSql.setHeight(that.getInnerHeight()/2);
                //that.pnlSql.show();
                //that.doLayout(true, true);
                store.reload();
            }
        );

        that.add(that.pnlSqlAreaUsage);
        that.add(that.pnlParameters);
        that.add(that.pnlSql);
        that.doLayout(true, true);
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
            module: 'oracle_perf',
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
            module: 'oracle_perf',
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
            module: 'oracle_perf',
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
                            balloonText: config.graphTitle + ' : ' + "[[value]]",
                            "title": config.graphTitle || 'No Title',
                            type: "line",
                            "valueField": "allo",
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
                action: 'list_memhisto',
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.port,
                db_host: this.host,
                pool: this.pool,
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
                            balloonText: config.graphTitle + ' : ' + "[[value]]",
                            "title": config.graphTitle,
                            type: "line",
                            "valueField": "taille",
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
                action: 'list_sgahisto',
                db_user: this.db_user,
                db_pass: this.db_pass,
                db_port: this.port,
                pool : this.pool,
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
            module: 'oracle_perf',
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
            module: 'oracle_perf',
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
            module: 'oracle_perf',
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
            if(that.pnlPgaAlloc && that.pnlSgaAlloc)
            {
                that.pnlPgaAlloc.setHeight(that.getInnerHeight()/2);
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
                that.pnlPgaAlloc.refreshData();
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

        that.pnlPgaAlloc = new Toc.MemoryAllocationChart({graphTitle : 'PGA',pool : 'pga',columnWidth: 1,label: 'PGA Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
        that.pnlSgaAlloc = new Toc.MemoryAllocationChart({graphTitle : 'SGA',pool : 'sga',columnWidth: 1,label: 'SGA Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

        //that.pnlSgaAlloc = new Toc.SgaAllocationChart({columnWidth: 1,label: 'SGA Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});
        //that.pnlSgaResize = new Toc.SgaResizePanel({width : '100%',sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
        //that.pnlMemoryResize = new Toc.MemoryResizePanel({width : '100%',sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

        that.add(that.pnlPgaAlloc);
        that.pnlPgaAlloc.setHeight(that.getInnerHeight()/2);
        that.doLayout(true, true);

        that.add(that.pnlSgaAlloc);
        that.pnlSgaAlloc.setHeight(that.getInnerHeight()/2);
        that.doLayout(true, true);

        that.pnlPgaAlloc.refreshData();
        that.pnlSgaAlloc.refreshData();
    }
});

Toc.SgaAllocationPanel = function (params) {
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
            if(that.pnlSgaUsage && that.pnlBufferAlloc)
            {
                that.pnlSgaUsage.setHeight(that.getInnerHeight()/3);
                that.pnlBufferAlloc.setHeight(that.getInnerHeight()/3);
                that.pnlSharedAlloc.setHeight(that.getInnerHeight()/3);
                that.pnlLargeAlloc.setHeight(that.getInnerHeight()/3);
                that.pnlJavaAlloc.setHeight(that.getInnerHeight()/3);
                that.pnlStreamsAlloc.setHeight(that.getInnerHeight()/3);
            }
        },
        scope: this
    };

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: function (panel) {
                that.pnlSgaUsage.getStore().reload();
                that.pnlBufferAlloc.refreshData();
                that.pnlSharedAlloc.refreshData();
                that.pnlLargeAlloc.refreshData();
                that.pnlJavaAlloc.refreshData();
                that.pnlStreamsAlloc.refreshData();
            },
            scope: this
        }
    ];

    Toc.SgaAllocationPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SgaAllocationPanel, Ext.Panel, {
    buildItems: function (params) {
        var that = this;

        var node = params.node;

        that.pnlSgaUsage = new Toc.SgaUsageGrid({columnWidth: 0.5,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

        that.add(that.pnlSgaUsage);
        that.pnlSgaUsage.setHeight(that.getInnerHeight()/3);
        that.doLayout(true, true);
        //that.pnlSgaUsage.refreshData();

        that.pnlBufferAlloc = new Toc.SgaAllocationChart({graphTitle : 'Buffer Cache',pool : 'buffer_cache',columnWidth: 0.5,label: 'Buffer Cache Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

        that.add(that.pnlBufferAlloc);
        that.pnlBufferAlloc.setHeight(that.getInnerHeight()/3);
        that.doLayout(true, true);
        that.pnlBufferAlloc.refreshData();

        that.pnlSharedAlloc = new Toc.SgaAllocationChart({graphTitle : 'Shared Pool',pool : 'shared',columnWidth: 0.5,label: 'Shared Pool Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

        that.add(that.pnlSharedAlloc);
        that.pnlSharedAlloc.setHeight(that.getInnerHeight()/3);
        that.doLayout(true, true);
        that.pnlSharedAlloc.refreshData();

        that.pnlLargeAlloc = new Toc.SgaAllocationChart({graphTitle : 'Large Pool',pool : 'large',columnWidth: 0.5,label: 'Large Pool Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

        that.add(that.pnlLargeAlloc);
        that.pnlLargeAlloc.setHeight(that.getInnerHeight()/3);
        that.doLayout(true, true);
        that.pnlLargeAlloc.refreshData();

        that.pnlJavaAlloc = new Toc.SgaAllocationChart({graphTitle : 'Java Pool',pool : 'java',columnWidth: 0.5,label: 'Java Pool Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

        that.add(that.pnlJavaAlloc);
        that.pnlJavaAlloc.setHeight(that.getInnerHeight()/3);
        that.doLayout(true, true);
        that.pnlJavaAlloc.refreshData();

        that.pnlStreamsAlloc = new Toc.SgaAllocationChart({graphTitle : 'Streams Pool',pool : 'streams',columnWidth: 0.5,label: 'Streams Pool Allocation', sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, typ: node.attributes.typ});

        that.add(that.pnlStreamsAlloc);
        that.pnlStreamsAlloc.setHeight(that.getInnerHeight()/3);
        that.doLayout(true, true);
        that.pnlStreamsAlloc.refreshData();
    }
});

Toc.SgaUsageGrid = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.started = false;
    config.region = 'center';
    config.loadMask = true;
    config.width = '100%';
    //config.border = true;
    //config.autoHeight = true;
    config.title = 'SGA Stats';
    //config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (panel) {
            panel.getStore().load();
        }
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.ORACLE_URL,
        baseParams: {
            action: 'list_sgausage',
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
            id: 'pool'
        }, [
            'pool',
            'total',
            'used',
            'pct'
        ]),
        listeners: {
        },
        autoLoad: true
    });

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'pool', header: 'Pool', dataIndex: 'pool', width: 50, align: 'left',renderer:render},
        { id: 'pct', header: '% Usage', dataIndex: 'pct', width: 50, renderer: Toc.content.ContentManager.renderUsagePct, align: 'center'}
    ]);

    Toc.SgaUsageGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SgaUsageGrid, Ext.grid.GridPanel, {
});

Toc.PxBufferAdviceGrid = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Buffer Advice';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    config.listeners = {
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'oracle_perf',
            action: 'list_pxbufferadvice',
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

    this.addEvents({'selectchange': true});
    Toc.PxBufferAdviceGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PxBufferAdviceGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    }
});
