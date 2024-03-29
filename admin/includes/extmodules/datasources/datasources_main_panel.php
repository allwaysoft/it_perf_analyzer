<?php
?>
Toc.datasources.mainPanel = function(config) {
    config = config || {};
    config.border = false;

    Toc.datasources.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.datasources.mainPanel, Ext.Panel, {
   start : function(windows){
    windows.getEl().mask('Metadata ...');
     Ext.Ajax.request({
     method: 'GET',
     url: '<?php echo METABASE_URL; ?>' + '/api/user/current',
     headers: {
        Accept: 'application/json',
        'Content-Type' : 'application/json'
     },
     callback: function (options, success, response) {
        windows.getEl().unmask();
        var result = Ext.decode(response.responseText);

            if(result.id > 0)
            {
                var cmp = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none'},height: 600,id: 'datasources_iframe',width: 600});

                this.add(cmp);
                this.doLayout(true, true);
                windows.doLayout(true, true);
                cmp.el.dom.src = '<?php echo METABASE_URL . '/admin/databases'; ?>' + '?username=' + result.email + '&password=' + '<?php echo METABASE_DEV_PASS; ?>';

                cmp.el.dom.onload = function() {
                    console.log('iframe onload ...')
                    windows.getEl().unmask();
                };

                windows.getEl().mask('<?php echo $osC_Language->get('loading'); ?>');
            }
            else
            {
               if(windows && windows.close)
               {
                 windows.close();
               }

               Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            }
        },
          scope: this
        });
   }
});