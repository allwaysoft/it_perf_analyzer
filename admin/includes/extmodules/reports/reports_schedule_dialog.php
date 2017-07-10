<?php
/*
  $Id: reports_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.reports.reportScheduleDialog = function(config) {
  config = config || {};
  
  config.id = 'reports-schedule-win';
  //config.title = 'Executer un Etat';
  config.layout = 'fit';
  config.width = 800;
  config.height = 460;
  config.resizable = false;
  config.minimizable = true,
  config.modal = true;
  config.iconCls = 'icon-reports-win';
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

  Toc.reports.reportScheduleDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.reports.reportScheduleDialog, Ext.Window, {

  show: function(record,cId) {
    this.record = record;
    this.reportsId = record.reports_id || null;
    var categoriesId = cId || 0;
    
    this.frmReport.form.reset();  
    this.frmReport.form.baseParams['reports_id'] = record.reports_id;
    this.frmReport.form.baseParams['owner'] = record.created_by;
    this.frmReport.form.baseParams['content_name'] = record.content_name;
    this.frmReport.form.baseParams['current_category_id'] = categoriesId;

    Toc.reports.reportsDialog.superclass.show.call(this);
    this.loadReportParameters(this.tabreports);
  },

  loadReportParameters : function(panel){
     if (this.reportsId && this.reportsId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement parametres ....');
      }
        
      this.frmReport.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_reportparameters',
          reports_id: this.reportsId
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
            this.tabreports.hideTabStripItem(0);
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
   this.pnlSchedule = new Toc.reports.SchedulePanel({parent : this});
   this.pnlSchedule.setTitle('Planification');
   this.pnlParameters = new Toc.reports.ParametersPanel({parent : this});
   this.pnlParameters.setTitle('Parametres');
   this.pnlFormats = new Toc.reports.ScheduleFormatsPanel({parent : this});
   this.pnlFormats.setTitle('Formats');
   this.pnlNotification = new Toc.reports.ScheduleNotificationPanel({parent : this});
   this.pnlNotification.setTitle('Notification');

    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        
    this.tabreports = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      border:false,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [this.pnlParameters,this.pnlSchedule,this.pnlFormats,this.pnlNotification
      ]
    });

    return this.tabreports;
  },
  buildForm: function() {
    this.frmReport = new Ext.form.FormPanel({
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'reports',
        action : 'schedule_report'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmReport;
  },
  submitForm : function() {
    var params = {
    };

    this.frmReport.form.submit({
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