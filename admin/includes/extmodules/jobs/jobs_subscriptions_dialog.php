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

Toc.jobs.subscriptionsDialog = function(config) {

  config = config || {};
  
  //config.id = 'databases-snapshots-dialog-win';
  //config.title = 'Snaphots browser';
  config.layout = 'fit';
  config.width = 850;
  config.height = 400;
  config.modal = true;
  config.iconCls = 'icon-feed-win';
  config.items = this.getContentPanel();
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess': true});

  Toc.jobs.subscriptionsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.jobs.subscriptionsDialog, Ext.Window, {
  show: function(data,path) {
    this.data = data;
    this.categoryId = path;

    Toc.jobs.jobsDialog.superclass.show.call(this);
    this.listSubscriptions(data);
  },
  getContentPanel: function() {
    this.pnlSubscriptions = new Toc.jobs.subscriptionsGrid({parent : this});

    return this.pnlSubscriptions;
  },
  listSubscriptions : function(data){
     this.pnlSubscriptions.getStore().baseParams['jobs_name'] = data.content_name;
     this.pnlSubscriptions.getStore().baseParams['jobs_id'] = data.jobs_id;
     this.pnlSubscriptions.getStore().load();
  }
});