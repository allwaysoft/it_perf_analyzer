<?php
/*
  $Id: rss_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.rss.RssDialog = function(config) {
  
  config = config || {};
  
  config.id = 'rss-dialog-win';
  config.title = 'Nouveau Flux RSS';
  config.layout = 'fit';
  config.width = 500;
  config.height = 255;
  config.modal = true;
  config.iconCls = 'icon-rss-win';
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
  
  Toc.rss.RssDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.rss.RssDialog, Ext.Window, {

  show: function(id, cId) {
    var rssId = id || null;
    var categoriesId = cId || 0;
    
    this.frmArticle.form.reset();  
    this.frmArticle.form.baseParams['rss_id'] = rssId;
    this.frmArticle.form.baseParams['current_category_id'] = categoriesId;
   
    if (rssId > 0) {
      this.frmArticle.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_rss',
          rss_id: rssId
        },
        success: function(form, action) {
          Toc.rss.RssDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this
      });
    } else {
      Toc.rss.RssDialog.superclass.show.call(this);
    }

    this.pnlData.setCategoryId(categoriesId);
  },

  getContentPanel: function() {
    this.pnlData = new Toc.rss.DataPanel();
        
    tabRss = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData
      ]
    });
    
    return tabRss;
  },
  
  buildForm: function() {
    this.frmArticle = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
<!--      title:'--><?php //echo $osC_Language->get('heading_title_data'); ?><!--',-->
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'rss',
        action : 'save_rss'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });  
    
    return this.frmArticle;
  },
  
  submitForm : function() {
    this.frmArticle.form.submit({
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