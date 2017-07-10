<?php
/*
  $Id: categories_main_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.databases.logPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  config.title = 'Logs';
  config.listeners = {
        activate : function(panel) {
            if (!this.loaded) {
                this.pnlFiles.getStore().reload();
            }
        },
        scope: this
    }

  config.pnlFiles = new Toc.content.LogsPanel({host : config.host,server_port : config.server_port,server_pass : config.server_pass,server_user : config.server_user,servers_id : config.servers_id,content_id : config.content_id,content_type : 'databases',owner : Toc.content.ContentManager,mainPanel: this});
  //config.txtLog = new Ext.form.TextArea({owner: config.owner, mainPanel: this,region:'center'});
  config.logsGrid = new Toc.content.logfileGrid({owner: config.owner, mainPanel: this});

  config.pnlFiles.on('selectchange', this.onNodeSelectChange, this);

  config.items = [config.pnlFiles, config.logsGrid];
  
  Toc.databases.logPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.logPanel, Ext.Panel, {

  onNodeSelectChange: function(json) {
    if(json)
    {
       this.logsGrid.refreshGrid(json);
    }
  },

  maskText: function() {
    this.getEl().mask('Chargement Fichier ....');
  },
  
  getCategoriesTree: function() {
    return this.pnlFiles;
  },

  getCategoryPath: function(){
        return this.pnlCategoriesTree.getCategoriesPath();
  },

  getCategoryPermissions: function(){
    return this.pnlCategoriesTree.getCategoryPermissions();
  },

  setLogCount: function(logs_id,count){
    this.pnlFiles.setLogCount(logs_id,count);
  }
});