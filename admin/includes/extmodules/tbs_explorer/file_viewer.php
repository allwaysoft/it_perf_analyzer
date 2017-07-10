<?php
/*
  $Id: databases_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.databases.FileViewer = function(config) {

  config = config || {};
  
  config.id = 'databases_file_viewer-win';
  config.title = 'File Viewer';
  config.layout = 'fit';
  config.width = 800;
  config.height = 600;
  config.modal = true;
  config.iconCls = 'icon-databases-win';
  config.items = this.getContentPanel();
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess': true});
  Toc.databases.FileViewer.superclass.constructor.call(this, config);
}

Ext.extend(Toc.databases.FileViewer, Ext.Window, {
  
  show: function (config,owner) {
    this.owner = owner || null;
    if(config)
    {
       this.config = config;
       Toc.databases.FileViewer.superclass.show.call(this);
       this.loadFile(config);
    }
  },

  getPath : function(){
    return this.config.mount;
  },

  setPath : function(path){
    this.config.mount = path;
    this.setTitle(path);
    this.loadDir(this.config);
  },

  getContentPanel: function() {
    this.pnlFile = new Toc.content.logfileGrid({owner: this.owner, mainPanel: this});
    return this.pnlFile;
  },

  loadFile : function(config){
     console.log('loading file ... ' + config.url);
     var json = {};
     json.user = config.server_user;
     json.pass = config.server_pass,
     json.port = config.server_port,
     json.host = config.host
     json.url = config.url;

     this.pnlFile.refreshFileGrid(json);
  }
});