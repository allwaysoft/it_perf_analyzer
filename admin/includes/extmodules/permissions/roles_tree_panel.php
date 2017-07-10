<?php
/*
  $Id: roles_tree_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.permissions.RolesTreePanel = function(config) {
  config = config || {};
  
  config.region = 'west';
  config.border = false;
  config.autoScroll = true;
  config.containerScroll = true;
  config.split = true;
  config.width = 170;
  config.enableDD = true;
  config.rootVisible = true;
  
  config.root = new Ext.tree.AsyncTreeNode({
    text: 'Roles',
    icon : 'templates/default/images/icons/16x16/administrators_log.png',
    draggable: false,
    id: '0',
    expanded: true
  });
  config.currentCategoryId = '0';
    
  config.loader = new Ext.tree.TreeLoader({
    dataUrl: Toc.CONF.CONN_URL,
    preloadChildren: true, 
    baseParams: {
      module: 'roles',
      action: 'load_roles_tree'
    },
    listeners: {
      load: function() {
        this.expandAll();
        this.setCategoryId(-1);
      },
      scope: this
    }
  });
  
  config.tbar = [{
    text: TocLanguage.btnRefresh,
    iconCls: 'refresh',
    handler: this.refresh,
    scope: this
  }];
  
  config.listeners = {
    "click": this.onCategoryNodeClick, 
    "nodedragover": this.onCategoryNodeDragOver,
    "nodedrop": this.onCategoryNodeDrop,
    "contextmenu": this.onCategoryNodeRightClick
  };
  
  this.addEvents({'selectchange' : true});
  
  Toc.permissions.RolesTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.permissions.RolesTreePanel, Ext.tree.TreePanel, {
  
  setCategoryId: function(categoryId) {
    var currentNode = this.getNodeById(categoryId);
    currentNode.select();
    this.currentCategoryId = categoryId;
    
    this.fireEvent('selectchange', categoryId);
  },
  
  onCategoryNodeClick: function (node) {
    node.expand();
    this.setCategoryId(node.id);
  },
  
  onCategoryNodeDragOver: function (e) {
    if (e.target.leaf == true) {
	    e.target.leaf = false;
	  }
	  
	  return true;
  },
  
  onCategoryNodeDrop: function(e) {
    if (e.point == 'append') {
      parent_id = e.target.id;
      currentCategoryId = e.target.id;    
    } else {
      parent_id = e.target.parentNode.id;
      currentCategoryId = e.target.parentNode.id;
    }
    
    Ext.Ajax.request ({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'roles',
        action: 'move_roles',
        roles_ids: e.dropNode.id,
        parent_category_id: parent_id
      },
      callback: function(options, success, response){
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.setCategoryId(currentCategoryId);
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  getRolesPath: function(node) {
    var cpath = [];
    node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;
    
    while (node.id > 0) {
      cpath.push(node.id);
      node = node.parentNode;
    }
    
    return cpath.reverse().join('_');
  },
  
  onCategoryNodeRightClick: function(node, event) {
    event.preventDefault();
    node.select();
    
    this.menuContext = new Ext.menu.Menu({
      items: [
        {
          text: TocLanguage.btnAdd,
          iconCls: 'add',
          handler: function() {
            var dlg = this.owner.createRolesDialog();
            
            dlg.on('saveSuccess', function(feedback, rolesId, text) {
              node.appendChild({
                id: rolesId, 
                text: text, 
                cls: 'x-tree-node-collapsed', 
                parent_id: node.id, 
                leaf: true
              });
              
              node.expand();
            }, this);         
            
            dlg.show(null, this.getRolesPath(node));
          },
          scope: this          
        },
        {
          text: TocLanguage.tipEdit,
          iconCls: 'edit',
          handler: function() {
            var dlg = this.owner.createRolesDialog();
            
            dlg.on('saveSuccess', function(feedback, rolesId, text) {
              node.setText(text);
            }, this);
            
            dlg.show(node.id, this.getRolesPath(node));
          },
          scope: this
        },
        {
          text: TocLanguage.tipDelete,
          iconCls: 'remove',
          handler:  function() {
            Ext.MessageBox.confirm(
              TocLanguage.msgWarningTitle, 
              TocLanguage.msgDeleteConfirm, 
              function (btn) {
                if (btn == 'yes') {
                  currentCategoryId = node.parentNode.id;
                  
                  Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                      module: 'roles',
                      action: 'delete_category',
                      roles_id: node.id
                    },
                    callback: function (options, success, response) {
                      var result = Ext.decode(response.responseText);
                      
                      if (result.success == true) {
                        var pNode = node.parentNode;
                        pNode.ui.addClass('x-tree-node-collapsed');
                        
                        node.remove();
                        this.setCategoryId(currentCategoryId);
                      } else {
                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                      }
                    },
                    scope: this
                  });
                }
              }, 
              this
            );
          },
          scope: this
        }
      ]
    });
    
    this.menuContext.showAt(event.getXY());;
  },

  getCategoriesPath: function(node) {
    var cpath = [];
    node = (node == null) ? this.getNodeById(this.currentCategoryId) : node;

    while (node.id > 0) {
      cpath.push(node.id);
      node = node.parentNode;
    }

    return cpath.reverse().join('_');
  },
    
  refresh: function() {
    this.root.reload();
  }
});