<?php
/*
  $Id: ctx_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.ctx.ctxDialog = function(config) {
  
  config = config || {};
  
  config.id = 'ctx-dialog-win';
  config.layout = 'fit';
  config.width = 850;
  config.title = 'Nouveau compte Utilisateur';
  config.height = 570;
  config.modal = true;
  config.iconCls = 'icon-ctx-win';
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
  
  Toc.ctx.ctxDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.ctx.ctxDialog, Ext.Window, {

  show: function(uid,aid) {
    this.ctxId = uid || null;
    
    this.frmArticle.form.reset();  
    this.frmArticle.form.baseParams['administrators_id'] = aid || null;

    Toc.ctx.ctxDialog.superclass.show.call(this);
    this.loadUser(this.pnlData);            
  },

  loadUser : function(panel){
     if (this.ctxId && this.ctxId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }

      this.frmArticle.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_user',
          ctx_id: this.ctxId,
          wm : 0
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          var img = action.result.data.image_url;

          if (img != null) {
            var img = '../images/ctx/thumbnails/' + img;
            var html = '<div style="margin: 26px 0px 0px 20px"><img src="' + img + '" style="border: solid 1px #B5B8C8;" />&nbsp;&nbsp;<input type="checkbox" name="delimage" id="delimage" /><?php echo $osC_Language->get('field_delete'); ?></div>';

            this.frmArticle.findById('img_url').body.update(html);
          }

          this.pnlRoles.setRoles(action.result.data.roles_id)

          this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.ctxId,content_type : 'ctx',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.ctxId,content_type : 'ctx',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.ctxId,content_type : 'ctx',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.ctxId,content_type : 'ctx',owner : Toc.content.ContentManager});

          this.tabctx.add(this.pnlImages);
          this.tabctx.add(this.pnlDocuments);
          this.tabctx.add(this.pnlLinks);
          this.tabctx.add(this.pnlComments);
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

  getContentPanel: function() {
    this.pnlData = new Toc.ctx.DataPanel();
    this.pnlDescription = new Toc.content.DescriptionPanel({USE_WYSIWYG_TINYMCE_EDITOR : <?php echo USE_WYSIWYG_TINYMCE_EDITOR ?>,defaultLanguageCode : ''});
    this.pnlRoles = new Toc.ctx.RolesPanel();
    this.pnlDescription.setTitle('Description');
        
    this.tabctx = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.pnlDescription,
        this.pnlRoles
      ]
    });
    
    return this.tabctx;
  },
  
  buildForm: function() {
    this.frmArticle = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'ctx',
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
        Ext.MessageBox.alert(TocLanguage.msgErrTitle,'Vous devez selectionner au moins un Role pour cet utilisateur');
        this.tabctx.activate(this.pnlRoles);
    }
    else
    {
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