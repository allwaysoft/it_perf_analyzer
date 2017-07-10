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

Toc.reports.reportExecutionDialog = function(config) {
  config = config || {};
  
  config.id = 'reports-execution-win';
  config.title = 'Executer un Etat';
  config.layout = 'fit';
  config.width = 600;
  config.height = 365;
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

  Toc.reports.reportExecutionDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.reports.reportExecutionDialog, Ext.Window, {

  show: function(record,cId) {
    this.record = record;
    this.reportsId = record.reports_id || null;
    var categoriesId = cId || 0;
    
    this.frmReport.form.reset();  
    this.frmReport.form.baseParams['reports_id'] = this.reportsId;
    this.frmReport.form.baseParams['owner'] = record.created_by;
    this.frmReport.form.baseParams['content_name'] = record.content_name;
    this.frmReport.form.baseParams['current_category_id'] = categoriesId;

    Toc.reports.reportsDialog.superclass.show.call(this);
    Toc.loadReportParameters(this.tabreports,this.reportsId,this.frmReport,this);
  },
  buildParams : function (form, action,panel) {
    if (action.result.msg == '1') {
        var params = action.result.params;
        if (!this.pnlParameters.buildControls(params)) {
            this.tabreports.hideTabStripItem(0);
        }
    }
    else {
        if (action.result.msg != '') {
           Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.msg);
        }
    }

    if(panel)
    {
        panel.getEl().unmask();
    }
  },
  getContentPanel: function() {
    this.pnlParameters = new Toc.reports.ParametersPanel({parent : this});
    this.pnlParameters.setTitle('Parametres');
    this.pnlFormats = new Toc.reports.FormatsPanel({parent : this});
    this.pnlFormats.setTitle('Format');
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        
    this.tabreports = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      border:false,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
         this.pnlParameters,this.pnlFormats
      ]
    });

    return this.tabreports;
  },
  
  buildForm: function() {
    this.frmReport = new Ext.form.FormPanel({
      //fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'reports',
        state: 'AD_HOC_ACTIVE',
        action : 'schedule_report'
        //action : 'run_report'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmReport;
  },
  downloadReport : function(request) {
    var status = request.status;
    var action = "";

    switch(status)
    {
       case "run":
          action = "start_report";
       break;
       case "complete":
          action = "download_report";
       break;
       default:
          //action = "status_report";
          action = "status_job";
       break;
    }

    this.tabreports.getEl().mask(request.comments);

    Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'reports',
              action : action,
              id:request.requestId,
              subscriptions_id:request.subscriptions_id,
              format:this.pnlFormats.getFormat(),
              content_name:this.record.content_name,
              comments:request.comments
            },
            callback: function(options, success, response) {
              if(response.responseText)
              {
                result = Ext.decode(response.responseText);
                switch(action)
                {
                  case 'download_report':
                    this.tabreports.getEl().unmask();
                    url = result.file_name;
                    params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
                    window.open(url, "",params);
                    this.buttons[0].enable();
                  break;
                  default:
                    var req = result;
                    this.requestId = req.requestId;
                    this.downloadReport(req);
                  break;
                }
              }
              else
              {
                var request = {
                   status : action == 'download_report' ? 'ready' : 'execution',
                   currentPage : '?',
                   requestId : this.requestId
                };

                this.tabreports.getEl().unmask();
                this.downloadReport(request);
              }
            },
            scope: this
     });
  },
  submitForm : function() {
    var params = {
    };

    this.buttons[0].disable();

    //Toc.runReport(this.frmReport, 40, this.owner, this.owner);

    this.frmReport.form.submit({
      waitMsg: 'Creation du job, veuillez patienter SVP ...',
      params : params,
      timeout:0,
      success: function(form, action){
        var request = action.result;
        this.downloadReport(request);
      },    
      failure: function(form, action) {
        if(action.failureType == 'connect') {
          //this.submitForm();
          Ext.Msg.alert(TocLanguage.msgErrTitle, 'Delai depassé !!!');
        }
        else
        {
           if(action.result.msg == '1')
           {
              var request = action.result;
              this.downloadReport(request);
           }
           else
           {
              Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.msg);
           }
        }
      }, 
      scope: this
    });   
  }
});