<?php
/*
  $Id: contact_groupes_tree_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.contact_groupes.Contact_groupesTreePanel = function(config) {
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
    text: 'Root',
    draggable: false,
    id: '0',
    expanded: true
  });
  config.currentContact_groupeId = '0';
    
  config.loader = new Ext.tree.TreeLoader({
    dataUrl: Toc.CONF.CONN_URL,
    preloadChildren: true, 
    baseParams: {
      module: 'contact_groupes',
      action: 'load_contact_groupes_tree'
    },
    listeners: {
      load: function() {
        this.expandAll();
        this.setContact_groupeId(0);
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
    "click": this.onContact_groupeNodeClick, 
    "nodedragover": this.onContact_groupeNodeDragOver,
    "nodedrop": this.onContact_groupeNodeDrop,
    "contextmenu": this.onContact_groupeNodeRightClick
  };
  
  this.addEvents({'selectchange' : true});
  
  Toc.contact_groupes.Contact_groupesTreePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.contact_groupes.Contact_groupesTreePanel, Ext.tree.TreePanel, {
  
  setContact_groupeId: function(categoryId) {
    var currentNode = this.getNodeById(categoryId);
    currentNode.select();
    this.currentContact_groupeId = categoryId;
    
    this.fireEvent('selectchange', categoryId);
  },
  
  onContact_groupeNodeClick: function (node) {
    node.expand();
    this.setContact_groupeId(node.id);
  },
  
  onContact_groupeNodeDragOver: function (e) {
    if (e.target.leaf == true) {
	    e.target.leaf = false;
	  }
	  
	  return true;
  },
  
  onContact_groupeNodeDrop: function(e) {
    if (e.point == 'append') {
      parent_id = e.target.id;
      currentContact_groupeId = e.target.id;    
    } else {
      parent_id = e.target.parentNode.id;
      currentContact_groupeId = e.target.parentNode.id;
    }
    
    Ext.Ajax.request ({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'contact_groupes',
        action: 'move_contact_groupes',
        contact_groupes_ids: e.dropNode.id,
        parent_category_id: parent_id
      },
      callback: function(options, success, response){
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.setContact_groupeId(currentContact_groupeId);
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  getContact_groupesPath: function(node) {
    var cpath = [];
    node = (node == null) ? this.getNodeById(this.currentContact_groupeId) : node;
    
    while (node.id > 0) {
      cpath.push(node.id);
      node = node.parentNode;
    }
    
    return cpath.reverse().join('_');
  },
  
  onContact_groupeNodeRightClick: function(node, event) {
    event.preventDefault();
    node.select();
    
    this.menuContext = new Ext.menu.Menu({
      items: [
        {
          text: TocLanguage.btnAdd,
          iconCls: 'add',
          handler: function() {
            var dlg = this.owner.createContact_groupesDialog();
            
            dlg.on('saveSuccess', function(feedback, contact_groupesId, text) {
              node.appendChild({
                id: contact_groupesId, 
                text: text, 
                cls: 'x-tree-node-collapsed', 
                parent_id: node.id, 
                leaf: true
              });
              
              node.expand();
            }, this);         
            
            dlg.show(null, this.getContact_groupesPath(node));
          },
          scope: this          
        },
        {
          text: TocLanguage.tipEdit,
          iconCls: 'edit',
          handler: function() {
            var dlg = this.owner.createContact_groupesDialog();
            
            dlg.on('saveSuccess', function(feedback, contact_groupesId, text) {
              node.setText(text);
            }, this);
            
            dlg.show(node.id, this.getContact_groupesPath(node));
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
                  currentContact_groupeId = node.parentNode.id;
                  
                  Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                      module: 'contact_groupes',
                      action: 'delete_category',
                      contact_groupes_id: node.id
                    },
                    callback: function (options, success, response) {
                      var result = Ext.decode(response.responseText);
                      
                      if (result.success == true) {
                        var pNode = node.parentNode;
                        pNode.ui.addClass('x-tree-node-collapsed');
                        
                        node.remove();
                        this.setContact_groupeId(currentContact_groupeId);
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