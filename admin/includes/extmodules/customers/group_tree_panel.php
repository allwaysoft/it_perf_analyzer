<?php
/*
  $Id: sms_tree_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.GroupTreePanel = function(config) {
  config = config || {};
  
  config.region = 'west';
  config.border = false;
  config.autoScroll = true;
  config.containerScroll = true;
  config.split = true;
  config.width = '30%';
  config.enableDD = true;
  config.rootVisible = false;
  
  config.root = new Ext.tree.AsyncTreeNode({
    text: '',
    draggable: false,
    id: 'root',
    expanded: true
  });
  config.currentCategoryId = '0';
    
  config.loader = new Ext.tree.TreeLoader({
    dataUrl: Toc.CONF.CONN_URL,
    preloadChildren: true, 
    baseParams: {
      module: 'customers',
      action: 'load_group_tree',
      start:0,
      limit:10000
    },
    listeners: {
      load: function() {
        this.expandAll();
        if(this.root.hasChildNodes())
        {
           this.setCategoryId(this.currentCategoryId != '0' ? this.currentCategoryId : this.root.firstChild.id);
        }
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
  
  Toc.customers.GroupTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.GroupTreePanel, Ext.tree.TreePanel, {
  
  setCategoryId: function(categoryId) {
    var currentNode = this.getNodeById(categoryId);
    if(currentNode != undefined)
    {
           currentNode.select();
    }    
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
        module: 'group',
        action: 'move_group',
        group_ids: e.dropNode.id,
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
  
  getGroupPath: function(node) {
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
            var dlg = this.owner.createGroupDialog();
            
            dlg.on('saveSuccess', function(feedback, groupId, text) {
              node.appendChild({
                id: groupId, 
                text: text, 
                cls: 'x-tree-node-collapsed', 
                parent_id: node.id, 
                leaf: true
              });
              
              node.expand();
            }, this);         
            
            dlg.show(null, this.getGroupPath(node));
          },
          scope: this          
        },
        {
          text: TocLanguage.tipEdit,
          iconCls: 'edit',
          handler: function() {
            var dlg = this.owner.createGroupDialog();
            
            dlg.on('saveSuccess', function(feedback, groupId, text) {
              node.setText(text);
            }, this);
            
            dlg.show(node.id, this.getGroupPath(node));
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
                      module: 'group',
                      action: 'delete_category',
                      group_id: node.id
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
    
  refresh: function() {
    this.root.reload();
  }
});