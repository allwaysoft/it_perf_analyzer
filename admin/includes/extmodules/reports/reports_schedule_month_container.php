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

Toc.reports.ScheduleMonthPanel = function(config) {
  config = config || {};    
  
  //config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.reports.ScheduleMonthPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports.ScheduleMonthPanel, Ext.Panel, {
  getDataPanel: function() {

    var lov_month = new Ext.ux.form.LovCombo({
		 width:275
        ,disabled : true
		,hideOnSelect:false
		,maxHeight:200
		,store:[
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
		],
		triggerAction:'all'
		,mode:'local',
        hideLabel : true,
        name: 'months'
	});

    var rd_every = new Ext.form.Radio({hideLabel : true,boxLabel: 'Chaque mois', name: 'month', inputValue: 'every',style: {'overflow': 'hidden'}, checked: true});
    var rd_specif = new Ext.form.Radio({hideLabel : true,boxLabel: 'Choisir', name: 'month', inputValue: 'specif',style: {'overflow': 'hidden'}});

    rd_specif.on('check',function(chkBox,checked){
       checked ? lov_month.enable() : lov_month.disable();
    });

    rd_every.on('check',function(chkBox,checked){
       checked ? lov_month.disable() : lov_month.enable();
    });

    var pnlData = {
            xtype: 'fieldset',
            autoHeight: true,
            baseCls : 'x-fieldset1',
            border : 'false',
            hideLabel : true,
            hideBorders : true,
            layout: 'form',
            items : [rd_every,rd_specif,lov_month]
        };

        return pnlData;
    }
});