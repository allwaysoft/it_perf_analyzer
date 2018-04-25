<?php
?>
Toc.datasources.mainPanel = function(config) {
    config = config || {};
    config.border = false;

    //config.DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo REDASH_URL . '/data_sources'; ?>'},height: 600,id: 'redash_iframe',width: 600});

    //config.items = [config.DsPanel];

    Toc.datasources.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.datasources.mainPanel, Ext.Panel, {
   start : function(windows){
     console.log('activate datasources ...');
            this.getEl().mask('Chargement Metadata ...');
            Ext.Ajax.request({
                method : 'GET',
                url: Toc.CONF.CONN_URL,
                params: {
                    module : 'databases',
                    action: 'get_currentuser'
                },
                callback: function (options, success, response) {
                    this.getEl().unmask();
                    var result = Ext.decode(response.responseText);

                    if(result.success)
                    {
                        that.username = result.username;
                        this.getEl().mask('...');
                        if (this.items) {
                          this.removeAll(true);
                        }

                       //var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo REDASH_URL . '/login?next=' . REDASH_URL . '/data_sources&email='; ?>' + result.username + '&password=12345'},height: 600,width: 600});
                       var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo METABASE_URL . '/admin/databases'; ?>' + '?username=' + that.username + '@gmail.com' + '&password=' + '<?php echo METABASE_DEV_PASS; ?>'},height: 600,width: 600});

                       this.add(DsPanel);
                       this.doLayout();
                       this.getEl().unmask();
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