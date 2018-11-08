<?php
require('includes/ext_config.php');

require('includes/classes/json.php');
$toC_Json = new toC_Json();

if (!isset($_SESSION['admin'])) {
    require('templates/' . CLIENT . '/login/login.php');
    exit;
}

$users = array('ADMIN','MAKAKI','MK030', 'FA025', 'EN125', 'DS094', 'CN121', 'ON026', 'HM084', 'JO033','CA136','EN145','JO109');
if (isset($users)) {
    if(is_array($users))
    {
        if(!in_array(strtoupper($_SESSION['admin']['username']),$users))
        {
            unset($_SESSION['admin']);
            die("Identifiant non autorisé !!! Veuillez contacter HMN CONSULTING pour autoriser ce Compte");
        }
    }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="<?php echo $osC_Language->getTextDirection(); ?>" xml:lang="<?php echo $osC_Language->getCode(); ?>"
      lang="<?php echo $osC_Language->getCode(); ?>">
<head>
    <title><?php PLATFORM_NAME; ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="-1"/>

    <link rel="stylesheet" type="text/css" href="external/extjs/resources/css/ext-all.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/colorpicker/colorpicker-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/form/fileuploadfield-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/content/progressbar.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/form/multiselect-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/form/lovcombo.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/form/statictextfield-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/grid/rowactions-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/pagetoolbar/pagetoolbar-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/portal/portal-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/tree/checktreepanel-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/uploadpanel/uploadpanel-min.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/wizard/ext-ux-wiz-min.css"/>

    <link rel="stylesheet" type="text/css" href="templates/default/desktop/css/all.css"/>
    <link rel="stylesheet" type="text/css" href="templates/default/extensions/portal/portal.css"/>

    <!--[if gte IE 8.0]>
    <link rel="stylesheet" type="text/css" href="templates/default/desktop/css/ie8.css"/>
    <![endif]-->

    <style type="text/css">
        <?php
          foreach ($osC_Language->getAll() as $l) {
            echo ".icon-" . $l['country_iso'] . "-win {background-image: url(../images/worldflags/" . $l['country_iso'] . ".png) !important;}";
          }
        ?>
    </style>
</head>

<body scroll="no">
<div id="x-loading-mask"
     style="width:100%; height:100%; background:#000000; position:absolute; z-index:20000; left:0; top:0;">&#160;</div>
<div id="x-loading-panel"
     style="position:absolute;left:40%;top:40%;border:1px solid #9c9f9d;padding:2px;background:#d1d8db;width:300px;text-align:center;z-index:20001;">
    <div class="x-loading-panel-mask-indicator"
         style="border:1px solid #c1d1d6;color:#666;background:white;padding:10px;margin:0;padding-left: 20px;height:110px;text-align:left;">
        <!--      <img class="x-loading-panel-logo" style="display:block;margin-bottom:15px;" src="images/tomatocart.jpg" />-->
        <img src="images/loading.gif" style="width:16px;height:16px;vertical-align:middle"/>&#160;
        <span id="load-status"><?php echo $osC_Language->get('init_system'); ?></span>

        <div style="font-size:10px; font-weight:normal; margin-top:15px;">Copyright &copy;<?php echo COPYRIGHT; ?></div>
    </div>
</div>

<div id="x-desktop">
    <!--    <a id="tomatocart-logo" href="http://www.mefobe.com" target="_blank" style="margin:5px; float:right;"><img src="images/power_by_button.png" /></a>-->
</div>

<div id="ux-sidebar"></div>
<div id="ux-sidebar-background"></div>

<div id="ux-taskbar">
    <div id="ux-taskbar-start"></div>
    <div id="ux-taskbar-panel-wrap">
        <div id="ux-quickstart-panel"></div>
        <div id="ux-taskbuttons-panel"></div>
    </div>
    <div id="ux-systemtray-panel"></div>
    <div class="x-clear"></div>
</div>

<!-- EXT JS LIBRARY -->
<script type="text/javascript" src="external/extjs/adapter/ext/ext-base-2.3.0.js"></script>
<script type="text/javascript" src="external/extjs/ext-all.js"></script>

<!--  <script type="text/javascript" src="external/devAnalogClock/swfobject.js"></script>-->

<?php if (USE_WYSIWYG_TINYMCE_EDITOR == '1') { ?>

    <!--  <script type="text/javascript" src="external/tinymec/miframe-min.js"></script>-->
    <!--  <script type="text/javascript" src="external/tinymec/tiny_mce/tiny_mce.js"></script>-->
    <!--  <script type="text/javascript" src="external/tinymec/Ext.ux.TinyMCE.min.js"></script>-->
    <script src="external/amcharts_3.20.19.free/amcharts/amcharts.js" type="text/javascript"></script>
    <script src="external/amcharts_3.20.19.free/amcharts/serial.js" type="text/javascript"></script>
    <script src="external/amcharts_3.20.19.free/amcharts/gauge.js" type="text/javascript"></script>

<?php } ?>

<script type="text/javascript"
        src="templates/default/locale/ext-lang-<?php echo $osC_Language->getCode(); ?>-min.js"></script>

<script type="text/javascript">
    Ext.namespace("Toc");
    Ext.namespace("Toc.content");

    Toc.CONF = {
        CONN_URL: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
        ORACLE_URL: '<?php echo osc_href_link_admin(FILENAME_ORACLE); ?>',
        LINUX_URL: '<?php echo osc_href_link_admin(FILENAME_LINUX); ?>',
        MYSQL_URL: '<?php echo osc_href_link_admin(FILENAME_MYSQL); ?>',
        WINDOWS_URL: '<?php echo osc_href_link_admin(FILENAME_WINDOWS); ?>',
        REPORT_URL: '<?php echo HTTP_REPORT . DIR_REPORT_HTTP_CATALOG . DIR_FS_ADMIN . '/json.php'; ?>',
        LOAD_URL: '<?php echo osc_href_link_admin(FILENAME_LOAD); ?>',
        PDF_URL: '<?php echo osc_href_link_admin(FILENAME_PDF); ?>',

        GRID_PAGE_SIZE: <?php echo MAX_DISPLAY_SEARCH_RESULTS; ?>,
        GRID_STEPS: <?php echo EXT_GRID_STEPS; ?>,
        JSON_READER_ROOT: '<?php echo EXT_JSON_READER_ROOT; ?>',
        JSON_READER_TOTAL_PROPERTY: '<?php echo EXT_JSON_READER_TOTAL; ?>'
    };

    Toc.Languages = [];
    <?php 
      foreach ($osC_Language->getAll() as $l) {
        echo 'Toc.Languages.push(' . $toC_Json->encode($l) . ');';
      }
      
      $desktop_translations = $osC_Language->parseIniFile('modules/ext_desktop.php');
    ?>

    var TocLanguage = {};
    TocLanguage = <?php echo $toC_Json->encode($desktop_translations); ?>;
</script>

<script type="text/javascript" src="templates/default/desktop/core/all.js"></script>
<script type="text/javascript" src="templates/default/desktop/setting/all.js"></script>
<script type="text/javascript" src="templates/default/extensions/extension-all.js"></script>
<script type="text/javascript" src="<?php echo osc_href_link_admin('tocdesktop.php'); ?>"></script>
<script type="text/javascript" src="templates/default/extensions/CategoriesComboBox.js"></script>
<script type="text/javascript" src="templates/default/extensions/CategoriesTreePanel.js"></script>
<script type="text/javascript" src="templates/default/extensions/ActionsTreePanel.js"></script>
<script type="text/javascript" src="templates/default/extensions/form/CompositeField.js"></script>
<script type="text/javascript" src="templates/default/extensions/form/lovcombo.js"></script>
<script type="text/javascript" src="templates/default/extensions/uploadpanel/all.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/progressbar.min.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_images_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_move_dialog.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_copy_dialog.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_images_grid.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_categories_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_documents_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_logs_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_meta_info_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_documents_dialog.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_logs_dialog.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_links_dialog.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_links_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_description_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_comments_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_comment_dialog.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_permissions_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_notifications_panel.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/content_manager.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/oracle.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/perf.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/security.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/ogg.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/server.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/ha.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/memory.js"></script>
<script type="text/javascript" src="templates/default/extensions/oracle/storage.js"></script>
<script type="text/javascript" src="templates/default/extensions/content/production.js"></script>
<script type="text/javascript" src="templates/default/extensions/form/SliderField.js"></script>
</body>
</html>