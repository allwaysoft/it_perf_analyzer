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

Toc.databases.MovedatafileDialog = function(config) {

  config = config || {};
  
  config.id = 'move-datafile-dialog-win';
  config.layout = 'fit';
  config.width = 465;
  config.height = 200;
  config.modal = true;
  config.iconCls = 'icon-databases-win';
  config.items = this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess': true});  
  
  Toc.databases.MovedatafileDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.databases.MovedatafileDialog, Ext.Window, {
  
  show: function (json,owner) {
    this.owner = owner || null;
    if(json)
    {
       this.databasesId = json.databases_id || null;
       this.servers_id = json.servers_id || null;
       this.file_id = json.file_id || null;
       this.file_name = json.file_name || null;
       this.tablespace_name = json.tablespace_name || null;
       this.server_user = json.server_user || null;
       this.server_pass = json.server_pass || null;
       this.server_port = json.server_port || null;
       this.db_user = json.db_user || null;
       this.db_pass = json.db_pass || null;
       this.db_port = json.port || null;
       this.sid = json.sid || null;
       this.host = json.host || null;
    }
    
    this.frmDatabase.form.reset();
    this.frmDatabase.form.baseParams['databases_id'] = this.databasesId;
    this.frmDatabase.form.baseParams['file_id'] = this.file_id;
    this.frmDatabase.form.baseParams['file_name'] = this.file_name;
    this.frmDatabase.form.baseParams['tablespace_name'] = this.tablespace_name;
    this.frmDatabase.form.baseParams['servers_id'] = this.servers_id;
    this.frmDatabase.form.baseParams['server_user'] = this.server_user;
    this.frmDatabase.form.baseParams['server_pass'] = this.server_pass;
    this.frmDatabase.form.baseParams['server_port'] = this.server_port;
    this.frmDatabase.form.baseParams['db_user'] = this.db_user;
    this.frmDatabase.form.baseParams['db_pass'] = this.db_pass;
    this.frmDatabase.form.baseParams['db_port'] = this.db_port;
    this.frmDatabase.form.baseParams['sid'] = this.sid;
    this.frmDatabase.form.baseParams['host'] = this.host;
    Toc.databases.DatabasesDialog.superclass.show.call(this);
  },

  getContentPanel: function() {
    this.pnlData = new Ext.Panel({
            layout: 'form',
            border: false,
            region:'center',
            autoHeight: true,
            style: 'padding: 6px',
            items: [
                {
                    layout: 'form',
                    border: false,
                    labelSeparator: ' ',
                    columnWidth: .7,
                    autoHeight: true,
                    defaults: {
                        anchor: '97%'
                    },
                    items: [
                        {xtype:'textfield', fieldLabel: 'Name', name: 'file_name', id: 'file_name',allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'Tablespace', name: 'tablespace_name', id: 'tablespace_name',allowBlank:false},
                        {xtype:'textfield', fieldLabel: 'New Name', name: 'new_name', id: 'new_name',allowBlank:false},
                        new Ext.ProgressBar({hidden: true,hideLabel: true})
                    ]
                }
            ]
    });

    return this.pnlData;
  },
  
  buildForm: function() {
    this.frmDatabase = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'servers',
        action : 'move_datafile'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });
    
    return this.frmDatabase;
  },

  submitForm: function() {
    var params = {
    };

    this.frmDatabase.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});