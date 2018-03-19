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
        activate: function (panel) {
            if (!this.activated) {
                this.activated = true;
                this.getStore().load();
            }
        },
        'rowclick': this.onRowClick, scope: this
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

Toc.SqlDetail = function (record, scope) {
    console.log('SqlDetail ...');
    console.debug(scope);
    var dlg = new Toc.SqlDialog({sql_id : record.get('sql_id'),db_user: scope.db_user,
        db_pass: scope.db_pass,
        db_port: scope.db_port,
        db_host: scope.host,
        db_sid: scope.sid});

    dlg.show(scope);
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


