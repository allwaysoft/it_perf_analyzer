/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 10/2/11
 * Time: 11:49 AM
 * To change this template use File | Settings | File Templates.
 */
Toc.DatabasesTreePanel = function(config) {
    config = config || {};

    config.region = 'west';
    config.border = false;
    config.autoScroll = true;
    config.containerScroll = true;
    config.split = true;
    config.width = 170;
    config.enableDD = true;
    config.rootVisible = true;

    config.root = new Ext.tree.AsyncTreeNode({
        text: config.rootTitle || 'Databases',
        draggable: false,
        id: '0',
        expanded: true
    });
    config.currentCategoryId = '0';

    config.loader = new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: false,
        baseParams: {
            module: 'databases',
            action: 'list_databases_tree',
            filter : config.filter || -1,
            scc : config.showContentCount !== undefined ? config.showContentCount : 0,
            sh : config.showHome !== undefined ? config.showHome : 1,
            cp : config.checkPermission !== undefined ? config.checkPermission : 1
        },
        listeners: {
            load: function() {
                this.expandAll();
                this.setCategoryId(this.currentCategoryId || 0);
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
        "click": this.onCategoryNodeClick,
        "nodedragover": this.onCategoryNodeDragOver,
        "nodedrop": this.onCategoryNodeDrop,
        "contextmenu": this.onCategoryNodeRightClick
    };

    this.addEvents({'selectchange' : true});

    Toc.DatabasesTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DatabasesTreePanel, Ext.tree.TreePanel, {

    setCategoryId: function(categoryId) {
        var currentNode = this.getNodeById(categoryId);
        currentNode.select();
        this.currentCategoryId = categoryId;

        this.fireEvent('selectchange', categoryId);
    },

    onCategoryNodeClick: function (node) {
        node.expand();
        this.setCategoryId(node.id);
    },

    getCategoriesPath: function(node) {
        var cpath = [];
        node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;

        if (node.id == 0) {
            return 0;
        }

        while (node.id > 0) {
            cpath.push(node.id);
            node = node.parentNode;
        }

        return cpath.reverse().join('_');
    },

    refresh: function() {
        this.root.reload();
    },

    refreshGrid: function(parent_id) {
        if (parent_id) {
            this.setCategoryId(parent_id);
        }
    },

    getCategoryPermissions: function(node) {
        node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;
        var permissions = node.attributes.permissions;

        return permissions != undefined ? permissions : '';
    },

    setFilter: function(filter) {
        this.loader.baseParams.filter = filter;
    }
});