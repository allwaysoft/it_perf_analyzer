<?php
/*
  $Id: databases_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.reports.snaphotsDialog = function(config) {

  config = config || {};
  
  config.id = 'databases-snapshots-dialog-win';
  config.title = 'Snaphots browser';
  config.layout = 'fit';
  config.width = 690;
  config.height = 400;
  config.modal = true;
  config.iconCls = 'icon-databases-win';
  config.items = this.getContentPanel();
  
  config.buttons = [
    {
      text: 'Selectionner',
      handler: function() {
        this.selectSnap();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess': true});

  Toc.reports.snaphotsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.reports.snaphotsDialog, Ext.Window, {
  show: function(start_or_end,start_field,end_field,params) {
    this.config = params;
    this.start_or_end = start_or_end;
    this.start_field = start_field;
    this.end_field = end_field;

    Toc.reports.reportsDialog.superclass.show.call(this);
    this.listSnaps(this.config);
  },
  getContentPanel: function() {
    this.pnlSnapshots = new Toc.snapshotsGrid({parent : this});

    return this.pnlSnapshots;
  },
  listSnaps : function(config){
     config.module = 'servers';
     config.action = 'list_snapshots';
     this.pnlSnapshots.getStore().baseParams = config;
     this.pnlSnapshots.getStore().load();
  },
  selectSnap : function(capture,value){
     if(this.snap_id)
        {
           switch(this.start_or_end)
           {
              case 'start':
                this.start_field.setValue(this.config.capture == 'snap_id' ? this.snap_id : this.time);
                this.start_field.snap_id = this.snap_id;
              break;
              case 'end':
                this.end_field.setValue(this.config.capture == 'snap_id' ? this.snap_id : this.time);
              break;
           }
           this.close();
        }
        else
        {
           Ext.Msg.alert(TocLanguage.msgErrTitle, 'Veuillez selectionner une capture !!!');
        }
  }
});