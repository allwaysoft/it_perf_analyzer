<?php
/*
  $Id: servers_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.reports.SnapshotBrowser = function(config) {
    config = config || {};

    config.deferredRender = false;
    config.header = false;
    config.border = false;
    config.items = this.getDataPanel(config.capture);

    Toc.reports.SnapshotBrowser.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports.SnapshotBrowser, Ext.Panel, {
    getDataPanel: function(capture) {
        var that = this;
        var showDialog = function(start_or_end,start_field,end_field,start_id){
          var params = that.parent.getParams();

          if(params)
          {
             params.start_id = start_id;
             params.capture = capture;
             var dlg = new Toc.reports.snaphotsDialog();
             dlg.show(start_or_end,start_field,end_field,params);
          }
          else
          {
             Ext.Msg.alert(TocLanguage.msgErrTitle, 'Vous devez selectionner une base !!!');
          }
        };

        switch(capture)
        {
           case 'snap_id':
             var txtStartId =  new Ext.form.NumberField({fieldLabel: 'Start Snap',allowBlank:false,name : 'PARAM_START_SNAP',width : 405});
             var txtEndId =  new Ext.form.NumberField({fieldLabel: 'End Snap',allowBlank:false,name : 'PARAM_END_SNAP',width : 405});
           break;

           case 'time':
             var txtStartId =  new Ext.form.TextField({fieldLabel: 'Start Snap',allowBlank:false,name : 'PARAM_START_SNAP_TIME',width : 405});
             var txtEndId =  new Ext.form.TextField({fieldLabel: 'End Snap',allowBlank:false,name : 'PARAM_END_SNAP_TIME',width : 405});
           break;

           default:
             Ext.Msg.alert(TocLanguage.msgErrTitle, 'Vous devez selectionner une capture de base !!!');
             return null;
           break;
        }

        var btnStartId = new Ext.Button(
        {
           text : '...',
           handler: function(){
             showDialog('start',txtStartId,txtEndId);
           },
           scope:this
        });

        var btnEndId = new Ext.Button(
        {
           text : '...',
           handler: function(){
             var start_id = txtStartId.snap_id;
             if(start_id)
             {
                 showDialog('end',txtStartId,txtEndId,start_id);
             }
             else
             {
                 Ext.Msg.alert(TocLanguage.msgErrTitle, 'Vous devez selectionner une capture de base !!!');
             }
           },
           scope:this
        });

        var pnlData = {
            xtype: 'fieldset',
            autoHeight: true,
            baseCls : 'x-fieldset1',
            border : 'false',
            hideLabel : true,
            hideBorders : true,
            layout: 'form',
            items : [
               {
                  layout : 'column',
                  items: [{
                     //columnWidth: .90,
                     layout: 'form',
                     style: {
                        'border-color': 'white'
                     },
                     border : false,
                     items: [txtStartId]
                    },
                    {
                     //columnWidth: .1,
                     layout: 'form',
                     border : false,
                     items: [btnStartId]
                    }]
               },
               {
                  layout : 'column',
                  items: [{
                     //columnWidth: .90,
                     layout: 'form',
                     border : false,
                     items: [txtEndId]
                    },
                    {
                     //columnWidth: .1,
                     layout: 'form',
                     border : false,
                     items: [btnEndId]
                    }]
               }]
        };

        return pnlData;
    }
});