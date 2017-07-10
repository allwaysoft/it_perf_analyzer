<?php
/*
  $Id: categories_main_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.permissions.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  config.region = center;

  config.pnlRolesTree = new Toc.permissions.RolesTreePanel({owner: config.owner, parent: this});
  config.grdPermissions = new Toc.content.PermissionsPanel({owner : this.owner,content_id : '',content_type : 'roles',module : 'categories',action :  'list_role_permissions',id_field : 'categories_id',autoExpandColumn : 'categories_name'});
  
  config.pnlRolesTree.on('selectchange', this.onPnlCategoriesTreeNodeSelectChange, this);
  
  config.items = [config.pnlRolesTree, config.grdPermissions];
  
  Toc.permissions.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.permissions.mainPanel, Ext.Panel, {
  
  onPnlCategoriesTreeNodeSelectChange: function(categoryId) {
    this.grdPermissions.refreshGrid(categoryId);
  },

  refreshTree :function(){
     this.pnlRolesTree.refresh();
  },
  
  getCategoriesTree: function() {
    return this.pnlRolesTree.getCategoriesPath();
  },

  getCategoryPath: function(){
        return this.pnlRolesTree.getCategoriesPath();
  }
});
