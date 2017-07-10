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

Toc.jobs.jobsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'jobs-dialog-win';
  config.title = 'Nouvel job';
  config.layout = 'fit';
  config.width = 850;
  config.height = 570;
  config.modal = true;
  config.iconCls = 'icon-jobs-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
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
  
  Toc.jobs.jobsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.jobs.jobsDialog, Ext.Window, {

  show: function(id, cId) {
    this.jobsId = id || null;
    var categoriesId = cId || 0;
    
    this.frmjob.form.reset();
    this.frmjob.form.baseParams['jobs_id'] = this.jobsId;
    this.frmjob.form.baseParams['current_category_id'] = categoriesId;

    Toc.jobs.jobsDialog.superclass.show.call(this);
    this.loadjob(this.pnlData);
  },

  loadjob : function(panel){
     if (this.jobsId && this.jobsId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement job....');
      }
        
      this.frmjob.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_job',
          jobs_id: this.jobsId
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          var img = action.result.data.jobs_image;

          if (img != null && img != undefined) {
            var img = '../images/jobs/thumbnails/' + img;
            var html = '<div style="margin: 26px 0px 0px 20px"><img src="' + img + '" style="border: solid 1px #B5B8C8;" />&nbsp;&nbsp;<input type="checkbox" name="delimage" id="delimage" /><?php echo $osC_Language->get('field_delete'); ?></div>';

            this.frmjob.findById('job_image_url').body.update(html);
          }

          this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.jobsId,content_type : 'jobs',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.jobsId,content_type : 'jobs',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.jobsId,content_type : 'jobs',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.jobsId,content_type : 'jobs',owner : Toc.content.ContentManager});

          this.tabjobs.add(this.pnlImages);
          this.tabjobs.add(this.pnlDocuments);
          this.tabjobs.add(this.pnlLinks);
          this.tabjobs.add(this.pnlComments);

          this.pnlPages.setCategories(action.result.data.categories_id);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.close();
        },
        scope: this
      });
    }
  },

  getContentPanel: function() {
    var defaultLanguageCode = '<?php list($defaultLanguageCode) = split("_", $osC_Language->getCode()); echo $defaultLanguageCode ?>';
    this.pnlData = new Toc.jobs.DataPanel({parent : this});
    this.pnlDescription = new Toc.content.DescriptionPanel({USE_WYSIWYG_TINYMCE_EDITOR : <?php echo USE_WYSIWYG_TINYMCE_EDITOR ?>,defaultLanguageCode : defaultLanguageCode});
    this.pnlMetaInfo = new Toc.content.MetaInfoPanel();
    this.pnlPages = new Toc.content.CategoriesPanel();
    this.pnlPages.setTitle('Pages');
        
    this.tabjobs = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.pnlDescription,
        this.pnlMetaInfo,
        this.pnlPages
      ]
    });

    return this.tabjobs;
  },
  
  buildForm: function() {
    this.frmjob = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      title:'<?php echo $osC_Language->get('heading_title_data'); ?>',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'jobs',
        action : 'save_job'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });

    return this.frmjob;
  },
  
  submitForm : function() {
    var params = {
      content_categories_id: this.pnlPages.getCategories()
    };

    this.frmjob.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      params : params,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      }, 
      scope: this
    });   
  }
});