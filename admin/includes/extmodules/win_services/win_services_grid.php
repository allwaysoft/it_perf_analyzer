<?php
/*
  $Id: customers_groups_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.win_services.WinServicesGrid = function(config) {

config = config || {};
config.loadMask = true;
config.border = false;

config.ds = new Ext.data.Store({
url: Toc.CONF.CONN_URL,
baseParams: {
module: 'win_services',
action: 'list_services'
},
reader: new Ext.data.JsonReader({
root: Toc.CONF.JSON_READER_ROOT,
totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
id: 'win_services_id'
}, [
'win_services_id',
'ServiceName',
'ServiceDescription',
'CurrentState',
'ProcessId',
'ServiceFlags'
]),
sortInfo: {
field: 'ServiceName',
direction: 'ASC'
},
autoLoad: true
});

config.rowActions = new Ext.ux.grid.RowActions({
actions:[
{iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
{iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
widthIntercept: Ext.isSafari ? 4 : 2
});
config.rowActions.on('action', this.onRowAction, this);
config.plugins = config.rowActions;

renderStatus = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };

config.sm = new Ext.grid.CheckboxSelectionModel();
config.cm = new Ext.grid.ColumnModel([
config.sm,
{
id: 'ServiceName',
header: 'Nom du Service',
dataIndex: 'ServiceDescription'
},
{
header: 'Status',
dataIndex: 'CurrentState',
align: 'center',
renderer: renderStatus
},
config.rowActions
]);
config.autoExpandColumn = 'ServiceName';

config.tbar = [
{
text: 'Installer',
iconCls:'add',
handler: this.onAdd,
scope: this
},
'-',
{
text: TocLanguage.btnDelete,
iconCls:'remove',
handler: this.onBatchDelete,
scope: this
},
'-',
{
text: TocLanguage.btnRefresh,
iconCls:'refresh',
handler: this.onRefresh,
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
text: TocLanguage.btnAdd,
iconCls: 'add',
handler: function() {
thisObj.onAdd();
}
},
{
text: TocLanguage.btnDelete,
iconCls: 'remove',
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

Toc.win_services.WinServicesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.win_services.WinServicesGrid, Ext.grid.GridPanel, {

onAdd: function() {
var dlg = this.owner.createWinServicesDialog();

dlg.on('saveSuccess', function(){
this.onRefresh();
}, this);

dlg.show();
},

onEdit: function(record) {
var dlg = this.owner.createWinServicesDialog();
dlg.setTitle(record.get("customers_groups_name"));

dlg.on('saveSuccess', function() {
this.onRefresh();
}, this);

dlg.show(record.get("win_services_id"));
},

onDelete: function(record) {
var name = record.get('ServiceName');

Ext.MessageBox.confirm(
TocLanguage.msgWarningTitle,
TocLanguage.msgDeleteConfirm,
function(btn) {
if (btn == 'yes') {
Ext.Ajax.request({
url: Toc.CONF.CONN_URL,
params: {
module: 'win_services',
action: 'delete_service',
name: name
},
callback: function(options, success, response) {
result = Ext.decode(response.responseText);

if (result.success == true) {
this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
this.getStore().reload();
}else{
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

onBatchDelete: function() {
var keys = this.getSelectionModel().selections.keys;

if (keys.length > 0) {
var batch = keys.join(',');

Ext.MessageBox.confirm(
TocLanguage.msgWarningTitle,
TocLanguage.msgDeleteConfirm,
function(btn) {
if (btn == 'yes') {
Ext.Ajax.request({
url: Toc.CONF.CONN_URL,
params: {
module: 'win_services',
action: 'delete_win_services',
batch: batch
},
callback: function(options, success, response) {
result = Ext.decode(response.responseText);

if (result.success == true) {
this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
this.getStore().reload();
}else{
Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
}
},
scope: this
});
}
},
this
);
}else{
Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
}
},

        setStatus: function(win_services_id, flagName, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'win_services',
        action: 'set_status',
        win_services_id: win_services_id,
        flag_name: flagName,
        flag: flag
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);

        if (result.success == true) {
          var store = this.getStore();
          store.getById(win_services_id).set(flagName, flag);
          store.commitChanges();
          store.reload();
        }

        this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  },

onRefresh: function() {
this.getStore().reload();
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
        action = btn.className.replace(/img-button btn-/, '').trim();
        var win_services_id = this.getStore().getAt(row).get('win_services_id');

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.setStatus(win_services_id, flagName, flag);
            break;
        }
      }
    }
  },

onRowAction:function(grid, record, action, row, col) {
switch(action) {
case 'icon-delete-record':
this.onDelete(record);
break;

case 'icon-edit-record':
this.onEdit(record);
break;
}
}
});