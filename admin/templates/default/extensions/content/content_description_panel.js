Toc.content.DescriptionPanel = function(config) {
    config = config || {};

    config.title = 'Contenu';
    config.activeTab = 0;
    config.deferredRender = false;
    config.items = this.buildForm(config.USE_WYSIWYG_TINYMCE_EDITOR,config.defaultLanguageCode);

    Toc.content.DescriptionPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.content.DescriptionPanel, Ext.TabPanel, {

    buildForm: function(USE_WYSIWYG_TINYMCE_EDITOR,defaultLanguageCode) {
        var items = [];
        var languages = Toc.Languages;
        var i = 0;
        while (i < languages.length) {
            var language = languages[i];
            if (USE_WYSIWYG_TINYMCE_EDITOR == 1) {
                this.editor = Toc.content.ContentManager.getTinyEditor(language['id']);
            }
            else {
                this.editor = {
                    xtype: 'htmleditor',
                    fieldLabel: 'Description',
                    name: 'content_description[' + language['id'] + ']',
                    height: 230
                };
            }

            var pnlLang = new Ext.Panel({
                    labelWidth: 100,
                    title : language['name'],
                    iconCls : 'icon-' + language['country_iso'] + '-win',
                    layout : 'form',
                    labelSeparator : '',
                    style : 'padding: 6px',
                    defaults: {
                        anchor : '97%'
                    },
                    items: [
                        {
                            xtype : 'textfield',
                            fieldLabel : 'Nom',
                            name : 'content_name[' + language['id'] + ']',
                            allowBlank : false
                        },
                        this.editor
                    ]
                }
            );

            items.push(pnlLang);
            i++;
        }

        return items;
    }
});