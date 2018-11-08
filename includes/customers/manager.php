<?php
define('HTTP_SERVER', 'http://manager.mefobe.net/');
define('HTTPS_SERVER', 'http://manager.mefobe.net/');
define('ENABLE_SSL', false);
define('HTTP_COOKIE_DOMAIN', 'manager.mefobe.net');
define('HTTPS_COOKIE_DOMAIN', 'manager.mefobe.net');
define('HTTP_COOKIE_PATH', '/');
define('HTTPS_COOKIE_PATH', '/');
define('DIR_WS_HTTP_CATALOG', '/');
define('DIR_WS_HTTPS_CATALOG', '/');
define('DIR_WS_IMAGES', 'images/');
define('DIR_WS_REPORTS', 'reports/');

define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
define('DIR_FS_CATALOG', '/home/bragu/public_html/manager.mefobe.net/');
define('DIR_FS_ADMIN', 'admin/');
define('DIR_FS_WORK', '/home/bragu/public_html/manager.mefobe.net/includes/work/');
define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
define('DIR_FS_PARAMETERS', DIR_FS_CATALOG . 'download/parameters/');
define('DIR_FS_WATCHDOG', DIR_FS_CATALOG . 'download/watchdogfiles/');
define('DIR_FS_ARCHIVES', DIR_FS_CATALOG . 'download/archives/');
define('DIR_FS_ERRORS', DIR_FS_CATALOG . 'download/errors/');
define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
define('DIR_FS_BACKUP', '/home/bragu/public_html/manager.mefobe.net/' . DIR_FS_ADMIN . 'backups/');
define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
define('DIR_FS_CACHE_ADMIN', DIR_FS_CACHE . DIR_FS_ADMIN);

define('DB_SERVER', 'localhost');
define('DB_SERVER_USERNAME', 'bragu');
define('DB_SERVER_PASSWORD', 'vUUN-pcBWlqH');
define('DB_DATABASE', 'bragu_manager');
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

define('CURL_RUNNER', 'localhost');
define('CURL_USER', 'guyfomi');
define('CURL_PASS', '12345');

//EMAIL
define('ALL_EMAIL', 'guyfomi@gmail.com');
define('ADMIN_EMAIL', 'guyfomi@gmail.com');
define('NOTIFICATION_EMAIL', 'guyfomi@gmail.com');

//METABASE
define('METABASE_URL', 'http://localhost/bi');
define('METABASE_DEV_USER', 'guyfomi@gmail.com');
define('METABASE_DEV_PASS', 'Guy2p@cc');
define('METABASE_ADMIN_USER', 'guyfomi@gmail.com');
define('METABASE_ADMIN_PASS', 'Guy2p@cc');
?>