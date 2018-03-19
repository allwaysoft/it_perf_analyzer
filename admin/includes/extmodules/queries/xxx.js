Ext.Ajax.request({
    method : 'POST',
    url: '<?php echo METABASE_URL; ?>' + '/api/session',
    jsonData : {
        username : '<?php echo METABASE_DEV_USER; ?>',
        password: '<?php echo METABASE_DEV_PASS; ?>'
    },
    callback: function (options, success, response) {
        this.getEl().unmask();
        var result = Ext.decode(response.responseText);
    },
    scope: this
});