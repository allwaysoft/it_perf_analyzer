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

Toc.reports.ParametersPanel = function(config) {
  config = config || {};
  config.autoScroll = true;
  config.builtin = new Array("SERVERS_CONNEXION","SERVER_CONNEXION","ORACLE_CONNEXION","LABEL_SERVEUR","LABEL_DATABASE","START_SNAP","END_SNAP","START_SNAP_TIME","END_SNAP_TIME");
  config.title = 'Parametres';
  //config.header = true;
  //config.headerAsText = false;
  config.deferredRender = false;
  config.border = false;
  config.layout = 'form';
  config.items = this.getDataPanel();
    
  Toc.reports.ParametersPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports.ParametersPanel, Ext.Panel, {
  getDataPanel: function() {
    this.pnlData = this;
    return this.pnlData;
  },
  setParams : function(data){
        this.params = {};
        var par = data.split("#");

        var i = 0;
        while(i < par.length)
        {
            var current_param = par[i].split("=");

            this.params[current_param[0]] = current_param[1];

            i++;
        }
  },
  getParams : function(){
     return this.params;
  },
  buildControls: function(params,panel) {
    var that = this;
    var i = 0;
       while(i < params.length)
       {
          var param = params[i];

          switch(param.label)
          {
              case 'ORACLE_CONNEXION':
                var connexion_combo = Toc.content.ContentManager.getOracleConnexionsCombo();
                var label_database = new Ext.form.Hidden({allowBlank:false,name:'PARAM_LABEL_DATABASE'});
                var label_connexion = new Ext.form.Hidden({allowBlank:false,name:'PARAM_ORACLE_CONNEXION'});
                this.pnlData.add(connexion_combo);
                this.pnlData.add(label_connexion);
                this.pnlData.add(label_database);
                connexion_combo.on('select',function(combo,record,index){
                    label_database.setValue(record.data.label_database);
                    label_connexion.setValue(record.data.oracle_connexion);
                    that.setParams(record.data.oracle_connexion);
                });

                connexion_combo.getStore().on('beforeload',function(store,options){
                    if(panel)
                    {
                       panel.getEl().mask('Chargement des bases ....');
                    }
                });
                connexion_combo.getStore().on('load',function(store,records,options){
                    if(panel)
                    {
                       panel.getEl().unmask();
                    }
                });
              break;

              case 'SERVER_CONNEXION':
                var connexion_combo = Toc.content.ContentManager.getServersConnexionsCombo();
                var label_server = new Ext.form.Hidden({allowBlank:false,name:'PARAM_LABEL_SERVER'});
                var label_connexion = new Ext.form.Hidden({allowBlank:false,name:'PARAM_SERVER_CONNEXION'});
                this.pnlData.add(connexion_combo);
                this.pnlData.add(label_connexion);
                this.pnlData.add(label_server);
                connexion_combo.on('select',function(combo,record,index){
                    label_server.setValue(record.data.label_server);
                    label_connexion.setValue(record.data.server_connexion);
                    that.setParams(record.data.server_connexion);
                });

                connexion_combo.getStore().on('beforeload',function(store,options){
                    if(panel)
                    {
                       panel.getEl().mask('Chargement des serveurs ....');
                    }
                });
                connexion_combo.getStore().on('load',function(store,records,options){
                    if(panel)
                    {
                       panel.getEl().unmask();
                    }
                });
              break;

              case 'SERVERS_CONNEXION':
                var lbl_connexion = new Ext.form.Hidden({allowBlank:false,name:'PARAM_SERVERS_CONNEXION'});
                var servers_grid = new Toc.serversGrid({owner: null, mainPanel: null,height : 200,label : lbl_connexion,border : true});

                this.pnlData.add(servers_grid);
                this.pnlData.add(lbl_connexion);
              break;

              case 'DATABASES_CONNEXION':
                var lbl_connexion = new Ext.form.Hidden({allowBlank:false,name:'PARAM_DATABASES_CONNEXION'});
                var databases_grid = new Toc.DatabasesGrid({owner: null, mainPanel: null,height : 200,label : lbl_connexion,border : true});

                this.pnlData.add(databases_grid);
                this.pnlData.add(lbl_connexion);
              break;

              case 'START_SNAP':
                 var browser = new Toc.SnapshotBrowser({parent : this,capture : 'snap_id'});
                 this.pnlData.add(browser);
              break;

              case 'START_SNAP_TIME':
                 var browser = new Toc.SnapshotBrowser({parent : this,capture : 'time'});
                 this.pnlData.add(browser);
              break;

              default:
                if(this.builtin.indexOf(param.id) == -1)
                {
                   switch(param.type)
                   {
                     case 'singleValueText':
                       var lbl = new Ext.form.TextField({width : 405,fieldLabel:param.label,allowBlank:false,name:'PARAM_' + param.id});
                       this.pnlData.add(lbl);
                     break;
                     case 'bool':
                       var lbl = new Ext.form.Checkbox({width : 405,fieldLabel:param.label,name:'PARAM_' + param.id});
                       this.pnlData.add(lbl);
                     break;
                     case 'singleValueNumber':
                       var lbl = new Ext.form.NumberField({width : 405,fieldLabel:param.label,allowBlank:false,name:'PARAM_' + param.id});
                       this.pnlData.add(lbl);
                     break;
                     case 'singleValueDate':
                       var lbl = new Ext.form.DateField({width : 405,fieldLabel:param.label,allowBlank:false,name:'PARAM_' + param.id});
                       this.pnlData.add(lbl);
                     break;
                   }
                }
              break;
          }

          i++;
       }

       this.pnlData.doLayout();
       return params.length > 0;
  }
});