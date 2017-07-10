Toc.content.ImagesGrid = function(config) {

  config = config || {};
  config.loadMask = true;
  config.region = 'center';
  config.border = false;
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: config.module || 'content',
      action: 'get_images',
      content_id: config.content_id,
      content_type : config.content_type
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'id'
    }, config.columns || [
      'id',
      'image',
      'name',
      'size'
    ]),
    listeners: {
      load: function() {
        this.loaded = true;
        this.fireEvent('imagechange', this.getStore(), this);
      },
      scope: this
    }
  });

  function renderAction(value) {
    if(value == '1') {
      return '<img class="img-button btn-delete" style="cursor: pointer" src="templates/default/images/icons/16x16/delete.png" />';
    } else {
      return '<img class="img-button btn-delete" style="cursor: pointer" src="templates/default/images/icons/16x16/delete.png" />';
    }
  }

  config.cm = new Ext.grid.ColumnModel([
    { header: '&nbsp;', dataIndex: 'image', align: 'center'},
    { id:'products_image_name', header: '', dataIndex: 'name'},
    { header: '&nbsp;',dataIndex: 'size'},
    { header: '&nbsp;',dataIndex: 'default', width:50, renderer: renderAction, align: 'center'}
  ]);
  config.autoExpandColumn = 'products_image_name';
  
  config.tbar = [
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
  
  this.addEvents({'imagechange' : true});
  
  Toc.content.ImagesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.ImagesGrid, Ext.grid.GridPanel, {
  onSetDefault: function(row) {
    var record = this.getStore().getAt(row);
    var image  = Ext.isEmpty(record.get('id')) ? record.get('name') : record.get('id');

    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'products',
        action: 'set_default',
        image: image
      },
      callback: function(options, success, response){
        var result = Ext.decode(response.responseText);

        if (result.success == true) {
          this.getStore().reload();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
        
  onDelete: function(row) {
    var that = this;
    var record = this.getStore().getAt(row);
    var image  = Ext.isEmpty(record.get('id')) ? record.get('name') : record.get('id');   
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: that.module || 'content',
              action: 'delete_image',
              image: image
            },
            callback: function(options, success, response){
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.getStore().reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });   
        }
      }, this);
  },
    
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onClick:function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var action = false;

    if (row !== false) {
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
        var code = this.getStore().getAt(row).get('code');
        var title = this.getStore().getAt(row).get('title');
        
        switch(action) {
          case 'set-default':
            this.onSetDefault(row);
            break;
          case 'delete':
            this.onDelete(row);
            break;
        }
      }
    }
  }
});