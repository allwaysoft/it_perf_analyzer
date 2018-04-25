<?php

?>

Toc.bi.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel(config);
    
  Toc.bi.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.bi.DataPanel, Ext.Panel, {
  getDataPanel: function(config) {
    var tabLanguage = new Ext.TabPanel({
        region: 'center',
        defaults:{
        hideMode:'offsets'
        },
        activeTab: 0,
        deferredRender: false
    });

    <?php
            foreach ($osC_Language->getAll() as $l) {

            echo 'var pnl' . $l['id'] . ' = new Ext.Panel({
            title:\'' . $l['name'] . '\',
            iconCls: \'icon-' . $l['country_iso'] . '-win\',
            layout: \'form\',
            labelSeparator: \' \',
            border: true,
            width : \'100%\',
            autoHeight: true,
            defaults: {
                anchor: \'97%\'
            },
            items: [
            {xtype:\'textfield\', fieldLabel: \'' . $osC_Language->get('name') . '\', name: \'content_name[' . $l['id'] . ']\', id: \'content_name[' . $l['id'] . ']\'},
            {xtype:\'textarea\', fieldLabel: \'' . $osC_Language->get('description') . '\',name: \'content_description[' . $l['id'] . ']\',id: \'content_description[' . $l['id'] . ']\',maxLength : 500,height:200}
            ]
            });

            tabLanguage.add(pnl' . $l['id'] . ');
            ';
            }
        ?>

    this.pnlData = new Ext.Panel({
      layout: 'form',
          border: false,
          labelSeparator: ' ',
          autoHeight: true,
          defaults: {
            anchor: '97%'
          },
          items: [
            Toc.content.ContentManager.getContentStatusFields(),
            {xtype:'textfield', fieldLabel: '<?php echo $osC_Language->get('link'); ?>', name: 'reports_uri', id: 'reports_uri'},
            tabLanguage
          ]
    });

    return this.pnlData;
  }
});