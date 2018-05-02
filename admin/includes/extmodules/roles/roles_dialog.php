<?php

?>
Toc.roles.RolesDialog = function(config) {
  config = config || {};
  
  config.id = 'roles_dialog-win';
  config.title = 'Configurer un Profil';
  config.width = 800;
  config.height = 500;
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
    var administratorsId = data.administrators_id || null;
    this.rolesId= data.roles_id || null;
    this.data = data;
    
    this.frmAdministrator.form.reset();
    this.frmAdministrator.form.baseParams['roles_id'] = this.rolesId;
    this.frmAdministrator.form.baseParams['administrators_id'] = administratorsId;

    Toc.roles.RolesDialog.superclass.show.call(this);
    this.loadRole(this.pnlAdmin);
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
          action: 'load_user',
          src:this.data.src
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
  
  onCheckChange: function(node, checked) {
    if (node.hasChildNodes) {
      node.expand();
      node.eachChild(function(child) {
        child.ui.toggleCheck(checked);
      });
    }
  },
  
  checkAll: function() {
    this.pnlAccessTree.root.cascade(function(n) {
      if (!n.getUI().isChecked()) {
        n.getUI().toggleCheck(true);
      }
    });
  },
  
  uncheckAll: function() {
    this.pnlAccessTree.root.cascade(function(n) {
      if (n.getUI().isChecked()) {
        n.getUI().toggleCheck(false);
      }
    });
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
        },
        {
          xtype: 'textfield',
          disabled:true,
          fieldLabel: 'Nom',
          name: 'roles_name',
          allowBlank: false
        },
        {
          xtype: 'textarea',
          disabled:true,
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
  
  getAccessPanel: function() {
    this.chkGlobal = new Ext.form.Checkbox({
      name: 'access_globaladmin', 
      boxLabel: 'Acces Global',
      listeners: {
        check: function(chk, checked) {
          if(checked)
            this.checkAll();
          else
            this.uncheckAll();
        },
        scope: this
      }
    });  
  
    this.pnlAccessTree = new Ext.ux.tree.CheckTreePanel({
      name: 'access_modules', 
      id: 'access_modules',
      xtype: 'checktreepanel',
      height : 400,
<!--      layout : 'fit',-->
      title : 'Modules',
      deepestOnly: true,
      bubbleCheck: 'none',
      cascadeCheck: 'none',
      autoScroll: true,
      containerScroll: false,
      border: false,
      bodyStyle: 'background-color:white;border:1px solid #B5B8C8',
      rootVisible: false,
      anchor: '-24 -60',
      root: {
        nodeType: 'async',
        text: 'root',
        id: 'root',
        expanded: true,
        uiProvider: false
      },
      loader: new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: false,
        baseParams: {
          module: 'roles',
          action: 'get_accesses'
        },
        listeners: {
          load: function() {
            this.pnlAccessTree.setValue(this.access_modules);
            this.treeLoaded = true;

            if(this.access_globaladmin == true) {
              this.chkGlobal.setValue(true);
              this.checkAll();
            }else {
              this.pnlAccessTree.getEl().unmask();
            }
          },
          beforeload: function(_this,node,callback) {
            return this.isVisible();
          },
          scope: this
        }
      }),
      listeners: {
        checkchange: this.onCheckChange,
        activate : function(panel) {
            if (!this.treeLoaded) {
                this.pnlAccessTree.loader.preloadChildren = true;
                this.pnlAccessTree.getEl().mask('Chargement des modules............');
                this.pnlAccessTree.loader.load(this.pnlAccessTree.getRootNode());
            }
        },
        show : function(comp) {
        },
        beforeshow : function(comp) {
        },
        scope: this
      },
      tbar: [this.chkGlobal]
    });      

    return this.pnlAccessTree;
  },
  
  buildForm: function() {
    this.tabRoles = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
      hideMode:'offsets'
      },
    deferredRender: false,
    items: [this.getAccessPanel()]
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
    this.frmAdministrator.baseParams['modules'] = this.pnlAccessTree.getValue().toString();
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