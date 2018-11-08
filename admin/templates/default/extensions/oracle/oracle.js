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
            action: 'list_databaseGroups'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'group_id'
        }, [
            'group_id',
            'group_name'
        ]),
        autoLoad: false
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Groupe',
        store: dsDatabasesCategoryCombo,
        displayField: 'group_name',
        valueField: 'group_id',
        hiddenName: 'group_id',
        name: 'group_id',
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
            'username',
            'label'
        ]),
        autoLoad: false
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Categorie',
        store: dsSchemas,
        displayField: 'label',
        valueField: 'username',
        hiddenName: 'category',
        name: 'schema',
        mode: 'local',
        width: 100,
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

Toc.TbsCombo = function (config) {
    var tbsStore = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'databases',
            action: 'list_tablespacecombo',
            db_user: config.db_user,
            db_pass: config.db_pass,
            db_port: config.db_port,
            db_host: config.db_host,
            db_sid: config.db_sid
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            id: 'tablespace_name',
            fields: ['tablespace_name', 'label']
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
        autoLoad: config.autoLoad
    });

    return new Ext.form.ComboBox({
        typeAhead: true,
        name: 'tbs',
        autoSelect: true,
        width: 100,
        listWidth: 100,
        hiddenName: "tbs",
        allowBlank: false,
        //id: 'tbs',
        fieldLabel: 'Espace logique',
        triggerAction: 'all',
        mode: 'local',
        //emptyText: 'Selectionner un espace logique',
        store: tbsStore,
        valueField: 'tablespace_name',
        displayField: 'label'
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

Toc.JobDialog = function (params) {
    config = {};

    config.layout = 'fit';
    config.width = 650;
    config.height = 400;
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
    setServer: function (servers_id) {
        this.serverCombo.getStore().on('load', function () {
            this.serverCombo.setValue(servers_id);
        }, this);

        this.serverCombo.getStore().load();
    },
    getDataPanel: function () {
        this.serverCombo = Toc.content.ContentManager.getServerCombo();
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
                        {xtype: 'textfield', fieldLabel: 'SID', name: 'sid', id: 'sid', allowBlank: false}
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});

Toc.DatabaseDialog = function (config) {

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

    this.addEvents({'saveSuccess': true});

    Toc.DatabaseDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabaseDialog, Ext.Window, {

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
        Toc.DatabaseDialog.superclass.show.call(this);
        this.loadDatabase(this.frmDatabase, json);
        //this.setTitle(json.label);
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

                    this.tabDatabases.add(this.pnlSessions);
                    this.tabDatabases.add(this.pnlVerrous);
                    this.tabDatabases.add(this.pnlUsers);
                    this.tabDatabases.add(this.pnlLogs);
                    this.tabDatabases.add(this.pnlTbs);
                    this.tabDatabases.add(this.pnlDatafiles);
                    this.tabDatabases.add(this.pnlTables);
                    this.tabDatabases.add(this.pnlIndexes);
                    this.tabDatabases.add(this.pnlFS);
                    this.tabDatabases.add(this.pnlMemory);
                    this.tabDatabases.add(this.tab_rman);
                    this.tabDatabases.add(this.pnlNotifications);
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
        this.tabDatabases = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: []
        });

        return this.tabDatabases;
    },

    buildForm: function () {
        this.frmDatabase = new Ext.form.FormPanel({
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'databases',
                action: 'save_database'
            },
            deferredRender: false,
            items: [this.getContentPanel()]
        });

        return this.frmDatabase;
    }
});

Toc.DatabaseEditDialog = function (config) {

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

    Toc.DatabaseEditDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabaseEditDialog, Ext.Window, {

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

        this.frmDatabase.form.reset();
        this.frmDatabase.form.baseParams['databases_id'] = this.databasesId;
        Toc.DatabaseEditDialog.superclass.show.call(this);
        this.editDatabase(this.frmDatabase, panels, json);
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

                    this.pnlData.setServer(action.result.data.servers_id);
                    this.pnlGroupes.setRoles(action.result.data.group_id);
                    //this.pnlData.setCategory(action.result.data.category);

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
            this.pnlData.loadServers(this.frmDatabase);
        }

        //this.maximize();

        this.center();
    },

    getContentPanel: function () {
        var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        this.pnlData = new Toc.DatabaseDataPanel({parent: this});
        this.pnlGroupes = new Toc.databases.GroupsPanel();
        this.pnlData.setTitle('Connexion');
        this.pnlGroupes.setTitle('Groupes');

        this.tabDatabases = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
                this.pnlData, this.pnlGroupes
            ]
        });

        return this.tabDatabases;
    },

    buildForm: function () {
        this.frmDatabase = new Ext.form.FormPanel({
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'databases',
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
            group_id: this.pnlGroupes.getRoles(),
            servers_id: data.json.servers_id,
            host: data.json.host
        };

        if (params.group_id.toString() == '') {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, 'Vous devez selectionner au moins un Groupe pour cette base !!!');
            this.tabDatabases.activate(this.pnlGroupes);
        }
        else {
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
        var dlg = new Toc.DatabaseEditDialog();
        //var path = this.owner.getCategoryPath();
        dlg.on('saveSuccess', function () {
            if (this.mainPanel) {
                this.mainPanel.refreshTree();
            }
            else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No mainPanel defined !!!");
            }
        }, this);

        dlg.show(null, null, null, this.owner);
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
        var dlg = new Toc.DatabaseEditDialog();
        //var path = this.owner.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.on('saveSuccess', function () {
            if (this.mainPanel) {
                this.mainPanel.refreshTree();
            }
            else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No mainPanel defined !!!");
            }
        }, this);

        dlg.show(record.json, null, this.owner);
    },

    onView: function (record) {
        var dlg = new Toc.DatabaseDialog();
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
        //var permissions = this.mainPanel.getCategoryPermissions();
        var store = this.getStore();

        //store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;
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
        //console.debug(action);
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

Toc.ParameterGrid = function (config) {
    var that = this;
    config = config || {};
    //config.autoHeight = true;
    config.loadMask = true;
    config.title = 'Parametres';
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
            module: 'databases',
            action: 'list_parameters',
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
            id: 'num'
        }, [
            'num',
            'name',
            'display_value',
            'description'
        ]),
        autoLoad: false
    });

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'name', header: 'Name', dataIndex: 'name', sortable: true, align: 'left', width: 30},
        { id: 'display_value', header: 'Value', dataIndex: 'display_value', sortable: false, align: 'left', width: 20},
        { id: 'description', header: 'Description', dataIndex: 'description', align: 'left', width: 50,renderer : render}
    ]);
    config.autoExpandColumn = 'name';
    config.stripeRows = true;

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    if(!config.scope)
    {
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
    }

    var thisObj = this;

    this.addEvents({'selectchange': true});
    Toc.ParameterGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ParameterGrid, Ext.grid.GridPanel, {

    onRefresh: function () {
        this.refreshGrid();
    },

    refreshGrid: function (schema) {
        var store = this.getStore();
        store.reload();
    },

    onSearch: function () {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();
        store.baseParams['search'] = filter;
        store.reload();
    }
});

Toc.AlertLogPanel = function (config) {
    config = config || {};

    config.layout = 'fit';
    config.border = false;
    config.title = 'AlertLog';
    config.listeners = {
        activate: function (panel) {
            if (panel.items) {
                panel.removeAll(true);
            }

            panel.getEl().mask('Chargement Metadata ...');
            Ext.Ajax.request({
                url: Toc.CONF.CONN_URL,
                params: {
                    module: 'databases',
                    action: 'get_alertlog',
                    db_user: config.db_user,
                    db_pass: config.db_pass,
                    db_host: config.host,
                    db_sid: config.sid,
                    port: config.server_port,
                    pass: config.server_pass,
                    user: config.server_user
                },
                callback: function (options, success, response) {
                    panel.getEl().unmask();
                    var result = Ext.decode(response.responseText);

                    var rec = result.records[0];

                    if (rec.size > 0) {
                        var logsGrid = new Toc.content.logfileGrid({typ: 'alert_log', sid: config.sid, host: config.host, db_port: config.db_port, db_pass: config.db_pass, db_user: config.db_user, owner: panel, mainPanel: panel});
                        panel.add(logsGrid);
                        panel.doLayout();

                        var json =
                        {
                            logs_id: -1,
                            sid: config.sid,
                            host: config.host,
                            db_port: config.db_port,
                            db_pass: config.db_pass,
                            db_user: config.db_user,
                            port: config.server_port,
                            pass: config.server_pass,
                            user: config.server_user,
                            url: rec.url,
                            lines: rec.lines
                        };

                        logsGrid.refreshGrid(json);
                    }
                    else {

                    }
                },
                scope: this
            });
        },
        scope: this
    };

    //config.pnlFiles = new Toc.content.LogsPanel({host: config.host, server_port: config.server_port, server_pass: config.server_pass, server_user: config.server_user, servers_id: config.servers_id, content_id: config.content_id, content_type: 'servers', owner: Toc.content.ContentManager, mainPanel: this});
    //config.txtLog = new Ext.form.TextArea({owner: config.owner, mainPanel: this,region:'center'});

    //config.pnlFiles.on('selectchange', this.onNodeSelectChange, this);

    Toc.AlertLogPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AlertLogPanel, Ext.Panel, {
});

Toc.downloadJobReport = function (request, scope) {
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

    scope.getEl().mask(request.comments);

    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'databases',
            action: action,
            task_id: request.task_id,
            comments: request.comments
        },
        callback: function (options, success, response) {
            if (response.responseText) {
                result = Ext.decode(response.responseText);
                switch (action) {
                    case 'download_report':
                        scope.getEl().unmask();
                        url = result.file_name;
                        params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
                        window.open(url, "", params);
                        //scope.buttons[0].enable();
                        break;
                    default:
                        var req = result.records[0];
                        //console.debug(req);
                        if (req.task_id) {
                            scope.task_id = req.task_id;
                            scope.getEl().unmask();
                            Toc.downloadJobReport(req, scope);
                        }
                        else {
                            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "No task id specified !!!");
                        }

                        break;
                }
            }
            else {
                Toc.downloadJobReport(request, scope);
            }
        },
        scope: scope
    });
};

Toc.exploreDatabase = function (node, panel,profil) {
    panel.removeAll();

    if (node.id == 0) {
        panel.removeAll(true);
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
                    },
                    enable: function (comp) {
                    },
                    beforerender: function (comp) {
                    },
                    render: function (comp) {
                    },
                    afterrender: function (comp) {
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

            var pnlUsers = new Toc.usersGrid({label: node.attributes.label, databases_id: node.attributes.databasesId, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

            if(profil && profil == 'security')
            {
                panel.add(tab);

                tab.add(pnlUsers);
                tab.activate(pnlUsers);
            }
            else
            {
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

                            var pnlSessions = new Toc.SessionsGrid({inAshPanel: false,label: node.attributes.label, databases_id: node.attributes.databases_id, sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
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

                            pnl.add(pnlSessions);
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

                            pnl.activate(pnlSessions);
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
//                        var pnlSgaUsage = new Toc.SgaUsageGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                            var pnlPga = new Toc.PgaStatGrid({sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                            var pnlMemoryAllocation = new Toc.MemoryAllocationPanel({label : 'Allocation',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                            var pnlSgaAllocation = new Toc.SgaAllocationPanel({label : 'SGA Allocation',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                            var pnlSharedPool = new Toc.SharedPoolPanel({label : 'Shared Pool',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

                            pnl.add(pnlMemoryResize);
                            pnl.add(pnlMemoryAllocation);
                            pnl.add(pnlSgaAllocation);
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

                var tab_cbo = new Ext.TabPanel({
                    activeTab: 0,
                    hideParent: false,
                    title: 'CBO',
                    region: 'center',
                    defaults: {
                        hideMode: 'offsets'
                    },
                    deferredRender: true,
                    listeners: {
                        activate: function (pnl) {
                            pnl.removeAll();
                            var pnlTableStats = new Toc.SegmentStatsChart({segment_type : 'table',label : 'Tables Stats',title : 'Tables Stats',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});
                            var pnlIndexStats = new Toc.SegmentStatsChart({segment_type : 'index',label : 'Indexes Stats',title : 'Indexes Stats',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

                            pnl.add(pnlTableStats);
                            pnl.add(pnlIndexStats);

                            pnl.doLayout(true, true);
                            pnl.activate(pnlTableStats);
                        },
                        deactivate: function (pnl) {
                            pnl.removeAll();
                        }
                    },
                    items: []
                });

                tab.add(tab_cbo);

                var pnlPx = new Toc.PxPanel({label : 'PX',node : node,sid: node.attributes.sid, host: node.attributes.host, db_port: node.attributes.db_port, db_pass: node.attributes.db_pass, db_user: node.attributes.db_user, owner: this.owner});

                tab.add(pnlPx);

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
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucune Database selectionnee !!!");
        }
    }

    panel.mainPanel.doLayout();

    return true;
};