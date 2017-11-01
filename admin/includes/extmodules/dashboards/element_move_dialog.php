<?php
/*
  $Id: asset_move_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.asset.assetMoveDialog = function (config) {
  config = config || {};
  
  config.id = 'asset-move-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_batch_move_asset"); ?>';
  config.asset = 'fit';
  config.width = 400;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-asset-win';
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
  
  Toc.asset.assetMoveDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.asset.assetMoveDialog, Ext.Window, {

  show: function (assetId, cPath) {
    assetId = assetId || null;
    this.cPath = cPath || null;

    this.frmasset.form.reset();
    this.frmasset.form.baseParams['asset_ids'] = assetId;

    Toc.asset.assetMoveDialog.superclass.show.call(this);
    this.pnlGeneral.cboParentasset.getStore().on('load', function() {
      this.pnlGeneral.cboParentasset.setValue(parentId);
    }, this);
  },
  
  buildForm: function () {

    this.cboParentasset = Toc.content.ContentManager.getassetCombo();
    
    this.frmasset = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'asset',
        action: 'move_asset'
      },
      border: false,
      frame: false,
      autoHeight: true,      
      labelAlign: 'top',
      defaults: {anchor: '97%'},
      assetConfig: { labelSeparator: '' },
      labelWidth: 160,
      items: this.cboParentasset
    });
    
    return this.frmasset;
  },
    
  submitForm: function () {
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
  }
});