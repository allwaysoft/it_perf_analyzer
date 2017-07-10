Toc.content.CategoriesPanel = function(config) {
  config = config || {};
  
  config.title = 'Pages';
  config.layout = 'border';

  config.listeners = {
      activate : function(panel){
        if(!this.treeLoaded)
        {
            this.refresh();
        }
      },
      scope: this
  };

  config.style = 'padding: 5px';
  config.treeLoaded = false;
  config.items = this.buildForm();
  this.pnlCategoriesTree.on('beforeload',function(node){
      if(!this.isVisible())
      {
          return false;
      }
  });
  
  Toc.content.CategoriesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.CategoriesPanel, Ext.Panel, {
  buildForm: function() {
    var that = this;
    this.pnlCategoriesTree = new Ext.ux.tree.CheckTreePanel({
      region: 'center',
      name: 'categories', 
      bubbleCheck: 'none',
      cascadeCheck: 'none',
      autoScroll: true,
      border: false,
      bodyStyle: 'background-color:white;',
      rootVisible: false,
      anchor: '-24 -60',
      root: {
        nodeType: 'async',
        text: 'root',
        id: 'root',
        expanded: true,
        uiProvider: false
      },
      loader: new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: false,
        baseParams: {
          module: 'products',
          action: 'get_categories_tree'
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
    
    return this.pnlCategoriesTree;    
  },

  refresh: function() {
    this.getEl().mask('Chargement des espaces, veuillez patienter....');
    this.pnlCategoriesTree.root.reload();
  },
  
  setCategories: function(categoryId) {
    this.categoryId = categoryId;
    if (this.treeLoaded == true) {
      this.pnlCategoriesTree.setValue(categoryId);
    } else {
      this.pnlCategoriesTree.loader.on('load', function(){
        this.pnlCategoriesTree.setValue(categoryId);
      }, this);
    }    
  },
  
  getCategories: function() {
      if(this.treeLoaded == true)
      {
          return this.pnlCategoriesTree.getValue();
      }

      return this.categoryId;
  }
});