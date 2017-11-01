<?php
?>
Toc.dashboards.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlTree = new Toc.dashboards.DashboardsTreePanel({owner: config.owner, parent: this,can_edit : false,autoRefresh : true});
  config.PnlIFrame = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: ''},height: 600,id: 'dashboards_iframe',width: 600});

  config.pnlTree.on('selectchange', this.onPnlTreeNodeSelectChange, this);
  
  config.items = [config.pnlTree,config.PnlIFrame];

  Toc.dashboards.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.dashboards.mainPanel, Ext.Panel, {

onPnlTreeNodeSelectChange: function(node) {
      this.PnlIFrame = null;

      if(node.attributes)
      {
         //Toc.exploreAsset(node,this.explorerPanel);
      }
  },
  
  getTree: function() {
    return this.pnlTree;
  }
});
