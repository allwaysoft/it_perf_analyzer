<?php
/*
  $Id: sms_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.sms.smsGrid = function (config) {

    config = config || {};
    config.region = 'center';
    config.loadMask = true;
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'sms',
            action: 'list_sms'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'sms_id'
        }, [
            'sms_id',
            'customerid',
            'customer_name',
            'message',
            'no_phone'
        ]),
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions:[
            {iconCls: 'icon-move-record', qtip: 'Renvoyer'},
            {iconCls: 'icon-detail-record', qtip: 'Details'}],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);
    config.plugins = config.rowActions;

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        { id: 'no_phone', header: 'No Telephone', align: 'left', dataIndex: 'no_phone'},
        { id: 'customerid', header: 'Code Client', dataIndex: 'customerid', sortable: false, align: 'center'},
        { id: 'customer_name', header: 'Nom Client', dataIndex: 'customer_name', sortable: false, align: 'left', renderer: render},
        { id: 'message', header: 'Message', dataIndex: 'message', sortable: false, align: 'left', renderer: render},
        config.rowActions
    ]);
    config.autoExpandColumn = 'message';

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

    config.pBar = new Ext.ProgressBar({
        hidden: true,
        width: 300,
        hideLabel: true
    });

    config.tbar = [
        {
            text: 'renvoyer',
            iconCls:'icon-move-record',
            handler: this.onBatchSend,
            scope: this
        },
        '-',
        config.pBar,
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
        btnsConfig: [
        ],
        beforePageText: TocLanguage.beforePageText,
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

    Toc.sms.smsGrid.superclass.constructor.call(this, config);
}
;

Ext.extend(Toc.sms.smsGrid, Ext.grid.GridPanel, {

    onAdd: function () {
        var dlg = this.owner.createsmsDialog();
        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(null, null);
    },

    onEdit: function (record) {
        /*var dlg = this.owner.createsmsDialog();
        dlg.setTitle(record.get("sms_name"));

        dlg.on('saveSuccess', function () {
            this.onRefresh();
        }, this);

        dlg.show(record.get("sms_id"), record.get("administrators_id"));*/
    },

    onSend: function (record) {
        var sms_id = record.get('sms_id');

        Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle,
            'Voulez-vous vraiment renvoyer ce Message ?',
            function (btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            module: 'sms',
                            action: 'resend_sms',
                            sms_id: sms_id
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
    },

    onBatchSend: function () {
        var count = this.selModel.getCount();
        if (count > 0) {
            this.pBar.reset();
            this.pBar.updateProgress(0, "", true);
            this.pBar.val = 0;
            this.pBar.count = 0;
            this.pBar.show();
            var step = 1 / count;

            for (var i = 0; i < count; i++) {
                var sms_id = this.selModel.selections.items[i].data.sms_id;

                this.reSend(sms_id,this.pBar, step, count);
            }
        }
        else {
            Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
        }
    },

    reSend: function (sms_id,pbar,step,max) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'sms',
                action: 'resend_sms',
                sms_id: sms_id
            },
            callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);

                pbar.val = pbar.val + step;
                pbar.count = pbar.count + 1;
                pbar.updateProgress(pbar.val,sms_id + " envoyé ...",true);

                if(pbar.count >= max)
                {
                    pbar.reset();
                    pbar.hide();
                    this.onRefresh();
                }

                if (result.success == true) {
                    var store = this.getStore();
                }
                else
                    this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            },
            scope: this
        });
    },

    onRefresh: function () {
        this.mainPanel.getCategoriesTree().refresh();
    },

    refreshGrid: function (categoriesId, count) {
        var store = this.getStore();

        store.baseParams['categories_id'] = categoriesId;
        store.baseParams['count'] = count;
        this.categoriesId = categoriesId;
        store.load();
    },

    onSearch: function () {
        var filter = this.txtSearch.getValue() || null;
        var store = this.getStore();

        store.baseParams['current_category_id'] = this.categoriesId || -1;
        store.baseParams['search'] = filter;
        store.reload();
        store.baseParams['search'] = '';
    },

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-move-record':
                this.onSend(record);
                break;

            case 'icon-detail-record':
                this.onEdit(record);
                break;
        }
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
                var cuti = this.getStore().getAt(row).get('cuti');
                var unix = this.getStore().getAt(row).get('unix');
                var module = 'deconnectUser';

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.onAction(module, cuti, flag, unix);

                        break;
                }
            }
        }
    },

    onAction: function (action, cuti, flag, unix) {
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
});