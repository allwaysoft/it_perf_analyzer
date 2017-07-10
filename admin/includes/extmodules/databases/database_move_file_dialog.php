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

Toc.databases.MovefileDialog = function(config) {

  config = config || {};
  
  config.id = 'move-file-dialog-win';
  config.layout = 'fit';
  config.width = 600;
  config.height = 170;
  //config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-databases-win';
  config.items = this.buildForm();
  config.shouldRefresh = false;

  config.btnClose = new Ext.Button({
      text: TocLanguage.btnClose,
      handler: function() {
        this.shouldRefresh ? this.fireEvent('saveSuccess','') : this.fireEvent('dummy','');
        this.close();
      },
      scope: this
    });

  config.buttons = [
    config.btnClose
  ];

  this.addEvents({'saveSuccess': true});  
  
  Toc.databases.MovefileDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.databases.MovefileDialog, Ext.Window, {
  
  show: function (json,owner,contents) {
    this.owner = owner || null;
    if(json)
    {
       this.url = json.url || null;
       this.server_user = json.server_user || null;
       this.server_pass = json.server_pass || null;
       this.server_port = json.server_port || null;
       this.host = json.host || null;
    }
    
    this.frmDatabase.form.reset();
    this.frmDatabase.form.baseParams['server_user'] = this.server_user;
    this.frmDatabase.form.baseParams['server_pass'] = this.server_pass;
    this.frmDatabase.form.baseParams['server_port'] = this.server_port;
    this.frmDatabase.form.baseParams['host'] = this.host;

    if(Ext.isArray(contents))
    {
       var h = 170;
       var i = 0;
       while(i < contents.length)
       {
          var content = contents[i];
          json.action = content.typ == 'file' ? 'move_file' : 'move_folder';
          json.typ = content.typ;
          json.url = content.url;

          var src = new Ext.form.TextField({id : 'move_panel_src_' + i,width :'85', fieldLabel: 'Source',allowBlank:false,disabled:true,value:json.url});
          var dest = new Ext.form.TextField({id : 'move_panel_dest_' + i,fieldLabel: 'Destination',allowBlank:false,width :'85',value:json.url});
          var pBar = new Ext.ProgressBar({hidden: true,width: 472});

          var move_panel = new Toc.content.ContentManager.MoveDataPanel(json,content.typ,src,dest,pBar,i,this.frmDatabase);
          this.frmDatabase.add(move_panel);
          h = i > 0 ? h + 100 : h;
          this.setHeight(h);
          i++;
       }

       this.center();
       contents = [];
    }

    Toc.databases.DatabasesDialog.superclass.show.call(this);
  },

  getContentPanel: function() {
  },
  
  buildForm: function() {
    this.frmDatabase = new Ext.form.FormPanel({
      layout: 'form',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'servers',
        action : 'move_file'
      },
      deferredRender: false,
      items: []
    });
    
    return this.frmDatabase;
  },

  watchFileMove: function(params,pbar) {
    //this.frmDatabase.form.baseParams['action'] = 'watch_filemove';
    params.action = 'watch_filemove';

    this.frmDatabase.form.submit({
      //waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      success: function(form, action){
        var size = action.result.src_size;
        var dest_size = action.result.dest_size;
        percent = dest_size/size;
        pbar.updateProgress(percent,action.result.feedback,true);

        if(size == dest_size)
        {
            pbar.reset();
            pbar.hide();
            this.fireEvent('saveSuccess', action.result.feedback);
            this.close();
        }
        else
        {
           this.watchFileMove(params,pbar);
        }
      },
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  },

  getParams : function() {
     var params = {};
     var dest = this.dest.getValue();
     var src = this.src.getValue();
     if(src == dest)
     {
        Ext.MessageBox.alert(TocLanguage.msgErrTitle,"Source et destination doivent etre differentes ...");
        return null;
     }

     params.dir = dest.substring(0,dest.lastIndexOf('/') + 1);
     params.file_name = dest.substring(dest.lastIndexOf('/') + 1);
     return params;
  },

  submitForm: function() {
    this.btnClose.disable();
    this.pBar.reset();
    this.pBar.updateProgress(0,"Deplacement en cours ...",true);
    this.pBar.show();

    var params = this.getParams();

    if(params)
    {
        this.frmDatabase.form.submit({
        //waitMsg: 'Deplacement en cours ...',
        params : params,
        success: function(form, action){
          var src_size = action.result.src_size;
          var dest_size = action.result.dest_size;
          if(src_size == dest_size)
          {
            this.pBar.updateProgress(1);
            this.fireEvent('saveSuccess', action.result.feedback);
            this.close();
          }
          else
          {
            params.src_size = src_size;
            this.watchFileMove(params,this.pBar);
          }
        },
        failure: function(form, action) {
          if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
          }
        },
         scope: this
      });
    }
  }
});