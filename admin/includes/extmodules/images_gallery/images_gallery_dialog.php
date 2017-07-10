 <?php
/*
  $Id: slide_images_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.images_gallery.ImagesGalleryDialog = function(config) {
  
  config = config || {};
  
  config.id = 'slide_images_dialog-win';
  config.title = 'Nouvelle image';
  config.layout = 'fit';
  config.modal = true;
  config.width = 600;
  config.height = 500;
  config.iconCls = 'icon-slide_images-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];
    
  Toc.images_gallery.ImagesGalleryDialog.superclass.constructor.call(this, config);

  this.addEvents({'save' : true});  
}

Ext.extend(Toc.images_gallery.ImagesGalleryDialog, Ext.Window, {
  show: function (id,cId) {
    var images_galleryId = id || null;
    var categoriesId = cId || -1;
    
    this.pnlImagesGallery.form.reset();
    this.pnlImagesGallery.form.baseParams['image_id'] = images_galleryId;
    this.pnlImagesGallery.form.baseParams['categories_id'] = categoriesId;
    
    if (images_galleryId > 0) {
      this.pnlImagesGallery.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'slide_images',
          action: 'load_slide_images'
        },
        success: function(form, action) {
          <?php 
            foreach ($osC_Language->getAll() as $l) {
              echo " 
                if (action.result.data.slide_image" . $l['id'] . ") {
                  var image = action.result.data.slide_image" . $l['id'] . ";
                  this.pnlImagesGallery.findById('uploaded_img" . $l['id'] . "').body.update(image);
                }";
             
            }
          ?>

          this.pnlPages.setCategories(action.result.data.categories_id);
          Toc.images_gallery.ImagesGalleryDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData)
        },
        scope: this   
      }); 
    }
    else
    {
      Toc.images_gallery.ImagesGalleryDialog.superclass.show.call(this);
      if (!Ext.isEmpty(categoriesId) && (categoriesId > 0)) {
          this.pnlPages.setCategories(categoriesId);
      }
    }
  },

  getDataPanel: function() {
      var pnlData = new Ext.Panel({
      region: 'north',
      title: 'Donnees',
      border: false,
      labelWidth: 108,
      autoHeight: true,
      layout: 'form',
      defaults: {
        anchor: '98%'
      },
      items: [
        {
          layout: 'column',
          border: false,
          items: [
            {
              width: 200,
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items:[
                {fieldLabel: '&nbsp;&nbsp;Status', boxLabel: 'Activé' , name: 'status', xtype:'radio', inputValue: '1'}
              ]
            },
            {
              width: 80,
              layout: 'form',
              border: false,
              items: [
                {hideLabel: true, boxLabel: 'Désactivé', xtype:'radio', name: 'status', inputValue: '0'}
              ]
            }
          ]
        },                           
        { 
          labelSeparator: ' ',
          xtype: 'numberfield', 
          fieldLabel: '&nbsp;&nbsp;Position',
          name: 'sort_order',
          width: 402
        }
      ]
    });
    
    return pnlData;
  },
  
  getTabPanel: function() {
    this.pnlPages = new Toc.content.CategoriesPanel();
    this.pnlPages.setTitle('Pages');
    var tabImages = new Ext.TabPanel({
       region: 'center',
       defaults:{
         hideMode: 'offsets'
       },
       activeTab: 0,
       deferredRender: false
    });  
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
        echo 'this.' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          defaults: {
            anchor: \'98%\'
          },
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 8px\',
          items: [
            {layout: \'column\', width: 500, border: false, items: [{layout: \'form\', labelSeparator: \' \', border: false, items: [{xtype: \'fileuploadfield\', width: \'300\', fieldLabel: \'Image\', name: \'image' . $l['id'] . '\'}]},{layout: \'form\', border: false, items: [{xtype: \'panel\', border: false, html:\'<span style= "padding: 5px 0px 0px 10px; display: block;"><b>200 kb</b></span>\'}]}]},
            {xtype: \'panel\', border: false, width: 400, name: \'uploaded_img'.$l['id'].'\', id: \'uploaded_img'.$l['id'].'\', html:\'\'},
            {xtype: \'textarea\', id: \''.$l['code'].'\', fieldLabel: \'Description\', width: 400, height: 150, name: \'description[' . $l['id'] . ']\'},
            {xtype: \'textfield\', fieldLabel: \'Url\', width: 400, name: \'image_url[' . $l['id'] . ']\'}
          ]
        });
        
        tabImages.add(this.' . $l['code'] . ');
        ';
      }
    ?>
    tabImages.add(this.pnlPages);
    return tabImages;
  },

  buildForm: function() {
    this.pnlImagesGallery = new Ext.FormPanel({
      layout: 'border',
      width: 600,
      height: 350,
      border: false,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'slide_images',
        action: 'save_slide_images'
      }, 
      fileUpload: true,
      items: [this.getDataPanel(), this.getTabPanel()]
    });
    
    return this.pnlImagesGallery;  
  },
  
  submitForm : function() {
    var params = {
      categories_id: this.pnlPages.getCategories()
    };

    this.pnlImagesGallery.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
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