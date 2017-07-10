<?php
/*
  $Id: categories_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.users.RolesPanel = function(config) {
  config = config || {};
  
  config.title = 'Groupes';
  config.layout = 'border';
  config.style = 'padding: 5px';
  config.treeLoaded = false;
  config.items = this.buildForm();
  config.listeners = {
      activate : function(panel){
        if(!this.treeLoaded)
        {
            this.refresh();
        }
      },
      scope: this
  };

  this.pnlRolesTree.on('beforeload',function(node){
      if(!this.isVisible())
      {
          return false;
      }
  });
  
  Toc.users.RolesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.users.RolesPanel, Ext.Panel, {
  onCheckChange: function(node, checked) {
    if(checked)
    {
        this.checkedNodes.push(node.attributes.roles_id);
    }
    else
    {
        var newNodes = [];
        var i = 0;
        while(i < this.checkedNodes.length)
        {
           if(this.checkedNodes[i] != node.attributes.roles_id)
           {
               newNodes.push(this.checkedNodes[i]);
           }

           i++;
        }
        this.checkedNodes = newNodes;
    }

    if (node.hasChildNodes) {
      node.expand();
      node.eachChild(function(child) {
        child.ui.toggleCheck(checked);
      });
    }
  },
  buildForm: function() {
    var that = this;
    this.checkedNodes = [];
    this.pnlRolesTree = new Ext.ux.tree.CheckTreePanel({
      region: 'center',
      name: 'roles_id',
      id: 'roles_id',
      bubbleCheck: 'none',
      cascadeCheck: 'none',
      autoScroll: true,
      border: false,
      bodyStyle: 'background-color:white;',
      rootVisible: false,
      anchor: '-24 -60',
      root: {
        nodeType: 'async',
        text: 'Roles',
        id: 'root',
        expanded: true,
        uiProvider: false
      },
      listeners: {
          load: function() {
            this.treeLoaded = true;
          },
          checkchange: this.onCheckChange,
          scope: this
      },
      loader: new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: false,
        baseParams: {
          module: 'roles',
          action: 'load_roles_tree'
        },
        listeners: {
          load: function() {
            this.treeLoaded = true;
            var elem = that.getEl();
            elem.unmask();
          },
          scope: this
        }
      })
    });  
    
    return this.pnlRolesTree;    
  },
  refresh : function(){
    this.getEl().mask('Chargement des roles, veuillez patienter....');
    this.pnlRolesTree.root.reload();
  },
  setRoles: function(categoryId) {
    if (this.treeLoaded == true) {
      this.pnlRolesTree.setValue(categoryId);
    } else {
      this.pnlRolesTree.loader.on('load', function(){
        this.pnlRolesTree.setValue(categoryId);
      }, this);
    }    
  },
  
  getRoles: function() {
        var roles = '';
        var i = 0;
        while(i < this.checkedNodes.length)
        {
           roles = roles + this.checkedNodes[i];
           if(i < this.checkedNodes.length -1)
           {
               roles = roles  + ',';
           }
           i++;
        }

        return roles;
  }
});