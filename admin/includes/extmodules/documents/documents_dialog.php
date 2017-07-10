<?php
/*
  $Id: documents_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.documents.DocumentsDialog = function(config) {
  config = config || {};
  
  config.id = 'documents_documents_dialog-win';
  config.width = 450;
  config.height = 325;
  config.iconCls = 'icon-documents_documents-win';
  
  config.items = this.buildForm();
  
  config.buttons = [{
    text: TocLanguage.btnSave,
    handler: function() {
      this.submitForm();
    },
    scope: this
  }, {
    text: TocLanguage.btnClose,
    handler: function() { 
      this.close();
    },
    scope: this
  }];
  
  Toc.documents.DocumentsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.documents.DocumentsDialog, Ext.Window, {
  show: function (id,cId) {
    var categoriesId = cId || 0;
    var documentsId = id || null;

    this.frmAttachment.form.baseParams['current_category_id'] = categoriesId;
    this.frmAttachment.form.baseParams['documents_id'] = documentsId;
    
    if (documentsId > 0) {
      this.frmAttachment.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'documents',
          action: 'load_document'
        },
        success: function (form, action) {
          Toc.documents.DocumentsDialog.superclass.show.call(this);
          var htmFile = action.result.data.html;
          
          this.pnlAttachmentFile.findById('documents_file').body.update(htmFile);
          var comp = this.pnlAttachmentFile.findById('documents_file_name');
          comp.disable();
          comp.button.disable();
          comp.container.hide();
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      });
    } else {
      Toc.documents.DocumentsDialog.superclass.show.call(this);
    }

    this.setCategoryId(categoriesId);
  },

  setCategoryId : function(categoriesId)
  {
     categoriesId = categoriesId == 0 ? -1 : categoriesId;
     this.cboCategories.getStore().on('load', function() {
     this.cboCategories.setValue(categoriesId);
  }, this);
  },
  
  getAttachmentFilePanel: function() {
    this.cboCategories = Toc.content.ContentManager.getCategoriesCombo();

    this.pnlAttachmentFile = new Ext.Panel({
      border: false,
      layout: 'form',
      defaults: {
        anchor: '96%'
      },
      items: [
            Toc.content.ContentManager.getContentStatusFields(),
            this.cboCategories,
        {id: 'documents_file_name',xtype: 'fileuploadfield', fieldLabel: 'Fichier',name: 'documents_file_name'
      },{
        xtype: 'panel',
        border: false,
        id: 'documents_file',
        style: 'margin-left: 115px; text-decoration: underline'
      }]
    });
    
    return this.pnlAttachmentFile;
  },
  
  getAttachmentDescriptionPanel: function() {
    this.tabLanguage = new Ext.TabPanel({
      activeTab: 0,
      enableTabScroll: true,
      deferredRender: false,
      border: false
    });  
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
        echo 'var pnlLang' . $l['code'] . ' = new Ext.Panel({
          labelWidth: 100,
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          autoHeight: true,
          labelSeparator: \' \',
          defaults: {
            anchor: \'96%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'Nom\', name: \'documents_name[' . $l['id'] . ']\', allowBlank: false},
            {xtype: \'textarea\', fieldLabel: \'Description\', name: \'documents_description[' . $l['id'] . ']\', height: 120}
          ]
        });
        
        this.tabLanguage.add(pnlLang' . $l['code'] . ');
        ';
      }
    ?>
    
    return this.tabLanguage;
  },

  buildForm: function() {
    this.frmAttachment = new Ext.form.FormPanel({
      border: false,
      url: Toc.CONF.CONN_URL,
      fileUpload: true,
      labelWidth: 100,
      baseParams: {  
        module: 'documents',
        action: 'save_document'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        this.getAttachmentFilePanel(),
        this.getAttachmentDescriptionPanel()
      ]
    });
    
    return this.frmAttachment;
  },
  
  submitForm: function() {
    this.frmAttachment.form.submit({
      success:function(form, action) {
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