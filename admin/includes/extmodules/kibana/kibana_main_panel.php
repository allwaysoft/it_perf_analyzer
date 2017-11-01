<?php
?>
Toc.kibana.mainPanel = function(config) {
    config = config || {};
    config.border = false;

    config.kibanaPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: 'http://10.100.18.19:5601/app/kibana'},height: 600,id: 'kibana_iframe',width: 600});

    config.items = [config.kibanaPanel];

    Toc.kibana.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.kibana.mainPanel, Ext.Panel, {
});