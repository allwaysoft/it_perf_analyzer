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

Toc.reports.ScheduleMinutesPanel = function(config) {
  config = config || {};    
  
  //config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.reports.ScheduleMinutesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports.ScheduleMinutesPanel, Ext.Panel, {
  getDataPanel: function() {

    var lov_minutes = new Ext.ux.form.LovCombo({
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
,[24, '24']
,[25, '25']
,[26, '26']
,[27, '27']
,[28, '28']
,[29, '29']
,[30, '30']
,[31, '31']
,[32, '32']
,[33, '33']
,[34, '34']
,[35, '35']
,[36, '36']
,[37, '37']
,[38, '38']
,[39, '39']
,[40, '40']
,[41, '41']
,[42, '42']
,[43, '43']
,[44, '44']
,[45, '45']
,[46, '46']
,[47, '47']
,[48, '48']
,[49, '49']
,[50, '50']
,[51, '51']
,[52, '52']
,[53, '53']
,[54, '54']
,[55, '55']
,[56, '56']
,[57, '57']
,[58, '58']
,[59, '59']
		],
		triggerAction:'all'
		,mode:'local',
        hideLabel : true,
        name: 'minutes'
	});

    var rd_every = new Ext.form.Radio({hideLabel : true,boxLabel: 'Chaque minute', name: 'minute', inputValue: 'every',style: {'overflow': 'hidden'}, checked: true});
    var rd_specif = new Ext.form.Radio({hideLabel : true,boxLabel: 'Choisir', name: 'minute', inputValue: 'specif',style: {'overflow': 'hidden'}});

    rd_specif.on('check',function(chkBox,checked){
       checked ? lov_minutes.enable() : lov_minutes.disable();
    });

    rd_every.on('check',function(chkBox,checked){
       checked ? lov_minutes.disable() : lov_minutes.enable();
    });

    var pnlData = {
            xtype: 'fieldset',
            autoHeight: true,
            baseCls : 'x-fieldset1',
            border : 'false',
            hideLabel : true,
            hideBorders : true,
            layout: 'form',
            items : [rd_every,rd_specif,lov_minutes]
        };

        return pnlData;
    }
});