<?php
?>

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
    refreshData: function (scope) {
        var that = this;

        if (scope && scope.started) {
            if (scope.reqs == 0) {
                //console.log('reqs++');

                //console.log('starting eachChild LayoutTreePanel .... ');
                //console.debug(this.root.childNodes);

                var i = 0;
                while (i < this.root.childNodes.length) {
                    var node = that.getNodeById(this.root.childNodes[i].id);
                    that.setStatus(node.parentNode,node,scope,0,null);
                    scope.reqs++;
                    i++;
                }

            }

            if (scope.count == 0) {
                var interval = setInterval(function () {
                    scope.refreshData(scope);
                }, scope.freq || 10000);
                scope.count++;
                scope.interval = interval;
            }
            else {
                //console.log('that.reqs LayoutTreePanel .... ' + scope.reqs);
            }
        }
    },
    setStatus: function (parentNode,node,scope,index,status) {
        var that = this;

        var iel = null;
        var el = null;

        //if(status == 'error' && node.attributes.content_type != 'customer' && node.attributes.content_type != 'plant' && node.attributes.content_type != 'line')
        if(1 == 2)
        {
            scope.reqs--;

            iel = node.getUI().getIconEl();
            if (iel) {
                el = Ext.get(iel);
                if (el) {
                    el.addClass('x-tree-node-inline-icon');
                    el.dom.src = 'templates/default/images/icons/16x16/' + node.attributes.content_type + '_error.png';
                }
            }

            if (node.hasChildNodes()) {
                var i = 0;
                while (i < node.childNodes.length) {
                    var noeud = that.getNodeById(node.childNodes[i].id);
                    that.setStatus(node,noeud,scope,i,status);
                    scope.reqs++;
                    //console.log(scope.reqs);
                    i++;
                }
            }
        }
        else
        {
            Ext.Ajax.request({
                url: Toc.CONF.CONN_URL,
                params: {
                    module: 'categories',
                    action: 'get_status',
                    content_type: node.attributes.content_type,
                    content_id: node.id
                },
                callback: function (options, success, response) {
                    //console.log(scope.reqs);
                    scope.reqs--;
                    var json = Ext.decode(response.responseText);
                    //console.debug(json);

                    if (success) {
                        iel = node.getUI().getIconEl();
                        if (iel) {
                            el = Ext.get(iel);
                            if (el) {
                                el.addClass('x-tree-node-inline-icon');
                                el.dom.src = json.cls;
                            }
                        }

                        if (node.hasChildNodes()) {
                            var i = 0;
                            while (i < node.childNodes.length) {
                                var noeud = that.getNodeById(node.childNodes[i].id);
                                that.setStatus(node,noeud,scope,i,json.feedback);
                                scope.reqs++;
                                //console.log(scope.reqs);
                                i++;
                            }
                        }
                    }
                    else {
                        //console.log(json.feedback);
                    }
                },
                scope: this
            });
        }
    },

    onStart: function () {
        //console.log('starting LayoutTreePanel...');
        this.count = 0;
        this.reqs = 0;
        this.started = true;
        this.refreshData(this);
        //console.log('started LayoutTreePanel...')
    },

    onStop: function () {
        //console.log('stopping LayoutTreePanel...');
        this.started = false;
        this.count = 10;
        this.reqs = 10;
        this.refreshData(this);

        if (this.interval) {
            clearInterval(this.interval);
        }
        else {
            //Ext.MessageBox.alert(TocLanguage.msgErrTitle,"No job defined !!!");
        }

        //console.log('stopped LayoutTreePanel...');
    },

    setCategoryId: function (categoryId) {
        var currentNode = this.getNodeById(categoryId);
        if (currentNode) {
            currentNode.select();
            this.fireEvent('selectchange', currentNode);
        }
    },

    onExport: function () {
        if (this.currentNode) {
            if (this.content_type) {
                switch (this.content_type) {
                    case 'asset':
                        Toc.ExportAsset(this.currentNode.id, this.currentNode.text, this);
                        break;

                    case 'customer':
                        Toc.ExportCustomer(this.currentNode.id, this.currentNode.text, this);
                        break;

                    default:
                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible d'exporter cet element !!!");
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

    onAdd: function () {
        var dlg = null;

        if (this.currentNode) {
            if (this.content_type) {
                switch (this.content_type) {
                    case 'customer':
                        dlg = new Toc.PlantDialog({customers_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        //dlg.setTitle(record.get('layout_name'));

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        dlg.center();
                        break;

                    case 'plant':
                        dlg = new Toc.LineDialog({plants_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        //dlg.setTitle(record.get('layout_name'));

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        dlg.center();
                        break;

                    case 'line':
                        dlg = new Toc.AssetDialog({lines_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        //dlg.setTitle(record.get('layout_name'));

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        dlg.center();
                        break;

                    case 'asset':
                        dlg = new Toc.ComponentDialog({asset_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        //dlg.setTitle(record.get('layout_name'));

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        dlg.center();
                        break;

                    case 'component':
                        dlg = new Toc.SensorDialog({component_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        //dlg.setTitle(record.get('layout_name'));

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
                    case 'customer':
                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, "Impossible d'editer cet element !!!");
                        break;

                    case 'plant':
                        dlg = new Toc.PlantDialog({customers_id: this.currentNode.parentNode.id, plants_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        dlg.setTitle("Editer une Usine : " + this.currentNode.text);

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        break;

                    case 'line':
                        dlg = new Toc.LineDialog({plants_id: this.currentNode.parentNode.id, lines_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        dlg.setTitle("Editer une Ligne : " + this.currentNode.text);

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        break;

                    case 'asset':
                        dlg = new Toc.AssetDialog({lines_id: this.currentNode.parentNode.id, asset_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        dlg.setTitle("Editer un Asset : " + this.currentNode.text);

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        break;

                    case 'component':
                        dlg = new Toc.ComponentDialog({asset_id: this.currentNode.parentNode.id, component_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        dlg.setTitle("Editer un Component : " + this.currentNode.text);

                        dlg.on('saveSuccess', function () {
                            this.refresh();
                        }, this);

                        dlg.show();
                        break;

                    case 'sensor':
                        dlg = new Toc.SensorDialog({asset_id: this.currentNode.parentNode.id, sensors_id: this.currentNode.id});
                        //var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
                        dlg.setTitle("Editer un Sensor : " + this.currentNode.text);

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

                    case 'line':
                        Toc.DeleteLine(this.currentNode.id, this);
                        break;

                    case 'asset':
                        Toc.DeleteAsset(this.currentNode.id, this);
                        break;

                    case 'component':
                        Toc.DeleteComponent(this.currentNode.id, this);
                        break;

                    case 'sensor':
                        Toc.DeleteSensor(this.currentNode.id, this);
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

            if (node.attributes) {
                if (node.attributes.content_type) {
                    if (node.attributes.content_type == 'customer') {
                        this.customers_id = node.id;
                    }

                    this.content_type = node.attributes.content_type;
                    if (this.can_edit) {
                        this.topToolbar.items.items[2].disable();
                        this.topToolbar.items.items[4].disable();
                        this.topToolbar.items.items[6].disable();
                        this.topToolbar.items.items[8].disable();
                        this.topToolbar.items.items[10].disable();
                        this.topToolbar.items.items[12].disable();
                    }

                    if (node.attributes.content_type == "asset") {
                        if (this.can_edit) {
                            this.topToolbar.items.items[2].enable();
                            this.topToolbar.items.items[4].enable();
                            this.topToolbar.items.items[6].enable();
                            this.topToolbar.items.items[8].enable();
                            this.topToolbar.items.items[10].enable();
                            this.topToolbar.items.items[12].enable();
                        }

                    }
                    else if (node.attributes.content_type == "plant" || node.attributes.content_type == "line" || node.attributes.content_type == "system" || node.attributes.content_type == "subsystem" || node.attributes.content_type == "component") {
                        if (this.can_edit) {
                            this.topToolbar.items.items[2].enable();
                            this.topToolbar.items.items[4].enable();
                            this.topToolbar.items.items[6].enable();
                            this.topToolbar.items.items[8].enable();
                            this.topToolbar.items.items[10].enable();
                            this.topToolbar.items.items[12].disable();
                        }

                    } else if (node.attributes.content_type == "sensor") {
                        if (this.can_edit) {
                            this.topToolbar.items.items[2].disable();
                            this.topToolbar.items.items[4].enable();
                            this.topToolbar.items.items[6].enable();
                            this.topToolbar.items.items[8].enable();
                            this.topToolbar.items.items[10].enable();
                            this.topToolbar.items.items[12].disable();
                        }
                    }
                    else if (node.attributes.content_type == "customer") {
                        if (this.can_edit) {
                            this.topToolbar.items.items[2].enable();
                            this.topToolbar.items.items[4].disable();
                            this.topToolbar.items.items[6].disable();
                            this.topToolbar.items.items[8].disable();
                            this.topToolbar.items.items[10].disable();
                            this.topToolbar.items.items[12].enable();
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

            node.customers_id = this.customers_id;

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
        switch (e.data.node.attributes.content_type) {
            case 'sensor':
                return e.target.attributes.content_type == 'component';
            case 'component':
                return e.target.attributes.content_type == 'asset';
            case 'asset':
                return e.target.attributes.content_type == 'line';
            case 'line':
                return e.target.attributes.content_type == 'plant';
            case 'plant':
                return e.target.attributes.content_type == 'customer';
        }

        return false;
    },

    onCategoryNodeDrop: function (e) {
        //console.log('onCategoryNodeDrop');
        //console.debug(e);
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
                module: 'categories',
                action: 'move_categories',
                categories_ids: e.dropNode.id,
                parent_category_id: parent_id
            },
            callback: function (options, success, response) {
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
        //console.log('onCategoryNodeDrop');
        //console.debug(e);
        event.preventDefault();
        node.select();

        this.menuContext = new Ext.menu.Menu({
            items: [
                {
                    text: TocLanguage.btnAdd,
                    iconCls: 'add',
                    handler: function () {
                        var dlg = this.owner.createCategoriesDialog();

                        dlg.on('saveSuccess', function (feedback, categoriesId, text) {
                            node.appendChild({
                                id: categoriesId,
                                text: text,
                                cls: 'x-tree-node-collapsed',
                                parent_id: node.id,
                                leaf: true
                            });

                            node.expand();
                        }, this);

                        dlg.show(null, this.getCategoriesPath(node));
                    },
                    scope: this
                },
                {
                    text: TocLanguage.tipEdit,
                    iconCls: 'edit',
                    handler: function () {
                        var dlg = this.owner.createCategoriesDialog();

                        dlg.on('saveSuccess', function (feedback, categoriesId, text) {
                            node.setText(text);
                        }, this);

                        dlg.show(node.id, this.getCategoriesPath(node));
                    },
                    scope: this
                },
                {
                    text: TocLanguage.tipDelete,
                    iconCls: 'remove',
                    handler: function () {
                        Ext.MessageBox.confirm(
                            TocLanguage.msgWarningTitle,
                            TocLanguage.msgDeleteConfirm,
                            function (btn) {
                                if (btn == 'yes') {
                                    currentCategoryId = node.parentNode.id;

                                    Ext.Ajax.request({
                                        url: Toc.CONF.CONN_URL,
                                        params: {
                                            module: 'categories',
                                            action: 'delete_category',
                                            categories_id: node.id
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