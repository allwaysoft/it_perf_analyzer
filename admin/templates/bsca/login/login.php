<?php
$osC_Language->loadIniFile('login.php');
$trial = base64_decode(LAIRT);
$date1 = new DateTime(base64_decode(SD));
$date2 = new DateTime();
//echo "date2 => " . $date2;
$interval = $date2->diff($date1);

if($interval->days >= $trial)
{
    die($osC_Language->get('trial_expired'));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $osC_Language->getTextDirection(); ?>"
      xml:lang="<?php echo $osC_Language->getCode(); ?>" lang="<?php echo $osC_Language->getCode(); ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="PRAGMA" content="NO-CACHE">
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
    <meta http-equiv="EXPIRES" content="-1">
    <title><?php echo $osC_Language->get('administration_title'); ?></title>

    <!-- EXT JS LIBRARY -->
    <link rel="stylesheet" type="text/css" href="external/extjs/resources/css/ext-all.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/login/login.css"/>
</head>

<body scroll="no">
<img src="images/Logo_BSCA_BANK.png"/>
<div id="x-loading-mask"
     style="width:100%; height:100%; background:#000000; position:absolute; z-index:20000; left:0; top:0;">&#160;</div>
<div id="x-loading-panel"
     style="position:absolute;left:40%;top:40%;border:1px solid #9c9f9d;padding:2px;background:#d1d8db;width:300px;text-align:center;z-index:20001;">
    <div class="x-loading-panel-mask-indicator"
         style="border:1px solid #c1d1d6;color:#666;background:white;padding:10px;margin:0;padding-left: 20px;height:110px;text-align:left;">
        <img class="x-loading-panel-logo" style="display:block;margin-bottom:15px;" src="images/tomatocart.jpg"/>
        <img src="images/loading.gif" style="width:16px;height:16px;vertical-align:middle"/>&#160;
        <span id="load-status"><?php echo $osC_Language->get('init_system'); ?></span>

        <div style="font-size:10px; font-weight:normal; margin-top:15px;">Copyright &copy;</div>
    </div>
</div>

<div id="x-login-panel">
    <img style="width: 798px; height: 510px;" src="images/Immeuble_BSCA_3.jpg">
    <img src="templates/default/desktop/images/default/s.gif" class="login-logo abs-position" />

    <img src="templates/default/desktop/images/default/s.gif" class="login-screenshot abs-position" />

    <div id="x-login-form" class="x-login-form abs-position"><a id='forget-password' onclick="javascript:forgetPassword();"><?php echo $osC_Language->get("label_forget_password"); ?></a></div>
</div>

<script src="external/extjs/adapter/ext/ext-base.js"></script>
<script src="external/extjs/ext-all.js"></script>
<script type="text/javascript">
Ext.onReady(function () {
    Ext.util.Cookies = {
        set: function(name, value){
            var argv = arguments;
            var argc = arguments.length;
            var expires = (argc > 2) ? argv[2] : null;
            var path = (argc > 3) ? argv[3] : '/';
            var domain = (argc > 4) ? argv[4] : null;
            var secure = (argc > 5) ? argv[5] : false;
            document.cookie = name + "=" + escape(value) + ((expires === null) ? "" : ("; expires=" + expires.toGMTString())) + ((path === null) ? "" : ("; path=" + path)) + ((domain === null) ? "" : ("; domain=" + domain)) + ((secure === true) ? "; secure" : "");
        },
        get : function(name){
            var arg = name + "=";
            var alen = arg.length;
            var clen = document.cookie.length;
            var i = 0;
            var j = 0;
            while(i < clen){
                j = i + alen;
                if(document.cookie.substring(i, j) == arg){
                    return Ext.util.Cookies.getCookieVal(j);
                }
                i = document.cookie.indexOf(" ", i) + 1;
                if(i === 0){
                    break;
                }
            }
            return null;
        },
        clear : function(name){
            if(Ext.util.Cookies.get(name)){
                document.cookie = name + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT";
            }
        },
        getCookieVal: function(offset){
            var endstr = document.cookie.indexOf(";", offset);
            if(endstr == -1){
                endstr = document.cookie.length;
            }
            return unescape(document.cookie.substring(offset, endstr));
        }
    };

    Ext.BLANK_IMAGE_URL = 'templates/default/desktop/images/default/s.gif';
    Ext.EventManager.onWindowResize(centerPanel);

    var loginPanel = Ext.get("x-login-panel");

    centerPanel();

    Ext.namespace("Toc");
    Toc.Languages = [];
    <?php 
      foreach ($osC_Language->getAll() as $l) {
        echo 'Toc.Languages.push(["' . $l['code'] . '", "' . $l['name'] . '"]);';
      }
    ?>
    var cboLanguage = new Ext.form.ComboBox({
        store: new Ext.data.SimpleStore({
            fields: ['id', 'text'],
            data: Toc.Languages
        }),
        fieldLabel: '<?php echo $osC_Language->get("field_language"); ?>',
        name: 'language',
        hiddenName: 'language',
        displayField: 'text',
        valueField: 'id',
        mode: 'local',
        triggerAction: 'all',
        forceSelection: true,
        editable: false,
        value: '<?php echo $osC_Language->getCode(); ?>'
    });

    cboLanguage.on(
        'select',
        function () {
            document.location = '<?php echo osc_href_link_admin(FILENAME_DEFAULT); ?>?admin_language=' + cboLanguage.getValue();
        },
        this
    );

    var frmlogin = new Ext.form.FormPanel({
        url: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
        baseParams: {
            module: 'login',
            action: 'login'
        },
        labelWidth: 100,
        width: 335,
        autoHeight: true,
        border: false,
        applyTo: 'x-login-form',
        bodyStyle: 'background: transparent',
        defaults: {anchor: '100%'},
        labelSeparator: ' ',
        items: [
            cboLanguage,
            {xtype: 'textfield', name: 'user_name', fieldLabel: '<?php echo $osC_Language->get("field_username"); ?>', allowBlank: false},
            {xtype: 'textfield', name: 'user_password', fieldLabel: '<?php echo $osC_Language->get("field_password"); ?>', inputType: 'password', allowBlank: false}
        ],
        keys: [
            {
                key: Ext.EventObject.ENTER,
                fn: login,
                scope: this
            }
        ],
        buttonAlign: 'right',
        buttons: [
            {
                text: '<?php echo $osC_Language->get("button_login"); ?>',
                handler: login,
                scope: this
            }
        ],
        listeners: {
            'render': function () {
                this.findByType('textfield')[1].focus(true, true);
            }
        }
    });

    function centerPanel() {
        var xy = loginPanel.getAlignToXY(document, 'c-c');
        positionPanel(loginPanel, xy[0], xy[1]);
    }

    function loginBi(username) {
        Ext.get('x-login-panel').mask('BI Session ...');
        Ext.Ajax.request({
            method: 'POST',
            url: '<?php echo METABASE_URL; ?>' + '/api/session',
            headers: {
                Accept: 'application/json'
            },
            jsonData: {
                username: username + '@gmail.com',
                password: '<?php echo METABASE_DEV_PASS; ?>'
            },
            callback: function (options, success, response) {
                Ext.get('x-login-panel').unmask();
                var result = Ext.decode(response.responseText);
                if (result.id) {
                    Ext.util.Cookies.set('metabase.SESSION_ID',result.id);
                    window.location = '<?php echo osc_href_link_admin(FILENAME_DEFAULT); ?>?admin_language=' + cboLanguage.getValue() + '&id=' + result.id;
                }
                else {
                    console.log('could not create metabase session ... creating new user');
                    createBiUser(username);
                    //alert('could not create BI session ...' + result.toString());
                }
            },
            scope: this
        });
    }

    function createBiUser(username) {
        console.log('admin login to create a new user');
        Ext.get('x-login-panel').mask('BI User Configuration ...');
        Ext.Ajax.request({
            method: 'POST',
            url: '<?php echo METABASE_URL; ?>' + '/api/session',
            headers: {
                Accept: 'application/json',
                'Content-Type' : 'application/json'
            },
            jsonData: {
                username: '<?php echo METABASE_ADMIN_USER; ?>',
                password: '<?php echo METABASE_ADMIN_PASS; ?>'
            },
            callback: function (options, success, response) {
                Ext.get('x-login-panel').unmask();
                var result = Ext.decode(response.responseText);
                if (result.id) {
                    console.log('creating a new user');
                    Ext.get('x-login-panel').mask('BI User Activation ...');
                    Ext.util.Cookies.set('metabase.SESSION_ID',result.id);
                    Ext.Ajax.request({
                        method: 'POST',
                        url: '<?php echo METABASE_URL; ?>' + '/api/user',
                        params: {
                            session_id: result.id,
                            'X-Metabase-Session':result.id
                        },
                        headers: {
                            Accept: 'application/json',
                            'X-Metabase-Session':result.id,
                            'Content-Type' : 'application/json'
                        },
                        jsonData: {
                            'X-Metabase-Session':result.id,
                            email: username + '@gmail.com',
                            first_name: username,
                            last_name: '...',
                            password: '<?php echo METABASE_DEV_PASS; ?>'
                        },
                        callback: function (options, success, response) {
                            Ext.get('x-login-panel').unmask();
                            Ext.Ajax.request({
                                method: 'DELETE',
                                url: '<?php echo METABASE_URL; ?>' + '/api/session',
                                params: {
                                    session_id: result.id,
                                    'X-Metabase-Session':result.id
                                },
                                headers: {
                                    Accept: 'application/json',
                                    'X-Metabase-Session':result.id,
                                    'Content-Type' : 'application/json'
                                },
                                jsonData: {
                                    session_id : result.id
                                },
                                callback: function (options, success, response) {
                                    console.log('user ' + username + 'created ... login');
                                    loginBi(username);
                                },
                                scope: this
                            });
                        },
                        scope: this
                    });
                }
                else {
                    console.log('could not login admin');
                    //createBiUser(username);
                    alert('could not create BI session ...' + result.toString());
                }
            },
            scope: this
        });
    }

    function login() {
        Ext.get('x-login-panel').mask('<?php echo $osC_Language->get("login"); ?>');
        frmlogin.form.submit({
            success: function (form, action) {
                //console.log('creating metabase session ...');
                Ext.get('x-login-panel').unmask();

                var username = action.result.username;
                if(action.result.changepwd == true)
                {
                    Toc.changePwdDialog = function(config) {

                        config = config || {};

                        config.title = '<?php echo $osC_Language->get("must_change_pwd"); ?>';
                        config.width = 345;
                        config.height = 120;
                        config.modal = true;
                        config.iconCls = 'icon-databases-win';
                        config.items = this.buildForm();

                        config.buttons = [
                            {
                                text: '<?php echo $osC_Language->get("button_save"); ?>',
                                handler: function() {
                                    this.submitForm();
                                },
                                scope: this
                            },
                            {
                                text: '<?php echo $osC_Language->get("button_close"); ?>',
                                handler: function() {
                                    this.close();
                                },
                                scope: this
                            }
                        ];

                        this.addEvents({'saveSuccess': true});

                        Toc.changePwdDialog.superclass.constructor.call(this, config);
                    };

                    Ext.extend(Toc.changePwdDialog, Ext.Window, {

                        show: function (databases_id,event) {
                            Toc.changePwdDialog.superclass.show.call(this);

                            this.center();
                            this.doLayout(true, true);
                        },

                        buildForm: function() {
                            this.frmchangePwdDialog = new Ext.form.FormPanel({
                                autoScroll: true,
                                id : 'frmchangePwdDialog',
                                layout: 'form',
                                url: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
                                baseParams: {
                                    module: 'login',
                                    action : 'reset'
                                },
                                deferredRender: false,
                                items: [
                                    {xtype: 'textfield', name: 'user_password1', fieldLabel: '<?php echo $osC_Language->get("field_password1"); ?>', inputType: 'password', allowBlank: false},
                                    {xtype: 'textfield', name: 'user_password2', fieldLabel: '<?php echo $osC_Language->get("field_password2"); ?>', inputType: 'password', allowBlank: false}
                                ]
                            });

                            return this.frmchangePwdDialog;
                        },

                        submitForm: function() {
                            this.frmchangePwdDialog.form.submit({
                                waitMsg: '...',
                                success: function(form, action){
                                    this.fireEvent('saveSuccess', action.result.feedback);
                                    this.close();
                                    loginBi(username);
                                },
                                failure: function(form, action) {
                                    if (action.failureType != 'client') {
                                        Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                                    }
                                },
                                scope: this
                            });
                        }
                    });

                    var diag = new Toc.changePwdDialog();
                    diag.show();
                }
                else
                {
                    loginBi(username);
                }
            },
            failure: function (form, action) {
                Ext.get('x-login-panel').unmask();
                if (action.failureType != 'client') {
                    alert(action.result.feedback);
                }
            },
            scope: this
        });
    }

    function positionPanel(el, x, y) {
        if (x && typeof x[1] == 'number') {
            y = x[1];
            x = x[0];
        }

        el.pageX = x;
        el.pageY = y;

        if (x === undefined || y === undefined) { // cannot translate undefined points
            return;
        }

        if (y < 0) {
            y = 10;
        }

        var p = el.translatePoints(x, y);
        el.setLocation(p.left, p.top);

        return el;
    }

    function removeLoadMask() {
        var loading = Ext.get('x-loading-panel');
        var mask = Ext.get('x-loading-mask');
        loading.hide();
        mask.hide();
    }

    removeLoadMask();
});

function forgetPassword() {
    var email = prompt('<?php echo $osC_Language->get("ms_forget_password_text"); ?>');

    if (!Ext.isEmpty(email)) {
        Ext.get('x-login-panel').mask('<?php echo $osC_Language->get("ms_sending_email"); ?>');

        Ext.Ajax.request({
            url: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
            params: {
                module: 'login',
                action: 'get_password',
                email_address: email
            },
            callback: function (options, success, response) {
                Ext.get('x-login-panel').unmask();

                result = Ext.decode(response.responseText);
                alert(result.feedback);
            },
            scope: this
        });
    }
}
</script>
</body>
</html>