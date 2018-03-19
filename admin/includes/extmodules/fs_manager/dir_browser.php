<?php

?>

Toc.databases.Dirbrowser = function(config) {

  config = config || {};
  
  config.id = 'databases_dir_dialog-win';
  config.title = 'FS browser';
  config.layout = 'fit';
  config.width = 800;
  config.height = 400;
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
  
  Toc.databases.Dirbrowser.superclass.constructor.call(this, config);
}

Ext.extend(Toc.databases.Dirbrowser, Ext.Window, {
  
  show: function (config,owner) {
    this.owner = owner || null;
    if(config)
    {
       this.config = config;
       Toc.databases.Dirbrowser.superclass.show.call(this);
       this.loadDir(config);
    }
  },

  getPath : function(){
    return this.config.mount;
  },

  getConfig : function(){
    return this.config;
  },

  setPath : function(path){
    this.config.mount = path;
    //this.setTitle(path);
    this.loadDir(this.config);
  },

  getContentPanel: function() {
    this.pnlDir = new Toc.databases.dirGrid({owner : this});

    return this.pnlDir;
  },

  loadDir : function(config){
     var param = {};
     param.server_user = config.server_user;
     param.server_pass = config.server_pass,
     param.port = config.server_port,
     param.host = config.host
     param.path = config.mount;
     this.pnlDir.getStore().load({params:param});
  }
});