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

