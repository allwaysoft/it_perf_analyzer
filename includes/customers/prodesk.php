<?php
define('HTTP_SERVER', 'http://10.100.18.21/');
define('HTTPS_SERVER', 'http://10.100.18.21/');
define('ENABLE_SSL', false);
define('HTTP_COOKIE_DOMAIN', '10.100.18.21');
define('HTTPS_COOKIE_DOMAIN', '10.100.18.21');
define('HTTP_COOKIE_PATH', 'dev/');
define('HTTPS_COOKIE_PATH', '/');
define('DIR_WS_HTTP_CATALOG', 'dev/');
define('DIR_REPORT_HTTP_CATALOG', 'dev/');
define('DIR_WS_HTTPS_CATALOG', 'dev/');
define('DIR_WS_IMAGES', 'images/');
define('DIR_WS_REPORTS', 'reports/');

define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
define('DIR_FS_CATALOG', '/opt/lampp/htdocs/dev/');
define('DIR_FS_ADMIN', 'admin/');
define('DIR_FS_WORK', '/opt/lampp/htdocs/dev/includes/work/');
define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
define('DIR_FS_PARAMETERS', DIR_FS_CATALOG . 'download/parameters/');
define('DIR_FS_WATCHDOG', DIR_FS_CATALOG . 'download/watchdogfiles/');
define('DIR_FS_ARCHIVES', DIR_FS_CATALOG . 'download/archives/');
define('DIR_FS_ERRORS', DIR_FS_CATALOG . 'download/errors/');
define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
define('DIR_FS_BACKUP', '/opt/lampp/htdocs/dev/' . DIR_FS_ADMIN . 'backups/');
define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
define('DIR_FS_CACHE_ADMIN', DIR_FS_CACHE . DIR_FS_ADMIN);
define('MAX_DISPLAY_SEARCH_RESULTS', '20');

define('DB_SERVER', 'localhost');
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', 'WLTG0621%bntc');
define('DB_DATABASE', 'bicec');
define('DB_DATABASE_CLASS', 'mysql');
define('DB_TABLE_PREFIX', 'delta_');
define('USE_PCONNECT', 'false');
define('STORE_SESSIONS', 'mysql');
define('AUTH', 'local');

//define('DB_USER', 'gm_fomi');
//define('DB_PASS', 'Kouepe3073');
//define('DB_HOST', '10.100.1.50');
//define('DB_SID', 'STOCKV10');
//define('APP_HOST', '10.100.1.57');
//define('APP_USER', 'plateformesvp');
//define('APP_PASS', 'Kouepe@2015');

define('REPORT_RUNNER', 'localhost');
define('CURL_RUNNER', 'localhost');
define('CURL_USER', 'guyfomi');
define('CURL_PASS', 'Fomi@2016');

//EMAIL
define('ALL_EMAIL', 'guyfomi@gmail.com');
define('ADMIN_EMAIL', 'guyfomi@gmail.com');
define('NOTIFICATION_EMAIL', 'guyfomi@gmail.com');

//METABASE
define('METABASE_URL', 'http://10.100.18.21/bi');
define('METABASE_DEV_USER', 'makaki@gmail.com');
define('METABASE_DEV_PASS', 'Guy2p@cc');
define('METABASE_ADMIN_USER', 'admin@gmail.com');
define('METABASE_ADMIN_PASS', 'Guy2p@cc');
//define('USERS',array('ADMIN','MK030', 'FA025', 'EN125', 'DS094', 'CN121', 'ON026', 'HM084', 'JO033','CA136','EN145','JO109'));
?>