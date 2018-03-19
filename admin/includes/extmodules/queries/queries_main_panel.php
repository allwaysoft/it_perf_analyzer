<?php ?>
Toc.queries.mainPanel = function(config) {
    config = config || {};
    config.border = false;

    //config.QueriesPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo REDASH_URL . '/queries/my'; ?>'},height: 600,width: 600});

    //config.items = [config.QueriesPanel];

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
    var that = this;
    this.getEl().mask('Chargement Session Metadata  ...');
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
            this.getEl().mask('Chargement liste de requetes ...');
            if (this.items) {
              this.removeAll(true);
            }

           //var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo METABASE_URL . '/auth/login?redirect=/questions'; ?>' + '&username=' + that.username + '@gmail.com' + '&password=' + '<?php echo METABASE_DEV_PASS; ?>'},height: 600,width: 600});
           var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo METABASE_URL . '/questions'; ?>' + '?username=' + that.username + '@gmail.com' + '&password=' + '<?php echo METABASE_DEV_PASS; ?>'},height: 600,width: 600});

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
   },
   onAdd : function(windows){
            if(this.username)
            {
              this.getEl().mask('Chargement du Gestionnaire de requetes ...');
              if (this.items) {
              this.removeAll(true);
            }

              //var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo METABASE_URL . '/question/new'; ?>'},height: 600,width: 600});
              var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo METABASE_URL . '/question/new'; ?>' + '?username=' + this.username + '@gmail.com' + '&password=' + '<?php echo METABASE_DEV_PASS; ?>'},height: 600,width: 600});

              this.add(DsPanel);
              this.doLayout();
              this.getEl().unmask();
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
this.getEl().mask('Chargement du Gestionnaire de requetes ...');
if (this.items) {
this.removeAll(true);
}

var DsPanel = new Ext.Component({autoEl:{tag: 'iframe',style: 'height: 100%; width: 100%; border: none',src: '<?php echo METABASE_URL . '/questions'; ?>' + '?username=' + this.username + '@gmail.com' + '&password=' + '<?php echo METABASE_DEV_PASS; ?>'},height: 600,width: 600});

this.add(DsPanel);
this.doLayout();
this.getEl().unmask();
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