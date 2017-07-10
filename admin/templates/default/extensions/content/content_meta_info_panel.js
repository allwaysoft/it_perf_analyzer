Toc.content.MetaInfoPanel = function(config) {
  config = config || {};
  
  config.title = 'Infos page';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm();
  
  Toc.content.MetaInfoPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.MetaInfoPanel, Ext.TabPanel, {
    buildForm: function() {
        var panels = [];
        var languages = Toc.Languages;
        var i = 0;
        while (i < languages.length) {
            var language = languages[i];
            var panel = new Ext.Panel({
                title : language.name,
                iconCls: 'icon-' + language.country_iso + '-win',
                layout : 'form',
                labelSeparator: '',
                style: 'padding: 6px',
                defaults: {
                    anchor: '98%'
                },
                items: [
                    {xtype: 'textfield', fieldLabel: 'Titre de la Page', name: 'page_title[' + language.id + ']'},
                    {xtype: 'textarea', fieldLabel: 'Mots cles', name: 'meta_keywords[' + language.id + ']'},
                    {xtype: 'textarea', fieldLabel: ' Description de la Page', name: 'meta_descriptions[' + language.id + ']'},
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Url',
                        labelStyle: 'background: url(../images/worldflags/"' + language['country_iso'] + '.png) no-repeat right center !important;',
                        name: 'content_url[' + language.id + ']'
                    }
                ]
            });

            panels.push(panel);

            i++;
        }

        return panels;
    }
});