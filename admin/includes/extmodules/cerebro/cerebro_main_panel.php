<?php
?>
Toc.cerebro.mainPanel = function(config) {
    config = config || {};
    config.border = false;

    config.IframePanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: 'http://10.100.18.19:9000'},height: 600,id: 'cerebro_iframe',width: 600});

    config.items = [config.IframePanel];

    Toc.cerebro.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.cerebro.mainPanel, Ext.Panel, {
});