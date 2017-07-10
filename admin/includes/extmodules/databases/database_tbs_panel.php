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
Toc.databases.tbsPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  config.title = 'Data';
  config.listeners = {
        activate : function(panel) {
           this.pnlTbs.getStore().reload();
        },
        scope: this
    }

  config.pnlTbs = new Toc.databases.tbsGrid({region:'center',sid:config.sid,host : config.host,db_port : config.db_port,db_pass : config.db_pass,db_user : config.db_user,owner : this.owner,mainPanel: this});
  config.pnlDatafiles =  new Toc.databases.datafilesGrid({split: true,height: 200,region: 'south',sid:config.sid,host : config.host,db_port : config.db_port,db_pass : config.db_pass,db_user : config.db_user,owner : this.owner,mainPanel: this});

  config.pnlTbs.on('selectchange', this.onTbsChange, this);

  config.items = [config.pnlTbs, config.pnlDatafiles];
  
  Toc.databases.tbsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.tbsPanel, Ext.Panel, {

  onTbsChange: function(tablespace_name) {
    if(tablespace_name)
    {
       this.pnlDatafiles.refreshGrid(tablespace_name);
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