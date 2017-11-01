Toc.dashboards.DashboardsTreePanel = function (config) {
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
        text: config.rootTitle || 'Dashboards',
        draggable: false,
        id: '0',
        expanded: true
    });
    config.currentCategoryId = '0';

    config.loader = new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: false,
        baseParams: {
            module: 'categories',
            action: 'load_dashboard_tree',
            filter: config.filter || -1,
            scc: 0,
            sh: 0,
            cp: 0
        },
        listeners: {
            load: function () {
                this.expandAll();

                if (this.autoRefresh) {
                    //this.onStart();
                }
                else {
                    console.log('autoRefresh not defined ...')
                }
//this.setCategoryId(this.currentCategoryId || 0);
            },
            scope: this
        }
    });

    config.tbar = config.can_edit ? [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.refresh,
            scope: this
        },
        '-',
        {
            text: '',
            iconCls: 'add',
            disabled: true,
            handler: this.onAdd,
            scope: that
        },
        '-',
        {
            text: '',
            iconCls: 'remove',
            handler: this.onDelete,
            disabled: true,
            scope: that
        },
        '-',
        {
            text: '',
            iconCls: 'icon-move-record',
            handler: this.onMove,
            disabled: true,
            scope: that
        },
        '-',
        {
            text: '',
            iconCls: 'icon-copy-record',
            handler: this.onCopy,
            disabled: true,
            scope: that
        },
        '-',
        {
            text: '',
            iconCls: 'icon-edit-record',
            handler: this.onEdit,
            disabled: true,
            scope: that
        }
    ] : [
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
            that.onStop();
        },
        destroy: function (panel) {
            //console.log('destroy');
            that.onStop();
        },
        disable: function (panel) {
            //console.log('disable');
            that.onStop();
        },
        remove: function (container, panel) {
            //console.log('remove');
            that.onStop();
        },
        removed: function (container, panel) {
            //console.log('removed');
            that.onStop();
        }
    };

    this.addEvents({'selectchange': true});

    Toc.dashboards.DashboardsTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.dashboards.DashboardsTreePanel, Ext.tree.TreePanel, {
    onStart: function () {
    },
    onStop: function () {
    },
    setCategoryId: function (categoryId) {
        var currentNode = this.getNodeById(categoryId);
        if (currentNode) {
            currentNode.select();
            this.fireEvent('selectchange', currentNode);
        }
    },

    onAdd: function () {
        var dlg = null;

        if (this.currentNode) {
            if (this.content_type) {
                switch (this.content_type) {
                    case 'root':
                        dlg = new Toc.PlantDialog({customers_id: this.currentNode.id});

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        dlg.center();
                        break;

                    case 'folder':
                        dlg = new Toc.LineDialog({plants_id: this.currentNode.id});

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        dlg.center();
                        break;
                }
            }
            else {
                this.topToolbar.items.items[2].disable();
                this.topToolbar.items.items[4].disable();
                this.topToolbar.items.items[6].disable();
                this.topToolbar.items.items[8].disable();
                this.topToolbar.items.items[10].disable();
                this.topToolbar.items.items[12].disable();
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun Type d'element defini !!!");
            }
        }
        else {
            this.topToolbar.items.items[2].disable();
            this.topToolbar.items.items[4].disable();
            this.topToolbar.items.items[6].disable();
            this.topToolbar.items.items[8].disable();
            this.topToolbar.items.items[10].disable();
            this.topToolbar.items.items[12].disable();
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun noeud selectionné !!!");
        }
    },

    onEdit: function () {
        if (this.currentNode) {
            if (this.content_type) {
                var dlg = null;
                switch (this.content_type) {
                    case 'page':
                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible d'editer cet element !!!");
                        break;

                    case 'dashboard':
                        dlg = new Toc.PlantDialog({customers_id: this.currentNode.parentNode.id, plants_id: this.currentNode.id});
                        dlg.setTitle("Editer une Usine : " + this.currentNode.text);

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        break;
                }
            }
            else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun Type d'element defini !!!");
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun noeud selectionné !!!");
        }
    },

    onDelete: function () {
        if (this.currentNode) {
            if (this.content_type) {
                switch (this.content_type) {
                    case 'customer':
                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible de supprimer cet element !!!");
                        break;

                    case 'plant':
                        Toc.DeletePlant(this.currentNode.id, this);
                        break;
                }
            }
            else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun Type d'element defini !!!");
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Aucun element selectionné !!!");
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
            }
        }
    },

    onCategoryNodeClick: function (node) {
        if (node) {
            if (node.attributes) {
                if (node.attributes.content_type) {

                    this.content_type = node.attributes.content_type;
                    if (this.can_edit) {
                        this.topToolbar.items.items[2].disable();
                        this.topToolbar.items.items[4].disable();
                        this.topToolbar.items.items[6].disable();
                        this.topToolbar.items.items[8].disable();
                        this.topToolbar.items.items[10].disable();
                    }

                    if (node.attributes.content_type == "root") {
                        if (this.can_edit) {
                            this.topToolbar.items.items[2].enable();
                            this.topToolbar.items.items[4].enable();
                            this.topToolbar.items.items[6].disable();
                            this.topToolbar.items.items[8].disable();
                            this.topToolbar.items.items[10].enable();
                        }
                    }
                    else if (node.attributes.content_type == "folder") {
                        if (this.can_edit) {
                            this.topToolbar.items.items[2].enable();
                            this.topToolbar.items.items[4].enable();
                            this.topToolbar.items.items[6].disable();
                            this.topToolbar.items.items[8].disable();
                            this.topToolbar.items.items[10].enable();
                        }

                    } else if (node.attributes.content_type == "dashboard") {
                        if (this.can_edit) {
                            this.topToolbar.items.items[2].enable();
                            this.topToolbar.items.items[4].disable();
                            this.topToolbar.items.items[6].enable();
                            this.topToolbar.items.items[8].enable();
                            this.topToolbar.items.items[10].enable();
                        }
                    }
                }
                else {
                    this.content_type = null;
                    if (this.can_edit) {
                        this.topToolbar.items.items[2].enable();
                        this.topToolbar.items.items[4].disable();
                        this.topToolbar.items.items[6].disable();
                        this.topToolbar.items.items[8].disable();
                        this.topToolbar.items.items[10].disable();
                        this.topToolbar.items.items[12].disable();
                    }
                }
            }

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