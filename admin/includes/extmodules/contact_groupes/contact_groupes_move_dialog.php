<?php
/*
  $Id: contact_groupes_move_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.contact_groupes.Contact_groupesMoveDialog = function (config) {
  config = config || {};
  
  config.id = 'contact_groupes-move-dialog-win';
  config.title = 'Deplacer des groupes de contact';
  config.layout = 'fit';
  config.width = 400;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-contact_groupes-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
        this.disable();
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
  
  Toc.contact_groupes.Contact_groupesMoveDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.contact_groupes.Contact_groupesMoveDialog, Ext.Window, {

  show: function (contact_groupesId, cPath) {
    contact_groupesId = contact_groupesId || null;
    this.cPath = cPath || null;

    this.frmContact_groupes.form.reset();
    this.frmContact_groupes.form.baseParams['contact_groupes_ids'] = contact_groupesId;

    Toc.contact_groupes.Contact_groupesMoveDialog.superclass.show.call(this);
  },
  
  buildForm: function () {
    dsParentContact_groupes = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'contact_groupes',
        action: 'list_parent_category'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'id', 
          'text'
        ]
      }),
      autoLoad: true,
      listeners: {
        load: function() {
          this.cboParentContact_groupes.setValue(this.cPath);
        },
        scope: this
      }
    });
    
    this.cboParentContact_groupes = new Toc.Contact_groupesComboBox({
      store: dsParentContact_groupes,
      displayField: 'text',
      mode: 'local',
      fieldLabel: '<?php echo $osC_Language->get("field_parent_category"); ?>',
      valueField: 'id',
      hiddenName: 'parent_category_id',
      triggerAction: 'all',
      allowBlank: true,
      editable: false
    });
    
    this.frmContact_groupes = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'contact_groupes',
        action: 'move_contact_groupes'
      },
      border: false,
      frame: false,
      autoHeight: true,      
      labelAlign: 'top',
      defaults: {anchor: '97%'},
      layoutConfig: { labelSeparator: '' },
      labelWidth: 160,
      items: this.cboParentContact_groupes
    });
    
    return this.frmContact_groupes;
  },
    
  submitForm: function () {
    this.frmContact_groupes.form.submit({
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
  }
});