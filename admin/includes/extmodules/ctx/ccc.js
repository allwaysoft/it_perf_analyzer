Ext.namespace("Toc.ctx");
Toc.ctx.AgencesTreePanel = function(config) {
    config = config || {};

    config.region = 'west';
    config.border = false;
    config.autoScroll = true;
    config.containerScroll = true;
    config.split = true;
    config.width = 350;
    config.enableDD = true;
    config.rootVisible = true;

    config.root = new Ext.tree.AsyncTreeNode({
        text: 'Agences',
        icon : 'templates/default/images/icons/16x16/home.png',
        draggable: false,
        id: '0',
        expanded: true
    });
    config.currentCategoryId = -1;

    config.loader = new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: true,
        baseParams: {
            module: 'roles',
            action: 'load_agences_tree_delta'
        },
        listeners: {
            load: function() {
                this.expandAll();
                var category = this.currentCategoryId || -1;
                var count = this.nodeHash[category].attributes.count;
                this.setCategoryId(category,count);
            },
            scope: this
        }
    });

    config.tbar = [{
        text: TocLanguage.btnRefresh,
        iconCls: 'refresh',
        handler: this.refresh,
        scope: this
    }];

    config.listeners = {
        "click": this.onCategoryNodeClick,
        "nodedragover": this.onCategoryNodeDragOver,
        "nodedrop": this.onCategoryNodeDrop,
        "contextmenu": this.onCategoryNodeRightClick
    };

    this.addEvents({'selectchange' : true});

    Toc.ctx.AgencesTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ctx.AgencesTreePanel, Ext.tree.TreePanel, {

    setCategoryId: function(categoryId,count) {
        var currentNode = this.getNodeById(categoryId);
        currentNode = currentNode || this.getRootNode();
        currentNode.select();
        this.currentCategoryId = currentNode.id;

        this.fireEvent('selectchange', this.currentCategoryId,count);
    },

    onCategoryNodeClick: function (node) {
        node.expand();
        this.setCategoryId(node.id,node.attributes.count);
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

        Ext.Ajax.request ({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'roles',
                action: 'move_roles',
                roles_ids: e.dropNode.id,
                parent_category_id: parent_id
            },
            callback: function(options, success, response){
                result = Ext.decode(response.responseText);

                if (result.success == true) {
                    this.setCategoryId(currentCategoryId);
                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
            },
            scope: this
        });
    },

    getRolesPath: function(node) {
        var cpath = [];
        node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;

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
                        var dlg = this.owner.createRolesDialog();

                        dlg.on('saveSuccess', function(feedback, rolesId, text) {
                            node.appendChild({
                                id: rolesId,
                                text: text,
                                cls: 'x-tree-node-collapsed',
                                parent_id: node.id,
                                leaf: true
                            });

                            node.expand();
                        }, this);

                        dlg.show(null, this.getRolesPath(node));
                    },
                    scope: this
                },
                {
                    text: TocLanguage.tipEdit,
                    iconCls: 'edit',
                    handler: function() {
                        var dlg = this.owner.createRolesDialog();

                        dlg.on('saveSuccess', function(feedback, rolesId, text) {
                            node.setText(text);
                        }, this);

                        dlg.show(node.id, this.getRolesPath(node));
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
                                            module: 'roles',
                                            action: 'delete_category',
                                            roles_id: node.id
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

        this.menuContext.showAt(event.getXY());;
    },

    getCategoriesPath: function(node) {
        var cpath = [];
        node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;

        while (node.id > 0) {
            cpath.push(node.id);
            node = node.parentNode;
        }

        return cpath.reverse().join('_');
    },

    refresh: function() {
        this.root.reload();
    }
});
Toc.ctx.ctxGrid = function(config) {

    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'users',
            action: 'list_ctx'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'cli'
        }, [
            'cli',
            'dctx',
            'uti',
            'nomrest',
            'lib'
        ]),
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions:[
            {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    renderPublish = function(status) {
        if(status == 1) {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        }else {
            return '<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    renderAccount = function(account) {
        return '<span style="font-size: large;">' + account.user_name + '</span><div style = "white-space : normal">' + account.description + '</div>';
    };

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'client',header: 'Client', dataIndex: 'nomrest'},
        { id: 'dctx', header: 'Date CTX', dataIndex: 'dctx',align: 'center',width:150},
        { id: 'uti', header: 'Utilisateur', dataIndex: 'lib',align: 'center',width:200},
        config.rowActions
    ]);
    config.autoExpandColumn = 'client';

    config.txtSearch = new Ext.form.TextField({
        width: 100,
        hideLabel: true,
        listeners:{
            scope:this,
            specialkey: function(f,e){
                console.debug(f);
                console.debug(e);
                if(e.getKey()==e.ENTER){
                    this.onSearch();
                }
            }
        }
    });

    config.tbar = [
        {
            text: 'Deconnecter',
            iconCls:'remove',
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
        btnsConfig:[
            {
                text: TocLanguage.btnDelete,
                iconCls:'remove',
                handler: function() {
                    thisObj.onBatchDelete();
                }
            }
        ],
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

    Toc.ctx.ctxGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ctx.ctxGrid, Ext.grid.GridPanel, {

    onAdd: function() {
        var dlg = this.owner.createctxDialog();
        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show(null, null);
    },

    onEdit: function(record) {
        var dlg = this.owner.createctxDialog();
        dlg.setTitle(record.get("ctx_name"));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show(record.get("ctx_id"),record.get("administrators_id"));
    },

    onDelete: function(record) {
        var unix = record.get('unix');
        var cuti = record.get('cuti');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            'Voulez-vous vraiment deconnecter cet utilisateur ?',
            function(btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'users',
                            action: 'deconnect_user',
                            unix: unix,
                            cuti: cuti
                        },
                        callback: function(options, success, response) {
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

    deconnectUser: function (action, cuti, flag, unix,pbar,step,max) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'users',
                action: action,
                cuti: cuti,
                unix: unix,
                flag: flag
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                pbar.val = pbar.val + step;
                pbar.count = pbar.count + 1;
                pbar.updateProgress(pbar.val,cuti + " deconnecte ...",true);

                if(pbar.count >= max)
                {
                    pbar.reset();
                    pbar.hide();
                }

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(cuti).set('status', 0);
                    store.commitChanges();
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    },

    onBatchDelete: function() {
        var count = this.selModel.getCount();
        if(count > 0)
        {
            this.pBar.reset();
            this.pBar.updateProgress(0,"",true);
            this.pBar.val = 0;
            this.pBar.count = 0;
            this.pBar.show();
            var step = 1/count;

            for (var i=0;i<count;i++)
            {
                var cuti = this.selModel.selections.items[i].data.cuti;
                var unix = this.selModel.selections.items[i].data.unix;
                var module = 'deconnectUser';

                this.deconnectUser(module, cuti,1,unix,this.pBar,step,count);
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    onRefresh: function() {
        this.mainPanel.getCategoriesTree().refresh();
    },

    refreshGrid: function (categoriesId,count) {
        var store = this.getStore();

        store.baseParams['categories_id'] = categoriesId;
        store.baseParams['count'] = count;
        this.categoriesId = categoriesId;
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

    onRowAction: function(grid, record, action, row, col) {
        switch(action) {
            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-edit-record':
                this.onEdit(record);
                break;
        }
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
                var cuti = this.getStore().getAt(row).get('cuti');
                var unix = this.getStore().getAt(row).get('unix');
                var module = 'deconnectUser';

                switch(action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, cuti, flag,unix);

                        break;
                }
            }
        }
    },

    onAction: function(action, cuti, flag,unix) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'users',
                action: action,
                cuti: cuti,
                unix: unix,
                flag: flag
            },
            callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    var store = this.getStore();
                    store.getById(cuti).set('status', 0);
                    store.commitChanges();

                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    }
});Toc.ctx.mainPanel = function(config) {
    config = config || {};

    config.layout = 'border';
    config.border = false;

    config.pnlAgencesTree = new Toc.ctx.AgencesTreePanel({owner: config.owner, parent: this});
    config.grdctx = new Toc.ctx.ctxGrid({owner: config.owner, mainPanel: this});

    config.pnlAgencesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);

    config.items = [config.AgencesTreePanel, config.grdctx];

    Toc.ctx.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ctx.mainPanel, Ext.Panel, {

    onPnlCategoriesTreeNodeSelectChange: function(categoryId,count) {
        if(count)
        {
            this.grdctx.refreshGrid(categoryId,count);
        }
    },

    refreshTree :function(){
        this.pnlAgencesTree.refresh();
    },

    getCategoriesTree: function() {
        return this.pnlAgencesTree;
    },

    getCategoryPath: function(){
        return this.pnlAgencesTree.getCategoriesPath();
    }
});

Ext.override(TocDesktop.CtxWindow, {

    createWindow: function() {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('ctx-win');

        if (!win) {
            pnl = new Toc.ctx.mainPanel({owner: this});

            win = desktop.createWindow({
                id: 'ctx-win',
                title: 'Clients en Contentieux',
                width: 800,
                height: 400,
                iconCls: 'icon-ctx-win',
                layout: 'fit',
                items: pnl
            });
        }

        win.show();
    }
});

