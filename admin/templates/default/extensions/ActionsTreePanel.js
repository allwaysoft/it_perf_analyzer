/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 10/2/11
 * Time: 11:49 AM
 * To change this template use File | Settings | File Templates.
 */
Toc.ActionsTreePanel = function(config) {
    config = config || {};

    config.region = 'west';
    config.border = false;
    config.autoScroll = true;
    config.containerScroll = true;
    config.split = true;
    config.width = 200;
    config.enableDD = true;
    config.rootVisible = true;

    config.root = new Ext.tree.AsyncTreeNode({
        text: 'Actions',
        draggable: false,
        id: '0',
        expanded: true
    });
    config.currentCategoryId = '0';

    config.loader = new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        requestMethod : 'GET',
        preloadChildren: false,
        baseParams: {
            module: 'jobs',
            action: 'list_actions_tree',
            filter : config.filter || -1,
            scc : config.showContentCount !== undefined ? config.showContentCount : 0,
            sh : config.showHome !== undefined ? config.showHome : 1,
            cp : config.checkPermission !== undefined ? config.checkPermission : 1,
            content_type : config.content_type
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

    Toc.ActionsTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ActionsTreePanel, Ext.tree.TreePanel, {

    setCategoryId: function(categoryId) {
        var currentNode = this.getNodeById(categoryId);
        if(currentNode)
        {
            currentNode.select();
            this.currentCategoryId = categoryId;

            this.fireEvent('selectchange', categoryId);
        }
        else
        {
            //Ext.MessageBox.alert(TocLanguage.msgErrTitle,'Espace ' + categoryId + ' invalide ... Veuillez contacter le support');
        }
    },

    onCategoryNodeClick: function (node) {
        node.expand();
        this.setCategoryId(node.id);
    },

    onCategoryNodeDragOver: function (e) {
        if (e.target.leaf == true) {
            e.target.leaf = false;
        }

        return true;
    },

    onCategoryNodeDrop: function(e) {
        if (e.point == 'append') {
            parent_id = e.target.id;
            currentCategoryId = e.target.id;
        } else {
            parent_id = e.target.parentNode.id;
            currentCategoryId = e.target.parentNode.id;
        }

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'Actions',
                action: 'move_Actions',
                Actions_ids: e.dropNode.id,
                parent_category_id: parent_id
            },
            callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    this.setCategoryId(currentCategoryId);
                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    },

    getActionsPath: function(node) {
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

    onCategoryNodeRightClick: function(node, event) {
        event.preventDefault();
        node.select();

        this.menuContext = new Ext.menu.Menu({
            items: [
                {
                    text: TocLanguage.btnAdd,
                    iconCls: 'add',
                    handler: function() {
                        var dlg = this.owner.createActionsDialog();

                        dlg.on('saveSuccess', function(feedback, ActionsId, text) {
                            node.appendChild({
                                id: ActionsId,
                                text: text,
                                cls: 'x-tree-node-collapsed',
                                parent_id: node.id,
                                leaf: true
                            });

                            node.expand();
                        }, this);

                        dlg.show(null, this.getActionsPath(node));
                    },
                    scope: this
                },
                {
                    text: TocLanguage.tipEdit,
                    iconCls: 'edit',
                    handler: function() {
                        var dlg = this.owner.createActionsDialog();

                        dlg.on('saveSuccess', function(feedback, ActionsId, text) {
                            node.setText(text);
                        }, this);

                        dlg.show(node.id, this.getActionsPath(node));
                    },
                    scope: this
                },
                {
                    text: TocLanguage.tipDelete,
                    iconCls: 'remove',
                    handler:  function() {
                        Ext.MessageBox.confirm(
                            TocLanguage.msgWarningTitle,
                            TocLanguage.msgDeleteConfirm,
                            function (btn) {
                                if (btn == 'yes') {
                                    currentCategoryId = node.parentNode.id;

                                    Ext.Ajax.request({
                                        url: Toc.CONF.CONN_URL,
                                        params: {
                                            module: 'Actions',
                                            action: 'delete_category',
                                            Actions_id: node.id
                                        },
                                        callback: function (options, success, response) {
                                            var result = Ext.decode(response.responseText);

                                            if (result.success == true) {
                                                var pNode = node.parentNode;
                                                pNode.ui.addClass('x-tree-node-collapsed');

                                                node.remove();
                                                this.setCategoryId(currentCategoryId);
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
                    scope: this
                }
            ]
        });

        this.menuContext.showAt(event.getXY());
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