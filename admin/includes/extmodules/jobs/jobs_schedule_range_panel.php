<?php
/*
  $Id: jobs_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.jobs.ScheduleRangePanel = function(config) {
  config = config || {};    

  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.jobs.ScheduleRangePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.jobs.ScheduleRangePanel, Ext.Panel, {
  getDataPanel: function() {
                    
    var start_date = new Ext.form.DateField({fieldLabel : 'Date debut',name : 'start_date',
       labelStyle : 'width: 68px;',format : "Y-m-d",
       listeners: {
          blur: function(field){
            console.log('blur fired');
            var d = new Date();
            var time = d.getMinutes() > 45 ? (d.getHours() + 1) + ':' + d.getMinutes() : d.getHours() + ':' + (d.getMinutes() + 15);
            console.log('start_time .... ' + time);
            start_time.setValue(time);
            end_date.setValue('2035-01-01');
            end_time.setValue(time);
        }
       }
    });

    var start_time = new Ext.form.TimeField({
      minValue: '00:00',
      maxValue: '23:55',
      increment: 5,
      width : 80,
      name : 'start_time',
      format : 'H:i'
    });

    var end_date = new Ext.form.DateField({fieldLabel : 'Date fin',name : 'end_date',labelStyle : 'width: 68px;',format : "Y-m-d"});
    var end_time = new Ext.form.TimeField({
      minValue: '00:00',
      maxValue: '23:55',
      increment: 5,
      width : 80,
      name : 'end_time',
      format : 'H:i'
    });

    var pnlData = {
            xtype: 'fieldset',
            autoHeight: true,
            baseCls : 'x-fieldset1',
            border : 'false',
width : 285,
            hideLabel : true,
            hideBorders : true,
            style : {
              'padding-left' : '7px',
              'padding-top' : '3px'
            },
            layout: 'form',
            items : [new Ext.form.Label({text : 'Debut'}),
              {
                 layout : 'column',
                 items : [start_date,start_time]
              },
              new Ext.form.Label({text : 'Fin',style : {
                  'padding-bottom' : '2px',
                  'padding-top' : '2px'
                }}),
              {
                 layout : 'column',
                 items : [end_date,end_time]
              }
            ]
        };

     return pnlData;
  }
});