<?php
/*
  $Id: contact_groupes_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.contact_groupes.Contact_groupDialog = function (config) {
  config = config || {};
  
  config.id = 'contact_groupes-dialog-win';
  config.title = 'Nouveau Groupe';
  config.layout = 'fit';
  config.width = 520;
  config.height = 380;
  config.modal = true;
  config.iconCls = 'icon-contact_groupes-win';
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
  
  Toc.contact_groupes.Contact_groupDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.contact_groupes.Contact_groupDialog, Ext.Window, {
  
  show: function (id, pId) {
    var contact_groupesId = id || null;
    var parentId = pId || 0;
    
    this.frmContact_group.form.reset();
    this.frmContact_group.form.baseParams['contact_groupes_id'] = contact_groupesId;
    
    if (contact_groupesId > 0) {
      this.frmContact_group.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_category'
        },
        success: function (form, action) {
          var ratings = action.result.data.ratings;
          var records = new Array();
          this.pnlRatings.getStore().each(function(record) { 
            if (ratings.contains(record.id))   
              records.push(record);   
          });   
          this.pnlRatings.getSelectionModel().selectRecords(records, true);
          
          this.pnlGeneral.cboParentContact_group.disable();
          var img = action.result.data.contact_groupes_image;
          
          if (img) {
            var html = '<img src ="../images/contact_groupes/' + img + '"  style = "margin-left: 170px; width: 70px; height:70px" /><br/><span style = "padding-left: 170px;">/images/contact_groupes/' + img + '</span>';
            this.frmContact_group.findById('contact_groupes_image_panel').body.update(html);
          }
          
          Toc.contact_groupes.Contact_groupDialog.superclass.show.call(this);
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      },
        this
      );
    } else {
      Toc.contact_groupes.Contact_groupDialog.superclass.show.call(this);
    }
    
    this.pnlGeneral.cboParentContact_group.getStore().on('load', function() {
      this.pnlGeneral.cboParentContact_group.setValue(parentId);
    }, this);
  },
  
  buildForm: function () {
    this.pnlGeneral = new Toc.contact_groupes.GeneralPanel();
    this.pnlMetaInfo = new Toc.contact_groupes.MetaInfoPanel();
    this.pnlRatings = new Toc.contact_groupes.RatingsGridPanel();
    
    tabContact_group = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlGeneral,
        this.pnlMetaInfo,
        this.pnlRatings   
      ]
    });
    
    this.frmContact_group = new Ext.form.FormPanel({
      id: 'form-contact_groupes',
      layout: 'fit',
      fileUpload: true,
      labelWidth: 120,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'contact_groupes',
        action: 'save_category'
      },
      scope: this,
      items: tabContact_group
    });
    
    return this.frmContact_group; 
  },
  
  submitForm: function () {
    this.frmContact_group.form.baseParams['ratings'] = this.pnlRatings.getSelectionModel().selections.keys;
    
    var status = this.pnlGeneral.findById('status').findByType('radio');
    status = status[0].getGroupValue();
    
    if(status == 0) {
      this.frmContact_group.form.baseParams['product_flag'] = 1;
    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDisableProducts, 
        function (btn) {
          if (btn == 'no') {
            this.frmContact_group.form.baseParams['product_flag'] = 0;

				    this.frmContact_group.form.submit({
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
				    this.frmContact_group.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback, action.result.contact_groupes_id, action.result.text);
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
	    this.frmContact_group.form.submit({
	      waitMsg: TocLanguage.formSubmitWaitMsg,
	      success: function (form, action) {
	        this.fireEvent('saveSuccess', action.result.feedback, action.result.contact_groupes_id, action.result.text);
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