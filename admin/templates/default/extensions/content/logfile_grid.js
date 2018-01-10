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