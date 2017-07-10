<?php
/*
  $Id: contact_groupes_main_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.contact_groupes.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnlContact_groupesTree = new Toc.contact_groupes.Contact_groupesTreePanel({owner: config.owner, parent: this});
  config.grdContact_groupes = new Toc.contact_groupes.Contact_groupesGrid({owner: config.owner, mainPanel: this});
  
  config.pnlContact_groupesTree.on('selectchange', this.onPnlContact_groupesTreeNodeSelectChange, this);
  
  config.items = [config.pnlContact_groupesTree, config.grdContact_groupes];
  
  Toc.contact_groupes.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.contact_groupes.mainPanel, Ext.Panel, {
  
  onPnlContact_groupesTreeNodeSelectChange: function(contact_groupeId) {
    this.grdContact_groupes.refreshGrid(contact_groupeId);
  },
  
  getContact_groupesTree: function() {
    return this.pnlContact_groupesTree;
  }
});
