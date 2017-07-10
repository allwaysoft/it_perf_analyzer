Toc.content.ContentManager.getOracleConnexionsCombo = function (config) {
    var dsOracleConnexionsCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_databasesconnexions'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            fields: [
                'id',
                'oracle_connexion', 'label_database'
            ]
        }),
        autoLoad: true
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Database',
        store: dsOracleConnexionsCombo,
        displayField: 'label_database',
        valueField: config ? config.valueField || 'oracle_connexion' : 'oracle_connexion',
        hiddenName: config ? config.name || 'oracle_connexion' : 'oracle_connexion',
        name: 'ORACLE_CONNEXION',
        mode: 'local',
        width: 410,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.content.ContentManager.getDatabasesCategoryCombo = function () {

    var dsDatabasesCategoryCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_categories'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'event'
        }, [
            'key',
            'value'
        ]),
        autoLoad: false
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Categorie',
        store: dsDatabasesCategoryCombo,
        displayField: 'value',
        valueField: 'key',
        hiddenName: 'category',
        name: 'category',
        mode: 'local',
        width: 410,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.content.ContentManager.getDatabaseSchemasCombo = function (config) {

    var dsSchemas = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_allusers',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'username'
        }, [
            'username'
        ]),
        autoLoad: false
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Categorie',
        store: dsSchemas,
        displayField: 'username',
        valueField: 'username',
        hiddenName: 'category',
        name: 'schema',
        mode: 'local',
        width: 250,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.content.ContentManager.getFrequenceCombo = function () {

    var dsCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_freq'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'value',
            'display'
        ]),
        autoLoad: false
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Frequence',
        store: dsCombo,
        displayField: 'display',
        valueField: 'value',
        hiddenName: 'frequence',
        name: 'hfrequence',
        mode: 'local',
        width: 100,
        //value : 5000,
        disabled: true,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.content.ContentManager.getArchDestCombo = function () {

    var DSArchDest = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_dest'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'value',
            'display'
        ]),
        autoLoad: true
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'ID Destination',
        store: DSArchDest,
        displayField: 'display',
        valueField: 'value',
        hiddenName: 'dest_id',
        name: 'destid',
        mode: 'local',
        width: 100,
        //value : 5000,
        disabled: true,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.content.ContentManager.getTbsCombo = function (config) {
    var tbsStore = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_tbscombo',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            id: 'tablespace_name',
            fields: ['tablespace_name', 'content']
        }),
        listeners: {
            load: function (store, records, opt) {
                if (config.panel) {
                    config.panel.getEl().unmask();
                }
            },
            beforeload: function (store, opt) {
                if (config.panel) {
                    config.panel.getEl().mask('Chargement des espaces logiques ....');
                }
            }, scope: this
        },
        autoLoad: config.autoLoad || true
    });

    return new Ext.form.ComboBox({
        typeAhead: true,
        name: 'tbs',
        autoSelect: true,
        width: 310,
        listWidth: 310,
        hiddenName: "tbs",
        allowBlank: false,
        //id: 'tbs',
        fieldLabel: 'Espace logique',
        triggerAction: 'all',
        mode: 'local',
        emptyText: 'Selectionner un espace logique',
        store: tbsStore,
        valueField: 'tablespace_name',
        displayField: 'content'
    });
};

Toc.content.ContentManager.getTempTbsCombo = function (config) {
    var tbsStore = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_temptbs',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            id: 'tablespace_name',
            fields: ['tablespace_name', 'content']
        }),
        listeners: {
            load: function (store, records, opt) {
                if (config.panel) {
                    config.panel.getEl().unmask();
                }
            },
            beforeload: function (store, opt) {
                if (config.panel) {
                    config.panel.getEl().mask('Chargement des espaces logiques ....');
                }
            }, scope: this
        },
        autoLoad: true
    });

    return new Ext.form.ComboBox({
        typeAhead: true,
        name: 'temptbs',
        autoSelect: true,
        width: 310,
        listWidth: 310,
        hiddenName: "temp",
        allowBlank: false,
        //id: 'temptbs',
        fieldLabel: 'Espace logique temp',
        triggerAction: 'all',
        mode: 'local',
        emptyText: 'Selectionner un espace logique temporarire',
        store: tbsStore,
        valueField: 'tablespace_name',
        displayField: 'content'
    });
};

Toc.content.ContentManager.getProfilesCombo = function (config) {
    var tbsStore = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_profiles',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            id: 'profile',
            fields: ['profile']
        }),
        listeners: {
            load: function (store, records, opt) {
                if (config.panel) {
                    config.panel.getEl().unmask();
                }
            },
            beforeload: function (store, opt) {
                if (config.panel) {
                    config.panel.getEl().mask('Chargement des profiles ....');
                }
            }, scope: this
        },
        autoLoad: true
    });

    return new Ext.form.ComboBox({
        typeAhead: true,
        name: 'profile',
        autoSelect: true,
        width: 310,
        listWidth: 310,
        //id: 'profile',
        fieldLabel: 'Profile',
        allowBlank: false,
        triggerAction: 'all',
        mode: 'local',
        emptyText: 'Selectionner un Profile',
        store: tbsStore,
        valueField: 'profile',
        displayField: 'profile'
    });
};

Toc.content.ContentManager.getRolesCombo = function (config) {
    var tbsStore = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_roles',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            id: 'role',
            fields: ['role']
        }),
        listeners: {
            load: function (store, records, opt) {
                if (config.panel) {
                    config.panel.getEl().unmask();
                }
            },
            beforeload: function (store, opt) {
                if (config.panel) {
                    config.panel.getEl().mask('Chargement des roles ....');
                }
            }, scope: this
        },
        autoLoad: true
    });

    return new Ext.ux.form.LovCombo({
        typeAhead: true,
        name: 'roles',
        autoSelect: true,
        width: 310,
        listWidth: 310,
        allowBlank: false,
        //id: 'profile',
        fieldLabel: 'Roles',
        triggerAction: 'all',
        mode: 'local',
        emptyText: 'Selectionner un Role',
        store: tbsStore,
        valueField: 'role',
        displayField: 'role'
    });
};

Toc.SgaResizePanel = function (config) {
    config = config || {};
    config.loadMask = false;
    config.border = true;
    config.title = 'SGA resize OPS';
    config.autoHeight = true;
    config.viewConfig = {
        emptyText: TocLanguage.gridNoRecords, forceFit: true
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
            'oper_type',
            'status',
            'nbre'
        ]),
        autoLoad: true
    });

    config.cm = new Ext.grid.ColumnModel([
        {header: '', dataIndex: 'icon', width: 5},
        {id: 'component', header: 'Component', dataIndex: 'component', width: 40},
        {header: 'Oeration', dataIndex: 'oper_type', width: 25},
        {header: 'Status', align: 'center', dataIndex: 'status', width: 20},
        {header: 'Nbre', align: 'center', dataIndex: 'nbre', width: 10}
    ]);
    config.autoExpandColumn = 'component';

    var thisObj = this;

    Toc.SgaResizePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SgaResizePanel, Ext.grid.GridPanel, {
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

    config.combo_freq = Toc.content.ContentManager.getFrequenceCombo();
    //config.categoryCombo = Toc.content.ContentManager.getDatabasesCategoryCombo();

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
        },
        '-',
        config.combo_freq
    ];

    config.combo_freq.getStore().load();

    var thisObj = this;

    config.combo_freq.on('select', function (combo, record, index) {
        thisObj.onStop();
        var freq = thisObj.combo_freq.getValue();
        thisObj.buildItems(freq);
    });

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

Toc.databaseSpaceDashboard = function (config) {
    var that = this;
    config = config || {};
    //console.log(config.isProduction);
    config.region = 'center';
    config.started = false;
    config.layout = 'Column';
    config.loadMask = false;
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
                this.buildItems('all', 10000);
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
                if(thisObj.started)
                    thisObj.onStop();
                thisObj.combo_freq.enable();
                var category = record.data.key;
                var freq = thisObj.combo_freq.getValue();
                thisObj.buildItems(category, freq);
            });

            config.combo_freq.on('select', function (combo, record, index) {
                if(thisObj.started)
                    thisObj.onStop();
                var category = thisObj.categoryCombo.getValue();
                var freq = thisObj.combo_freq.getValue();
                thisObj.buildItems(category, freq);
            });
        }
        else {
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
                    //text: this.started ? 'Stop' : 'Start',
                    text: '',
                    iconCls: this.started ? 'stop' : 'play',
                    handler: this.started ? this.onStop : this.onStart,
                    scope: this
                }
            ];
        }
    }

    Toc.databaseSpaceDashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databaseSpaceDashboard, Ext.Panel, {
    onAdd: function () {
        var dlg = this.owner.createDatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(null, path, null, this.owner);
    },

    onRefresh: function () {
        var category = config.isProduction ? 'all' : this.categoryCombo.getValue();
        this.buildItems(category);
    },

    buildItems: function (category, freq) {
        if (this.items) {
            this.removeAll(true);
        }

        this.panels = [];

        var frequence = freq || 10000;

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
                        db.width = '25%';

                        var panel = new Toc.SpaceCriticalPanel(db);
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

        this.started = true;
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

Toc.DatabaseDashboard = function (config) {
    var that = this;
    config = config || {};
    //console.log(config.isProduction);
    config.region = 'center';
    //config.header = false;
    config.layout = 'Column';
    config.loadMask = false;
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
                this.buildItems('all', 15000);
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
                config.combo_freq,
                '->',
                config.categoryCombo
            ];

            config.combo_freq.getStore().load();
            config.categoryCombo.getStore().load();

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
        else {
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
                }
            ];
        }
    }

    Toc.DatabaseDashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabaseDashboard, Ext.Panel, {
    onAdd: function () {
        var dlg = this.owner.createDatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(null, path, null, this.owner);
    },

    onRefresh: function () {
        var category = config.isProduction ? 'all' : this.categoryCombo.getValue();
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
                        db.width = '25%';

                        var panel = new Toc.DatabaseDashboardPanel(db);
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

Toc.DatabasesDashboardPanel = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.border = true;
    config.layout = 'Column';
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            //console.log('activate');
        },
        deactivate: function (panel) {
            //console.log('deactivate');
        },
        scope: this
    };

    switch (config.status) {
        case 'down':
            config.title = config.label + '  ==>  Hors ligne : ' + config.comments;
            break;
        case 'up':
        case 'warning':
            config.title = config.label + '  ==>  En ligne depuis ' + config.startup_time + ' sur le serveur ' + config.host + ' avec une taille globale de ' + config.db_size + ' GB';
            break;
        default :
            config.title = '?????';
            break;
    }


    config.autoHeight = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};
    //config.items = this.buildItems(config);

    config.tools = [
        {
            id: 'refresh',
            qtip: 'Refresh form Data',
            // hidden:true,
            handler: function (event, toolEl, panel) {
                // refresh logic
            }
        },
        {
            id: 'edit',
            qtip: 'edit',
            handler: function (event, toolEl, panel) {
                var dlg = new Toc.databases.DatabasesDialog();
                dlg.setTitle(panel.label);

                dlg.on('saveSuccess', function () {
                    this.onRefresh();
                }, this);

                var params = {
                    databases_id: panel.databases_id,
                    label: panel.label,
                    servers_id: panel.servers_id,
                    server_user: panel.server_user,
                    server_pass: panel.server_pass,
                    server_port: panel.server_port,
                    db_user: panel.db_user,
                    db_pass: panel.db_pass,
                    db_port: panel.port,
                    sid: panel.sid,
                    host: panel.host,
                    typ: panel.typ
                };

                dlg.show(params, null, null, null);
            }
        }
    ];

    Toc.DatabasesDashboardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabasesDashboardPanel, Ext.Panel, {
    buildItems: function (params) {
        params.owner = this.owner;

        switch (this.status) {
            case 'up':
            case 'warning':
                if (params.log_applied && params.role.toLowerCase() == 'primary') {
                    this.add(new Toc.DataguardPanel(params));
                }
                else {
                    this.add(new Toc.GeneralPanel(params));
                }

                this.add(new Toc.AvailabilityPanel(params));
                this.add(new Toc.TbsCriticalPanel(params));
                this.add(new Toc.FsCriticalPanel(params));

                //console.log('????????');
                //this.add(new Toc.SpaceCriticalPanel(params));
                //this.add(new Toc.TopEventsPanel(params));
                //this.add(new Toc.TopWaitingSessionsPanel(params));
                //this.add(new Toc.LibrayCachePanel(params));
                break;
            default :
                break;
        }
    },
    onEdit: function (record) {
        var dlg = this.owner.createDatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(record.json, path, this.owner);
    }
});

Toc.SpaceCriticalPanel = function (params) {
    var that = this;
    config = {};
    config.started = false;
    config.params = params;
    config.region = 'center';
    config.border = true;
    config.width = config.params.width || '25%';
    config.layout = 'column';
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

    Toc.SpaceCriticalPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SpaceCriticalPanel, Ext.Panel, {
    buildItems: function (params) {
        //console.debug(params);
        params.owner = this.owner;
        params.width = '50%';

        this.tbs = new Toc.TopTbsPanel(params);
        this.fs = new Toc.TopFsPanel(params);
        //this.osperf = new Toc.OsPerfPanel(params);

        this.add(this.tbs);
        this.add(this.fs);

        var conf = {
            status: 'up',
            width: '50%',
            autoExpandColumn: 'event',
            label: 'Waits Events',
            body_height: '120px',
            freq: params.freq,
            hideHeaders: true,
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
    },

    start: function () {
        this.tbs.start();
        this.fs.start();
        //this.setTitle(this.params.label + ' ( |> )');
    },

    stop: function () {
        this.tbs.stop();
        this.fs.stop();
        //this.setTitle(this.params.label + ' ( || )');
    }
});

Toc.DatabaseDashboardPanel = function (params) {
    var that = this;
    config = {};
    config.params = params;
    config.region = 'center';
    config.border = true;
    config.width = config.params.width || '33%';
    config.layout = 'column';
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

    Toc.DatabaseDashboardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabaseDashboardPanel, Ext.Panel, {
    buildItems: function (params) {
        //console.debug(params);
        params.owner = this.owner;
        params.width = '25%';

        this.tbs = new Toc.TopTbsPanel(params);
        this.fs = new Toc.TopFsPanel(params);
        //this.osperf = new Toc.OsPerfPanel(params);

        this.add(this.tbs);
        this.add(this.fs);

        var conf = {
            status: 'up',
            width: '50%',
            autoExpandColumn: 'event',
            label: 'Waits Events',
            body_height: '120px',
            freq: params.freq,
            hideHeaders: true,
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

        //this.dbperf = new Toc.TopEventsPanel(conf);
        this.dbperf = new Toc.TopEventsPanelCharts(conf);
        //this.add(this.osperf);
        this.add(this.dbperf);
    },

    start: function () {
        this.tbs.start();
        this.fs.start();
        //this.osperf.start();
        this.dbperf.start();
        this.setTitle(this.params.label + ' ( Monitoring )');
    },

    stop: function () {
        this.tbs.stop();
        this.fs.stop();
        //this.osperf.stop();
        this.dbperf.stop();
        this.setTitle(this.params.label);
    }
});

Toc.TbsCriticalPanel = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.width = '25%';
    //config.border = true;
    config.autoHeight = true;
    config.title = 'TBS critiques';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    var data = [];

    if (config.tbs && config.tbs != null) {
        //console.debug(config.fs);
        var result = "";
        var res = config.tbs.split("#");
        //console.debug(res);
        if (res.length > 0) {
            var i = 0;
            while (i < res.length) {
                var tb = res[i].split(";");
                var name = tb[0];
                var pct = tb[1];
                var rest = tb[2];

                data[i] = [name, pct, rest];

                i++;
            }
        }
    }

    config.ds = new Ext.data.SimpleStore({
        fields: ['name', 'pct_used', 'rest'],
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
        { id: 'name', header: 'TBS', dataIndex: 'name', width: 80},
        { align: 'center', dataIndex: 'rest', width: 70},
        { id: 'pct_used', align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true, width: 60},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'pct_used';

    var thisObj = this;

    config.tools = [];

    Toc.TbsCriticalPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TbsCriticalPanel, Ext.grid.GridPanel, {

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

Toc.TopEventsPanel = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    //config.boxMinHeight = 500;
    config.loadMask = false;
    config.width = this.params.width || '25%';
    //config.border = true;
    config.autoHeight = true;
    //config.title = 'Top Events';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: ''};
    config.title = config.label;

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_topevents',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid,
            tbs: config.tbs
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'id'
        }, [
            'event',
            'pct_used'
        ]),
        autoLoad: config.status != 'down' ? true : false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-search-record', qtip: 'Diagnostiquer'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'event', header: 'Event', dataIndex: 'event', width: 190, renderer: render},
        { id: 'pct_used', align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderEventProgress, sortable: true, width: 50},
        config.rowActions
    ]);

    var thisObj = this;

    config.task = {
        run: function () {
            thisObj.getStore().load();
        },
        interval: config.freq / 5 || 5000 //5 second
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
                    panel.setTitle(panel.label + ' ( Monitoring )');
                }
                else {
                    panel.runner.stop(panel.task);
                    panel.setTitle(panel.label);
                }
            }
        }
    ];

    Toc.TopEventsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TopEventsPanel, Ext.grid.GridPanel, {

    onEdit: function (record) {

        var event = record.data.event;

        var params = {
            label: this.label,
            servers_id: this.servers_id,
            databases_id: this.databases_id,
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host || this.db_host,
            db_port: this.port || this.db_port,
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_host: this.host || this.db_host,
            db_sid: this.sid || this.db_sid,
            sid: this.sid || this.db_sid,
            typ: this.typ
        };

        var dlg = new Toc.DatabasesDialog();
        //var path = this.owner.getCategoryPath();
        //dlg.setTitle(record.get("content_name"));
        dlg.setTitle(this.db_host + '/' + this.db_sid);

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        //var panels = [];
        //panels[0] = 'sessions';

        dlg.showDetails(params, null, this.owner);
        //dlg.show(params, null, this.owner, panels);
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
        this.runner.start(this.task);
        this.setTitle(this.label + ' ( Monitoring )');
    },

    stop: function () {
        this.runner.stop(this.task);
        this.setTitle(this.label);
    }
});

Toc.TopTbsPanel = function (config) {
    this.params = config;
    this.owner = config.owner;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = false;
    config.width = this.params.width || '50%';
    config.autoHeight = true;
    config.count = 0;
    config.reqs = 0;
    config.title = 'Tablespaces';
    config.hideHeaders = true;
    config.viewConfig = {emptyText: 'Aucune donnee recue ...', forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
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
            load: function (store, records, opt) {
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

    config.tools = [];

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
                thisObj.getStore().load();

                /*panel.started = !panel.started;

                 if (panel.started) {
                 panel.runner.start(panel.task);
                 }
                 else {
                 panel.runner.stop(panel.task);
                 }*/
            }
        }
    ];

    Toc.TopTbsPanel.superclass.constructor.call(this, config);
    this.getView().scrollOffset = 0;
};

Ext.extend(Toc.TopTbsPanel, Ext.grid.GridPanel, {
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

Toc.TopWaitClassPanel = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.region = 'center';
    config.width = '25%';
    config.autoHeight = true;
    config.title = 'Top Wait Class';

    switch (config.status) {
        case 'down':
            config.title = config.label + '  ==>  Hors ligne : ' + config.comments;
            break;
        case 'up':
        case 'warning':
            config.title = config.label;
            break;
        default :
            config.title = '?????';
            break;
    }

    config.store = new Ext.data.JsonStore({
        fields: ['date', 'application', 'idle', 'administrative', 'userio', 'other', 'network', 'scheduler', 'commit', 'systemio', 'concurrency', 'configuration'],
        data: []
    });

    config.items = {
        xtype: 'stackedbarchart',
        store: config.store,
        yField: 'year',
        xAxis: new Ext.chart.NumericAxis({
            stackingEnabled: true,
            labelRenderer: Ext.util.Format.usMoney
        }),
        series: [
            {
                xField: 'application',
                displayName: 'application'
            },
            {
                xField: 'idle',
                displayName: 'idle'
            },
            {
                xField: 'administrative',
                displayName: 'administrative'
            },
            {
                xField: 'userio',
                displayName: 'user i/o'
            },
            {
                xField: 'other',
                displayName: 'other'
            },
            {
                xField: 'network',
                displayName: 'network'
            },
            {
                xField: 'scheduler',
                displayName: 'scheduler'
            },
            {
                xField: 'commit',
                displayName: 'commit'
            },
            {
                xField: 'systemio',
                displayName: 'system i/o'
            },
            {
                xField: 'concurrency',
                displayName: 'concurrency'
            },
            {
                xField: 'configuration',
                displayName: 'configuration'
            }
        ]
    };

    var thisObj = this;

    config.task = {
        run: function () {
            thisObj.getStore().load();
        },
        interval: 5000 //5 second
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
                    panel.setTitle(panel.label + ' ( Monitoring )');
                }
                else {
                    panel.runner.stop(panel.task);
                    panel.setTitle(panel.label);
                }
            }
        }
    ];

    Toc.TopWaitClassPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TopWaitClassPanel, Ext.Panel, {

    onRefresh: function () {
        this.getStore().reload();
    },

    start: function () {
        this.runner.start(this.task);
        this.setTitle(this.label + ' ( Monitoring )');
    },

    stop: function () {
        this.runner.stop(this.task);
        this.setTitle(this.label);
    }
});

Toc.PerfPanel = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.width = '25%';
    //config.border = true;
    config.autoHeight = true;
    config.title = 'Top Events';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_perf',
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
            id: 'key'
        }, [
            'key',
            'label',
            'value'
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

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'event', header: 'Event', dataIndex: 'event', width: 200, renderer: render},
        { id: 'pct_used', align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderEventProgress, sortable: true, width: 50}
    ]);
    //config.autoExpandColumn = 'pct_used';

    var thisObj = this;

    config.task = {
        run: function () {
            thisObj.getStore().load();
        },
        interval: 5000 //5 second
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

    Toc.PerfPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PerfPanel, Ext.grid.GridPanel, {

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

Toc.GeneralPanel = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.width = '25%';
    config.autoHeight = true;
    config.title = 'General';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    var data = [];

    data[0] = ['Status', config.status || ''];
    data[1] = ['Up Depuis', config.startup_time || ''];
    data[2] = ['Version', config.version || ''];
    data[3] = ['Host', config.host || ''];
    data[4] = ['Taille', config ? config.db_size + ' GB' : ''];

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

    Toc.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.GeneralPanel, Ext.grid.GridPanel, {

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

Toc.TbsTreePanel = function (config) {

    config = config || {};

    config.region = 'west';
    config.border = false;
    config.autoScroll = true;
    config.containerScroll = true;
    config.split = true;
    config.autoHeight = true;
    config.width = 170;
    //config.enableDD = true;
    config.rootVisible = true;

    config.root = new Ext.tree.AsyncTreeNode({
        text: config.tbs || 'Espace logique',
        icon: 'templates/default/images/icons/16x16/folder_drafts.png',
        draggable: false,
        id: '0',
        expanded: true
    });
    config.currentCategoryId = 632;

    config.loader = new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: true,
        baseParams: {
            module: 'databases',
            action: 'load_tbs_tree',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_host: config.host,
            db_sid: config.sid,
            tbs: config.tbs
        },
        listeners: {
            load: function () {
                this.expandAll();
                var category = this.currentCategoryId || 632;
                var count = this.nodeHash[category].attributes.count || 0;
                this.setCategoryId(category, count);
            },
            scope: this
        }
    });

    config.tbar = [
        {
            text: TocLanguage.btnRefresh,
            iconCls: 'refresh',
            handler: this.refresh,
            scope: this
        }
    ];

    config.listeners = {
        "click": this.onCategoryNodeClick
    };

    this.addEvents({'selectchange': true});

    Toc.TbsTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TbsTreePanel, Ext.tree.TreePanel, {

    setCategoryId: function (categoryId, count) {
        var currentNode = this.getNodeById(categoryId);
        currentNode = currentNode || this.getRootNode();
        currentNode.select();
        this.currentCategoryId = currentNode.id;

        this.fireEvent('selectchange', this.currentCategoryId, count);
    },

    getRolesPath: function (node) {
        var cpath = [];
        node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;

        while (node.id > 0) {
            cpath.push(node.id);
            node = node.parentNode;
        }

        return cpath.reverse().join('_');
    },

    onCategoryNodeClick: function (node) {
        node.expand();
        this.setCategoryId(node.id, node.attributes.count);
    },

    getCategoriesPath: function (node) {
        var cpath = [];
        node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;

        while (node.id > 0) {
            cpath.push(node.id);
            node = node.parentNode;
        }

        return cpath.reverse().join('_');
    },

    refresh: function () {
        this.root.reload();
    }
});

Toc.TbsBrowser = function (params) {
    config = {};

    config.title = 'TBS browser';
    config.layout = 'fit';
    config.modal = true;
    config.iconCls = 'icon-tbs-win';
    config.items = this.getContentPanel(params);

    this.addEvents({'saveSuccess': true});

    Toc.TbsBrowser.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TbsBrowser, Ext.Window, {

    show: function (config, owner) {
        this.owner = owner || null;
        if (config) {
            this.config = config;
        }

        Toc.TbsBrowser.superclass.show.call(this);

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
            tbs: config.tbs
        };
    },

    getContentPanel: function (params) {
        this.datafilesGrid = new Toc.datafilesGrid({tbs: params.tbs, sid: params.db_sid, host: params.db_host, db_port: params.db_port, db_pass: params.db_pass, db_user: params.db_user, owner: params.owner, mainPanel: this, server_user: params.server_user, server_pass: params.server_pass, server_typ: params.server_typ, server_port: params.server_port});
        this.indexesGrid = new Toc.indexesGrid({tbs: params.tbs, sid: params.db_sid, host: params.db_host, db_port: params.db_port, db_pass: params.db_pass, db_user: params.db_user, owner: params.owner, mainPanel: this});
        this.tablesGrid = new Toc.tablesGrid({tbs: params.tbs, sid: params.db_sid, host: params.db_host, db_port: params.db_port, db_pass: params.db_pass, db_user: params.db_user, owner: params.owner, mainPanel: this});
        this.map = new Toc.TbsMap({tbs: params.tbs, sid: params.db_sid, host: params.db_host, db_port: params.db_port, db_pass: params.db_pass, db_user: params.db_user, owner: params.owner, mainPanel: this, file_id: params.file_id || -1});

        this.tabdatabases = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
                this.indexesGrid, this.tablesGrid, this.datafilesGrid, this.map
            ]
        });

        return this.tabdatabases;
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

Toc.JobDialog = function (params) {
    config = {};

    config.layout = 'fit';
    config.width = 600;
    config.height = 200;
    config.modal = true;
    config.closable = false;
    config.iconCls = 'icon-resize-win';
    config.items = this.getContentPanel(params);
    config.listeners = {
        close: function (panel) {
            panel.jobGrid.stop();
        },
        scope: this
    };

    config.buttons = [
        {
            text: TocLanguage.btnClose,
            disabled: true,
            handler: function () {
                this.close();
            },
            scope: this
        }
    ];

    this.addEvents({'finished': true});
    this.addEvents({'success': true});

    Toc.JobDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.JobDialog, Ext.Window, {

    show: function (config, owner) {
        this.owner = owner || null;
        if (config) {
            this.config = config;
        }

        Toc.JobDialog.superclass.show.call(this);

        //this.maximize();

        var params = {
            server_user: config.server_user,
            server_pass: config.server_pass,
            server_port: config.server_port,
            host: config.host,
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_host: config.host,
            db_sid: config.db_sid,
            tbs: config.tbs
        };

        this.jobGrid.start();
    },

    getContentPanel: function (params) {
        this.jobGrid = new Toc.JobGrid({job_name: params.job_name, sid: params.db_sid, host: params.db_host, db_port: params.db_port, db_pass: params.db_pass, db_user: params.db_user, owner: this, mainPanel: this});

        return this.jobGrid;
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
        this.frmMoveTable.add(this.browser);
        this.frmMoveTable.doLayout(false, true);

        this.txtFilename = new Ext.form.TextField({
            fieldLabel: 'Nom Fichier ',
            allowBlank: false,
            width: '95%'
        });
        this.frmMoveTable.add(this.txtFilename);
        this.frmMoveTable.doLayout(false, true);

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
                db_sid: params.db_sid
            },
            deferredRender: false,
            //items: [this.getContentPanel(params)]
            items: []
        });

        return this.frmMoveTable;
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

Toc.DropProcedure = function (panel) {
    if (panel) {
        panel.getEl().mask('Suppression de la procedure ...');
    }

    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'databases',
            action: 'drop_procedure',
            proc_name: panel.proc_name,
            db_user: panel.params.db_user,
            db_pass: panel.params.db_pass,
            db_port: panel.params.db_port,
            db_host: panel.params.db_host,
            db_sid: panel.params.db_sid
        },
        callback: function (options, success, response) {
            if (panel) {
                panel.getEl().unmask();
            }

            var result = Ext.decode(response.responseText);

            if (result.success == true) {
                //this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                //this.close();
            }
            else
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible de finaliser ce Job : " + result.feedback);
        },
        scope: this
    });
};

Toc.tbsGrid = function (config) {
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.loadMask = true;
    config.title = 'Tablespaces';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.listeners = {
        'rowclick': this.onRowClick
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
            module: 'databases',
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
                capture : config.capture,
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

Toc.datafilesGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    config.title = 'Datafiles';
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
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
        { id: 'file_id', header: 'ID', dataIndex: 'file_id', sortable: true},
        { id: 'file_name', header: 'Nom', dataIndex: 'file_name', sortable: true},
        { id: 'tablespace_name', header: 'Tablespace', dataIndex: 'tablespace_name', sortable: true},
        { header: '% Used', align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true},
        { header: '% Total', align: 'center', dataIndex: 'total_pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true},
        { header: 'Taille (MB)', align: 'center', dataIndex: 'size', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Libre (MB)', align: 'center', dataIndex: 'free_mb', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Max (MB)', align: 'center', dataIndex: 'maxsize', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Inc (MB)', align: 'center', dataIndex: 'increment_by', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Auto Ext', align: 'center', dataIndex: 'autoextensible', sortable: true, renderer: renderAuto},
        { id: 'status', header: 'Status', align: 'center', dataIndex: 'status', renderer: renderStatus},
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

Toc.DatabaseDataPanel = function (config) {
    config = config || {};

    config.title = 'General';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.DatabaseDataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabaseDataPanel, Ext.Panel, {
    setTyp: function (typ) {
        var group = Ext.getCmp('typ');
        group.setValue(typ);

        for (i = 0; i < group.items.length; i++) {
            var item = group.items.items[i];

            if (item.inputValue == typ) {
                item.setValue(true);
            }
            else {
                item.setValue(false);
            }
        }
    },
    getServerData: function () {
        return this.serverCombo.getStore().getById(this.serverCombo.getValue());
    },
    loadServers: function (panel) {
        if (panel) {
            this.serverCombo.getStore().on('beforeload', function () {
                panel.getEl().mask('Chargement des serveurs ...');
            }, this);

            this.serverCombo.getStore().on('load', function () {
                panel.getEl().unmask();
            }, this);
        }

        this.serverCombo.getStore().load();
    },
    loadCategories: function (panel) {
        if (panel) {
            this.categoryCombo.getStore().on('beforeload', function () {
                panel.getEl().mask('Chargement des categories ...');
            }, this);

            this.categoryCombo.getStore().on('load', function () {
                panel.getEl().unmask();
            }, this);
        }

        this.categoryCombo.getStore().load();
    },
    setServer: function (servers_id) {
        this.serverCombo.getStore().on('load', function () {
            this.serverCombo.setValue(servers_id);
        }, this);

        this.serverCombo.getStore().load();
    },
    setCategory: function (category) {
        this.categoryCombo.getStore().on('load', function () {
            this.categoryCombo.setValue(category);
        }, this);

        this.categoryCombo.getStore().load();
    },
    getDataPanel: function () {
        this.serverCombo = Toc.content.ContentManager.getServerCombo();
        this.categoryCombo = Toc.content.ContentManager.getDatabasesCategoryCombo();
        this.pnlData = new Ext.Panel({
            layout: 'form',
            border: false,
            autoHeight: true,
            style: 'padding: 6px',
            items: [
                {
                    layout: 'form',
                    border: false,
                    labelSeparator: ' ',
                    columnWidth: .7,
                    autoHeight: true,
                    defaults: {
                        anchor: '97%'
                    },
                    items: [
                        Toc.content.ContentManager.getContentStatusFields(),
                        this.serverCombo,
                        {xtype: 'textfield', fieldLabel: 'Label', name: 'label', id: 'label', allowBlank: false},
                        {xtype: 'textfield', fieldLabel: 'User', name: 'user', id: 'user', allowBlank: false},
                        {xtype: 'textfield', fieldLabel: 'Pass', name: 'pass', id: 'pass', allowBlank: false},
                        {xtype: 'numberfield', fieldLabel: 'Port', name: 'port', id: 'port', width: 200, allowBlank: false},
                        {xtype: 'textfield', fieldLabel: 'SID', name: 'sid', id: 'sid', allowBlank: false},
                        this.categoryCombo
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});

Toc.DatabasesDialog = function (config) {

    config = config || {};

    config.id = 'databases_dialog-win';
    config.title = 'New Database';
    config.layout = 'fit';
    config.width = 465;
    config.height = 330;
    config.maximizable = true;
    config.minimizable = true;
    config.resizable = true;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.buildForm();

    config.buttons = [
        {
            text: TocLanguage.btnSave,
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

    Toc.DatabasesDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabasesDialog, Ext.Window, {

    show: function (json, cId, owner, panels) {
        this.owner = owner || null;
        if (json) {
            this.databasesId = json.databases_id || null;
            this.label = json.label || null;
            this.servers_id = json.servers_id || null;
            this.server_user = json.server_user || null;
            this.server_pass = json.server_pass || null;
            this.server_port = json.server_port || null;
            this.db_user = json.db_user || null;
            this.db_pass = json.db_pass || null;
            this.db_port = json.port || null;
            this.sid = json.sid || null;
            this.host = json.host || null;
            this.typ = json.typ || null;
            this.setTitle(json.label);
        }

        var categoriesId = cId || -1;

        this.frmDatabase.form.reset();
        this.frmDatabase.form.baseParams['databases_id'] = this.databasesId;
        this.frmDatabase.form.baseParams['current_category_id'] = categoriesId;
        Toc.DatabasesDialog.superclass.show.call(this);
        this.editDatabase(this.frmDatabase, panels, json);
    },

    showDetails: function (json, cId, owner) {
        this.owner = owner || null;
        if (json) {
            this.databasesId = json.databases_id || null;
            this.label = json.label || null;
            this.servers_id = json.servers_id || null;
            this.server_user = json.server_user || null;
            this.server_pass = json.server_pass || null;
            this.server_port = json.server_port || null;
            this.db_user = json.db_user || null;
            this.db_pass = json.db_pass || null;
            this.db_port = json.port || null;
            this.sid = json.sid || null;
            this.host = json.host || null;
            this.typ = json.typ || null;
        }

        var categoriesId = cId || -1;

        this.frmDatabase.form.reset();
        this.frmDatabase.form.baseParams['databases_id'] = this.databasesId;
        this.frmDatabase.form.baseParams['current_category_id'] = categoriesId;
        Toc.DatabasesDialog.superclass.show.call(this);
        this.loadDatabase(this.frmDatabase, json);
        //this.setTitle(json.label);
    },

    editDatabase: function (panel, panels, json) {
        if (this.databasesId && this.databasesId > 0) {
            if (panel) {
                panel.getEl().mask('Chargement infos DB....');
            }

            this.frmDatabase.load({
                url: Toc.CONF.CONN_URL,
                params: {
                    module: 'databases',
                    action: 'load_database'
                },
                success: function (form, action) {
                    if (panel) {
                        panel.getEl().unmask();
                    }

                    this.pnlData.setServer(action.result.data.databases_id);
                    this.pnlData.setCategory(action.result.data.category);

                    //this.maximize();
                },
                failure: function (form, action) {
                    Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
                    if (panel) {
                        panel.getEl().unmask();
                    }

                    this.close();
                },
                scope: this
            });
        }
        else {
            this.pnlData.loadCategories(this.frmDatabase);
            this.pnlData.loadServers(this.frmDatabase);
        }

        //this.maximize();

        this.center();
    },

    loadDatabase: function (panel, json) {
        if (this.databasesId && this.databasesId > 0) {
            if (panel) {
                panel.getEl().mask('Chargement infos DB....');
            }

            this.frmDatabase.load({
                url: Toc.CONF.CONN_URL,
                params: {
                    module: 'databases',
                    action: 'load_database'
                },
                success: function (form, action) {
                    if (panel) {
                        panel.getEl().unmask();
                    }

                    //this.pnlData.hide();
                    //this.pnlData.setServer(action.result.data.servers_id);
                    //this.pnlData.setCategory(action.result.data.category);

                    this.tabdatabases.removeAll();

                    this.pnlSessions = new Toc.SessionsGrid({label: this.label, databases_id: this.databasesId, sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner});
                    this.pnlVerrous = new Toc.LockTreeGrid({label: this.label, databases_id: this.databasesId, sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner});
                    this.pnlUsers = new Toc.usersGrid({label: this.label, databases_id: this.databasesId, sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner});
                    this.pnlLogs = new Toc.logPanel({host: this.host, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, content_id: this.databasesId, content_type: 'databases', owner: this.owner});
                    this.pnlTbs = new Toc.tbsGrid({sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner});
                    this.pnlDatafiles = new Toc.datafilesGrid({sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, typ: this.typ});
                    this.pnlFS = new Toc.fsGrid({host: this.host, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, owner: this.owner, typ: this.typ});
                    this.pnlTables = new Toc.tablesGrid({sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner});
                    this.pnlIndexes = new Toc.indexesGrid({sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner});
                    this.pnlMemory = new Toc.MemoryDashboardPanel({sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, typ: this.typ});
                    this.pnlNotifications = new Toc.notificationsGrid({databases_id: this.databasesId, owner: this.owner});
                    //this.pnlDocuments = new Toc.content.DocumentsPanel({content_id: this.databasesId, content_type: 'databases', owner: this.owner});
                    //this.pnlLinks = new Toc.content.LinksPanel({content_id: this.databasesId, content_type: 'databases', owner: this.owner});
                    //this.pnlComments = new Toc.content.CommentsPanel({content_id: this.databasesId, content_type: 'databases', owner: this.owner});

                    this.pnlRmanConfig = new Toc.RmanConfigGrid({sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, typ: this.typ});
                    this.pnlRmanBackup = new Toc.RmanBackupGrid({sid: this.sid, host: this.host, db_port: this.db_port, db_pass: this.db_pass, db_user: this.db_user, owner: this.owner, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, typ: this.typ});

                    this.tab_rman = new Ext.TabPanel({
                        activeTab: 0,
                        hideParent: false,
                        title: 'Rman',
                        region: 'center',
                        defaults: {
                            hideMode: 'offsets'
                        },
                        deferredRender: false,
                        items: [
                            this.pnlRmanConfig, this.pnlRmanBackup
                        ]
                    });

                    this.tabdatabases.add(this.pnlSessions);
                    this.tabdatabases.add(this.pnlVerrous);
                    this.tabdatabases.add(this.pnlUsers);
                    this.tabdatabases.add(this.pnlLogs);
                    this.tabdatabases.add(this.pnlTbs);
                    this.tabdatabases.add(this.pnlDatafiles);
                    this.tabdatabases.add(this.pnlTables);
                    this.tabdatabases.add(this.pnlIndexes);
                    this.tabdatabases.add(this.pnlFS);
                    this.tabdatabases.add(this.pnlMemory);
                    this.tabdatabases.add(this.tab_rman);
                    this.tabdatabases.add(this.pnlNotifications);
                    //this.tabdatabases.add(this.pnlDocuments);
                    //this.tabdatabases.add(this.pnlLinks);
                    //this.tabdatabases.add(this.pnlComments);

                    this.maximize();
                },
                failure: function (form, action) {
                    Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
                    if (panel) {
                        panel.getEl().unmask();
                    }

                    this.close();
                },
                scope: this
            });
        }

        this.center();
    },

    getContentPanel: function () {
        var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        this.pnlData = new Toc.DatabaseDataPanel({parent: this});
        this.pnlData.setTitle('Connexion');

        this.tabdatabases = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
                this.pnlData
            ]
        });

        return this.tabdatabases;
    },

    buildForm: function () {
        this.frmDatabase = new Ext.form.FormPanel({
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'servers',
                action: 'save_database'
            },
            deferredRender: false,
            items: [this.getContentPanel()]
        });

        return this.frmDatabase;
    },

    submitForm: function () {
        var data = this.pnlData.getServerData();
        var params = {
            servers_id: data.json.servers_id,
            host: data.json.host
        };

        this.frmDatabase.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            params: params,
            success: function (form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});

Toc.DatabasesGrid = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    //config.header = false;
    //config.title = 'Databases';
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_db'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'databases_id'
        }, [
            'databases_id',
            'servers_id',
            'label',
            'content_name',
            'host',
            'server_user',
            'server_pass',
            'server_port',
            'db_user',
            'db_pass',
            'port',
            'sid',
            'typ',
            'content_status',
            'can_read',
            'can_write',
            'can_modify',
            'can_publish'
        ]),
        autoLoad: true
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-detail-record', qtip: 'Details'},
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit, hideIndex: 'can_modify'},
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete, hideIndex: 'can_modify'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    renderPublish = function (status) {
        var currentRow = that.store.data.items[0];
        var json = currentRow.json;
        switch (json.can_publish) {
            case undefined:
            case 'undefined':
            case '0':
            case 0:
                if (status == 1) {
                    return '<img src="images/icon_status_green.gif"/>&nbsp;<img src="images/icon_status_red_light.gif"/>';
                } else {
                    return '<img src="images/icon_status_green_light.gif"/>&nbsp;<img src="images/icon_status_red.gif"/>';
                }
            case '1':
            case 1:
                if (status == 1) {
                    return '<img class="img-button" src="images/icon_status_green.gif"/>&nbsp;<img class="img-button btn-status-off" style="cursor: pointer"src="images/icon_status_red_light.gif"/>';
                } else {
                    return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif"/>&nbsp;<img class="img-button" src="images/icon_status_red.gif"/>';
                }
        }
    };

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'content_name', header: 'Libelle', dataIndex: 'label', sortable: true},
        { header: 'Server', align: 'left', dataIndex: 'label'},
        { header: 'Host', align: 'left', dataIndex: 'host'},
        { header: 'Port', align: 'center', dataIndex: 'port'},
        { header: 'SID', left: 'center', dataIndex: 'sid'},
        config.rowActions
    ]);
    config.autoExpandColumn = 'content_name';

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

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

    config.bbar = [
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
        }
    ];

    var thisObj = this;

    Toc.DatabasesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabasesGrid, Ext.grid.GridPanel, {

    onAdd: function () {
        var dlg = new Toc.DatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(null, path, null, this.owner);
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
        var dlg = new Toc.DatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(record.json, path, this.owner);
    },

    onView: function (record) {
        var dlg = new Toc.DatabasesDialog();
        var path = this.owner.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.showDetails(record.json, path, this.owner);
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
        console.debug(action);
        switch (action) {
            case 'icon-detail-record':
                this.onView(record);
                break;

            case 'icon-delete-record':
                this.onDelete(record);
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

Toc.CreateUserDialog = function (config) {

    config = config || {};

    config.id = 'databases_create_user_dialog-win';
    config.title = 'Creer un Compte';
    config.region = 'center';
    config.width = 470;
    config.height = 275;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.buildForm(config);

    config.buttons = [
        {
            text: 'OK',
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

    Toc.CreateUserDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.CreateUserDialog, Ext.Window, {

    show: function (json, caller) {
        if (json) {
            this.db_user = caller.db_user || null;
            this.databases_id = caller.databases_id || null;
            this.db_pass = caller.db_pass || null;
            this.db_port = caller.port || null;
            this.db_sid = caller.sid || null;
            this.db_host = caller.host || null;
            this.typ = caller.typ || null;
            this.label = caller.label || null;
        }

        this.frmUser.form.reset();
        this.frmUser.form.baseParams['username'] = this.username;
        this.frmUser.form.baseParams['label'] = this.label;
        this.frmUser.form.baseParams['databases_id'] = this.databases_id;
        this.frmUser.form.baseParams['db_user'] = this.db_user;
        this.frmUser.form.baseParams['db_pass'] = this.db_pass;
        this.frmUser.form.baseParams['db_port'] = this.db_port;
        this.frmUser.form.baseParams['db_sid'] = this.db_sid;
        this.frmUser.form.baseParams['db_host'] = this.db_host;
        Toc.CreateUserDialog.superclass.show.call(this);
    },

    buildForm: function (config) {
        config.panel = this;
        this.tbsCombo = new Toc.content.ContentManager.getTbsCombo(config);
        this.TemptbsCombo = new Toc.content.ContentManager.getTempTbsCombo(config);
        this.profileCombo = new Toc.content.ContentManager.getProfilesCombo(config);
        this.roleCombo = new Toc.content.ContentManager.getRolesCombo(config);
        this.frmUser = new Ext.form.FormPanel({
            //layout: 'border',
            url: Toc.CONF.CONN_URL,
            labelWidth: 125,
            baseParams: {
                module: 'users',
                action: 'create_user'
            },
            deferredRender: false,
            items: [
                {xtype: 'textfield', fieldLabel: 'Libelle', name: 'libelle', allowBlank: false, style: "width: 300px;"},
                {xtype: 'textfield', fieldLabel: 'Email', name: 'email', allowBlank: false, style: "width: 300px;", vtype: 'email'},
                {xtype: 'textfield', fieldLabel: 'Compte', name: 'account', allowBlank: false, style: "width: 300px;"},
                this.tbsCombo,
                this.TemptbsCombo,
                this.profileCombo,
                this.roleCombo
            ]
        });

        return this.frmUser;
    },

    submitForm: function () {
        this.frmUser.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function (form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});

Toc.ResetPwdDialog = function (config) {

    config = config || {};

    config.id = 'databases_reset_pwd_dialog-win';
    config.title = 'Reinitialiser un mot de passe';
    config.region = 'center';
    config.width = 443;
    config.height = 130;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.buildForm();

    config.buttons = [
        {
            text: 'Envoyer',
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

    Toc.ResetPwdDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.ResetPwdDialog, Ext.Window, {

    show: function (json, caller) {
        if (json) {
            this.username = json.username || null;
            this.db_user = caller.db_user || null;
            this.db_pass = caller.db_pass || null;
            this.db_port = caller.port || null;
            this.db_sid = caller.sid || null;
            this.db_host = caller.host || null;
            this.typ = caller.typ || null;
        }

        this.frmUser.form.reset();
        this.frmUser.form.baseParams['account'] = this.username;
        this.frmUser.form.baseParams['db_user'] = this.db_user;
        this.frmUser.form.baseParams['db_pass'] = this.db_pass;
        this.frmUser.form.baseParams['db_port'] = this.db_port;
        this.frmUser.form.baseParams['db_sid'] = this.db_sid;
        this.frmUser.form.baseParams['db_host'] = this.db_host;
        this.frmUser.form.baseParams['label'] = caller.label;
        this.frmUser.form.baseParams['databases_id'] = caller.databases_id;
        Toc.ResetPwdDialog.superclass.show.call(this);
        this.loadUser(this.frmUser);
    },

    loadUser: function (panel) {
        if (this.username) {
            if (panel) {
                panel.getEl().mask('Chargement infos User....');
            }

            this.frmUser.load({
                url: Toc.CONF.CONN_URL,
                params: {
                    module: 'users',
                    action: 'get_user'
                },
                success: function (form, action) {
                    if (panel) {
                        panel.getEl().unmask();
                    }
                },
                failure: function (form, action) {
                    if (panel) {
                        panel.getEl().unmask();
                    }
                },
                scope: this
            });
        }
    },

    buildForm: function (config) {
        this.frmUser = new Ext.form.FormPanel({
            //layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'users',
                action: 'change_pwd'
            },
            deferredRender: false,
            items: [
                {xtype: 'textfield', fieldLabel: 'Nom', name: 'name', id: 'name', allowBlank: false, style: "width: 300px;"},
                {xtype: 'textfield', fieldLabel: 'Email', name: 'email', id: 'email', allowBlank: false, style: "width: 300px;"}
            ]
        });

        return this.frmUser;
    },

    submitForm: function () {
        this.frmUser.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function (form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});

Toc.usersGrid = function (config) {
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.loadMask = true;
    config.title = 'Utilisateurs';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.listeners = {
        'rowclick': this.onRowClick
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_users',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid,
            exclude: config.exclude
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'username'
        }, [
            'username',
            'status',
            'expiration',
            'creation',
            'icon',
            'authentication_type'
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

    renderPublish = function (status) {
        if (status == 'OPEN') {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
            {iconCls: 'icon-locked-record', qtip: 'Reinitialiser Mot de Passe', hideIndex: 'authentication_type'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        {header: '', dataIndex: 'icon', width: 24},
        { id: 'username', header: 'Nom', dataIndex: 'username', sortable: true},
        { header: 'Date Creation', align: 'center', dataIndex: 'creation'},
        { header: 'Date Expiration', align: 'center', dataIndex: 'expiration'},
        {header: 'Status', align: 'center', renderer: renderPublish, dataIndex: 'status'},
        config.rowActions
    ]);
    config.autoExpandColumn = 'username';
    config.stripeRows = true;

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true,
        listeners: {
            scope: this,
            specialkey: function (f, e) {
                if (e.getKey() == e.ENTER) {
                    this.onSearch();
                }
            }
        }
    });

    var thisObj = this;

    if (!config.label) {
        config.combo = new Toc.content.ContentManager.getOracleConnexionsCombo({name: 'src_database', valueField: 'id'});

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

        config.combo.getStore().load();

        config.combo.on('select', function (combo, record, index) {
            //var category = record.data.key;
            console.debug(record);
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

    this.addEvents({'selectchange': true});
    Toc.usersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.usersGrid, Ext.grid.GridPanel, {

    onAdd: function () {
        var config = {
            db_user: this.db_user,
            databases_id: this.databases_id,
            db_pass: this.db_pass,
            db_port: this.db_port,
            db_sid: this.sid,
            db_host: this.host,
            label: this.label
        };

        var dlg = new Toc.CreateUserDialog(config);

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(config, this);
    },

    onEdit: function (record) {
        var config = {
            db_user: this.db_user,
            db_pass: this.db_pass,
            db_port: this.db_port,
            db_sid: this.sid,
            db_host: this.host
        };

        var dlg = new Toc.ResetPwdDialog(config);
        dlg.setTitle('Reinitialiser le mot de passe du Compte ' + record.get("username"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(record.json, this);
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
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['search'] = filter;
        store.reload();
        store.baseParams['search'] = '';
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-locked-record':
                this.onEdit(record);
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

            if (action != 'img-button') {
                var username = this.getStore().getAt(row).get('username');
                var module = 'lock_user';
                var flag = 0;

                switch (action) {
                    case 'status-off':
                        module = 'lock_user';
                        flag = 0;
                        this.onAction(module, username, flag);
                        break;
                    case 'status-on':
                        module = 'unlock_user';
                        flag = 1;
                        this.onAction(module, username, flag);
                        break;
                }
            }
        }
    },

    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
    },

    onAction: function (action, username, flag) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'users',
                action: action,
                account: username,
                flag: flag,
                label: this.label,
                databases_id: this.databases_id,
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
                    //store.getById(username).set('status', flag);
                    //store.commitChanges();
                    store.reload();

                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});

Toc.notificationsGrid = function (config) {
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.loadMask = true;
    config.title = ' Notifications';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.listeners = {
        'rowclick': this.onRowClick
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_subscribers',
            databases_id: config.databases_id
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'email'
        }, [
            'name',
            'email',
            'event'
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
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'name', header: 'Nom', dataIndex: 'name', sortable: true},
        { id: 'email', header: 'Email', dataIndex: 'email', sortable: true, width: 400},
        config.rowActions
    ]);
    config.autoExpandColumn = 'name';
    config.stripeRows = true;

    config.eventds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_events'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'event'
        }, [
            'event',
            'label'
        ]),
        autoLoad: true
    });

    config.comboEvents = new Ext.form.ComboBox({
        typeAhead: true,
        name: 'event',
        fieldLabel: 'Evenement',
        width: 400,
        triggerAction: 'all',
        mode: 'local',
        emptyText: '',
        store: config.eventds,
        editable: false,
        valueField: 'event',
        displayField: 'label'
    });

    var thisObj = this;

    config.tbar = [
        {
            text: '',
            iconCls: 'add',
            disabled: true,
            handler: this.onAdd,
            scope: this
        },
        '-',
        {
            text: '',
            //disabled : true,
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->',
        config.comboEvents
    ];

    config.comboEvents.on('select', function (combo, record, index) {
        var event = record.data.event;
        var store = thisObj.getStore();

        thisObj.topToolbar.items.items[0].enable();
        thisObj.topToolbar.items.items[1].enable();

        store.baseParams['event'] = event;
        store.reload();
    });

    config.bbar = new Ext.PageToolbar({
        pageSize: 50,
        store: config.ds,
        steps: Toc.CONF.GRID_STEPS,
        beforePageText: '',
        firstText: '',
        lastText: '',
        nextText: '',
        prevText: '',
        afterPageText: '',
        refreshText: '',
        displayInfo: true,
        displayMsg: '',
        emptyMsg: '',
        prevStepText: '',
        nextStepText: ''
    });

    this.addEvents({'selectchange': true});
    Toc.notificationsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.notificationsGrid, Ext.grid.GridPanel, {

    onAdd: function () {
        var dlg = new Toc.databases.AddSubscriberDialog();
        var event = this.comboEvents.getValue();
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(this.databases_id, event);
    },

    onDelete: function (record) {
        var event = record.get('event');
        var email = record.get('email');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            TocLanguage.msgDeleteConfirm,
            function (btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'users',
                            action: 'delete_subscriber',
                            databases_id: this.databases_id,
                            event: event,
                            email: email
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

    onSelect: function (combo, record, index) {
        var event = record.data.event;
        var store = this.getStore();

        store.baseParams['event'] = event;
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

            case 'icon-edit-record':
                this.onEdit(record);
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

Toc.AddSubscriberDialog = function (config) {

    config = config || {};

    config.id = 'databases_add_subscriber_dialog-win';
    config.title = 'Ajouter un Souscripteur';
    config.region = 'center';
    config.width = 443;
    config.height = 160;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.buildForm();

    config.buttons = [
        {
            text: 'Envoyer',
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

    Toc.AddSubscriberDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AddSubscriberDialog, Ext.Window, {

    show: function (databases_id, event) {
        this.frmUser.form.baseParams['databases_id'] = databases_id;
        this.frmUser.form.baseParams['event'] = event;
        Toc.AddSubscriberDialog.superclass.show.call(this);
    },

    buildForm: function () {
        this.frmUser = new Ext.form.FormPanel({
            //layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'users',
                action: 'add_subscriber'
            },
            deferredRender: false,
            items: [
                {xtype: 'textfield', fieldLabel: 'Nom', name: 'name', id: 'name', allowBlank: false, style: "width: 300px;"},
                {xtype: 'textfield', fieldLabel: 'Email', name: 'email', id: 'email', allowBlank: false, style: "width: 300px;", vtype: 'email'}
            ]
        });

        return this.frmUser;
    },

    submitForm: function () {
        this.frmUser.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function (form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});

Toc.watchJob = function (params) {
    var dlg = new Toc.JobDialog(params);
    dlg.setTitle(params.description);

    dlg.on('close', function () {
        if (params.panel && params.panel.onRefresh) {
            params.panel.onRefresh();
        }
    }, this);

    dlg.show(params);
};

Toc.indexesGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    //config.autoHeight = true;
    config.title = 'Indexes';
    //config.border = true;
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
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'owner', header: 'Proprietaire', dataIndex: 'owner', sortable: true, align: 'center', width: 70},
        { id: 'tablespace_name', header: 'Tablespace', dataIndex: 'tablespace_name', sortable: true, width: 100, align: 'center'},
        { id: 'table_name', header: 'Table', dataIndex: 'table_name', sortable: true, align: 'center', width: 100},
        { id: 'segment_name', header: 'Index', dataIndex: 'segment_name', sortable: true, align: 'center', width: 100},
        { id: 'table_blocks', header: 'Table Blocks', dataIndex: 'table_blocks', sortable: true, align: 'center', width: 100, renderer: Toc.content.ContentManager.FormatNumber},
        { id: 'clustering_factor', header: 'CFactor', dataIndex: 'clustering_factor', sortable: true, align: 'center', width: 100, renderer: Toc.content.ContentManager.FormatNumber},
        { id: 'table_rows', header: 'Table Rows', dataIndex: 'table_rows', sortable: true, align: 'center', width: 100, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Taille', align: 'center', dataIndex: 'size', sortable: true, width: 80},
        { header: 'Uniqueness', dataIndex: 'uniqueness', sortable: true, align: 'center', width: 90},
        { header: 'Comp', dataIndex: 'compression', sortable: true, align: 'center', width: 50, renderer: renderCompression},
        { header: 'Status', dataIndex: 'status', sortable: true, align: 'center', width: 50, renderer: renderStatus},
        { header: 'Logging', dataIndex: 'logging', sortable: true, align: 'center', width: 50, renderer: renderLogging},
        { header: 'Blevel', dataIndex: 'blevel', sortable: true, align: 'center', width: 50},
        { header: 'Lblocks', dataIndex: 'leaf_blocks', sortable: true, align: 'center', width: 80, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Dkeys', dataIndex: 'distinct_keys', sortable: true, align: 'center', width: 90, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Analyzed', dataIndex: 'last_analyzed', sortable: true, align: 'center', width: 70},
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
            handler: this.onRefresh,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'mobe',
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
    Toc.indexesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.indexesGrid, Ext.grid.GridPanel, {

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

            case 'icon-edit-record':
                this.onEdit(record);
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
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.activated = true;
                if (this.schema) {
                    this.getStore().load();
                }
            }
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
        { id: 'segment_type', header: 'Type', dataIndex: 'segment_type', sortable: true, align: 'center'},
        { id: 'tablespace_name', header: 'Tablespace', dataIndex: 'tablespace_name', sortable: true, align: 'center'},
        { id: 'segment_name', header: 'Table', dataIndex: 'segment_name', sortable: true, align: 'center'},
        { id: 'partition_name', header: 'Partition', dataIndex: 'partition_name', sortable: true, align: 'center'},
        { header: 'Taille (MB)', align: 'center', dataIndex: 'size', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Blocks', align: 'center', dataIndex: 'blocks', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Rows', align: 'center', dataIndex: 'num_rows', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Chains', align: 'center', dataIndex: 'chain_cnt', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Compression', dataIndex: 'compression', renderer: renderStatus, align: 'center', sortable: true},
        { header: 'Monitoring', dataIndex: 'monitoring', renderer: renderStatus, align: 'center', sortable: true},
        { header: 'Logging', dataIndex: 'logging', renderer: renderStatus, align: 'center', sortable: true},
        { id: 'last_analyzed', header: 'Last analyzed', dataIndex: 'last_analyzed', sortable: true, align: 'center'},
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

    config.schemasCombo = Toc.content.ContentManager.getDatabaseSchemasCombo(config);

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
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
        config.schemasCombo
        ,
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

    config.schemasCombo.getStore().load();

    config.schemasCombo.on('select', function (combo, record, index) {
        var schema = record.data.username;
        thisObj.schema = schema;
        //thisObj.refreshGrid(schema);
    });

    this.addEvents({'selectchange': true});
    Toc.tablesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tablesGrid, Ext.grid.GridPanel, {

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
                }
            }, this);
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
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
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

    onSearch: function () {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['schema'] = this.schema;
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

Toc.JobGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = false;
    config.viewConfig = {emptyText: 'Operation en cours, veuillez patienter SVP ...'};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'watch_job',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.host,
            db_sid: config.sid,
            job_name: config.job_name
        },
        listeners: {
            load: function (store, records, opt) {
                //console.debug(records);
                if (records.length > 0) {
                    var i = 0;
                    while (i < records.length) {
                        var record = records[i];
                        //console.debug(record);
                        if (record.data.status == 'FAILED' || record.data.status == 'STOPPED') {
                            this.stop();
                            if (this.owner) {
                                this.owner.buttons[0].enable();
                                console.debug(this.owner);
                            }
                        }
                        if (record.data.status == 'SUCCEEDED') {
                            this.stop();
                            if (this.owner) {
                                this.owner.fireEvent('success');
                                this.owner.close();
                            }
                            else {
                                console.debug(this);
                            }
                        }
                        i++;
                    }
                }
            },
            scope: this
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'log_id'
        }, [
            'log_id',
            'status',
            'err',
            'info'
        ]),
        autoLoad: false
    });

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'status', header: 'Status', dataIndex: 'status'},
        { id: 'info', header: 'Info', dataIndex: 'info', renderer: render}
    ]);
    config.autoExpandColumn = 'info';
    config.stripeRows = true;

    var thisObj = this;

    config.task = {
        run: function () {
            thisObj.getStore().load();
        },
        interval: config.freq || 2000 //5 second
    };

    config.runner = new Ext.util.TaskRunner();

    Toc.JobGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.JobGrid, Ext.grid.GridPanel, {

    start: function () {
        this.runner.start(this.task);
    },

    stop: function () {
        this.runner.stop(this.task);
    }
});