<?php
/*
  $Id: users_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.users.UsersDialog = function(config) {
  
  config = config || {};
  
  config.id = 'users-dialog-win';
  config.layout = 'fit';
  config.width = 635;
  config.title = 'Nouveau Compte Utilisateur';
  config.height = 375;
  config.modal = true;
  config.iconCls = 'icon-users-win';
  config.items = this.buildForm();
  
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
  
  Toc.users.UsersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.users.UsersDialog, Ext.Window, {

  show: function(uid,aid) {
    this.usersId = uid || null;
    
    this.frmArticle.form.reset();  
    this.frmArticle.form.baseParams['administrators_id'] = aid || null;

    Toc.users.UsersDialog.superclass.show.call(this);
    this.loadUser(this.pnlData);            
  },

  loadUser : function(panel){
     if (this.usersId && this.usersId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }

      this.frmArticle.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_user',
          users_id: this.usersId,
          wm : 0
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.pnlRoles.setRoles(action.result.data.roles_id);

          //this.access_globaladmin = action.result.data.access_globaladmin;
          this.access_modules = action.result.data.access_modules;

          //this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.usersId,content_type : 'users',owner : Toc.content.ContentManager});
          //this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.usersId,content_type : 'users',owner : Toc.content.ContentManager});
          //this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.usersId,content_type : 'users',owner : Toc.content.ContentManager});
          //this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.usersId,content_type : 'users',owner : Toc.content.ContentManager});

          //this.tabUsers.add(this.pnlImages);
          //this.tabUsers.add(this.pnlDocuments);
          //this.tabUsers.add(this.pnlLinks);
          //this.tabUsers.add(this.pnlComments);
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

  getAccessPanel: function() {

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

            this.pnlAccessTree.getEl().unmask();
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
      tbar: []
    });

    return this.pnlAccessTree;
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

  getContentPanel: function() {
    this.pnlData = new Toc.users.DataPanel();
    //this.pnlDescription = new Toc.content.DescriptionPanel({USE_WYSIWYG_TINYMCE_EDITOR : <?php echo USE_WYSIWYG_TINYMCE_EDITOR ?>,defaultLanguageCode : ''});
    this.pnlRoles = new Toc.users.RolesPanel();
    //this.pnlDescription.setTitle('Description');
        
    this.tabUsers = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.getAccessPanel(),
        this.pnlRoles
      ]
    });
    
    return this.tabUsers;
  },
  
  buildForm: function() {
    this.frmArticle = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'users',
        action : 'save_user'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });  
    
    return this.frmArticle;
  },
  
  submitForm : function() {
    var params = {
      roles_id: this.pnlRoles.getRoles()
    };    

    if(params.roles_id.toString() == '')
    {
        Ext.MessageBox.alert(TocLanguage.msgErrTitle,'Vous devez selectionner au moins un Groupe pour cet Utilisateur');
        this.tabUsers.activate(this.pnlRoles);
    }
    else
    {
        this.frmArticle.baseParams['modules'] = this.pnlAccessTree.getValue().toString();
        this.frmArticle.form.submit({
          params : params,
          waitMsg: TocLanguage.formSubmitWaitMsg,
          success: function(form, action){
            this.fireEvent('saveSuccess', action.result.feedback);
            this.close();
          },
          failure: function(form, action) {
            if(action.failureType != 'client') {
              Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
            }
            else
            {
              Ext.MessageBox.alert(TocLanguage.msgErrTitle,'Erreur');
            }
          },
          scope: this
        });
    }
  }
});