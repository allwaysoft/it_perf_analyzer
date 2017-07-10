<?php
/*
  $Id: environnements_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.environnements.environnementsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'environnements-dialog-win';
  config.title = 'Nouvel Article';
  config.layout = 'fit';
  config.width = 850;
  config.height = 570;
  config.modal = true;
  config.iconCls = 'icon-environnements-win';
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
  
  Toc.environnements.environnementsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.environnements.environnementsDialog, Ext.Window, {

  show: function(id, cId) {
    this.environnementsId = id || null;
    var categoriesId = cId || 0;
    
    this.frmArticle.form.reset();  
    this.frmArticle.form.baseParams['environnements_id'] = this.environnementsId;
    this.frmArticle.form.baseParams['current_category_id'] = categoriesId;

    Toc.environnements.environnementsDialog.superclass.show.call(this);
    this.loadArticle(this.pnlData);
  },

  loadArticle : function(panel){
     if (this.environnementsId && this.environnementsId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement article....');
      }
        
      this.frmArticle.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_article',
          environnements_id: this.environnementsId
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          var img = action.result.data.environnements_image;

          if (img != null && img != undefined) {
            var img = '../images/environnements/thumbnails/' + img;
            var html = '<div style="margin: 26px 0px 0px 20px"><img src="' + img + '" style="border: solid 1px #B5B8C8;" />&nbsp;&nbsp;<input type="checkbox" name="delimage" id="delimage" /><?php echo $osC_Language->get('field_delete'); ?></div>';

            this.frmArticle.findById('article_image_url').body.update(html);
          }

          this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.environnementsId,content_type : 'environnements',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.environnementsId,content_type : 'environnements',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.environnementsId,content_type : 'environnements',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.environnementsId,content_type : 'environnements',owner : Toc.content.ContentManager});

          this.tabenvironnements.add(this.pnlImages);
          this.tabenvironnements.add(this.pnlDocuments);
          this.tabenvironnements.add(this.pnlLinks);
          this.tabenvironnements.add(this.pnlComments);

          this.pnlPages.setCategories(action.result.data.categories_id);
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
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
    console.time('getContentPanel');
    console.time('pnlData');
    this.pnlData = new Toc.environnements.DataPanel({parent : this});
    console.timeEnd('pnlData');
    console.time('pnlDescription');
    this.pnlDescription = new Toc.content.DescriptionPanel({USE_WYSIWYG_TINYMCE_EDITOR : <?php echo USE_WYSIWYG_TINYMCE_EDITOR ?>,defaultLanguageCode : defaultLanguageCode});
    console.timeEnd('pnlDescription');
    console.time('pnlMetaInfo');
    this.pnlMetaInfo = new Toc.content.MetaInfoPanel();
    console.timeEnd('pnlMetaInfo');
    console.time('pnlPages');
    this.pnlPages = new Toc.content.CategoriesPanel();
    console.timeEnd('pnlPages');
    this.pnlPages.setTitle('Pages');
        
    this.tabenvironnements = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.pnlDescription,
        this.pnlMetaInfo,
        this.pnlPages
      ]
    });

    console.timeEnd('getContentPanel');
    return this.tabenvironnements;
  },
  
  buildForm: function() {
    this.frmArticle = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      title:'<?php echo $osC_Language->get('heading_title_data'); ?>',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'environnements',
        action : 'save_article'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmArticle;
  },
  
  submitForm : function() {
    var params = {
      content_categories_id: this.pnlPages.getCategories()
    };

    this.frmArticle.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      }, 
      scope: this
    });   
  }
});