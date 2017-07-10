<?php
/*
  $Id: events_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.events.EventsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'events-dialog-win';
  config.title = 'Nouvel Evenement';
  config.layout = 'fit';
  config.width = 850;
  config.height = 570;
  config.modal = true;
  config.iconCls = 'icon-events-win';
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
  
  Toc.events.EventsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.events.EventsDialog, Ext.Window, {

  show: function(id, cId) {
    this.eventsId = id || null;
    var categoriesId = cId || 0;
    
    this.frmArticle.form.reset();  
    this.frmArticle.form.baseParams['events_id'] = this.eventsId;
    this.frmArticle.form.baseParams['current_category_id'] = categoriesId;

    this.pnlPages.setCategories(categoriesId);
    Toc.events.EventsDialog.superclass.show.call(this);
    this.loadEvents(this.pnlData);
  },

  loadEvents : function(panel){
     if (this.eventsId && this.eventsId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }

      this.frmArticle.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_article',
          events_id: this.eventsId
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          var img = action.result.data.events_image;

          if (img != null) {
            var img = '../images/events/thumbnails/' + img;
            var html = '<div style="margin: 26px 0px 0px 20px"><img src="' + img + '" style="border: solid 1px #B5B8C8;" />&nbsp;&nbsp;<input type="checkbox" name="delimage" id="delimage" /><?php echo $osC_Language->get('field_delete'); ?></div>';

            this.frmArticle.findById('article_image_url').body.update(html);
          }

          this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.eventsId,content_type : 'events',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.eventsId,content_type : 'events',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.eventsId,content_type : 'events',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.eventsId,content_type : 'events',owner : Toc.content.ContentManager});

          this.tabEvents.add(this.pnlImages);
          this.tabEvents.add(this.pnlDocuments);
          this.tabEvents.add(this.pnlLinks);
          this.tabEvents.add(this.pnlComments);

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
    this.pnlData = new Toc.events.DataPanel();
    this.pnlDescription = new Toc.content.DescriptionPanel({USE_WYSIWYG_TINYMCE_EDITOR : <?php echo USE_WYSIWYG_TINYMCE_EDITOR ?>,defaultLanguageCode : ''});
    this.pnlMetaInfo = new Toc.content.MetaInfoPanel();
    this.pnlPages = new Toc.content.CategoriesPanel();
    this.pnlPages.setTitle('Pages');
        
    this.tabEvents = new Ext.TabPanel({
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
    
    return this.tabEvents;
  },
  
  buildForm: function() {
    this.frmArticle = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'events',
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
      }, 
      scope: this
    });   
  }
});