<?php
/*
  $Id: roles_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.roles.RolesDialog = function(config) {
  config = config || {};
  
  config.id = 'roles_dialog-win';
  config.title = "Configurer un Groupe d'utilisateurs";
  config.width = 800;
  config.height = 385;
  config.modal = true;
  config.iconCls = 'icon-roles-win';
  config.layout = 'fit';
  config.items = this.buildForm();  
  
  config.treeLoaded = false;
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];
  
  this.addEvents({'saveSuccess' : true});
  
  Toc.roles.RolesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.roles.RolesDialog, Ext.Window, {
  show: function (data) {
    if(data)
    {
       var administratorsId = data.administrators_id || null;
       this.rolesId= data.roles_id || null;
       this.data = data;

       this.frmAdministrator.form.reset();
       this.frmAdministrator.form.baseParams['roles_id'] = this.rolesId;
       this.frmAdministrator.form.baseParams['administrators_id'] = administratorsId;

       Toc.roles.RolesDialog.superclass.show.call(this);
       this.loadRole(this.pnlAdmin);
    }
    else
    {
       this.frmAdministrator.form.reset();
       Toc.roles.RolesDialog.superclass.show.call(this);
    }
  },
  loadRole : function(panel){
     if (this.rolesId && this.rolesId != -1) {
      if(panel)
      {
         panel.getEl().mask('Chargement en cours....');
      }

      this.frmAdministrator.load({
        url: Toc.CONF.CONN_URL,
        params:{
          module: 'roles',
          action: 'load_role',
          src:'local'
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.access_globaladmin = action.result.data.access_globaladmin;
          this.access_modules = action.result.data.access_modules;

          //this.tabRoles.add(new Toc.content.PermissionsPanel({owner : this.owner,content_id : this.rolesId,content_type : 'roles',module : 'categories',action :  'list_role_permissions',id_field : 'categories_id',autoExpandColumn : 'categories_name'}));
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.close();
        },
        scope: this
      });
    }
  },
      
  getAdminPanel: function() {
    this.pnlAdmin = new Ext.Panel({
      border: false,
      title : 'Details',
      layout: 'form',
      autoHeight: true,
      labelSeparator: ' ',
      defaults: {
        anchor: '98%'
      },
      frame: false,
      style: 'padding: 5px',
      items: [
        {
          xtype: 'hidden',
          name: 'roles_id',
          id: 'roles_id'
        },{
          xtype: 'hidden',
          name: 'department_id',
          id: 'department_id'
        },
        {
          xtype: 'textfield',
          disabled:false,
          fieldLabel: 'Nom',
          name: 'roles_name',
          allowBlank: false
        },
        {
          xtype: 'textarea',
          disabled:false,
          fieldLabel: 'Description',
          name: 'roles_description',
          id:  'roles_description',
          allowBlank: false,
          height : 250
        }
      ]
    });  
    
    return this.pnlAdmin;
  },
  
  buildForm: function() {
    this.tabRoles = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
      hideMode:'offsets'
      },
    deferredRender: false,
      items: [this.getAdminPanel()]
    });

    this.frmAdministrator = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      layout: 'fit',
      baseParams: {  
        module: 'roles'
      }, 
      border: false,
      items: [this.tabRoles]
    });
    
    return this.frmAdministrator;
  },

  submitForm : function() {
    this.frmAdministrator.baseParams['modules'] = '';
    this.frmAdministrator.form.submit({
      url: Toc.CONF.CONN_URL,
      params: {
        'module' : 'roles',
        'action' : 'save_role'
      },
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
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