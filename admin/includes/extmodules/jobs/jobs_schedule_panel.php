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

Toc.jobs.SchedulePanel = function(config) {
  config = config || {};    
  
  config.autoScroll = true;
  config.deferredRender = false;
  config.items = this.getDataPanel();
    
  Toc.jobs.SchedulePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.jobs.SchedulePanel, Ext.Panel, {
  getDataPanel: function() {

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
                 items : [new Toc.jobs.ScheduleRangePanel(),new Toc.jobs.ScheduleMinutesPanel()]
               },
               {
                 layout : 'column',
                 items : [new Toc.jobs.ScheduleHoursPanel(),new Toc.jobs.ScheduleMonthdayPanel()]
               },
               {
                 layout : 'column',
                 items : [new Toc.jobs.ScheduleMonthPanel(),new Toc.jobs.ScheduleWeekdayPanel()]
               }]
        };

        return pnlData;
    }
});