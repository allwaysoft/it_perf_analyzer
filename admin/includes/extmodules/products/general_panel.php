<?php
/*
  $Id: general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.GeneralPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_general'); ?>';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm(config.USE_WYSIWYG_TINYMCE_EDITOR || 0);
  
  Toc.products.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.GeneralPanel, Ext.TabPanel, {
  buildForm: function(USE_WYSIWYG_TINYMCE_EDITOR) {
    var panels = [];

    var languages = Toc.Languages;
        var i = 0;
        while (i < languages.length) {
            var language = languages[i];
            if (USE_WYSIWYG_TINYMCE_EDITOR == 1) {
                this.editor = Toc.content.ContentManager.getTinyEditor(language['id'],250);
            }
            else {
                this.editor = {
                    xtype: 'htmleditor',
                    fieldLabel: 'Description',
                    name: 'content_description[' + language['id'] + ']',
                    height: 230
                };
            }

            var pnlLang = new Ext.Panel({
                    labelWidth: 100,
                    title : language['name'],
                    iconCls : 'icon-' + language['country_iso'] + '-win',
                    layout : 'form',
                    labelSeparator : '',
                    style : 'padding: 6px',
                    defaults: {
                        anchor : '97%'
                    },
                    items: [
                        {xtype: 'textfield', fieldLabel: 'Nom', name: 'products_name[' + language['id'] + ']', allowBlank: false},
                        {xtype: 'textfield', fieldLabel: 'Tags', name: 'products_tags[' + language['id'] + ']'},
                        {xtype: 'textarea', fieldLabel: 'Description courte', name: 'products_short_description[' + language['id'] + ']', height: '50'},
                        this.editor,
                        {xtype: 'textfield', fieldLabel: 'Url', name: 'products_url[' + language['id'] + ']'}
                    ]
                }
            );

            panels.push(pnlLang);
            i++;
        }
    
    return panels;
  }
});