<?php
/*
  $Id: content_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.content.ContentDialog = function(config) {
  
  config = config || {};
  
  config.id = 'content-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title_new_article'); ?>';
  config.layout = 'fit';
  config.width = 850;
  config.height = 570;
  config.modal = true;
  config.iconCls = 'icon-content-win';
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
  
  Toc.content.ContentDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.content.ContentDialog, Ext.Window, {

  show: function(id, cId) {
    var contentId = id || null;
    var categoriesId = cId || 0;
    
    this.frmArticle.form.reset();  
    this.frmArticle.form.baseParams['content_id'] = contentId;
    this.frmArticle.form.baseParams['current_category_id'] = categoriesId;
   
    if (contentId > 0) {
      this.frmArticle.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_article',
          content_id: contentId
        },
        success: function(form, action) {
          var img = action.result.data.content_image;

          if (img != null) {
            var img = '../images/content/thumbnails/' + img;
            var html = '<div style="margin: 26px 0px 0px 20px"><img src="' + img + '" style="border: solid 1px #B5B8C8;" />&nbsp;&nbsp;<input type="checkbox" name="delimage" id="delimage" /><?php echo $osC_Language->get('field_delete'); ?></div>';

            this.frmArticle.findById('article_image_url').body.update(html);
          }

          this.pnlPages.setCategories(action.result.data.categories_id);
          
          Toc.content.ContentDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this
      });
    } else {
      Toc.content.ContentDialog.superclass.show.call(this);
    }

    if (!Ext.isEmpty(categoriesId) && (categoriesId > 0)) {
      this.pnlPages.setCategories(categoriesId);
    }
  },

  getContentPanel: function() {
    this.pnlData = new Toc.content.DataPanel();
    this.pnlGeneral = new Toc.content.GeneralPanel();
    this.pnlMetaInfo = new Toc.content.MetaInfoPanel();
    this.pnlPages = new Toc.products.CategoriesPanel();
    this.pnlPages.setTitle('Pages');
        
    tabContent = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.pnlGeneral,
        this.pnlMetaInfo,
        this.pnlPages
      ]
    });
    
    return tabContent;
  },
  
  buildForm: function() {
    this.frmArticle = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      title:'<?php echo $osC_Language->get('heading_title_data'); ?>',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'content',
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