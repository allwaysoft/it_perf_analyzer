Toc.content.logfileGrid = function (config) {

    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.border = true;
    config.header = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords,forceFit : true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_logcontent',
            typ : config.typ || null
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'lines_id'
        }, [
            'row'
        ]),
        listeners : {
            load : function(store,records,opt) {
                if(this.mainPanel.setLogCount)
                {
                    this.mainPanel.setLogCount(opt.params.logs_id,store.getTotalCount());
                }
            },
            beforeload : function(store,opt) {
                var filter = this.txtSearch.getValue() || null;

                store.baseParams['search'] = filter;

                if(filter)
                {
                    store.baseParams['start'] = 0;
                    var params = {};
                    params['start'] = 0;
                    params['limit'] = 10000;
                    params['count'] = null;
                }
            },scope: this
        },
        autoLoad: false
    });

    renderRow = function(row) {
        return '</span><div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'line', header: '', align: 'left', dataIndex: 'row',css : "white-space: normal;",renderer: renderRow,width : 100}
    ]);
    config.autoExpandColumn = 'line';

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true,
        disabled: true,
        listeners: {
            scope: this,
            specialkey: function (f, e) {
                if (e.getKey() == e.ENTER) {
                    this.onSearch();
                }
            }
        }
    });

    config.tbar = [
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
        pageSize: 1000,
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

    Toc.content.logfileGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.logfileGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        if(this.mainPanel.getCategoriesTree)
        {
            this.mainPanel.getCategoriesTree().refresh();
        }
        else
        {
            var store = this.getStore();
            store.reload();
        }
    },

    refreshGrid: function (json) {
        var params = {};
        params['start'] = json.lines - 1000;
        params['limit'] = 1000;
        params['count'] = json.lines;

        var store = this.getStore();

        store.baseParams['logs_id'] = json.logs_id;
        store.baseParams['host'] = json.host;
        store.baseParams['user'] = json.user;
        store.baseParams['pass'] = json.pass;
        store.baseParams['port'] = json.port;
        store.baseParams['url'] = json.url;
        //store.baseParams['count'] = json.lines;
        store.totalLength = json.lines;

        //this.bbar.changePage(json.lines/1000);

        store.load({params:params});
    },

    refreshFileGrid: function (json) {
        var params = {};
        params['start'] = 0;
        params['limit'] = 10000;

        var store = this.getStore();

        store.baseParams['host'] = json.host;
        store.baseParams['user'] = json.user;
        store.baseParams['pass'] = json.pass;
        store.baseParams['port'] = json.port;
        store.baseParams['url'] = json.url;

        store.load({params:params});
    },

    onSearch: function () {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['current_category_id'] = this.categoriesId || -1;
        store.baseParams['search'] = filter;
        store.reload();
        store.baseParams['search'] = '';
    }
});

Toc.content.ContentManager.getServersCategoryCombo = function () {

    var dsDatabasesCategoryCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_serverGroups'
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

Toc.content.ContentManager.MoveDataPanel = function (params, type, src, dest, pBar, index, form) {
    var watchMove = function (pbar, action) {
        params.action = action;

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: params,
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    console.debug(result);
                    var size = result.src_size;
                    var dest_size = result.dest_size;
                    percent = dest_size / size;
                    pbar.updateProgress(percent, result.feedback, true);

                    if (size == dest_size) {
                        pbar.updateProgress(1, result.feedback, true);
                    }
                    else {
                        watchMove(pbar, action);
                    }
                } else {
                    //Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                    pBar.updateProgress(0, result.feedback, true);
                    btnStart.enable();
                    //console.debug(pBar.textEl);
                }
            },
            scope: that
        });
    };

    var getParams = function () {
        console.log(index);
        var par = params;
        var _dest = form.findById('move_panel_dest_' + index).getValue();
        var _src = form.findById('move_panel_src_' + index).getValue();
        if (_src == _dest) {
            dest.focus(true);
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Source et destination doivent etre differentes ...");
            return null;
        }

        if (type == 'file') {
            par.dir = _dest.substring(0, _dest.lastIndexOf('/') + 1);
            par.file_name = _dest.substring(_dest.lastIndexOf('/') + 1);
            par.action = 'move_file';

            if (Ext.isEmpty(par.file_name)) {
                par.file_name = _src.substring(_src.lastIndexOf('/') + 1);
            }
        }
        else {
            _dest = _dest + '/';

            var source = _src;
            var len = source.length;
            var res = source.charAt(len - 1);
            while (res == '/') {
                source = source.substring(0, len - 1);
                len = source.length;
                res = source.charAt(len - 1);
            }

            par.dir = _dest;

            len = par.dir.length;
            res = par.dir.charAt(len - 1);
            while (res == '/') {
                par.dir = par.dir.substring(0, len - 1);
                len = par.dir.length;
                res = par.dir.charAt(len - 1);
            }

            par.dir = par.dir + '/';

            par.file_name = source.substring(source.lastIndexOf('/') + 1);
            par.action = 'move_folder';

            if ((par.dir + par.file_name + '/') == src.getValue()) {
                dest.focus(true);
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Source et destination doivent etre differentes ...");
                return null;
            }
        }

        if (Ext.isEmpty(par.file_name)) {
            dest.focus(true);
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Vous devez renseigner un nom de fichier destination ...");
            return null;
        }

        par.url = src.getValue();

        return par;
    };

    var a = function (c, b) {
        b = [].concat(b);
        for (var d = 0, a = b.length; d < a; d++) {
            this.data.insert(c, b[d]);
            b[d].join(this)
        }
        this.fireEvent("add", this, b, c)
    };

    var submitData = function () {
        var parameters = getParams();

        btnStart.disable();
        pBar.reset();
        pBar.show();
        pBar.updateProgress(0.1, "Deplacement en cours ...", true);

        if (parameters) {
            Ext.Ajax.request({
                url: Toc.CONF.CONN_URL,
                params: parameters,
                callback: function (options, success, response) {
                    var result = Ext.decode(response.responseText);

                    if (result.success == true) {
                        var src_size = result.src_size;
                        var dest_size = result.dest_size;
                        if (src_size == dest_size) {
                            pBar.updateProgress(1, result.feedback, true);
                            //fireEvent('saveSuccess', action.result.feedback);
                            //this.close();
                        }
                        else {
                            params.src_size = src_size;
                            watchMove(pBar, params.action == 'move_file' ? 'watch_filemove' : 'watch_foldermove');
                        }
                    } else {
                        //Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                        pBar.updateProgress(0, result.feedback, true);
                        btnStart.enable();
                        //console.debug(pBar.textEl);
                    }
                },
                scope: that
            });
        }
    };

    var btnStart = new Ext.Button({
        text: 'Demarrer',
        iconCls: 'icon-move-record',
        handler: submitData,
        scope: that
    });

    var pnlData = new Ext.Panel({
        layout: 'form',
        border: false,
        //autoHeight: true,
        style: 'padding: 6px',
        items: [
            {
                layout: 'form',
                border: true,
                labelSeparator: ' ',
                //columnWidth: .7,
                //autoHeight: true,
                defaults: {
                    anchor: '97%'
                },
                items: [
                    src,
                    dest
                ],
                bbar: new Ext.Toolbar({
                    hideBorders: false,
                    items: [
                        btnStart,
                        '-',
                        pBar
                    ]})
            }
        ]
    });

    return pnlData;
};

Toc.content.ContentManager.getServersConnexionsCombo = function () {
    var dsOracleConnexionsCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_serversconnexions'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            fields: [
                'id',
                'server_connexion', 'label_server'
            ]
        }),
        autoLoad: true
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Serveur',
        store: dsOracleConnexionsCombo,
        displayField: 'label_server',
        valueField: 'server_connexion',
        name: 'SERVER_CONNEXION',
        mode: 'local',
        width: 410,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.content.ContentManager.getServerCombo = function () {
    var serverStore = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_servers'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            id: 'servers_id',
            fields: ['content_name', 'servers_id', 'host']
        }),
        autoLoad: false
    });

    var serverCombo = new Ext.form.ComboBox({
        typeAhead: true,
        name: 'servers',
        autoSelect: true,
        id: 'servers',
        fieldLabel: 'Server',
        allowBlank: false,
        triggerAction: 'all',
        mode: 'local',
        emptyText: 'Select Server',
        store: serverStore,
        valueField: 'servers_id',
        displayField: 'content_name'
    });

    return serverCombo;
};

Toc.MovefileDialog = function (config) {

    config = config || {};

    config.id = 'move-file-dialog-win';
    config.layout = 'fit';
    config.width = 600;
    config.height = 170;
    //config.autoHeight = true;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.buildForm();
    config.shouldRefresh = false;

    config.btnClose = new Ext.Button({
        text: TocLanguage.btnClose,
        handler: function () {
            this.shouldRefresh ? this.fireEvent('saveSuccess', '') : this.fireEvent('dummy', '');
            this.close();
        },
        scope: this
    });

    config.buttons = [
        config.btnClose
    ];

    this.addEvents({'saveSuccess': true});

    Toc.MovefileDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MovefileDialog, Ext.Window, {

    show: function (json, owner, contents) {
        this.owner = owner || null;
        if (json) {
            this.url = json.url || null;
            this.server_user = json.server_user || null;
            this.server_pass = json.server_pass || null;
            this.server_port = json.server_port || null;
            this.host = json.host || null;
        }

        this.frmDatabase.form.reset();
        this.frmDatabase.form.baseParams['server_user'] = this.server_user;
        this.frmDatabase.form.baseParams['server_pass'] = this.server_pass;
        this.frmDatabase.form.baseParams['server_port'] = this.server_port;
        this.frmDatabase.form.baseParams['host'] = this.host;

        if (Ext.isArray(contents)) {
            var h = 170;
            var i = 0;
            while (i < contents.length) {
                var content = contents[i];
                json.action = content.typ == 'file' ? 'move_file' : 'move_folder';
                json.typ = content.typ;
                json.url = content.url;

                var src = new Ext.form.TextField({id: 'move_panel_src_' + i, width: '85', fieldLabel: 'Source', allowBlank: false, disabled: true, value: json.url});
                var dest = new Ext.form.TextField({id: 'move_panel_dest_' + i, fieldLabel: 'Destination', allowBlank: false, width: '85', value: json.url});
                var pBar = new Ext.ProgressBar({hidden: true, width: 472});

                var move_panel = new Toc.content.ContentManager.MoveDataPanel(json, content.typ, src, dest, pBar, i, this.frmDatabase);
                this.frmDatabase.add(move_panel);
                h = i > 0 ? h + 100 : h;
                this.setHeight(h);
                i++;
            }

            this.center();
            contents = [];
        }

        Toc.MovefileDialog.superclass.show.call(this);
    },

    getContentPanel: function () {
    },

    buildForm: function () {
        this.frmDatabase = new Ext.form.FormPanel({
            layout: 'form',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'servers',
                action: 'move_file'
            },
            deferredRender: false,
            items: []
        });

        return this.frmDatabase;
    },

    watchFileMove: function (params, pbar) {
        //this.frmDatabase.form.baseParams['action'] = 'watch_filemove';
        params.action = 'watch_filemove';

        this.frmDatabase.form.submit({
            //waitMsg: TocLanguage.formSubmitWaitMsg,
            params: params,
            success: function (form, action) {
                var size = action.result.src_size;
                var dest_size = action.result.dest_size;
                percent = dest_size / size;
                pbar.updateProgress(percent, action.result.feedback, true);

                if (size == dest_size) {
                    pbar.reset();
                    pbar.hide();
                    this.fireEvent('saveSuccess', action.result.feedback);
                    this.close();
                }
                else {
                    this.watchFileMove(params, pbar);
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

    getParams: function () {
        var params = {};
        var dest = this.dest.getValue();
        var src = this.src.getValue();
        if (src == dest) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Source et destination doivent etre differentes ...");
            return null;
        }

        params.dir = dest.substring(0, dest.lastIndexOf('/') + 1);
        params.file_name = dest.substring(dest.lastIndexOf('/') + 1);
        return params;
    },

    submitForm: function () {
        this.btnClose.disable();
        this.pBar.reset();
        this.pBar.updateProgress(0, "Deplacement en cours ...", true);
        this.pBar.show();

        var params = this.getParams();

        if (params) {
            this.frmDatabase.form.submit({
                //waitMsg: 'Deplacement en cours ...',
                params: params,
                success: function (form, action) {
                    var src_size = action.result.src_size;
                    var dest_size = action.result.dest_size;
                    if (src_size == dest_size) {
                        this.pBar.updateProgress(1);
                        this.fireEvent('saveSuccess', action.result.feedback);
                        this.close();
                    }
                    else {
                        params.src_size = src_size;
                        this.watchFileMove(params, this.pBar);
                    }
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

Toc.CreateFolderDialog = function (config) {

    config = config || {};

    config.id = 'create-folder-dialog-win';
    config.layout = 'fit';
    config.width = 600;
    config.height = 135;
    //config.autoHeight = true;
    config.modal = true;
    config.iconCls = 'icon-folder-win';
    config.items = this.buildForm();
    config.shouldRefresh = false;

    config.btnClose = new Ext.Button({
        text: TocLanguage.btnClose,
        handler: function () {
            this.shouldRefresh ? this.fireEvent('saveSuccess', '') : this.fireEvent('dummy', '');
            this.close();
        },
        scope: this
    });

    config.buttons = [
        {
            text: 'Creer',
            handler: function () {
                this.submitForm();
            },
            scope: this
        },
        config.btnClose
    ];

    this.addEvents({'saveSuccess': true});

    Toc.CreateFolderDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.CreateFolderDialog, Ext.Window, {

    show: function (json, owner) {
        this.owner = owner || null;
        if (json) {
            this.path = json.path;
            this.server_user = json.server_user || null;
            this.server_pass = json.server_pass || null;
            this.server_port = json.server_port || null;
            this.host = json.host || null;
        }

        this.path_field.setValue(this.path);
        this.frmDatabase.form.reset();
        this.frmDatabase.form.baseParams['path'] = this.path;
        this.frmDatabase.form.baseParams['server_user'] = this.server_user;
        this.frmDatabase.form.baseParams['server_pass'] = this.server_pass;
        this.frmDatabase.form.baseParams['server_port'] = this.server_port;
        this.frmDatabase.form.baseParams['host'] = this.host;

        Toc.CreateFolderDialog.superclass.show.call(this);
    },

    getContentPanel: function () {
    },

    buildForm: function () {
        this.path_field = new Ext.form.TextField({name: 'path', width: '450', fieldLabel: 'Path', allowBlank: false, disabled: true});
        this.folder_field = new Ext.form.TextField({name: 'folder', fieldLabel: 'Folder', allowBlank: false, width: '100'});

        this.frmDatabase = new Ext.form.FormPanel({
            layout: 'form',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'servers',
                action: 'create_folder'
            },
            deferredRender: false,
            items: [this.path_field, this.folder_field]
        });

        return this.frmDatabase;
    },

    submitForm: function () {
        this.btnClose.disable();

        this.frmDatabase.form.submit({
            waitMsg: 'Creation du dossier ...',
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

Toc.logPanel = function (config) {
    config = config || {};

    config.layout = 'border';
    config.border = false;
    config.title = 'Logs';
    config.listeners = {
        activate: function (panel) {
            if (!this.loaded) {
                this.pnlFiles.getStore().reload();
            }
        },
        scope: this
    };

    config.pnlFiles = new Toc.content.LogsPanel({host: config.host, server_port: config.server_port, server_pass: config.server_pass, server_user: config.server_user, servers_id: config.servers_id, content_id: config.content_id, content_type: 'servers', owner: Toc.content.ContentManager, mainPanel: this});
    //config.txtLog = new Ext.form.TextArea({owner: config.owner, mainPanel: this,region:'center'});
    config.logsGrid = new Toc.content.logfileGrid({owner: config.owner, mainPanel: this});

    config.pnlFiles.on('selectchange', this.onNodeSelectChange, this);

    config.items = [config.pnlFiles, config.logsGrid];

    Toc.logPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.logPanel, Ext.Panel, {

    onNodeSelectChange: function (json) {
        if (json) {
            this.logsGrid.refreshGrid(json);
        }
    },

    maskText: function () {
        this.getEl().mask('Chargement Fichier ....');
    },

    getCategoriesTree: function () {
        return this.pnlFiles;
    },

    getCategoryPath: function () {
        return this.pnlCategoriesTree.getCategoriesPath();
    },

    getCategoryPermissions: function () {
        return this.pnlCategoriesTree.getCategoryPermissions();
    },

    setLogCount: function (logs_id, count) {
        this.pnlFiles.setLogCount(logs_id, count);
    }
});

Toc.WatchFileMoveDialog = function (config) {

    config = config || {};

    config.id = 'watch_move-file-dialog-win';
    config.layout = 'fit';
    config.width = 600;
    config.height = 120;
    config.closable = false;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    config.items = this.buildForm();
    config.shouldRefresh = false;

    this.addEvents({'saveSuccess': true});

    Toc.WatchFileMoveDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.WatchFileMoveDialog, Ext.Window, {

    show: function (json, owner, src, dest) {
        this.owner = owner || null;
        if (json) {
            this.url = json.url || null;
            this.server_user = json.server_user || null;
            this.server_pass = json.server_pass || null;
            this.server_port = json.server_port || null;
            this.host = json.db_host || null;
            this.src = src || null;
            this.dest = dest || null;
        }

        this.frmDatabase.form.reset();
        this.frmDatabase.form.baseParams['server_user'] = this.server_user;
        this.frmDatabase.form.baseParams['server_pass'] = this.server_pass;
        this.frmDatabase.form.baseParams['server_port'] = this.server_port;
        this.frmDatabase.form.baseParams['host'] = this.host;

        var lblSrc = new Ext.form.TextField({fieldLabel: 'Source', value: this.src, width: '95%', disabled: true});
        this.frmDatabase.add(lblSrc);

        var lblDest = new Ext.form.TextField({fieldLabel: 'Destination', value: this.dest, width: '95%', disabled: true});
        this.frmDatabase.add(lblDest);

        this.pBar = new Ext.ProgressBar({width: '99%'});
        this.frmDatabase.add(this.pBar);

        this.frmDatabase.doLayout(true, true);

        Toc.WatchFileMoveDialog.superclass.show.call(this);
        this.submitForm();
    },

    getContentPanel: function () {
    },

    buildForm: function () {
        this.frmDatabase = new Ext.form.FormPanel({
            layout: 'form',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'servers',
                action: 'move_file'
            },
            deferredRender: false,
            items: []
        });

        return this.frmDatabase;
    },

    watchFileMove: function (params, pbar) {
        //this.frmDatabase.form.baseParams['action'] = 'watch_filemove';
        params.action = 'watch_filemove';

        this.frmDatabase.form.submit({
            //waitMsg: TocLanguage.formSubmitWaitMsg,
            params: params,
            success: function (form, action) {
                var size = action.result.src_size;
                var dest_size = action.result.dest_size;
                percent = dest_size / size;
                pbar.updateProgress(percent, action.result.feedback, true);

                if (size == dest_size) {
                    pbar.reset();
                    pbar.hide();
                    this.fireEvent('saveSuccess', action.result.feedback);
                    this.close();
                }
                else {
                    this.watchFileMove(params, pbar);
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

    getParams: function () {
        if (this.src == this.dest) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Source et destination doivent etre differentes ...");
            return null;
        }

        var params = {};
        params.url = this.src;
        params.dir = this.dest.substring(0, this.dest.lastIndexOf('/') + 1);
        params.file_name = this.dest.substring(this.dest.lastIndexOf('/') + 1);
        return params;
    },

    submitForm: function () {
        //this.btnClose.disable();
        this.pBar.reset();
        this.pBar.updateProgress(0, "Deplacement en cours ...", true);
        this.pBar.show();

        var params = this.getParams();

        if (params) {
            this.frmDatabase.form.submit({
                //waitMsg: 'Deplacement en cours ...',
                params: params,
                success: function (form, action) {
                    var src_size = action.result.src_size;
                    var dest_size = action.result.dest_size;
                    if (src_size == dest_size) {
                        this.pBar.updateProgress(1);
                        this.fireEvent('saveSuccess', action.result.feedback);
                        this.close();
                    }
                    else {
                        params.src_size = src_size;
                        this.watchFileMove(params, this.pBar);
                    }
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


Toc.DirSelectorField = function (params) {
    //console.debug(params);
    config = {};
    config.params = params;
    config.autoScroll = true;
    config.title = 'Selectionner un Repertoire ...';
    config.border = true;
    config.autoHeight = true;
    config.layout = 'column';

    Toc.DirSelectorField.superclass.constructor.call(this, config);
    this.getDataPanel(params);
};

Ext.extend(Toc.DirSelectorField, Ext.form.FieldSet, {
    getDataPanel: function (params) {
        this.pnlData = this;

        this.txtDir = new Ext.form.TextField({
            allowBlank: false,
            disabled: true,
            width: '90%'
        });
        this.add(this.txtDir);

        this.btnBrowse = new Ext.Button({
            text: '...',
            handler: function () {
                var dlg = new Toc.FsDialog({typ: params.server_typ, host: params.db_host, server_port: params.server_port, server_pass: params.server_pass, server_user: params.server_user, owner: params.owner, caller: this, show_files: false, close_dlg: true, file_name: params.file_name});
                dlg.setTitle('Systeme de fichiers du Serveur : ' + params.db_host);

                dlg.on('saveSuccess', function () {
                    //this.onRefresh();
                }, this);

                params.show_files = false;
                dlg.show(params, null);
            },
            scope: this
        });
        this.add(this.btnBrowse);

        this.doLayout(false, true);
    },
    getValue: function () {
        return this.txtDir.getValue();
    },
    setValue: function (value) {
        this.txtDir.setValue(value);
    }
});

Toc.Dirbrowser = function (config) {
    //console.debug(config);
    var params = config;
    config = config || {};

    config.id = 'databases_dir_dialog-win';
    config.title = 'FS browser';
    config.layout = 'fit';
    config.width = 800;
    config.height = 400;
    config.modal = true;
    config.iconCls = 'icon-fs-win';
    config.items = this.getContentPanel(params);
    config.back = [];
    config.fort = [];

    config.buttons = [
        {
            text: 'Selectionner',
            handler: function () {
                this.select();
            },
            disabled: true,
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

    Toc.Dirbrowser.superclass.constructor.call(this, config);
};

Ext.extend(Toc.Dirbrowser, Ext.Window, {

    show: function (config, owner) {
        this.owner = owner || null;
        if (config) {
            this.config = config;
            Toc.Dirbrowser.superclass.show.call(this);
            this.back = [];
            this.fort = [];
            this.loadDir(config);
        }
    },

    select: function () {
        if (this.caller) {
            var dir = this.pnlDir.getSelectedDir();
            //this.caller.setValue(dir || 'xxxxxxxxxx');
            //this.close();
            var path = this.file_name.substring(0, this.file_name.lastIndexOf('/') + 1);
            if (dir == path) {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, 'Le repertoire destination ne peut etre le meme que le repertoire source ...');
            }
            else {
                this.pnlDir.checkPath(dir, this.file_name, path, this.caller, this);
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, 'Impossible de determiner le caller ...');
        }
    },

    getPath: function () {
        return this.config.mount;
    },

    getConfig: function () {
        return this.config;
    },

    goback: function () {
        if (this.back.length > 0) {
            this.fort.push(this.config.mount);
            this.fort.reverse();
            var path = this.back[0];
            this.back.splice(0, 1);
            this.setPath(path);
        }
    },

    gofort: function () {
        if (this.fort.length > 0) {
            this.back.push(this.config.mount);
            this.back.reverse();
            var path = this.fort[0];
            this.fort.splice(0, 1);
            this.setPath(path);
        }
    },

    setPath: function (path) {
        this.config.mount = path;
        //this.setTitle(path);
        this.loadDir(this.config);
    },

    getContentPanel: function (params) {
        this.pnlDir = new Toc.dirGrid({owner: this, dlg: params.dlg, close_dlg: params.close_dlg, show_files: params.show_files});

        return this.pnlDir;
    },

    loadDir: function (config) {
        var param = {};
        param.server_user = config.server_user;
        param.server_pass = config.server_pass,
            param.port = config.server_port,
            param.host = config.host
        param.path = config.mount;
        param.typ = config.typ;
        param.show_files = config.show_files;
        this.pnlDir.getStore().load({params: param});
    }
});

Toc.fsGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    config.header = false;
    config.title = 'FS';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.listeners = {
        activate: function (panel) {
            if (!this.activated) {
                this.activated = true;
                this.getStore().load();
            }
        },
        'rowclick': this.onRowClick
        ,scope:this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_fs',
            user: config.server_user,
            pass: config.server_pass,
            port: config.server_port,
            host: config.host,
            typ: config.typ,
            show_files: config.show_files || true
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'mount'
        }, [
            'mount',
            'fs',
            'typ',
            {name: 'size', type: 'int'},
            {name: 'used', type: 'int'},
            {name: 'dispo', type: 'int'},
            {name: 'pct_used', type: 'int'}
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
        { id: 'mount', header: 'Nom', dataIndex: 'mount', sortable: true},
        { header: '%', align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true},
        { header: 'Taille (MB)', align: 'center', dataIndex: 'size', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Libre (MB)', align: 'center', dataIndex: 'dispo', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Utilise (MB)', align: 'center', dataIndex: 'used', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { id: 'typ', header: 'Type', dataIndex: 'typ', align: 'center', sortable: true},
        { id: 'fs', header: 'Filesystem', dataIndex: 'fs', sortable: true},
        config.rowActions
    ]);
    config.autoExpandColumn = 'fs';
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
        }
    ];

    var thisObj = this;

    this.addEvents({'selectchange': true});
    Toc.fsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.fsGrid, Ext.grid.GridPanel, {
    onEdit: function (record) {
        var config = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host,
            caller: this.caller,
            show_files: this.show_files,
            close_dlg: this.close_dlg,
            file_name: this.file_name,
            dlg: this.dlg,
            mount: record.get("mount")
        };

        var dlg = new Toc.Dirbrowser(config);
        dlg.setTitle(record.get("mount"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        if (this.close_dlg && this.dlg) {
            dlg.on('close', function () {
                this.dlg.close();
            }, this);
        }

        dlg.show(config, this.owner);
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
            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
    },

    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
    }
});

Toc.TopFsPanel = function (config) {
    var that = this;
    //this.params = config;
    config = config || {};
    config.loadMask = false;
    config.region = 'center';
    //config.width = this.params.width ||'50%';
    config.count = 0;
    config.try = 0;
    config.reqs = 0;
    config.title = 'FS';
    config.label = 'FS';
    config.height  = 110;
    config.hideHeaders = true;
    config.border = true;
    config.viewConfig = {emptyText: 'Aucune donnee recue ...', forceFit: true};

    config.listeners = {
        'rowclick': this.onRowClick,
        scope: that
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.LINUX_URL,
        baseParams: {
            module: 'servers',
            action: 'list_topfs',
            user: config.server_user,
            pass: config.server_pass,
            port: config.server_port,
            host: config.host,
            typ: config.typ
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'mount'
        }, [
            'mount',
            'fs',
            'typ',
            'qtip',
            {name: 'size', type: 'int'},
            {name: 'used', type: 'int'},
            {name: 'dispo', type: 'int'},
            {name: 'pct_used', type: 'int'},
            'rest'
        ]),
        listeners: {
            exception : function(misc){
                that.reqs--;
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
        { id: 'mount', header: 'Nom', dataIndex: 'mount', sortable: true, width: 70, renderer: render},
        { header: '%', align: 'center', dataIndex: 'rest', renderer: Toc.content.ContentManager.renderFsProgress, sortable: true, width: 30},
        config.rowActions
    ]);
    config.autoExpandColumn = 'mount';
    config.stripeRows = true;

    var thisObj = this;

    config.tools = [];

    config.tools = [
        /*{
         id: 'browse',
         qtip: 'Browse',
         handler: function (event, toolEl, panel) {
         var dlg = new Toc.FsDialog({typ: that.typ, host: that.host, server_port: that.server_port, server_pass: that.server_pass, server_user: that.server_user, owner: that.owner, caller: that, show_files: true, close_dlg: true});
         dlg.setTitle('Systeme de fichiers du Serveur : ' + params.db_host);

         dlg.on('saveSuccess', function () {
         //this.onRefresh();
         }, this);

         var json = {
         servers_id: that.servers_id || null,
         user: that.server_user,
         pass: that.server_pass,
         port: that.port,
         host: that.host,
         typ: that.typ
         };

         dlg.show(json, null);
         },
         scope: this
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

    this.addEvents({'selectchange': true});
    Toc.TopFsPanel.superclass.constructor.call(this, config);
    this.getView().scrollOffset = 0;
};

Ext.extend(Toc.TopFsPanel, Ext.grid.GridPanel, {
    refreshData: function (scope) {
        if (scope && scope.started) {
            scope.try++;
            scope.setTitle(scope.title + '.');

            if (scope.reqs == 0) {
                var store = this.getStore();
                scope.reqs++;
                store.load();
            }

            if(scope.try > 10)
            {
                scope.setTitle(scope.label);
                scope.try = 0;
            }
        }
    },
    onEdit: function (record) {
        var config = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host,
            caller: null,
            show_files: true,
            close_dlg: false,
            file_name: null,
            dlg: this,
            typ: this.typ,
            mount: record.get("mount")
        };

        var dlg = new Toc.Dirbrowser(config);
        dlg.setTitle('FS ' + record.get("mount") + ' sur le serveur ' + this.host);

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(config, this.owner);
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
            case 'icon-xxx-record':
                this.onEdit(record);
                break;
        }
    },

    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
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

        if(this.interval)
        {
            clearInterval(this.interval);
        }
    }
});

Toc.dirGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    config.title = '';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
    config.paths = [];

    config.listeners = {
        'rowclick': this.onRowClick
    };

    config.path_store = new Ext.data.SimpleStore({
        fields: ['path'],
        autoLoad: false
    });

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_dir'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'file_name'
        }, [
            'type',
            'permission',
            'file_name',
            'icon',
            'owner',
            'group',
            'date_mod',
            'size'
        ]),
        listeners: {
            load: function (store, records, opt) {
                this.owner.setTitle(opt.params.host + ' : ' + opt.params.path);
                this.path = opt.params.path;

                var path = Ext.data.Record.create([
                    {name: "path", type: "string"}
                ]);

                var record = new path({
                    path: opt.params.path
                });

                if (this.paths.indexOf(opt.params.path) <= -1) {
                    this.path_store.add(record);
                    this.path_store.sort('path', 'asc');
                    this.paths.push(opt.params.path);
                }
            },
            beforeload: function (store, opt) {
            }, scope: this
        },
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
            {iconCls: 'icon-open-record', qtip: 'Ouvrir'},
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete},
            {iconCls: 'icon-edit-record', qtip: "Editer"},
            {iconCls: 'icon-move-record', qtip: 'Deplacer'}
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    if (config.show_files == false) {
        config.sm = new Ext.grid.CheckboxSelectionModel({singleSelect: true});
    }
    else {
        config.sm = new Ext.grid.CheckboxSelectionModel();
    }

    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        {header: '', dataIndex: 'icon', width: 24},
        {id: 'permission', header: 'Permissions', dataIndex: 'permission', align: 'center'},
        {id: 'file_name', header: 'Nom', dataIndex: 'file_name'},
        {header: 'Owner', dataIndex: 'owner', align: 'center'},
        {header: 'Group', dataIndex: 'group', align: 'center'},
        {header: 'Date', dataIndex: 'date_mod', align: 'center'},
        {header: 'Taille', align: 'center', dataIndex: 'size', sortable: true},
        config.rowActions
    ]);
    config.autoExpandColumn = 'file_name';
    config.stripeRows = true;

    var thisObj = this;

    if (config.show_files == false) {
        config.sm.on('rowselect', function (sm, rowIndex, record) {
            thisObj.selected = thisObj.path + '/' + record.get('file_name') + '/';
            thisObj.owner.buttons[0].enable();
        });

        config.sm.on('rowdeselect', function (sm, rowIndex, record) {
            thisObj.selected = '';
            thisObj.owner.buttons[0].disable();
        });
    }

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true
    });

    config.path_combo = new Ext.form.ComboBox({
        typeAhead: true,
        forceSelection: true,
        mode: 'local',
        triggerAction: 'all',
        fieldLabel: 'Navigation',
        selectOnFocus: true,
        store: config.path_store,
        valueField: 'path',
        displayField: 'path',
        width: 300,
        listeners: {
            select: function (combo, record, index) {
                //console.debug(record);
                var path = record.data.path;
                if (path != this.path) {
                    this.owner.setPath(path);
                }
            }, scope: this
        }
    });

    config.tbar = [
        {
            text: '',
            iconCls: 'back',
            handler: this.onBack,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'avant',
            handler: this.onForward,
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
            iconCls: 'icon-delete-record',
            handler: this.onBatchDelete,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'icon-move-record',
            handler: this.onBatchMove,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'icon-create-folder',
            handler: this.onCreateFolder,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'icon-create-file',
            //handler: this.onCreateFolder,
            scope: this
        },
        '->',
        config.path_combo,
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

    this.addEvents({'selectchange': true});
    Toc.dirGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.dirGrid, Ext.grid.GridPanel, {

    onCreateFolder: function (record) {
        var dlg = new Toc.CreateFolderDialog();

        var current_path = this.owner.getPath();

        if (!current_path) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible de determiner le chemin actuel ...");
            return;
        }

        var config = this.owner.getConfig();
        config.path = current_path;

        dlg.setTitle('Creer un Dossier');
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(config, this.owner);
    },

    onOpen: function (record) {
        var type = record.get("type");

        if (type == 'folder') {
            var folder = record.get("file_name");
            var current_path = this.owner.getPath();
            var new_folder = current_path + '/' + folder;

            if (folder == "..") {
                this.owner.fort.push(current_path);
                this.owner.fort.reverse();
                var n = current_path.lastIndexOf("/");
                new_folder = current_path.substring(0, n);
            }
            else {
                this.owner.back.push(current_path);
                this.owner.back.reverse();
            }

            if (folder == ".") {
                new_folder = current_path;
            }

            if (new_folder.length == 0) {
                new_folder = '/';
            }

            this.owner.setPath(new_folder);
        }
        else {
            var dlg = new Toc.FileViewer();
            var file = record.get("file_name");
            var current_path = this.owner.getPath();
            var config = this.owner.getConfig();
            var new_file = current_path + '/' + file;
            config.url = new_file;
            dlg.setTitle(new_file);
            dlg.show(config, this.owner);
        }
    },

    onEdit: function (record) {
        var type = record.get("type");

        if (type == 'file') {
            var dlg = new Toc.FileEditDialog();
            var file = record.get("file_name");
            var current_path = this.owner.getPath();
            var config = this.owner.getConfig();
            var new_file = current_path + '/' + file;
            config.url = new_file;
            dlg.setTitle(new_file);
            dlg.show(current_path, file, config);
        }
    },

    getSelectedDir: function () {
        return this.selected;
    },

    onBatchDelete: function () {
        var selections = this.getSelectionModel().selections;
        var keys = selections.keys;
        var items = selections.items;
        var files = '';
        var folders = '';

        if (keys.length > 0) {
            //var batch = keys.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                TocLanguage.msgDeleteConfirm,
                function (btn) {
                    if (btn == 'yes') {
                        var config = this.owner.getConfig();
                        var current_path = this.owner.getPath();

                        var i = 0;
                        while (i < items.length) {
                            var item = items[i];

                            if (item.json.file_name != ".." && item.json.file_name != ".") {
                                if (item.json.type == 'file') {
                                    files = files + current_path + '/' + item.json.file_name + ';';
                                }
                                else {
                                    folders = folders + current_path + '/' + item.json.file_name + ';';
                                }
                            }

                            i++;
                        }

                        this.el.mask('Suppression en cours ...');
                        Ext.Ajax.request({
                            url: Toc.CONF.CONN_URL,
                            params: {
                                module: 'servers',
                                host: config.host,
                                port: config.server_port,
                                user: config.server_user,
                                pass: config.server_pass,
                                action: 'batch_delete',
                                files: files,
                                folders: folders
                            },
                            callback: function (options, success, response) {
                                this.el.unmask();
                                var result = Ext.decode(response.responseText);

                                if (result.success == true) {
                                    this.owner.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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
        var selections = this.getSelectionModel().selections;
        var keys = selections.keys;
        var items = selections.items;
        var contents = [];

        if (items.length > 0) {
            //var batch = keys.join(',');

            Ext.MessageBox.confirm(
                TocLanguage.msgWarningTitle,
                'Voulez vous vraiment deplacer ces elements ?',
                function (btn) {
                    if (btn == 'yes') {
                        var config = this.owner.getConfig();
                        config.module = 'servers';

                        var current_path = this.owner.getPath();
                        var i = 0;
                        while (i < items.length) {
                            var content = items[i];

                            var file = content.json.file_name;
                            var new_url = content.json.type == 'file' ? current_path + '/' + file : current_path + '/' + file + '/';

                            var _content = {};
                            _content.url = new_url;
                            _content.typ = content.json.type;
                            if (file != '.' && file != '..') {
                                contents.push(_content);
                            }

                            i++;
                        }

                        var dlg = this.owner.owner.createMoveFileDialog();
                        dlg.setTitle('Deplacer du contenu');
                        dlg.on('saveSuccess', function () {
                            this.onRefresh();
                        }, this);
                        dlg.show(config, this.owner, contents);
                    }
                },
                this
            );
        } else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    onMove: function (record) {
        var type = record.get("type");

        if (type == 'file') {
            var dlg = new Toc.MovefileDialog();

            var file = record.get("file_name");
            var current_path = this.owner.getPath();
            var config = this.owner.getConfig();
            var new_file = current_path + '/' + file;
            config.url = new_file;
            config.action = 'move_file';
            config.module = 'servers';
            dlg.setTitle('Deplacer le fichier ' + new_file);
            dlg.on('saveSuccess', function () {
                this.onRefresh();
            }, this);

            var contents = [];
            var content = {};
            content.url = config.url;
            content.typ = 'file';
            contents[0] = content;
            dlg.show(config, this.owner, contents);
        }
    },

    onDelete: function (record) {
        var type = record.get("type");
        var url = "";
        var action = 'delete_file';
        var config = this.owner.getConfig();

        if (type == 'folder') {
            var folder = record.get("file_name");

            if (folder != ".." && folder != ".") {
                var current_path = this.owner.getPath();
                url = current_path + '/' + folder;
                action = 'delete_folder';
            }
            else {
                return;
            }
        }
        else {
            var file = record.get("file_name");
            var current_path = this.owner.getPath();
            url = current_path + '/' + file;
        }

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            TocLanguage.msgDeleteConfirm,
            function (btn) {
                if (btn == 'yes') {
                    //console.debug(this);
                    this.el.mask('Suppresion en cours ....');
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'servers',
                            action: action,
                            user: config.server_user,
                            pass: config.server_pass,
                            port: config.server_port,
                            host: config.host,
                            url: url
                        },
                        callback: function (options, success, response) {
                            this.el.unmask();
                            var result = Ext.decode(response.responseText);

                            if (result.success == true) {
                                this.owner.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
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

    checkPath: function (path, file, src_path, caller, dlg) {
        if (!path) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Vous devez selectionner un repertoire !!!");
            return;
        }

        var config = this.owner.getConfig();
        this.el.mask('Verifications en cours ....');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'servers',
                action: 'check_path',
                user: config.server_user,
                pass: config.server_pass,
                port: config.server_port,
                host: config.host,
                path: path,
                file: file,
                src_path: src_path
            },
            callback: function (options, success, response) {
                this.el.unmask();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    caller.setValue(path);
                    dlg.close();
                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    },

    onRefresh: function () {
        this.getStore().reload();
    },

    onBack: function () {
        this.owner.goback();
    },

    onForward: function () {
        this.owner.gofort();
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

            case 'icon-open-record':
                this.onOpen(record);
                break;

            case 'icon-move-record':
                this.onMove(record);
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

    onAction: function (action, tbs, flag) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'servers',
                action: action,
                tbs: tbs,
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
                    store.getById(tbs).set('status', flag);
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

Toc.FileViewer = function (config) {
    config = config || {};

    config.id = 'databases_file_viewer-win';
    config.title = 'File Viewer';
    config.layout = 'fit';
    config.width = 800;
    config.height = 600;
    config.modal = true;
    config.iconCls = 'icon-databases-win';
    //config.pnlFile = new Toc.content.logfileGrid({owner: this.owner, mainPanel: this});
    config.items = this.getContentPanel();

    config.buttons = [
        {
            text: TocLanguage.btnClose,
            handler: function () {
                this.close();
            },
            scope: this
        }
    ];

    //this.addEvents({'saveSuccess': true});
    Toc.FileViewer.superclass.constructor.call(this, config);
}

Ext.extend(Toc.FileViewer, Ext.Window, {

    show: function (config, owner) {
        this.owner = owner || null;
        if (config) {
            this.config = config;
            Toc.FileViewer.superclass.show.call(this);
            this.loadFile(config);
        }
    },

    getPath: function () {
        return this.config.mount;
    },

    setPath: function (path) {
        this.config.mount = path;
        this.setTitle(path);
        this.loadDir(this.config);
    },

    getContentPanel: function () {
        this.pnlFile = new Toc.content.logfileGrid({owner: this.owner, mainPanel: this});
        return this.pnlFile;
    },

    loadFile: function (config) {
        var json = {};
        json.user = config.server_user;
        json.pass = config.server_pass;
        json.port = config.server_port;
        json.host = config.host;
        json.url = config.url;

        this.pnlFile.refreshFileGrid(json);
    }
});

Toc.FsDialog = function (config) {

    config = config || {};

    config.id = 'fsmanager-dialog-win';
    config.title = 'FS Explorer';
    config.layout = 'fit';
    config.width = 800;
    config.height = 400;
    config.modal = true;
    config.iconCls = 'icon-fsmanager-win';
    config.items = this.buildForm(config);

    config.buttons = [
        {
            text: TocLanguage.btnClose,
            handler: function () {
                this.close();
            },
            scope: this
        }
    ];

    //this.addEvents({'saveSuccess' : true});

    Toc.FsDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.FsDialog, Ext.Window, {

    show: function (json, id, cId) {
        //console.debug(json);
        if (json) {
            this.servers_id = json.servers_id || null;
            this.user = json.user || null;
            this.pass = json.pass || null;
            this.port = json.port || null;
            this.host = json.host || null;
            this.typ = json.typ || null;
        }

        var categoriesId = cId || -1;

        this.frmServer.form.reset();
        this.frmServer.form.baseParams['servers_id'] = this.servers_id;
        this.frmServer.form.baseParams['current_category_id'] = categoriesId;

        Toc.FsDialog.superclass.show.call(this);
        var store = this.pnlFS.getStore();

        store.load();
    },

    getContentPanel: function (config) {
        //console.debug(config);
        var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        this.pnlFS = new Toc.fsGrid({typ: config.typ, host: config.host, server_port: config.server_port, server_pass: config.server_pass, server_user: config.server_user, servers_id: config.servers_id, owner: config.owner, show_files: config.show_files, caller: config.caller, close_dlg: config.close_dlg, dlg: this, file_name: config.file_name});

        this.tabfsmanager = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [this.pnlFS]
        });

        return this.tabfsmanager;
    },

    buildForm: function (config) {
        this.frmServer = new Ext.form.FormPanel({
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'fsmanager',
                action: 'save_server'
            },
            deferredRender: false,
            items: [this.getContentPanel(config)]
        });

        return this.frmServer;
    },

    submitForm: function () {
        var params = {
        };

        this.frmServer.form.submit({
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


Toc.FsCriticalPanel = function (config) {
    //console.log(FsCriticalPanel);
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.autoHeight = true;
    config.width = '25%';
    //config.border = true;
    config.title = 'FS critiques';
    config.columnLines = false;
    config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    var data = [];

    if (config.fs && config.fs != null) {
        var result = "";
        var res = config.fs.split("#");
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

    //console.debug(data);

    config.ds = new Ext.data.SimpleStore({
        fields: ['mount', 'pct_used', 'rest'],
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
        { id: 'mount', header: 'FS', dataIndex: 'mount', width: 80},
        {align: 'center', dataIndex: 'rest', width: 70},
        { align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true, width: 60},
        config.rowActions
    ]);
    //config.autoExpandColumn = 'mount';

    var thisObj = this;

    config.tools = [];

    Toc.FsCriticalPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.FsCriticalPanel, Ext.grid.GridPanel, {

    onEdit: function (record) {
        var dlg = new Toc.Dirbrowser();
        dlg.setTitle(this.host + ' : ' + record.get("mount"));

        var config = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host,
            mount: record.get("mount")
        };

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(config, this.owner);
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

Toc.FileEditDialog = function (config) {

    config = config || {};

    config.id = 'file_edit_dialog_win';
    config.title = "<?php echo $osC_Language->get('heading_title'); ?>";
    config.width = 600;
    config.height = 450;
    config.layout = 'fit';
    config.modal = true;
    config.iconCls = 'icon-file_manager-win';
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

    this.current_directory = null;

    Toc.FileEditDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.FileEditDialog, Ext.Window, {
    show: function (directory, name, json) {
        if (json) {
            this.servers_id = json.servers_id || null;
            this.user = json.user || null;
            this.pass = json.pass || null;
            this.port = json.port || null;
            this.host = json.host || null;
            this.typ = json.typ || null;
        }

        var fileName = name || null;

        this.frmFileEdit.form.reset();
        this.frmFileEdit.form.baseParams['servers_id'] = this.servers_id;
        this.frmFileEdit.form.baseParams['user'] = this.user;
        this.frmFileEdit.form.baseParams['pass'] = this.pass;
        this.frmFileEdit.form.baseParams['port'] = this.port;
        this.frmFileEdit.form.baseParams['host'] = this.host;
        this.frmFileEdit.form.baseParams['typ'] = this.typ;
        this.frmFileEdit.form.baseParams['file_name'] = fileName;
        this.frmFileEdit.form.baseParams['url'] = fileName;
        this.frmFileEdit.form.baseParams['directory'] = directory;
        Toc.FileEditDialog.superclass.show.call(this);

        this.stxDirectory.setValue(directory);
        this.txtFilename.setValue(fileName);

        this.loadFile();
    },

    loadFile: function () {
        this.frmFileEdit.load({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'servers',
                action: 'load_file'
            },
            success: function () {
                Toc.FileEditDialog.superclass.show.call(this);
            },
            failure: function () {
                Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
            },
            scope: this
        });
    },

    buildForm: function () {
        this.frmFileEdit = new Ext.form.FormPanel({
            url: Toc.CONF.CONN_URL,
            labelWidth: 150,
            defaults: {
                anchor: '97%'
            },
            baseParams: {
                module: 'servers',
                action: 'save_file'
            },
            layoutConfig: {
                labelSeparator: ''
            },
            items: [
                this.txtFilename = new Ext.form.TextField({fieldLabel: "<?php echo $osC_Language->get('field_file_name');?>"}),
                this.stxDirectory = new Ext.ux.form.StaticTextField({fieldLabel: "<?php echo $osC_Language->get('field_directory');?>"}),
                {xtype: 'textarea', fieldLabel: "<?php echo $osC_Language->get('field_file_contents');?>", name: 'content', height: 300}
            ]
        });

        return this.frmFileEdit;
    },

    submitForm: function () {
        this.frmFileEdit.form.baseParams['file_name'] = this.txtFilename.getValue();

        this.frmFileEdit.form.submit({
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

Toc.ServersGrid = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_servers'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'servers_id'
        }, [
            'servers_id',
            'content_name',
            'host',
            'port',
            'content_status',
            'can_read',
            'can_write',
            'can_modify',
            'can_publish',
            'user',
            'pass',
            'typ'
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
        { id: 'content_name', header: 'Libelle', dataIndex: 'content_name', sortable: true},
        { header: 'Host', align: 'left', dataIndex: 'host'},
        { header: 'Port', align: 'center', dataIndex: 'port'},
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

    var thisObj = this;
    config.bbar = new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: config.ds,
        steps: Toc.CONF.GRID_STEPS,
        btnsConfig: [
            {
                text: TocLanguage.btnAdd,
                iconCls: 'add',
                handler: function () {
                    thisObj.onAdd();
                }
            },
            {
                text: TocLanguage.btnDelete,
                iconCls: 'remove',
                handler: function () {
                    thisObj.onBatchDelete();
                }
            }
        ],
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

    Toc.ServersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ServersGrid, Ext.grid.GridPanel, {

    onAdd: function () {
        var dlg = new Toc.ServerEditDialog();
        dlg.on('saveSuccess', function () {
            if(this.mainPanel)
            {
                this.mainPanel.refreshTree();
            }
            else
            {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle,"No mainPanel defined !!!");
            }
        }, this);

        dlg.show(null, null);
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

    onView: function (record) {
        var dlg = new Toc.ServerDialog();
        dlg.setTitle(record.get("content_name"));

        dlg.showDetails(record.json, null, this.owner);
    },

    onEdit: function (record) {
        var dlg = new Toc.ServerEditDialog();
        //var path = this.mainPanel.getCategoryPath();
        dlg.setTitle(record.get("content_name"));

        dlg.on('saveSuccess', function () {
            if(this.mainPanel)
            {
                this.mainPanel.refreshTree();
            }
            else
            {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle,"No mainPanel defined !!!");
            }
        }, this);

        dlg.show(record.json, null);
    },

    onDelete: function (record) {
        var serversId = record.get('servers_id');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            TocLanguage.msgDeleteConfirm,
            function (btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'servers',
                            action: 'delete_server',
                            servers_id: serversId
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
    },

    onBatchDelete: function () {
        var keys = this.selModel.selections.keys;

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
                                module: 'servers',
                                action: 'delete_servers',
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

Toc.ServerDialog = function (config) {

    config = config || {};

    config.id = 'servers-dialog-win';
    config.title = 'Infos Serveur';
    config.layout = 'fit';
    config.maximizable = true;
    config.minimizable = true;
    config.resizable = true;
    config.width = 465;
    config.height = 310;
    config.modal = true;
    config.iconCls = 'icon-servers-win';
    config.items = this.buildForm();

    this.addEvents({'saveSuccess': true});

    Toc.ServerDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ServerDialog, Ext.Window, {

    show: function (json, id, cId) {

        if (json) {
            this.serversId = json.servers_id || null;
            this.servers_id = json.servers_id || null;
            this.server_user = json.user || null;
            this.server_pass = json.pass || null;
            this.server_port = json.port || null;
            this.host = json.host || null;
            this.typ = json.typ || null;
        }

        //this.serversId = id || null;
        var categoriesId = cId || -1;

        this.frmServer.form.reset();
        this.frmServer.form.baseParams['servers_id'] = this.serversId;
        this.frmServer.form.baseParams['current_category_id'] = categoriesId;

        Toc.ServerDialog.superclass.show.call(this);
        this.loadServer(this.pnlData);
    },

    loadServer: function (panel) {
        if (this.serversId && this.serversId >= 0) {
            if (panel) {
                panel.getEl().mask('Chargement infos Serveur....');
            }

            this.frmServer.load({
                url: Toc.CONF.CONN_URL,
                params: {
                    module: 'servers',
                    action: 'load_server'
                },
                success: function (form, action) {
                    if (panel) {
                        panel.getEl().unmask();
                    }

                    this.pnlFS = new Toc.fsGrid({host: this.host, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, owner: this.owner, typ: this.typ});
                    this.pnlLogs = new Toc.logPanel({host: this.host, server_port: this.server_port, server_pass: this.server_pass, server_user: this.server_user, servers_id: this.servers_id, content_id: this.servers_id, content_type: 'servers', owner: Toc.content.ContentManager});
                    this.pnlDocuments = new Toc.content.DocumentsPanel({content_id: this.serversId, content_type: 'servers', owner: Toc.content.ContentManager});
                    this.pnlLinks = new Toc.content.LinksPanel({content_id: this.serversId, content_type: 'servers', owner: Toc.content.ContentManager});
                    this.pnlComments = new Toc.content.CommentsPanel({content_id: this.serversId, content_type: 'servers', owner: Toc.content.ContentManager});

                    this.tabServer.add(this.pnlFS);
                    this.tabServer.add(this.pnlLogs);
                    this.tabServer.add(this.pnlDocuments);
                    this.tabServer.add(this.pnlLinks);
                    this.tabServer.add(this.pnlComments);

                    //this.setWidth(850);
                    //this.setHeight(570);
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
    },

    getContentPanel: function () {
        this.tabServer = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: []
        });

        return this.tabServer;
    },

    buildForm: function () {
        this.frmServer = new Ext.form.FormPanel({
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'servers',
                action: 'save_server'
            },
            deferredRender: false,
            items: [this.getContentPanel()]
        });

        return this.frmServer;
    }
});

Toc.ServerEditDialog = function (config) {

    config = config || {};

    config.id = 'servers-dialog-win';
    config.title = 'Nouveau Serveur';
    config.layout = 'fit';
    config.maximizable = true;
    config.minimizable = true;
    config.resizable = true;
    config.width = 465;
    config.height = 310;
    config.modal = true;
    config.iconCls = 'icon-servers-win';
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

    Toc.ServerEditDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ServerEditDialog, Ext.Window, {

    show: function (json, id, cId) {

        if (json) {
            this.serversId = json.servers_id || null;
            this.servers_id = json.servers_id || null;
            this.server_user = json.user || null;
            this.server_pass = json.pass || null;
            this.server_port = json.port || null;
            this.host = json.host || null;
            this.typ = json.typ || null;
        }

        //this.serversId = id || null;
        var categoriesId = cId || -1;

        this.frmServer.form.reset();
        this.frmServer.form.baseParams['servers_id'] = this.serversId;
        this.frmServer.form.baseParams['current_category_id'] = categoriesId;

        Toc.ServerEditDialog.superclass.show.call(this);
        this.loadServer(this.pnlData);
    },

    loadServer: function (panel) {
        if (this.serversId && this.serversId >= 0) {
            if (panel) {
                panel.getEl().mask('Chargement infos Serveur....');
            }

            this.frmServer.load({
                url: Toc.CONF.CONN_URL,
                params: {
                    module: 'servers',
                    action: 'load_server'
                },
                success: function (form, action) {
                    if (panel) {
                        panel.getEl().unmask();
                    }

                    this.pnlData.setTyp(action.result.data.typ);
                    this.pnlGroupes.setRoles(action.result.data.group_id);

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
    },

    getContentPanel: function () {
        var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        this.pnlData = new Toc.ServerDataPanel({parent: this});
        this.pnlGroupes = new Toc.servers.GroupsPanel();
        this.pnlData.setTitle('Connexion');
        this.pnlGroupes.setTitle('Groupes');

        this.tabServers = new Ext.TabPanel({
            activeTab: 0,
            hideParent: true,
            region: 'center',
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [
                this.pnlData,this.pnlGroupes
            ]
        });

        return this.tabServers;
    },

    buildForm: function () {
        this.frmServer = new Ext.form.FormPanel({
            layout: 'border',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'servers',
                action: 'save_server'
            },
            deferredRender: false,
            items: [this.getContentPanel()]
        });

        return this.frmServer;
    },

    submitForm: function () {
        var params = {
            group_id: this.pnlGroupes.getRoles()
        };

        if(params.group_id.toString() == '')
        {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle,'Vous devez selectionner au moins un Groupe pour ce Serveur !!!');
            this.tabServers.activate(this.pnlGroupes);
        }
        else
        {
            this.frmServer.form.submit({
                waitMsg: TocLanguage.formSubmitWaitMsg,
                params: params,
                timeout: 60,
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

Toc.ServerDataPanel = function (config) {
    config = config || {};

    config.title = 'General';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.ServerDataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ServerDataPanel, Ext.Panel, {
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
    getDataPanel: function () {

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
                        {xtype: 'textfield', fieldLabel: 'Host', name: 'host', id: 'host', allowBlank: false},
                        {xtype: 'textfield', fieldLabel: 'Label', name: 'label', id: 'label', allowBlank: false},
                        {
// Use the default, automatic layout to distribute the controls evenly
// across a single row
                            xtype: 'radiogroup',
                            name: 'typ',
                            id: 'typ',
                            fieldLabel: 'Type',
                            items: [
                                {boxLabel: 'Windows', autoWidth: true, inputValue: 'win', name: 'typ', xtype: 'radio'},
                                {boxLabel: 'Linux', autoWidth: true, inputValue: 'lin', name: 'typ', checked: true, xtype: 'radio'},
                                {boxLabel: 'Aix', autoWidth: true, name: 'typ', inputValue: 'aix', xtype: 'radio'}
                            ]
                        },
                        {xtype: 'numberfield', fieldLabel: 'Port', name: 'port', id: 'port', width: 200, allowBlank: false},
                        {xtype: 'textfield', fieldLabel: 'User', name: 'user', id: 'user', allowBlank: false},
                        {xtype: 'textfield', fieldLabel: 'Pass', name: 'pass', id: 'pass', allowBlank: false}
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});

Toc.ServerDashboard = function (config) {
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
                this.buildItems('all', 2000);
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
            config.categoryCombo = Toc.content.ContentManager.getServersCategoryCombo();

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

    Toc.ServerDashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ServerDashboard, Ext.Panel, {
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
                module: 'servers',
                action: 'list_serverperf',
                category: category || '0',
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
                        //db.width = '33%';
                        db.classs = (i % 2 == 0) ? 'blue' : 'gray';

                        var panel = new Toc.ServerDashboardPanel(db);
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

Toc.ServerDashboardPanel = function (params) {
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

    Toc.ServerDashboardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ServerDashboardPanel, Ext.Panel, {
    buildItems: function (params) {
        params.owner = this.owner;
        params.width = '25%';

        var mem = {
            width: '20%',
            label: 'Memory',
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

        this.mem_usage = new Toc.MemoryCharts(mem);
        this.add(this.mem_usage);

        var cpu = {
            width: '20%',
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
            width: '20%',
            label: 'Disks (%)',
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
            width: '20%',
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

        var fs = {
            width: '20%',
            label: 'FS',
            body_height: '75px',
            freq: params.freq,
            //hideHeaders:true,
            server_user: params.server_user,
            server_pass: params.server_pass,
            server_port: params.server_port,
            servers_id: params.servers_id,
            port: params.port,
            typ: params.typ,
            host: params.host
        };

        this.fs_usage = new Toc.TopFsPanel(fs);
        this.add(this.fs_usage);
    },

    start: function () {
        this.mem_usage.start();
        this.cpu_usage.start();
        this.disk_usage.start();
        this.net_usage.start();
        this.fs_usage.start();
    },

    stop: function () {
        this.mem_usage.stop();
        this.cpu_usage.stop();
        this.disk_usage.stop();
        this.net_usage.stop();
        this.fs_usage.stop();
    }
});

Toc.CpuCharts = function (config) {
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
    config.width = this.params.width || '25%';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        render: function (comp) {

            var configChart = function () {
                thisObj.data = [];

                var chart;
                chart = new AmCharts.AmSerialChart();
                chart.width = '100%';
                chart.marginBottom = 1;
                chart.marginLeft = 1;
                chart.marginRight = 1;
                chart.marginTop = 1;
                //chart.autoResize = true;

                var type = "line";
                chart.dataProvider = thisObj.data;
                chart.marginTop = 5;
                chart.categoryField = "category";

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
                valueAxis.maximum = 100;
                //valueAxis.title = "%";
                valueAxis.titleColor = "green";
                valueAxis.labelsEnabled = false;
                chart.addValueAxis(valueAxis);

                // GRAPHS
                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "User";
                graph.valueField = "user";
                graph.lineColor = "#008200";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Sys";
                graph.valueField = "sys";
                graph.lineColor = "#FE2000";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Nice";
                graph.valueField = "nice";
                graph.lineColor = "#EF8E31";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "iowait";
                graph.valueField = "iowait";
                graph.lineColor = "darkblue";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                var graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Idle";
                graph.valueField = "idle";
                graph.lineColor = "#FFFFFF";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
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

    Toc.CpuCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.CpuCharts, Ext.Panel, {

    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {
            if(scope.chart)
            {
                var chart = scope.chart;

                var valueAxis = chart.valueAxes[0];

                if(scope.reqs == 0)
                {
                    scope.reqs++;
                    scope.transactionId = Ext.Ajax.request({
                        url: Toc.CONF.LINUX_URL,
                        params: {
                            module: 'servers',
                            action: 'cpu_usage',
                            server_user: this.server_user,
                            server_pass: this.server_pass,
                            db_host: this.db_host
                        },
                        callback: function (options, success, response) {
                            scope.reqs--;
                            scope.try = 0;
                            scope.setTitle(scope.label);

                            if(success)
                            {
                                var json = Ext.decode(response.responseText);

                                //console.debug(json);
                                var data = json ? json.records : [];

                                this.data.push(data);

                                if (this.data.length > 200) {
                                    this.data.shift();
                                }

                                chart.dataProvider = this.data;
                                if (chart.chartData.length > 200) {
                                    chart.chartData.shift();
                                }

                                if (valueAxis) {
                                    valueAxis.titleColor = "green";
                                    valueAxis.labelsEnabled = false;
                                    valueAxis.title = "";
                                    if (!json) {
                                        valueAxis.titleColor = "red";
                                        valueAxis.title = "No Data";
                                    }
                                    else {
                                        valueAxis.titleColor = "green";
                                        valueAxis.labelsEnabled = false;
                                        valueAxis.title = "";

                                        if (json.comment) {
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = json.comment;
                                            valueAxis.labelsEnabled = true;
                                        }
                                    }

                                    chart.validateNow();
                                }
                            }
                            else
                            {
                                if (valueAxis) {
                                    valueAxis.labelsEnabled = false;
                                    valueAxis.titleColor = "red";
                                    valueAxis.title = "Time out";

                                    chart.validateNow();
                                }
                            }
                        },
                        scope: this
                    });
                }

                chart.validateData();
            }

            if(scope.try > 10)
            {
                this.stop();
                this.start();
            }

            if(scope.count == 0)
            {
                var interval = setInterval(function(){
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

        if(this.interval)
        {
            clearInterval(this.interval);
        }

        this.interval = null;
    }
});

Toc.NetCharts = function (config) {
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
    config.width = this.params.width || '25%';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        render: function (comp) {
            var configChart = function () {
                thisObj.data = [];

                var chart;
                chart = new AmCharts.AmSerialChart();
                chart.width = '100%';
                chart.marginBottom = 1;
                chart.marginLeft = 1;
                chart.marginRight = 1;
                chart.marginTop = 1;
                //chart.autoResize = true;

                var type = "line";
                chart.dataProvider = thisObj.data;
                chart.categoryField = "category";

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
                //valueAxis.maximum = 100;
                valueAxis.title = "";
                valueAxis.titleColor = "green";
                valueAxis.labelsEnabled = true;
                chart.addValueAxis(valueAxis);

                // GRAPHS
                var graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Receive";
                graph.valueField = "rec";
                graph.lineColor = "#4A96C6";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Transmit";
                graph.valueField = "trans";
                graph.lineColor = "#B58242";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
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

    Toc.NetCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.NetCharts, Ext.Panel, {
    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {

            if(scope.chart)
            {
                var chart = scope.chart;
                var valueAxis = chart.valueAxes[0];

                if(scope.reqs == 0)
                {
                    scope.reqs++;
                    scope.transactionId = Ext.Ajax.request({
                        url: Toc.CONF.LINUX_URL,
                        params: {
                            module: 'servers',
                            action: 'net_usage',
                            server_user: this.server_user,
                            server_pass: this.server_pass,
                            db_host: this.db_host
                        },
                        callback: function (options, success, response) {

                            scope.reqs--;
                            scope.try = 0;
                            scope.setTitle(scope.label);

                            if(success)
                            {
                                var json = Ext.decode(response.responseText);

                                //console.debug(json);
                                var data = json ? json.records : [];

                                this.data.push(data);

                                if (this.data.length > 200) {
                                    this.data.shift();
                                }

                                chart.dataProvider = this.data;
                                if (chart.chartData.length > 200) {
                                    chart.chartData.shift();
                                }

                                if (valueAxis) {
                                    valueAxis.titleColor = "green";
                                    //valueAxis.labelsEnabled = false;
                                    valueAxis.title = "";
                                    if (!json) {
                                        valueAxis.titleColor = "red";
                                        valueAxis.title = "No Data";
                                    }
                                    else {
                                        if (json.comment) {
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = json.comment;
                                            //valueAxis.labelsEnabled = true;
                                        }
                                    }

                                    chart.validateNow();
                                }
                            }
                            else
                            {
                                if (valueAxis) {
                                    //valueAxis.labelsEnabled = false;
                                    valueAxis.titleColor = "red";
                                    valueAxis.title = "Time out";
                                    chart.validateNow();
                                }
                            }
                        },
                        scope: this
                    });
                }

                chart.validateData();
            }
        }

        if(scope.try > 10)
        {
            this.stop();
            this.start();
        }

        if(scope.count == 0)
        {
            var interval = setInterval(function(){
                scope.refreshData(scope)
            }, scope.freq || 5000);
            scope.count++;
            scope.interval = interval;
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

        if(this.interval)
        {
            clearInterval(this.interval);
        }

        this.interval = null;
    },

    onRefresh: function () {
        this.getStore().reload();
    }
});

Toc.MemCharts = function (config) {
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
    config.width = this.params.width || '25%';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        render: function (comp) {

            var configChart = function () {
                thisObj.gauge = AmCharts.makeChart(comp.body.id, {
                    "type": "gauge",
                    "adjustSize": true,
                    "fontSize": 10,
                    "marginBottom": 2,
                    "marginLeft": 2,
                    "marginRight": 2,
                    "marginTop": 2,
                    //"theme": "light",
                    "axes": [
                        {
                            "axisThickness": 1,
                            "axisAlpha": 0.2,
                            "tickAlpha": 0.2,
                            "valueInterval": 20,
                            "bottomTextColor" : "green",
                            labelsEnabled: false,
                            "bands": [
                                {
                                    "color": "#d6c50a",
                                    "endValue": 20,
                                    "startValue": 0
                                },
                                {
                                    "color": "#d68b0a",
                                    "endValue": 40,
                                    "startValue": 20
                                },
                                {
                                    "color": "#d6690a",
                                    "endValue": 60,
                                    "startValue": 40
                                },
                                {
                                    "color": "#d6550a",
                                    "endValue": 80,
                                    "startValue": 60
                                },
                                {
                                    "color": "#d6220a",
                                    "endValue": 100,
                                    "startValue": 80
                                }
                            ],
                            "bottomText": "0%",
                            "bottomTextYOffset": 0,
                            "endValue": 100
                        }
                    ],
                    "arrows": [
                        {
                            color : "black"
                        }
                    ],
                    "export": {
                        "enabled": false
                    }
                });
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        afterrender: function (comp) {

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

    Toc.MemCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MemCharts, Ext.Panel, {

    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {
            var gaugeChart = this.gauge;

            if(scope.reqs == 0)
            {
                scope.reqs++;

                scope.transactionId = Ext.Ajax.request({
                    url: Toc.CONF.LINUX_URL,
                    params: {
                        module: 'servers',
                        action: 'mem_usage',
                        server_user: this.server_user,
                        server_pass: this.server_pass,
                        db_host: this.db_host
                    },
                    callback: function (options, success, response) {

                        scope.reqs--;
                        scope.try = 0;
                        scope.setTitle(scope.label);

                        if(success)
                        {
                            var json = Ext.decode(response.responseText);

                            if (json) {
                                if(json.comment && json.comment.length > 2)
                                {
                                    if (gaugeChart) {
                                        if (gaugeChart.arrows) {
                                            if (gaugeChart.arrows[ 0 ]) {
                                                if (gaugeChart.arrows[ 0 ].setValue) {
                                                    gaugeChart.arrows[ 0 ].setValue(json.comment);
                                                    gaugeChart.axes[ 0 ].setBottomText(json.comment);
                                                    gaugeChart.axes[ 0 ].bottomTextColor = "red";
                                                }
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    if(json.pct)
                                    {
                                        if (gaugeChart) {
                                            if (gaugeChart.arrows) {
                                                if (gaugeChart.arrows[ 0 ]) {
                                                    if (gaugeChart.arrows[ 0 ].setValue) {
                                                        gaugeChart.arrows[ 0 ].setValue(Math.round(json.pct));
                                                        gaugeChart.arrows[ 0 ].color = json.pct > 0 ? "red" : "black";
                                                        gaugeChart.axes[ 0 ].setBottomText(Math.round(json.pct) + "%");
                                                        gaugeChart.axes[ 0 ].bottomTextColor = "green";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if (gaugeChart) {
                                if (gaugeChart.arrows) {
                                    if (gaugeChart.arrows[ 0 ]) {
                                        if (gaugeChart.arrows[ 0 ].setValue) {
                                            gaugeChart.arrows[ 0 ].setValue(0);
                                            //gaugeChart.axes[ 0 ].setBottomText("Time out");
                                            gaugeChart.axes[ 0 ].setBottomText(response.responseText);
                                            gaugeChart.axes[ 0 ].bottomTextColor = "red";
                                        }
                                    }
                                }
                            }
                        }
                    },
                    scope: this
                });
            }
        }

        if(scope.try > 10)
        {
            scope.setTitle(scope.label);
            scope.try = 0;
        }

        if(scope.count == 0)
        {
            var interval = setInterval(function(){
                scope.refreshData(scope)
            }, scope.freq || 5000);
            scope.count++;
            scope.interval = interval;
        }
    },

    onRefresh: function () {
        this.getStore().reload();
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

        if(this.interval)
        {
            clearInterval(this.interval);
        }

        this.interval = null;
    }
});

Toc.MemoryCharts = function (config) {
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
    config.width = this.params.width || '25%';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        render: function (comp) {

            var configChart = function () {
                thisObj.data = [];

                var chart;
                chart = new AmCharts.AmSerialChart();
                chart.width = '100%';
                chart.marginBottom = 1;
                chart.marginLeft = 1;
                chart.marginRight = 1;
                chart.marginTop = 1;
                //chart.autoResize = true;

                var type = "line";
                chart.dataProvider = thisObj.data;
                chart.marginTop = 5;
                chart.categoryField = "category";

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
                //valueAxis.maximum = 100;
                //valueAxis.title = "%";
                valueAxis.titleColor = "green";
                valueAxis.labelsEnabled = false;
                chart.addValueAxis(valueAxis);

                // GRAPHS
                var graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Utilise";
                graph.valueField = "used";
                graph.lineColor = "red";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Libre";
                graph.valueField = "free";
                graph.lineColor = "green";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                //graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
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

    Toc.MemoryCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.MemoryCharts, Ext.Panel, {

    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {
            if(scope.chart)
            {
                var chart = scope.chart;

                var valueAxis = chart.valueAxes[0];

                if(scope.reqs == 0)
                {
                    scope.reqs++;
                    scope.transactionId = Ext.Ajax.request({
                        url: Toc.CONF.LINUX_URL,
                        params: {
                            module: 'servers',
                            action: 'memory_usage',
                            server_user: this.server_user,
                            server_pass: this.server_pass,
                            db_host: this.db_host
                        },
                        callback: function (options, success, response) {
                            scope.reqs--;
                            scope.try = 0;
                            scope.setTitle(scope.label);

                            if(success)
                            {
                                var json = Ext.decode(response.responseText);

                                //console.debug(json);
                                var data = json ? json.records : [];

                                this.data.push(data);

                                if (this.data.length > 200) {
                                    this.data.shift();
                                }

                                chart.dataProvider = this.data;
                                if (chart.chartData.length > 200) {
                                    chart.chartData.shift();
                                }

                                if (valueAxis) {
                                    valueAxis.titleColor = "green";
                                    valueAxis.labelsEnabled = false;
                                    valueAxis.title = "";
                                    if (!json) {
                                        valueAxis.titleColor = "red";
                                        valueAxis.title = "No Data";
                                    }
                                    else {
                                        valueAxis.titleColor = "green";
                                        valueAxis.labelsEnabled = false;
                                        valueAxis.title = "";

                                        if (json.comment) {
                                            valueAxis.titleColor = "red";
                                            valueAxis.title = json.comment;
                                            valueAxis.labelsEnabled = true;
                                        }
                                    }

                                    chart.validateNow();
                                }
                            }
                            else
                            {
                                if (valueAxis) {
                                    valueAxis.labelsEnabled = false;
                                    valueAxis.titleColor = "red";
                                    valueAxis.title = "Time out";

                                    chart.validateNow();
                                }
                            }
                        },
                        scope: this
                    });
                }

                chart.validateData();
            }

            if(scope.try > 10)
            {
                this.stop();
                this.start();
            }

            if(scope.count == 0)
            {
                var interval = setInterval(function(){
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

        if(this.interval)
        {
            clearInterval(this.interval);
        }

        this.interval = null;
    }
});

Toc.DiskCharts = function (config) {
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.count = 0;
    config.reqs = 0;
    config.try = 0;
    config.width = this.params.width || '25%';
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
                chart.categoryField = "name";

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
                valueAxis.maximum = 100;
                //valueAxis.title = "%";
                valueAxis.titleColor = "green";
                valueAxis.labelsEnabled = false;
                chart.addValueAxis(valueAxis);

                // GRAPHS
                var graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Read";
                graph.valueField = "read";
                graph.lineColor = "darkblue";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
                chart.addGraph(graph);

                graph = new AmCharts.AmGraph();
                graph.type = type; // it's simple line graph
                graph.title = "Write";
                graph.valueField = "write";
                graph.lineColor = "red";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.6; // setting fillAlphas to > 0 value makes it area graph
                graph.balloonText = "<span style='font-size:14px; color:#000000;'><b>[[value]]</b></span>";
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

    Toc.DiskCharts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DiskCharts, Ext.Panel, {

    refreshData: function (scope) {
        scope.setTitle(scope.title + '.');
        scope.try++;
        if (scope && scope.started) {
            if(scope.chart)
            {
                var chart = scope.chart;
                var valueAxis = chart.valueAxes[0];

                if(scope.reqs == 0)
                {
                    scope.reqs++;

                    scope.transactionId = Ext.Ajax.request({
                        url: Toc.CONF.LINUX_URL,
                        params: {
                            module: 'servers',
                            action: 'disk_activity',
                            server_user: this.server_user,
                            server_pass: this.server_pass,
                            db_host: this.db_host
                        },
                        callback: function (options, success, response) {
                            scope.reqs--;
                            scope.try = 0;
                            scope.setTitle(scope.label);

                            if(success)
                            {
                                var json = Ext.decode(response.responseText);

                                this.data = json ? json.records : [];

                                if (valueAxis) {
                                    valueAxis.titleColor = "green";
                                    valueAxis.title = "";
                                    valueAxis.labelsEnabled = false;
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

                                if(json && json.records)
                                {
                                    if(json.records.length > 0)
                                    {
                                        chart.validateData();
                                    }
                                }
                            }
                            else
                            {
                                if (valueAxis) {
                                    valueAxis.labelsEnabled = false;
                                    valueAxis.titleColor = "red";
                                    valueAxis.title = "Time out";

                                    chart.validateNow();
                                }
                            }
                        },
                        scope: this
                    });
                }
            }
        }

        if(scope.try > 10)
        {
            scope.setTitle(scope.label);
            scope.try = 0;
        }

        if(scope.count == 0)
        {
            var interval = setInterval(function(){
                scope.refreshData(scope)
            }, scope.freq || 5000);
            scope.count++;
            scope.interval = interval;
        }
    },

    onRefresh: function () {
        this.getStore().reload();
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

        if(this.interval)
        {
            clearInterval(this.interval);
        }

        this.interval = null;
    }
});

Toc.exploreServer = function (node, panel) {
    panel.removeAll();

    if(node.id == 0)
    {
        panel.removeAll();
    }
    else
    {
        if (node) {

            if (node) {
                panel.node = node;
            }
            else {
                Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun element selectionne !!!");
                return false;
            }

            panel.getEl().mask("Chargement ...");

            var pnlLogs = new Toc.logPanel({host: node.attributes.host, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, content_id: node.attributes.servers_id, content_type: 'servers', owner: node.attributes.owner});
            var pnlFS = new Toc.fsGrid({host: node.attributes.host, server_port: node.attributes.server_port, server_pass: node.attributes.server_pass, server_user: node.attributes.server_user, servers_id: node.attributes.servers_id, owner: this.owner, typ: node.attributes.typ});
            var pnlDocuments = new Toc.content.DocumentsPanel({content_id: this.serversId, content_type: 'servers', owner: Toc.content.ContentManager});
            var pnlLinks = new Toc.content.LinksPanel({content_id: this.serversId, content_type: 'servers', owner: Toc.content.ContentManager});
            var pnlComments = new Toc.content.CommentsPanel({content_id: this.serversId, content_type: 'servers', owner: Toc.content.ContentManager});

            var tab = new Ext.TabPanel({
                activeTab: 0,
                defaults: {
                    hideMode: 'offsets'
                },
                deferredRender: false,
                items: [pnlFS,pnlLogs,pnlDocuments,pnlLinks,pnlComments]
            });

            panel.add(tab);
            panel.doLayout();

            panel.getEl().unmask();
        }
        else
        {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun Server selectionne !!!");
        }
    }

    panel.mainPanel.doLayout();

    return true;
};

Toc.PSGrid = function (config) {
    var that = this;
    config = config || {};
    config.loadMask = true;
    config.header = false;
    config.title = 'Processes';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.listeners = {
        'rowclick': this.onRowClick
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'servers',
            action: 'list_ps',
            user: config.server_user,
            pass: config.server_pass,
            port: config.server_port,
            host: config.host,
            typ: config.typ,
            show_files: config.show_files || true
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'pid'
        }, [
            'pid',
            'cpu',
            'mem',
            'vsz',
            'rss',
            'stat',
            'start',
            'time',
            'command'
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
        { id: 'mount', header: 'Nom', dataIndex: 'mount', sortable: true},
        { header: '%', align: 'center', dataIndex: 'pct_used', renderer: Toc.content.ContentManager.renderProgress, sortable: true},
        { header: 'Taille (MB)', align: 'center', dataIndex: 'size', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Libre (MB)', align: 'center', dataIndex: 'dispo', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { header: 'Utilise (MB)', align: 'center', dataIndex: 'used', sortable: true, renderer: Toc.content.ContentManager.FormatNumber},
        { id: 'typ', header: 'Type', dataIndex: 'typ', align: 'center', sortable: true},
        { id: 'fs', header: 'Filesystem', dataIndex: 'fs', sortable: true},
        config.rowActions
    ]);
    config.autoExpandColumn = 'fs';
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
        }
    ];

    var thisObj = this;

    this.addEvents({'selectchange': true});
    Toc.fsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.fsGrid, Ext.grid.GridPanel, {
    onEdit: function (record) {
        var config = {
            server_user: this.server_user,
            server_pass: this.server_pass,
            server_port: this.server_port,
            host: this.host,
            caller: this.caller,
            show_files: this.show_files,
            close_dlg: this.close_dlg,
            file_name: this.file_name,
            dlg: this.dlg,
            mount: record.get("mount")
        };

        var dlg = new Toc.Dirbrowser(config);
        dlg.setTitle(record.get("mount"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        if (this.close_dlg && this.dlg) {
            dlg.on('close', function () {
                this.dlg.close();
            }, this);
        }

        dlg.show(config, this.owner);
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
            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
    },

    onRowClick: function (grid, index, obj) {
        var item = grid.getStore().getAt(index);
        this.fireEvent('selectchange', item);
    }
});