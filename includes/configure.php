<?php
define('HTTP_SERVER', 'http://127.0.0.1');
define('HTTP_REPORT', 'http://10.100.18.17');
define('HTTPS_SERVER', 'http://127.0.0.1');
define('ENABLE_SSL', false);
define('HTTP_COOKIE_DOMAIN', '127.0.0.1');
define('HTTPS_COOKIE_DOMAIN', '127.0.0.1');
define('HTTP_COOKIE_PATH', '/dev/');
define('HTTPS_COOKIE_PATH', '/dev/');
define('DIR_WS_HTTP_CATALOG', '/dev/');
define('DIR_WS_HTTPS_CATALOG', '/dev/');
define('DIR_WS_IMAGES', 'images/');
define('DIR_WS_REPORTS', 'reports/');

define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
define('DIR_FS_CATALOG', 'C:/xampp/htdocs/dev/');
define('DIR_FS_ADMIN', 'admin/');
define('DIR_FS_WORK', 'C:/xampp/htdocs/dev/includes/work/');
define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
define('DIR_FS_BACKUP', 'C:/xampp/htdocs/dev/' . DIR_FS_ADMIN . 'backups/');
define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
define('DIR_FS_CACHE_ADMIN', DIR_FS_CACHE . DIR_FS_ADMIN);

define('DB_SERVER', 'plateformesvp.intra.bicec');
define('REPORT_SERVER', '10.100.18.19:8080');
define('REPORT_RUNNER', '10.100.18.19');
define('REPORT_USER', 'jasperadmin');
define('REPORT_PASS', 'jasperadmin');
define('JOB_SERVER', '10.100.120.252:8080');
define('JOB_USER', 'admin');
define('JOB_PASS', 'changeme');
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', '');
define('DB_DATABASE', 'bicec');
define('DB_DATABASE_CLASS', 'mysql');
define('DB_TABLE_PREFIX', 'delta_');
define('USE_PCONNECT', 'false');
define('STORE_SESSIONS', 'mysql');

define('DB_USER', 'gm_fomi');
define('DB_PASS', 'Kouepe3073');
define('DB_HOST', '10.100.1.50');
define('DB_SID', 'STOCKV10');
define('APP_HOST', '10.100.1.57');
define('APP_USER', 'plateformesvp');
define('APP_PASS', 'Kouepe@2015');
define('DEBUG_USER', 'formv10');
define('DEBUG_PASS', 'formv10');
define('GENERO_DATABASE', 'bicecv10');
define('BANK', '/appli/delta/FORM/gen_2.32.09/v10.0.56');
define('PATH', '/appli/fourjs/gen_2.32.09/bin');
define('FGLLDPATH', '/appli/delta/FORM/gen_2.32.09/v10.0.56/4gi/global');
define('LIBPATH', '/u01/product/11.2.0/dbhome_1/lib');
define('DBPATH', '/appli/delta/FORM/gen_2.32.09/v10.0.56/form_fr/global');
define('ORACLE_HOME', '/u01/product/11.2.0/dbhome_1');
define('LD_LIBRARY_PATH', '/u01/product/11.2.0/dbhome_1/lib/');
define('FGLDIR', '/appli/fourjs/gen_2.32.09');
define('FGLPROFILE', '/appli/fourjs/gen_2.32.09/etc/fglprofile.bank');
define('PROFILE_SCRIPT', 'profile_debug');
define('PROFILE_PATH', '/appli/delta/profilev10');

define('IRISDB_USER', 'sms');
define('IRISDB_PASS', 'sms');
define('IRISDB_HOST', '10.100.1.111');
define('IRISDB_SID', 'irisvm');
define('CURL_RUNNER', '10.100.1.111');

//SMS DB
define('DB_SMS_SERVER', '10.100.23.21');
define('DB_SMS_USER', 'root');
define('DB_SMS_PASS', 'WLTG0621%bntc');
define('DB_SMS_DATABASE', 'SmsServer');

/**
 * These constants are copied here from DataType for facility
 */
define("DT_TYPE_TEXT", 1);
define("DT_TYPE_NUMBER", 2);
define("DT_TYPE_DATE", 3);
define("DT_TYPE_DATE_TIME", 4);

/**
 * These constants are copied here from InputControl for facility
 */
define("IC_TYPE_BOOLEAN", 1);
define("IC_TYPE_SINGLE_VALUE", 2);
define("IC_TYPE_SINGLE_SELECT_LIST_OF_VALUES", 3);
define("IC_TYPE_SINGLE_SELECT_QUERY", 4);
define("IC_TYPE_MULTI_VALUE", 5);
define("IC_TYPE_MULTI_SELECT_LIST_OF_VALUES", 6);
define("IC_TYPE_MULTI_SELECT_QUERY", 7);
?>