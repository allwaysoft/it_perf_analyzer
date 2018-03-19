<?php


?>

Toc.bi.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.bi.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.bi.DataPanel, Ext.Panel, {
  getDataPanel: function() {
    this.pnlData = new Ext.Panel({
      layout: 'form',
          border: false,
          labelSeparator: ' ',
          //columnWidth: .9,
          autoHeight: true,
          defaults: {
            anchor: '97%'
          },
          items: [
            Toc.content.ContentManager.getContentStatusFields(),
            {xtype:'textfield', fieldLabel: 'Nom', name: 'content_name[2]', id: 'content_name'},
            {xtype:'textfield', fieldLabel: 'Link', name: 'reports_uri', id: 'reports_uri'},
            {xtype:'textarea', fieldLabel: 'Description',name: 'content_description[2]',id: 'content_description',maxLength : 500,height:200}
          ]
    });

    return this.pnlData;
  }
});