<?php
/*
  $Id: signatures_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>

Toc.signatures.SignaturesGrid = function (config) {

  config = config || {};
  
  config.border = false;
  config.viewConfig = {forceFit: true};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'servers',
      action: 'list_signatures'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'chemin'
    }, [
      'chemin',
      'original',
      'compresse',
      'gain'
    ]),
    listeners : {
            load : function(store,records,opt) {
                this.getEl().unmask();
            },
            beforeload : function(store,opt) {
                this.getEl().mask('Chargement des images ....');
            },scope: this
        },
    autoLoad: false
  });

  renderOriginal = function(data) {
    var tmp = data.toString().split(';');

    return '<table align="center" border="0" cellpadding="1" cellspacing="1" style="height:100%; width:100%"><tbody><tr><td style="background-color:#ff0000;height: 35px; text-align:center; vertical-align:middle"><font size="5" color="white">Original ==&gt; ' + tmp[1] + '</font></td></tr><tr><td style="height:100%; width:100%"><div style="width:100%; height:100%; overflow:scroll"><img alt="" src="' + tmp[0] + '" /></div></td></tr></tbody></table>';
  };

  renderCompresse = function(data) {
    var tmp = data.toString().split(';');

    return '<table align="center" border="0" cellpadding="1" cellspacing="1" style="height:100%; width:100%"><tbody><tr><td style="background-color:darkgreen;height: 35px; text-align:center; vertical-align:middle"><font size="5" color="white">Compresse ==&gt; ' + tmp[1] + ' (-' + tmp[2] + '%)</font></td></tr><tr><td style="height:100%; width:100%"><div style="width:100%; height:100%; overflow:scroll"><img alt="" src="' + tmp[0] + '" /></div></td></tr></tbody></table>';
  };

  config.cm = new Ext.grid.ColumnModel([
    {header: 'Original', dataIndex: 'original', width: 50,align : 'center',renderer : renderOriginal},
    {header: 'Compresse', dataIndex: 'compresse', width: 50,align : 'center',renderer : renderCompresse}
  ]);

  config.txtSearch = new Ext.form.TextField({
    width: 100,
    fieldLabel: 'No Compte'
  });
  
  config.tbar = [
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
    pageSize: 1,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
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

  Toc.signatures.SignaturesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.signatures.SignaturesGrid, Ext.grid.GridPanel, {
  onSearch: function() {
    this.getStore().removeAll();
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'servers',
        action: 'list_signaturescount',
        ncp: this.txtSearch.getValue()
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        //console.debug(result);
        if (result.success == true) {
          this.getStore().baseParams['ncp'] = this.txtSearch.getValue();
          this.getStore().baseParams['total'] = result.total;
          this.getStore().reload();
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  },
  onRefresh: function() {
    this.getStore().reload();
  }
}
);