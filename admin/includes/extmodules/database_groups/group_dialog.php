<?php

?>
Toc.database_groups.GroupDialog = function(config) {
  config = config || {};
  
  config.id = 'group_dialog-win';
  config.title = 'Configurer un Groupe de Databases';
  config.width = 800;
  config.height = 350;
  config.modal = true;
  config.iconCls = 'icon-database-win';
  config.layout = 'fit';
  config.items = this.buildForm();  
  
  config.treeLoaded = false;
  
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

  Toc.database_groups.GroupDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.database_groups.GroupDialog, Ext.Window, {
  
  show: function (data) {
    if(data)
    {
       this.group_id = data.group_id || null;
       this.data = data;

       this.frmGroup.form.reset();
       this.frmGroup.form.baseParams['group_id'] = this.group_id;
    }

    Toc.database_groups.GroupDialog.superclass.show.call(this);
    this.loadGroup(this.pnlAdmin);
  },

  loadGroup : function(panel){
     if (this.group_id && this.group_id != -1) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }

      this.frmGroup.load({
        url: Toc.CONF.CONN_URL,
        params:{
          module: 'databases',
          action: 'load_group'
        },
        success: function(form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }
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
        
  getAdminPanel: function() {
    this.pnlAdmin = new Ext.Panel({
      border: false,
      title : 'Details',
      layout: 'form',
      autoHeight: true,
      labelSeparator: ' ',
      defaults: {
        anchor: '98%'
      },
      frame: false,
      style: 'padding: 5px',
      items: [
        {
          xtype: 'hidden',
          name: 'group_id',
          id: 'group_id'
        },
        {
          xtype: 'textfield',
          disabled:false,
          fieldLabel: 'Nom',
          name: 'group_name',
          allowBlank: false
        },
        {
          xtype: 'textarea',
          disabled:true,
          fieldLabel: 'Description',
          name: 'roles_description',
          id:  'roles_description',
          allowBlank: false,
          height : 250
        }
      ]
    });  
    
    return this.pnlAdmin;
  }, 

  buildForm: function() {    

    this.frmGroup = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      layout: 'fit',
      baseParams: {  
        module: 'roles'
      }, 
      border: false,
      items: [this.getAdminPanel()]
    });
    
    return this.frmGroup;
  },

  submitForm : function() {
    this.frmGroup.form.submit({
      url: Toc.CONF.CONN_URL,
      params: {
        'module' : 'databases',
        'action' : 'save_group'
      },
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});