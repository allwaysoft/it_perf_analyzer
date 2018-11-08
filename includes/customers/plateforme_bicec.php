<?php
define('HTTP_SERVER', 'http://plateformesvp.intra.bicec');
define('HTTP_REPORT', 'http://10.100.18.19');
define('HTTPS_SERVER', 'https://plateformesvp.intra.bicec');
define('ENABLE_SSL', false);
define('HTTP_COOKIE_DOMAIN', 'plateformesvp.intra.bicec');
define('HTTPS_COOKIE_DOMAIN', 'plateformesvp.intra.bicec');
define('HTTP_COOKIE_PATH', '/bicec/');
define('HTTPS_COOKIE_PATH', '/bicec/');
define('DIR_WS_HTTP_CATALOG', '/bicec/');
define('DIR_REPORT_HTTP_CATALOG', '/bicec/');
define('DIR_WS_HTTPS_CATALOG', '/bicec/');
define('DIR_WS_IMAGES', 'images/');
define('DIR_WS_REPORTS', 'reports/');
define('STORE_SESSIONS', 'mysql');

define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
define('DIR_FS_CATALOG', '/opt/lampp/htdocs/bicec/');
define('DIR_FS_ADMIN', 'admin/');
define('DIR_FS_WORK', '/opt/lampp/htdocs/bicec/includes/work/');
//define('DIR_FS_WORK', '/dev/shm/');
define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
define('DIR_FS_BACKUP', '/opt/lampp/htdocs/bicec/' . DIR_FS_ADMIN . 'backups/');
define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
define('DIR_FS_CACHE_ADMIN', DIR_FS_CACHE . DIR_FS_ADMIN);
define('DB_SERVER', 'localhost');
define('REPORT_SERVER', '10.100.18.19:8080');
define('REPORT_RUNNER', '10.100.18.19');
define('REPORT_USER', 'jasperadmin');
define('REPORT_PASS', 'jasperadmin');
define('JOB_SERVER', '10.100.18.19:8080');
define('JOB_USER', 'admin');
define('JOB_PASS', 'changeme');
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', 'WLTG0621%bntc');
define('DB_DATABASE', 'plateforme_bicec');
define('DB_DATABASE_CLASS', 'mysql');
define('DB_TABLE_PREFIX', 'delta_');
define('USE_PCONNECT', 'false');

define('DB_USER','gm_fomi');
define('DB_PASS','Kouepe3073');
define('DB_HOST','10.100.1.50');
define('DB_SID','STOCKV10');
define('AUTH', 'amplitude');

define('APP_HOST','10.100.1.57');
define('APP_USER','plateformesvp');
define('APP_PASS','Ek@lle@2017');

/**
 * These constants are copied here from DataType for facility
 */
define("DT_TYPE_TEXT",1);
define("DT_TYPE_NUMBER",2);
define("DT_TYPE_DATE",3);
define("DT_TYPE_DATE_TIME",4);

/**
 * These constants are copied here from InputControl for facility
 */
define("IC_TYPE_BOOLEAN",1);
define("IC_TYPE_SINGLE_VALUE",2);
define("IC_TYPE_SINGLE_SELECT_LIST_OF_VALUES",3);
define("IC_TYPE_SINGLE_SELECT_QUERY",4);
define("IC_TYPE_MULTI_VALUE",5);
define("IC_TYPE_MULTI_SELECT_LIST_OF_VALUES",6);
define("IC_TYPE_MULTI_SELECT_QUERY",7);

define('CURL_RUNNER', 'localhost');
define('CURL_USER', 'guyfomi');
define('CURL_PASS', '12345');

//EMAIL
define('ALL_EMAIL', 'GuyMarcel.FOMINDIEFIE@t2safrica.com');
define('ADMIN_EMAIL', 'GuyMarcel.FOMINDIEFIE@t2safrica.com');
define('NOTIFICATION_EMAIL', 'GuyMarcel.FOMINDIEFIE@t2safrica.com');

//METABASE
define('METABASE_URL', 'http://plateformesvp.intra.bicec/bi');
define('METABASE_DEV_USER', 'makaki@gmail.com');
define('METABASE_DEV_PASS', 'Guy2p@cc');
define('METABASE_ADMIN_USER', 'admin@gmail.com');
define('METABASE_ADMIN_PASS', 'Guy2p@cc');
?>