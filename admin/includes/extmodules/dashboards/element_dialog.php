<?php

?>
Toc.asset.assetDialog = function (config) {
  config = config || {};
  
  config.id = 'asset-dialog-win';
  config.title = 'Nouvelle page';
  config.asset = 'fit';
  config.width = 730;
  config.height = 380;
  config.modal = true;
  config.iconCls = 'icon-asset-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
      },
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
    
  this.addEvents({'saveSuccess': true});
  
  Toc.asset.assetDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.asset.assetDialog, Ext.Window, {
  
  show: function (id, pId) {
    this.assetId = id || null;
    var parentId = pId || 0;
    
    this.frmasset.form.reset();
    this.frmasset.form.baseParams['asset_id'] = this.assetId;

    Toc.asset.assetDialog.superclass.show.call(this);
        
    this.pnlGeneral.cboParentasset.getStore().on('load', function() {
      this.pnlGeneral.cboParentasset.setValue(parentId);
    }, this);

    if(this.assetId == -1)
    {
        this.tabasset.removeAll(false);
        this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.assetId,content_type : 'pages',owner : this.owner});
        this.tabasset.add(this.pnlPermissions);
        this.buttons[0].disable();
        this.tabasset.activate(this.pnlPermissions);
        return;
    }

    if (this.assetId && this.assetId > 0) {
        this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.assetId,content_type : 'pages',owner : this.owner});
        this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.assetId,content_type : 'pages',owner : Toc.content.ContentManager});
        this.tabasset.add(this.pnlPermissions);
        this.tabasset.add(this.pnlComments);

        this.loadCategory(this.pnlGeneral);
    }
    else
    {
        this.tabasset.activate(this.pnlGeneral);
    }
  },

  loadCategory : function(panel){

    if (this.assetId && this.assetId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }
      this.frmasset.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_category'
        },
        success: function (form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          var img = action.result.data.asset_image;

          if (img) {
            var html = '<img src ="../images/asset/' + img + '"  style = "margin-left: 170px; width: 70px; height:70px" /><br/><span style = "padding-left: 170px;">/images/asset/' + img + '</span>';
            this.frmasset.findById('asset_image_panel').body.update(html);
          }
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.close();
        },
        scope: this
      },
        this
      );

      return;
    }
  },
  
  buildForm: function () {
    this.pnlGeneral = new Toc.asset.GeneralPanel();
    this.pnlMetaInfo = new Toc.content.MetaInfoPanel();

    this.tabasset = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [this.pnlGeneral,this.pnlMetaInfo]
    });
    
    this.frmasset = new Ext.form.FormPanel({
      id: 'form-asset',
      asset: 'fit',
      fileUpload: true,
      labelWidth: 120,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'asset',
        action: 'save_category'
      },
      scope: this,
      items: this.tabasset
    });
    
    return this.frmasset;
  },
  
  submitForm: function () {        
    var status = this.pnlGeneral.findById('status').findByType('radio');
    status = status[0].getGroupValue();
    
    if(status == 0) {
      this.frmasset.form.baseParams['product_flag'] = 1;
    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDisableProducts, 
        function (btn) {
          if (btn == 'no') {
            this.frmasset.form.baseParams['product_flag'] = 0;

				    this.frmasset.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback);
				        this.close();
				      },
				      failure: function (form, action) {
				        if (action.failureType != 'client') {
				          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				        }
				      },
				      scope: this
				    });

          } else{
				    this.frmasset.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback, action.result.asset_id, action.result.text);
				        this.close();
				      },
				      failure: function (form, action) {
				        if (action.failureType != 'client') {
				          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				        }
				      },
				      scope: this
				    });

          }
        }, 
        this
      );       
    } else {
	    this.frmasset.form.submit({
	      waitMsg: TocLanguage.formSubmitWaitMsg,
	      success: function (form, action) {
	        this.fireEvent('saveSuccess', action.result.feedback, action.result.asset_id, action.result.text);
	        this.close();
	      },
	      failure: function (form, action) {
	        if (action.failureType != 'client') {
	          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
	        }
	      },
	      scope: this
	    });
    }
  }
});