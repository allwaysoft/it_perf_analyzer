<?php ?>
Toc.queries.mainPanel = function(config) {
    config = config || {};
    config.border = false;

    config.tbar = [
        {
            text: 'Requetes',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '-',
        {
            text: TocLanguage.btnAdd,
            iconCls: 'add',
            handler: this.onAdd,
            scope: this
        }
    ];

    Toc.queries.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.queries.mainPanel, Ext.Panel, {
   start : function(windows){
    windows.getEl().mask('Metadata ...');
    var that = this;
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
            that.username = result.email;
            if (this.items) {
              this.removeAll(true);
            }

            var cmp = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none'},height: 600,id: 'queries_iframe',width: 600});
            this.add(cmp);
            this.doLayout(true, true);
            windows.doLayout(true, true);
            cmp.el.dom.src = '<?php echo METABASE_URL . '/questions'; ?>' + '?username=' + result.email + '&password=' + '<?php echo METABASE_DEV_PASS; ?>';

            cmp.el.dom.onload = function() {
                //console.log('iframe onload ...')
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
   },
   onAdd : function(windows){
       if(this.username)
        {
            if (this.items) {
                this.removeAll(true);
            }

            var cmp = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none'},height: 600,id: 'queries_iframe',width: 600});
            this.add(cmp);
            this.doLayout(true, true);
            cmp.el.dom.src = '<?php echo METABASE_URL . '/questions/new'; ?>' + '?username=' + this.username + '&password=' + '<?php echo METABASE_DEV_PASS; ?>';

            cmp.el.dom.onload = function() {
                //console.log('iframe onload ...')
                this.getEl().unmask();
            };

            this.getEl().mask('<?php echo $osC_Language->get('loading'); ?>');
        }
        else
        {
            if (that.items) {
                that.removeAll(true);
            }
            Ext.MessageBox.alert(TocLanguage.msgErrTitle,"Session expirée ...");
        }
   },
    onRefresh : function(windows){
        if(this.username)
        {
            if (this.items) {
                this.removeAll(true);
            }

            var cmp = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none'},height: 600,id: 'queries_iframe',width: 600});
            this.add(cmp);
            this.doLayout(true, true);
            cmp.el.dom.src = '<?php echo METABASE_URL . '/questions'; ?>' + '?username=' + this.username + '&password=' + '<?php echo METABASE_DEV_PASS; ?>';

            cmp.el.dom.onload = function() {
                //console.log('iframe onload ...')
                this.getEl().unmask();
            };

            this.getEl().mask('<?php echo $osC_Language->get('loading'); ?>');
        }
        else
        {
            if (that.items) {
                that.removeAll(true);
            }
            Ext.MessageBox.alert(TocLanguage.msgErrTitle,"Session expirée ...");
        }
    }
});