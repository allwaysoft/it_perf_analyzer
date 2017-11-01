<?php

?>
Toc.dashboards.FolderDialog = function (config) {
  config = config || {};
  
  config.id = 'categories-dialog-win';
  config.title = 'Configurer un Dossier';
  config.layout = 'fit';
  config.width = 730;
  config.height = 380;
  config.modal = true;
  config.iconCls = 'icon-categories-win';
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

  Toc.dashboards.FolderDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.dashboards.FolderDialog, Ext.Window, {
  
  show: function (id, pId) {
    this.categoriesId = id || null;
    var parentId = pId || 0;
    
    this.frmCategories.form.reset();
    this.frmCategories.form.baseParams['categories_id'] = this.categoriesId;

    Toc.categories.CategoriesDialog.superclass.show.call(this);

    if (this.categoriesId && this.categoriesId > 0) {
        this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.categoriesId,content_type : 'folder',owner : this.owner,action:'list_perms'});
        this.pnlNotifications = new Toc.content.NotificationsPanel({content_id : this.categoriesId,content_type : 'folder',owner : this.owner});
        this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.categoriesId,content_type : 'folder',owner : Toc.content.ContentManager});
        this.tabCategories.add(this.pnlPermissions);
        this.tabCategories.add(this.pnlNotifications);
        this.tabCategories.add(this.pnlComments);

        this.loadCategory(this.pnlGeneral);
    }
    else
    {
        this.tabCategories.activate(this.pnlGeneral);
    }
  },

  loadCategory : function(panel){

    if (this.categoriesId && this.categoriesId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }
      this.frmCategories.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_category'
        },
        success: function (form, action) {
          if(panel)
          {
             panel.getEl().unmask();
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
    this.pnlGeneral = new Toc.categories.GeneralPanel();
    this.pnlMetaInfo = new Toc.content.MetaInfoPanel();

    this.tabCategories = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [this.pnlGeneral,this.pnlMetaInfo]
    });
    
    this.frmCategories = new Ext.form.FormPanel({
      id: 'form-categories',
      layout: 'fit',
      labelWidth: 120,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'categories',
        action: 'save_category'
      },
      scope: this,
      items: this.tabCategories
    });
    
    return this.frmCategories; 
  },
  
  submitForm: function () {        
    var status = this.pnlGeneral.findById('status').findByType('radio');
    status = status[0].getGroupValue();
    
    if(status == 0) {
      this.frmCategories.form.baseParams['product_flag'] = 1;
    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDisableProducts, 
        function (btn) {
          if (btn == 'no') {
            this.frmCategories.form.baseParams['product_flag'] = 0;

				    this.frmCategories.form.submit({
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
				    this.frmCategories.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback, action.result.categories_id, action.result.text);
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
	    this.frmCategories.form.submit({
	      waitMsg: TocLanguage.formSubmitWaitMsg,
	      success: function (form, action) {
	        this.fireEvent('saveSuccess', action.result.feedback, action.result.categories_id, action.result.text);
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