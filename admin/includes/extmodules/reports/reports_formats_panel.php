<?php
/*
  $Id: reports_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.reports.FormatsPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  border = false;
  config.items = this.getDataPanel();
    
  Toc.reports.FormatsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports.FormatsPanel, Ext.Panel, {
  getFormat: function() {
    return Ext.getCmp('radiogroup_format').items.get(0).getGroupValue();
  },
  getDataPanel: function() {
   this.radiogroup = {
            xtype: 'radiogroup',
            id:'radiogroup_format',
            fieldLabel: '',
            itemCls: 'x-check-group-alt',
            columns: 1,
            items: [
                {boxLabel: 'XLS  ==> Microsoft Excel Spreadsheet', name: 'format', inputValue: 'xls'},
                {boxLabel: 'XLSX ==> Microsoft Excel Open XML Spreadsheet', name: 'format', inputValue: 'xlsx'},
                {boxLabel: 'PDF  ==> Portable Document Format', name: 'format', inputValue: 'pdf', checked: true},
                {boxLabel: 'DOCX ==> Microsoft Word Open Document', name: 'format', inputValue: 'docx'},
                {boxLabel: 'HTML ==> Hypertext Markup Language File', name: 'format', inputValue: 'html'},
                {boxLabel: 'ODT  ==> OpenOffice OpenDocument Text Document', name: 'format', inputValue: 'odt'},
                {boxLabel: 'ODS  ==> OpenOffice OpenDocument Spreadsheet', name: 'format', inputValue: 'ods'},
                {boxLabel: 'TXT  ==> Plain Text File', name: 'format', inputValue: 'txt'},
                {boxLabel: 'RTF  ==> Rich Text Format', name: 'format', inputValue: 'rtf'},
                {boxLabel: 'CSV  ==> Comma Separated Values', name: 'format', inputValue: 'csv'}
               ]
            };

    this.pnlData = new Ext.Panel({
          layout: 'column',
          border: false,
          labelSeparator: ' ',
          //columnWidth: .9,
          autoHeight: true,
          defaults: {
            //anchor: '60%'
          },
          items: [
             this.radiogroup
          ]
    });

    return this.pnlData;
  }
});