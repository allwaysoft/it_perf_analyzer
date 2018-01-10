<?php
?>
Toc.database_explorer.TreePanel = function (config) {
    var that = this;

    config = config || {};

    config.region = 'west';
    config.border = true;
    config.autoScroll = true;
    config.containerScroll = true;
    config.split = true;
    config.width = 250;
    config.enableDD = true;
    config.rootVisible = true;
    config.count = 0;
    config.reqs = 0;
    config.started = false;

    config.root = new Ext.tree.AsyncTreeNode({
        text: config.rootTitle || 'Databases',
        draggable: false,
        icon : 'templates/default/images/icons/16x16/database.png',
        id: '0',
        expanded: true
    });
    config.currentCategoryId = '0';

    config.loader = new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: false,
        baseParams: {
            module: 'databases',
            action: 'load_layout_tree'
        },
        listeners: {
            load: function () {
                this.expandAll();
            },
            scope: this
        }
    });

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.refresh,
            scope: this
        }
    ];

    config.listeners = {
        "load": this.onLoad,
        "beforeload": this.onBeforeload,
        "click": this.onCategoryNodeClick,
        "nodedragover": this.onCategoryNodeDragOver,
        "nodedrop": this.onCategoryNodeDrop,
        "expandnode": this.onExpandNode,
        "contextmenu": this.onCategoryNodeRightClick,
        deactivate: function (panel) {
            //console.log('deactivate');
            //that.onStop();
        },
        destroy: function (panel) {
            //console.log('destroy');
            //that.onStop();
        },
        disable: function (panel) {
            //console.log('disable');
            //that.onStop();
        },
        remove: function (container, panel) {
            //console.log('remove');
            //that.onStop();
        },
        removed: function (container, panel) {
            //console.log('removed');
            //that.onStop();
        }
    };

    this.addEvents({'selectchange': true});

    Toc.database_explorer.TreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.database_explorer.TreePanel, Ext.tree.TreePanel, {
    setCategoryId: function (categoryId) {
        var currentNode = this.getNodeById(categoryId);
        if (currentNode) {
            currentNode.select();
            this.fireEvent('selectchange', currentNode);
        }
    },

    onLoad: function (node) {
        //console.log('refresh');
        if (!node) {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun element selectionné !!!");
        }
        else {
            if (node.id == 0) {
                node.select();
                this.fireEvent('selectchange', node);
            }
        }
    },

    onBeforeload: function (node) {
        //console.log('onBeforeload');
        return true;
    },

    onExpandNode: function (node) {
        //console.log('onExpandNode');
        if (node.attributes) {
            if (node.attributes.content_type) {
                if (node.attributes.content_type == 'customer') {
                    this.customers_id = node.id;
                }
            }
        }
    },

    onCategoryNodeClick: function (node) {
        if (node) {
            //console.log('onCategoryNodeClick');
            //console.debug(node);

            this.currentCategoryId = node.id;
            this.currentNode = node;
            node.expand();
            this.setCategoryId(node.id);
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun element selectionné !!!");
        }
    },

    onCategoryNodeDragOver: function (e) {
        return false;
    },

    onCategoryNodeDrop: function (e) {
    },

    getCategoriesPath: function (node) {
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

    onCategoryNodeRightClick: function (node, event) {
    },

    refresh: function () {
        this.root.reload();
        /*if(this.currentCategoryId)
         {
         this.setCategoryId(this.currentCategoryId);
         }*/
    },

    refreshGrid: function (parent_id) {
        if (parent_id) {
            this.setCategoryId(parent_id);
        }
    },

    getCategoryPermissions: function (node) {
        node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;
        var permissions = node.attributes.permissions;

        return permissions != undefined ? permissions : '';
    },

    setFilter: function (filter) {
        this.loader.baseParams.filter = filter;
    }
});