var that = this;

Toc.content.ContentManager = {
};

Toc.content.ContentManager.createDocumentsDialog = function () {
    var dlg = TocDesktop.desktop.getWindow('documents-dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.DocumentsDialog);

        dlg.on('saveSuccess', function (feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.renderProgress = function (percent) {
    if (percent == 0) {
        return "";
    }

    if (percent >= 90) {
        return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar red'></div></div>";
    }
    else {
        if (percent < 90 && percent >= 80) {
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar yellow'></div></div>";
        }
        else {
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar green'></div></div>";
        }
    }
};

Toc.content.ContentManager.renderPct = function (percent) {
    if (percent == 0) {
        return "";
    }

    if (percent >= 90) {
        return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar green'></div></div>";
    }
    else {
        if (percent < 90 && percent >= 70) {
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar yellow'></div></div>";
        }
        else {
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar red'></div></div>";
        }
    }
};

Toc.content.ContentManager.renderUsagePct = function (percent) {
    if (percent == 0) {
        return "";
    }

    if (percent >= 90) {
        return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar red'></div></div>";
    }
    else {
        if (percent < 90 && percent >= 70) {
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar yellow'></div></div>";
        }
        else {
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar green'></div></div>";
        }
    }
};

renderNewLine = function (row) {
    return '<div style = "white-space : normal">' + row + '</div>';
};

Toc.content.ContentManager.renderOsProgress = function (percent) {
    if(percent && percent > 0)
    {
        return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar green'></div></div>";
    }

    return '';
};

Toc.content.ContentManager.renderFsProgress = function (rest) {
    var myarr = rest.split(";");
    var percent = myarr[0];
    percent = percent.split("%")[0];
    var size = myarr[1];
    var dispo = myarr[2];

    //console.log(percent);

    if (dispo <= 500) {
        return "<div id='my-progressbar' style='width: 100%; height: 10px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar red'></div></div>";
    }

    if (percent >= 90 && dispo < 5000) {
        return "<div id='my-progressbar' style='width: 100%; height: 10px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar red'></div></div>";
    }
    else {
        if (percent < 90 && percent >= 80) {
            return "<div id='my-progressbar' style='width: 100%; height: 10px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar yellow'></div></div>";
        }
        else {
            return "<div id='my-progressbar' style='width: 100%; height: 10px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar green'></div></div>";
        }
    }
};

Toc.content.ContentManager.renderSimpleProgress = function (percent) {
    if(percent > 0)
    {
        return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + percent + "%;' class='item-bar blue'></div></div>";
    }

    return '';
};

Toc.content.ContentManager.renderEventProgress = function (percent) {
    var res = percent.split("#");

    if (res.length > 0) {
        var event = res[0];
        var pct = res[1];
        var cls = res[2];

        switch(cls.toLowerCase())
        {
            case "application":
            return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar application'></div></div>";

            case "concurrency":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar concurrency'></div></div>";

            case "configuration":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar configuration'></div></div>";

            case "network":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar network'></div></div>";

            case "other":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar other'></div></div>";

            case "system i/o":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar systemio'></div></div>";

            case "user i/o":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar userio'></div></div>";

            case "administrative":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar administrative'></div></div>";

            case "commit":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar commit'></div></div>";

            case "scheduler":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar scheduler'></div></div>";

            case "error":
                return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + pct + "%;' class='item-bar red'></div></div>";
        }
    }

    return "<div id='my-progressbar' style='width: 100%; height: 15px;' class='progressbar-control'><div style='opacity: 1; float: left; position: relative; width: " + 0 + "%;' class='item-bar red'></div></div>";
};

Toc.content.ContentManager.FormatNumber = function (num) {
    if (num) {
        return num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }
    return null;
};

Toc.content.ContentManager.createLogsDialog = function () {
    var dlg = TocDesktop.desktop.getWindow('logs-dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.LogsDialog);

        dlg.on('saveSuccess', function (feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.createImagesGalleryDialog = function () {
    var dlg = TocDesktop.desktop.getWindow('images_gallery-dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.ImagesGalleryDialog);

        dlg.on('saveSuccess', function (feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.lPad = function(n, p, c) {
    var pad_char = typeof c !== 'undefined' ? c : ' ';
    var pad = new Array(1 + p).join(pad_char);
    return (pad + n).slice(-pad.length);
};

Toc.content.ContentManager.createLinksDialog = function () {
    var dlg = TocDesktop.desktop.getWindow('links_links_dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.LinksDialog);

        dlg.on('saveSuccess', function (feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.createCommentDialog = function () {
    var dlg = TocDesktop.desktop.getWindow('comment_comment_dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.CommentDialog);

        dlg.on('saveSuccess', function (feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.getCategoriesCombo = function () {
    var dsParentCategories = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_parent_category'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            fields: [
                'id',
                'text'
            ]
        }),
        autoLoad: true
    });

    var cboParentCategories = new Toc.CategoriesComboBox({
        store: dsParentCategories,
        displayField: 'text',
        fieldLabel: 'Parent',
        valueField: 'id',
        name: 'parent_category_id',
        hiddenName: 'parent_category_id',
        triggerAction: 'all'
    });

    return cboParentCategories;
};

Toc.content.ContentManager.getCountriesCombo = function (caller) {
    var dsCountries = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'customers',
            action: 'get_countries'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['country_id', 'country_title']
        }),
        autoLoad: true
    });

    var cboCountries = new Ext.form.ComboBox({
        fieldLabel: 'Pays',
        store: dsCountries,
        displayField: 'country_title',
        valueField: 'country_id',
        name: 'country',
        hiddenName: 'country_id',
        mode: 'local',
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false,
        listeners: {
            select: caller.onCboCountriesSelect,
            scope: caller
        }
    });

    return cboCountries;
};

Toc.content.ContentManager.getZonesCombo = function () {
    var dsZone = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'customers',
            action: 'get_zones'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['zone_code', 'zone_name']
        }),
        autoLoad: true
    });

    var cboZones = new Ext.form.ComboBox({
        store: dsZone,
        fieldLabel: 'Province',
        disabled: true,
        displayField: 'zone_name',
        valueField: 'zone_code',
        hiddenName: 'z_code',
        triggerAction: 'all',
        editable: false
    });

    return cboZones;
};

Toc.content.ContentManager.getCustomersGroupsCombo = function () {
    var dsCustomersGroups = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'customers',
            action: 'get_customers_groups'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'text']
        }),
        autoLoad: true
    });

    var cboCustomersGroups = new Ext.form.ComboBox({
        fieldLabel: 'Groupe',
        store: dsCustomersGroups,
        displayField: 'text',
        valueField: 'id',
        name: 'customers_groups',
        hiddenName: 'customers_groups_id',
        readOnly: true,
        forceSelection: true,
        mode: 'local',
        emptyText: 'Aucun',
        triggerAction: 'all'
    });

    return cboCustomersGroups;
};

Toc.content.ContentManager.getTinyEditor = function (name, height) {
    return {
        xtype: 'tinymce',
        fieldLabel: 'Description',
        name: 'content_description[' + name + ']',
        height: height || 350,
        tinymceSettings: {
            theme: "advanced",
            relative_urls: false, remove_script_host: false,
            plugins: "pagebreak,style,advhr,emotions,safari,advimage,preview,media,insertdatetime,print,contextmenu,paste,directionality",
            theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3: "hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,print,|,advhr,|,emotions,|,pagebreak",
            theme_advanced_toolbar_location: "top",
            theme_advanced_toolbar_align: "left",
            theme_advanced_statusbar_location: "bottom",
            theme_advanced_resizing: false,
            extended_valid_elements: "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
            template_external_list_url: "example_template_list.js"
        }
    };
};

Toc.content.ContentManager.getContentStatusFields = function (width) {
    return {
        layout: 'column',
        border: false,
        items: [
            {
                layout: 'form',
                border: false,
                labelSeparator: ' ',
                width: width || 200,
                items: [
                    {
                        fieldLabel: 'Publie',
                        xtype: 'radio',
                        name: 'content_status',
                        inputValue: '1',
                        checked: true,
                        boxLabel: 'Oui'
                    }
                ]
            },
            {
                layout: 'form',
                border: false,
                width: width || 200,
                items: [
                    {
                        hideLabel: true,
                        xtype: 'radio',
                        inputValue: '0',
                        name: 'content_status',
                        boxLabel: 'Non'
                    }
                ]
            }
        ]
    }
};

Toc.loadReportParameters = function(panel,reportsId,form,caller){
    if (reportsId && reportsId > 0) {
        if(panel)
        {
            panel.getEl().mask('Chargement des parametres ....');
        }

        form.load({
            url: Toc.CONF.CONN_URL,
            params:{
                action: 'load_reportparameters',
                reports_id: this.reportsId
            },
            success: function(form, action) {
                if(caller.buildParams)
                {
                    caller.buildParams(form, action,panel);
                }
            },
            failure: function(form, action) {
                if(caller.buildParams)
                {
                    caller.buildParams(form, action,panel);
                }
            },
            scope: this
        });
    }
};

Toc.runReport = function(panel,reportsId,form,caller){
    var params = {
        module: 'reports',
        state: 'AD_HOC_ACTIVE',
        action : 'schedule_report',
        reports_id : reportsId,
        format : 'pdf'
    };

    if (reportsId && reportsId > 0) {
        if(panel)
        {
            panel.getEl().mask('Creation du job, veuillez patienter SVP ...');
        }

        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: params,
            callback: function(options, success, response) {
                if(response.responseText)
                {
                    //console.debug(response);
                    var request = Ext.decode(response.responseText);
                    if(request.msg == '1')
                    {
                        Toc.downloadReport(request,panel,'pdf','',false,caller);
                    }
                    else
                    {
                        Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.msg);
                    }
                }
            },
            scope: caller
        });

    }
};

Toc.downloadReport = function(request,panel,format,name,open,caller) {
    var status = request.status;
    var action = "";

    switch(status)
    {
        case "run":
            action = "start_report";
            break;
        case "complete":
            action = "download_report";
            break;
        default:
            //action = "status_report";
            action = "status_job";
            break;
    }

    if(panel)
    {
        panel.getEl().mask(request.comments);
    }

    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        //url: Toc.CONF.REPORT_URL,
        params: {
            module: 'reports',
            action : action,
            id:request.requestId,
            subscriptions_id:request.subscriptions_id,
            format:format,
            content_name:name,
            comments:request.comments
        },
        callback: function(options, success, response) {
            if(response.responseText)
            {
                result = Ext.decode(response.responseText);
                switch(action)
                {
                    case 'download_report':
                        if(panel)
                        {
                            panel.getEl().unmask();
                        }
                        if(open && open == true)
                        {
                            url = result.file_name;
                            params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
                            window.open(url, "",params);
                        }

                        if(panel.getStore)
                        {
                            panel.getStore().reload();
                        }

                        if(panel.buildItems)
                        {
                            panel.buildItems();
                        }
                        break;
                    default:
                        var req = result;
                        this.requestId = req.requestId;
                        Toc.downloadReport(req,panel,format,name,open,caller);
                        break;
                }
            }
            else
            {
                var request = {
                    status : action == 'download_report' ? 'ready' : 'execution',
                    currentPage : '?',
                    requestId : this.requestId
                };

                if(panel)
                {
                    panel.getEl().unmask();
                }

                Toc.downloadReport(request,panel,format,name,open,caller);
            }
        },
        scope: caller
    });
};