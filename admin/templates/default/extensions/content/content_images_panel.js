Toc.content.ImagesPanel = function(config) {

  config = config || {};

  config.title = 'Images';
  config.layout = 'fit';
  config.listeners = {
      activate : function(panel){
        console.log('activating images panel.........loaded ? ' + this.loaded);
        if(!this.loaded)
        {
           this.grdImages.getStore().reload();
        }
      },
      scope: this
  }
  
  config.content_id = config.content_id || null;
  config.items = this.buildForm(config.content_id,config.content_type,config.module || 'content');
  
  Toc.content.ImagesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.ImagesPanel, Ext.Panel, {

  buildForm: function(content_id,content_type,module) {
    this.grdImages = new Toc.content.ImagesGrid({content_id: content_id,content_type : content_type,module : module});
    var that = this;
    this.grdImages.getStore().on('load',function() {
              that.loaded = true;
              console.log('grdImages loaded.........grdImages ? ' + that.loaded);
           });
    pnlImages = new Ext.Panel({
      layout: 'border',
      border: false,
      items:  [{
        region:'east',
        layout:'accordion',
        split: true,
        width: 250,
        minSize: 175,
        maxSize: 400,
        border:false,
        items: [this.getImageUploadPanel(content_id,content_type,module)]
      }, 
      this.grdImages
     ]
    });
    
   return pnlImages;
  },
  
  getImageUploadPanel: function(content_id,content_type,module) {
    if(content_type != null && content_type != undefined)
    {
        var appendURl = '?module=' + module + '&action=upload_image';

    if (content_id > 0 ) {
      appendURl += ('&content_id=' + content_id + '&content_type=' + content_type);
    }

    this.pnlImagesUpload = new Ext.ux.UploadPanel({
      title: 'Image Upload',
      border: false,
      id: 'content-img-upload',
      removeAllIconCls: 'remove',
      maxFileSize: 4194304,
      addText: TocLanguage.btnAdd,
      uploadText: TocLanguage.btnUpload,
      enableProgress: false,
      url: Toc.CONF.CONN_URL + appendURl
    });

    this.pnlImagesUpload.on('allfinished', function() {
      this.grdImages.getStore().reload();
      this.pnlImagesUpload.removeAll();
    }, this);

    return this.pnlImagesUpload;
    }

    Ext.MessageBox.alert(TocLanguage.msgErrTitle, 'content type non defini !!!');
  },

  setContentId : function(content_id){
    var store = this.grdImages.getStore();
    store.baseParams['content_id'] = content_id;    
  }
});