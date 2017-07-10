Toc.content.PermissionsPanel = function(config) {

    config = config || {};
    config.title = 'Permissions';
    config.loadMask = true;
    config.border = false;
    config.region = 'center';
    //config.autoScroll = true;
    //config.autoHeight = true;
    config.content_id = config.content_id || null;
    config.module = config.module || 'content';
    config.action = config.action || 'list_permissions';
    config.id_field = config.id_field || 'roles_id'
    config.autoExpandColumn = config.autoExpandColumn || 'roles_name';
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: config.module,
            action: config.action,
            content_id: config.content_id,
            content_type : config.content_type || ''
        },
        reader: new Ext.data.JsonReader({
                root: Toc.CONF.JSON_READER_ROOT,
                totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
                id: config.id_field
            },
            [
                config.id_field,
                'content_type',
                'administrators_id',
                'icon',
                'roles_id',
                config.autoExpandColumn,
                'can_read',
                'can_write',
                'can_modify',
                'can_publish',
                'content_id'
            ]),
        autoLoad: false,
        listeners:{
            load:function(store, records, options) {
                this.loaded = true;
            },
            scope:this
        }
    });

    render = function(status) {
        if (status == 1) {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.listeners = {
        activate : function(panel) {
            if (this.content_id && this.content_type) {
                if (!this.loaded) {
                    this.refreshGrid(this.content_id, this.content_type);
                }
            }
            else {
//                Ext.Msg.alert(TocLanguage.msgErrTitle, 'Vous devez specifier l identifiant du contenu et son type');
                return;
            }
        },
        show : function(comp) {
        },
        beforeshow : function(comp) {
            if (!this.content_id || !this.content_type) {
//                Ext.Msg.alert(TocLanguage.msgErrTitle, 'Vous devez specifier l identifiant du contenu et son type');
                return false;
            }
        },
        show : function(comp) {
        },scope: this
    };

    config.cm = new Ext.grid.ColumnModel([
        {header: '', dataIndex: 'icon', width : 24},
        {
            id: config.autoExpandColumn,
            header: 'Nom',
            sortable: true,
            dataIndex: config.autoExpandColumn
        },
        { header: 'Lecture', align: 'center', renderer: render, dataIndex: 'can_read'},
        { header: 'Ecriture', align: 'center', renderer: render, dataIndex: 'can_write'},
        { header: 'Modification', align: 'center', renderer: render, dataIndex: 'can_modify'},
        { header: 'Publication', align: 'center', renderer: render, dataIndex: 'can_publish'}
    ]);

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true,
        listeners:{
            scope:this,
            specialkey: function(f,e){
                if(e.getKey()==e.ENTER){
                    this.onSearch();
                }
            }
        }
    });

    config.tbar = [
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

    config.bbar = new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: config.ds,
        steps: Toc.CONF.GRID_STEPS,
        beforePageText : TocLanguage.beforePageText,
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

    var thisObj = this;

    Toc.content.PermissionsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.PermissionsPanel, Ext.grid.GridPanel, {

    refreshGrid: function (content_id, content_type) {
        this.content_type = content_type || this.content_type;

        if(content_id && this.content_type)
        {
            var store = this.getStore();

            store.baseParams['content_id'] = content_id;
            store.baseParams['content_type'] = content_type;
            store.load();
        }
    },

    setCategoriesId: function (content_id) {
        this.content_id = content_id;
    },

    onClick: function(e, target) {
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
                var field_id = this.getStore().getAt(row).get(this.id_field);
                var content_id = this.getStore().getAt(row).get('content_id');
                var content_type = this.getStore().getAt(row).get('content_type');
                var roles_id = this.getStore().getAt(row).get('roles_id');
                var module = 'setPermission';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, field_id, content_id, content_type, roles_id, permission, flag);

                        break;
                }
            }
        }
    },

    onClick: function(e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var col = v.findCellIndex(t);
        var action = false;

        if (row !== false) {
            if (col > 0) {
                var record = this.getStore().getAt(row);
                var flagName = this.getColumnModel().getDataIndex(col);
                this.fireEvent('selectchange', record);
            }

            var btn = e.getTarget(".img-button");

            if (btn) {
                var field_id = this.getStore().getAt(row).get(this.id_field);
                action = btn.className.replace(/img-button btn-/, '').trim();
                var content_id = this.getStore().getAt(row).get('content_id');
                var content_type = this.getStore().getAt(row).get('content_type');
                var roles_id = this.getStore().getAt(row).get('roles_id');
                var module = 'setPermission';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.setPermission(module, field_id, content_id, content_type, roles_id, flagName, flag);
                        break;
                }
            }
        }
    },

    onRefresh: function() {
        var store = this.getStore();
        store.load();
    },

    onSearch: function() {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['current_category_id'] = this.categoriesId || -1;
        store.baseParams['search'] = filter;
        store.reload();
        store.baseParams['search'] = '';
    },

    setPermission: function(action, field_id, content_id, content_type, roles_id, permission, flag) {
        var params = {
            module: this.module,
            action: action,
            content_id: content_id,
            content_type : content_type,
            roles_id: roles_id,
            flag: flag,
            permission : permission
        };

        params[this.id_field] = field_id;

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params : params,
            callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(field_id).set(permission, flag);
                    store.commitChanges();
                }
            },
            scope: this
        });
    }
});