<?php
/*
  $Id: reports_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.reports.reportsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'reports-dialog-win';
  config.title = 'Editer un Etat';
  config.layout = 'fit';
  config.width = 600;
  config.height = 400;
  config.modal = true;
  config.iconCls = 'icon-reports-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:'Deployer',
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
  
  Toc.reports.reportsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.reports.reportsDialog, Ext.Window, {

  show: function(id,owner,cId) {
    this.reportsId = id || null;
    var categoriesId = cId || 0;
    
    this.frmReport.form.reset();  
    this.frmReport.form.baseParams['reports_id'] = this.reportsId;
    this.frmReport.form.baseParams['owner'] = owner;
    this.frmReport.form.baseParams['current_category_id'] = categoriesId;

    Toc.reports.reportsDialog.superclass.show.call(this);
    this.loadReport(this.pnlData);
  },

  loadReport : function(panel){
     if (this.reportsId && this.reportsId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement Etat ....');
      }
        
      this.frmReport.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_report',
          reports_id: this.reportsId
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.reportsId,content_type : 'reports',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.reportsId,content_type : 'reports',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.reportsId,content_type : 'reports',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.reportsId,content_type : 'reports',owner : Toc.content.ContentManager});

          this.tabreports.add(this.pnlImages);
          this.tabreports.add(this.pnlDocuments);
          this.tabreports.add(this.pnlLinks);
          this.tabreports.add(this.pnlComments);

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
    this.pnlData = new Toc.reports.DataPanel({parent : this});
    this.pnlPages = new Toc.content.CategoriesPanel();
    this.pnlPages.setTitle('Espaces');
        
    this.tabreports = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.pnlPages
      ]
    });

    return this.tabreports;
  },
  
  buildForm: function() {
    this.frmReport = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'reports',
        action : 'save_report'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmReport;
  },
  
  submitForm : function() {
    var params = {
      content_categories_id: this.pnlPages.getCategories()
    };

    this.frmReport.form.submit({
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