<?php
/*
  $Id: customers_dialog.php 
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.CustomersDialog = function(config) {
  config = config || {};
  
  config.id = 'customers-dialog-win';
  config.title = 'Nouveau contact';
  config.modal = true;
  config.layout = 'fit';
  config.width = 800;
  config.iconCls = 'icon-customers-win';
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

  this.addEvents({'saveSuccess' : true});
  
  Toc.customers.CustomersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.customers.CustomersDialog, Ext.Window, {
  
  show: function (id) {
    this.tabCustomers.activate(this.pnlData);
    this.customersId = id || null;
    
    this.frmCustomers.form.reset();
    this.frmCustomers.form.baseParams['customers_id'] = this.customersId;

    Toc.customers.CustomersDialog.superclass.show.call(this);
    this.loadCustomer(this.pnlData);
  },

  loadCustomer : function(panel){
     if (this.customersId && this.customersId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }

      var that = this;

      this.frmCustomers.load({
        url: Toc.CONF.CONN_URL,
        params:{
          module: 'customers',
          action: 'load_customer'
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});

          this.pnlImages.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.pnlDocuments.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.pnlLinks.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.pnlComments.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.tabCustomers.add(this.pnlImages);
          this.tabCustomers.add(this.pnlDocuments);
          this.tabCustomers.add(this.pnlLinks);
          this.tabCustomers.add(this.pnlComments);          
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
    var that = this;
    this.pnlData = new Toc.customers.DataPanel({parent : this});
    this.pnlAdress = new Toc.customers.AddressPanel();

    this.pnlData.addListener('activate',function(panel){
        that.setHeight(250);
        that.center();
    });

    this.pnlAdress.addListener('activate',function(panel){
        that.setHeight(460);
        that.center();
    });

    this.tabCustomers = new Ext.TabPanel({
      activeTab: 1,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.pnlAdress
      ]
    });

    return this.tabCustomers;
  },
      
  buildForm: function() {
    this.frmCustomers = new Ext.form.FormPanel({ 
      fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'customers',
        action: 'save_customer'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });
    
    return this.frmCustomers;
  },

  submitForm : function() {
    this.frmCustomers.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});