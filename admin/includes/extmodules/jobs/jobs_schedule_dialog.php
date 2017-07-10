<?php
/*
  $Id: jobs_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.jobs.jobscheduleDialog = function(config) {
  config = config || {};
  
  config.id = 'jobs-schedule-win';
  //config.title = 'Executer un Etat';
  config.layout = 'fit';
  config.width = 800;
  config.height = 460;
  config.resizable = false;
  config.minimizable = true,
  config.modal = true;
  config.iconCls = 'icon-jobs-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:'Executer',
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];

  this.addEvents({'saveSuccess' : true});  

  Toc.jobs.jobscheduleDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.jobs.jobscheduleDialog, Ext.Window, {

  show: function(record,cId) {

    var categoriesId = cId || 0;

    Toc.jobs.jobsDialog.superclass.show.call(this);
    if(record)
    {
      this.record = record;
      this.jobsId = record.jobs_id || null;
      this.frmJob.form.reset();
      this.frmJob.form.baseParams['jobs_id'] = record.jobs_id;
      this.frmJob.form.baseParams['owner'] = record.created_by;
      this.frmJob.form.baseParams['content_name'] = record.content_name;
      this.frmJob.form.baseParams['current_category_id'] = categoriesId;
      this.loadJobParameters(this.tabjobs);
    }
  },

  loadJobParameters : function(panel){
     if (this.jobsId && this.jobsId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement parametres ....');
      }
        
      this.frmJob.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_Jobparameters',
          jobs_id: this.jobsId
        },
        success: function(form, action) {
          this.buildParams(form, action,panel);
        },
        failure: function(form, action) {
          this.buildParams(form, action,panel);
        },
        scope: this
      });
    }
  },
  buildParams : function (form, action,panel) {
    if (action.result.msg == '1') {
        var params = action.result.params;
        if (!this.pnlParameters.buildControls(params)) {
            this.tabjobs.hideTabStripItem(0);
        }
    }
    else {
        Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.msg);
    }

    if(panel)
    {
        panel.getEl().unmask();
    }
  },
  getContentPanel: function() {
   this.pnlData = new Toc.jobs.DataPanel({parent : this});
   this.pnlActions = new Toc.jobs.ActionsGrid({parent : this});
   this.pnlActions.setTitle('Actions');
   this.pnlPages = new Toc.content.CategoriesPanel();
   this.pnlPages.setTitle('Espaces');
   this.pnlSchedule = new Toc.jobs.SchedulePanel({parent : this});
   this.pnlSchedule.setTitle('Planification');
   this.pnlNotification = new Toc.jobs.ScheduleNotificationPanel({parent : this});
   this.pnlNotification.setTitle('Notification');

    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        
    this.tabjobs = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      border:false,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [this.pnlData,this.pnlActions,this.pnlPages,this.pnlSchedule,this.pnlNotification
      ]
    });

    return this.tabjobs;
  },
  buildForm: function() {
    this.frmJob = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'jobs',
        action : 'schedule_Job'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmJob;
  },
  submitForm : function() {
    var params = {
    };

    this.frmJob.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      timeout:0,
      success: function(form, action){
        if(action.result.msg == '1')
        {
            this.fireEvent('saveSuccess', action.result.feedback);
            //this.close();
        }
        else
        {
            Ext.Msg.alert(TocLanguage.msgErrTitle, 'Impossible de planifier cette tache : ' + action.result.msg);
        }
      },    
      failure: function(form, action) {
        if(action.result.msg == '1')
        {
            this.fireEvent('saveSuccess', action.result.feedback);
        }
        else
        {
            Ext.Msg.alert(TocLanguage.msgErrTitle, 'Impossible de planifier cette tache : ' + action.result.msg);
        }
      }, 
      scope: this
    });   
  }
});