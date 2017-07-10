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

Toc.jobs.ScheduleHoursPanel = function(config) {
  config = config || {};    
  
  //config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.jobs.ScheduleHoursPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.jobs.ScheduleHoursPanel, Ext.Panel, {
  getDataPanel: function() {

    var lov_hours = new Ext.ux.form.LovCombo({
		 width:275
        ,disabled : true
		,hideOnSelect:false
		,maxHeight:200
		,store:[[0, '0'],
			 [1, '1']
			,[2, '2']
			,[3, '3']
			,[4, '4']
			,[5, '5']
            ,[6, '6']
            ,[7, '7']
            ,[8, '8']
            ,[9, '9']
            ,[10, '10']
             ,[11, '11']
,[12, '12']
,[13, '13']
,[14, '14']
,[15, '15']
,[16, '16']
,[17, '17']
,[18, '18']
,[19, '19']
,[20, '20']
,[21, '21']
,[22, '22']
,[23, '23']
		],
		triggerAction:'all'
		,mode:'local',
        hideLabel : true,
        name: 'hours'
	});

    var rd_every = new Ext.form.Radio({hideLabel : true,boxLabel: 'Chaque heure', name: 'hour', inputValue: 'every',style: {'overflow': 'hidden'}, checked: true});
    var rd_specif = new Ext.form.Radio({hideLabel : true,boxLabel: 'Choisir', name: 'hour', inputValue: 'specif',style: {'overflow': 'hidden'}});

    rd_specif.on('check',function(chkBox,checked){
       checked ? lov_hours.enable() : lov_hours.disable();
    });

    rd_every.on('check',function(chkBox,checked){
       checked ? lov_hours.disable() : lov_hours.enable();
    });

    var pnlData = {
            xtype: 'fieldset',
            autoHeight: true,
            baseCls : 'x-fieldset1',
            border : 'false',
            hideLabel : true,
            hideBorders : true,
            layout: 'form',
            items : [rd_every,rd_specif,lov_hours]
        };

        return pnlData;
    }
});