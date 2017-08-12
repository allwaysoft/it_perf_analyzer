<?php
/*
  $Id: products_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.ProductDialog = function(config) {
  config = config || {};
  
  config.id = 'products-dialog-win';
  config.title = 'New Product';
  config.layout = 'fit';
  config.width = 870;
  config.height = 540;
  config.modal = true;
  config.iconCls = 'icon-products-win';
  config.productsId = config.products_id || null;
  this.owner = config.owner || null;
  this.flagContinueEdit = false;
  
  config.items = this.buildForm(config.productsId);
  
  config.buttons = [
    {
      text: TocLanguage.btnSaveAndContinue,
      handler: function(){
        this.flagContinueEdit = true;
        
        this.submitForm();
      },
      scope:this
    },
    {
      text:'Submit',
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: 'Close',
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];
    
  this.addEvents({'saveSuccess': true});      

  Toc.products.ProductDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.ProductDialog, Ext.Window, {
  buildForm: function(productsId) {
    console.time('buildForm');
    this.pnlData = new Toc.products.DataPanel();
    this.pnlImages = new Toc.content.ImagesPanel({content_id : productsId,content_type : 'products',owner : Toc.content.ContentManager,module : 'products'});;
    //this.pnlData.on('producttypechange', this.pnlVariants.onProductTypeChange, this.pnlVariants);
    this.pnlGeneral = new Toc.products.GeneralPanel({USE_WYSIWYG_TINYMCE_EDITOR : <?php echo USE_WYSIWYG_TINYMCE_EDITOR ?>,defaultLanguageCode : ''});
    this.pnlCategories = new Toc.content.CategoriesPanel({productsId: productsId})
    this.pnlCategories.setTitle('Categories');
    
    tabProduct = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlGeneral,
        this.pnlData,
        this.pnlCategories,
        this.pnlImages
      ]
    }); 

    this.frmProduct = new Ext.form.FormPanel({
      layout: 'fit',
      fileUpload: true,
      url: Toc.CONF.CONN_URL,
      labelWidth: 120,
      baseParams: {  
        module: 'products',
        action: 'save_product'
      },
      items: tabProduct
    });

    console.timeEnd('buildForm');
    return this.frmProduct;
  },

  loadProduct : function(panel){
     if (this.productsId && this.productsId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }

      this.frmProduct.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_product',
          products_id: this.productsId
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.pnlImages.grdImages.store.load();
          this.pnlData.onPriceNetChange();
          this.pnlData.updateCboTaxClass(action.result.data.products_type);
          this.pnlData.loadExtraOptionTab(action.result.data);
          this.pnlCategories.setCategories(action.result.data.categories_id);
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
    
  show: function(categoryId) {
    this.frmProduct.form.reset();
    Toc.products.ProductDialog.superclass.show.call(this);
    if (!Ext.isEmpty(categoryId) && (categoryId > 0)) {
      this.pnlCategories.setCategories(categoryId);
    }
        
    this.loadProduct(this.pnlGeneral);
  },

  submitForm: function() {
    var params = {
      action: 'save_product',
      accessories_ids: '',
      xsell_ids: '',
      products_variants: '',
      products_id: this.productsId,
      attachments_ids: '',
      categories_id: this.pnlCategories.getCategories(),
      customization_fields: ''
    };
    
    <?php if (USE_WYSIWYG_TINYMCE_EDITOR == '1') { ?>
      tinyMCE.triggerSave();
    <?php } ?>
    
    if (this.productsId > 0) {
      params.products_type = this.pnlData.getProductsType();
    }
    
    //var status = this.pnlVariants.checkStatus();
    var status = true;
    
    if (status == true) { 
      this.frmProduct.form.submit({
        params: params,
        waitMsg: TocLanguage.formSubmitWaitMsg,
        success:function(form, action){
          this.fireEvent('saveSuccess', action.result.feedback);

          if (this.flagContinueEdit == true) {
            this.productsId = action.result.productsId;
            this.frmProduct.form.baseParams['products_id'] = this.productsId;
            this.pnlImages.grdImages.getStore().baseParams['products_id'] = this.productsId;
            this.pnlImages.pnlImagesUpload.uploader.setUrl(Toc.CONF.CONN_URL + '?module=products&action=upload_image&products_id=' + this.productsId);
            this.pnlImages.productsId = this.productsId;
            
            this.pnlData.cboProductsType.disable();
            this.pnlImages.grdImages.getStore().reload();
            this.flagContinueEdit = false;  
            
            Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, action.result.feedback);
            
            Ext.each(action.result.urls, function(url) {
<!--              this.pnlMeta.txtProductUrl[url.languages_id].setValue(url.url);-->
            }, this);
          } else {
            this.close();
          }
        },    
        failure: function(form, action) {
          if(action.failureType != 'client') {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
          }
        },
        scope: this
      });  
    } else {
      Ext.MessageBox.alert(TocLanguage.msgErrTitle, '<?php echo $osC_Language->get('msg_select_default_variants_records'); ?>');
    }
  }
});