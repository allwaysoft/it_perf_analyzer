Toc.jobs.ActionBrowser = function(config) {
  config = config || {};

  config.id = 'jobs-actions-win';
  config.layout = 'fit';
  config.width = 800;
  config.height = 600;
  config.minimizable = true,
  config.modal = true;
  config.iconCls = 'icon-actions-win';
  config.items = this.getContentPanel();

  config.buttons = [
    {
      text:'Selectionner',
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

  Toc.jobs.ActionBrowser.superclass.constructor.call(this, config);
}

Ext.extend(Toc.jobs.ActionBrowser, Ext.Window, {

  show: function(record,cId) {

    var categoriesId = cId || 0;

    Toc.jobs.ActionBrowser.superclass.show.call(this);
    if(record)
    {
      this.record = record;
      this.jobsId = record.jobs_id || null;
      this.frmAction.form.reset();
      this.frmAction.form.baseParams['jobs_id'] = record.jobs_id;
      this.frmAction.form.baseParams['owner'] = record.created_by;
      this.frmAction.form.baseParams['content_name'] = record.content_name;
      this.frmAction.form.baseParams['current_category_id'] = categoriesId;
    }
  },
  getContentPanel: function() {
    this.pnl = new Toc.jobs.ActionPanel({owner: this});

    return this.pnl;
  },
  submitForm : function() {
    this.frmAction.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
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