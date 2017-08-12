<?php
?>
Toc.databases.GroupsPanel = function(config) {
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

  Toc.databases.GroupsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.databases.GroupsPanel, Ext.Panel, {
  onCheckChange: function(node, checked) {
    if(checked)
    {
        this.checkedNodes.push(node.attributes.group_id);
    }
    else
    {
        var newNodes = [];
        var i = 0;
        while(i < this.checkedNodes.length)
        {
           if(this.checkedNodes[i] != node.attributes.group_id)
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
      name: 'group_id',
      id: 'group_id',
      bubbleCheck: 'none',
      cascadeCheck: 'none',
      autoScroll: true,
      border: false,
      bodyStyle: 'background-color:white;',
      rootVisible: false,
      anchor: '-24 -60',
      root: {
        nodeType: 'async',
        text: 'Groupes',
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
          module: 'databases',
          action: 'load_groups_tree'
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
    this.getEl().mask('Chargement des Groupes, veuillez patienter....');
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