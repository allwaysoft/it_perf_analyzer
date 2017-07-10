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

Toc.jobs.JobExecutionDialog = function(config) {
  config = config || {};
  
  config.id = 'jobs-execution-win';
  config.title = 'Executer un Etat';
  config.layout = 'fit';
  config.width = 600;
  config.height = 365;
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

  Toc.jobs.JobExecutionDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.jobs.JobExecutionDialog, Ext.Window, {

  show: function(record,cId) {
    this.record = record;
    this.jobsId = record.jobs_id || null;
    var categoriesId = cId || 0;
    
    this.frmJob.form.reset();
    this.frmJob.form.baseParams['jobs_id'] = this.jobsId;
    this.frmJob.form.baseParams['owner'] = record.created_by;
    this.frmJob.form.baseParams['content_name'] = record.content_name;
    this.frmJob.form.baseParams['current_category_id'] = categoriesId;

    Toc.jobs.jobsDialog.superclass.show.call(this);
    Toc.loadJobParameters(this.tabjobs,this.jobsId,this.frmJob,this);
  },
  buildParams : function (form, action,panel) {
    if (action.result.msg == '1') {
        var params = action.result.params;
        if (!this.pnlParameters.buildControls(params)) {
            this.tabjobs.hideTabStripItem(0);
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
    this.pnlParameters = new Toc.jobs.ParametersPanel({parent : this});
    this.pnlParameters.setTitle('Parametres');
    this.pnlFormats = new Toc.jobs.FormatsPanel({parent : this});
    this.pnlFormats.setTitle('Format');
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
        
    this.tabjobs = new Ext.TabPanel({
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

    return this.tabjobs;
  },
  
  buildForm: function() {
    this.frmJob = new Ext.form.FormPanel({
      //fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'jobs',
        state: 'AD_HOC_ACTIVE',
        action : 'schedule_Job'
        //action : 'run_Job'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmJob;
  },
  downloadJob : function(request) {
  console.debug(request);
    var status = request.status;
    var action = "";

    switch(status)
    {
       case "run":
          action = "start_Job";
       break;
       case "complete":
          action = "download_Job";
       break;
       default:
          //action = "status_Job";
          action = "status_job";
       break;
    }

    this.tabjobs.getEl().mask(request.comments);

    Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'jobs',
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
                  case 'download_Job':
                    this.tabjobs.getEl().unmask();
                    url = result.file_name;
                    params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
                    window.open(url, "",params);
                    this.buttons[0].enable();
                  break;
                  default:
                    var req = result;
                    this.requestId = req.requestId;
                    this.downloadJob(req);
                  break;
                }
              }
              else
              {
                var request = {
                   status : action == 'download_Job' ? 'ready' : 'execution',
                   currentPage : '?',
                   requestId : this.requestId
                };

                this.tabjobs.getEl().unmask();
                this.downloadJob(request);
              }
            },
            scope: this
     });
  },
  submitForm : function() {
    var params = {
    };

    this.buttons[0].disable();

    //Toc.runJob(this.frmJob, 40, this.owner, this.owner);

    this.frmJob.form.submit({
      waitMsg: 'Creation du job, veuillez patienter SVP ...',
      params : params,
      timeout:0,
      success: function(form, action){
        var request = action.result;
        this.downloadJob(request);
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
              this.downloadJob(request);
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