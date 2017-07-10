<?php
/*-----------------------------------------------------------------------------+
| MagneticOne                                                                  |
| Copyright (c) 2005-2009 MagneticOne.com <contact@magneticone.com>            |
| All rights reserved                                                          |
+------------------------------------------------------------------------------+
|                                                                              |
| PHP MySQL Bridge                                                             |
|                                                                              |
| Bridge is just another way to connect to your database.                      |
| Normally program uses direct MySQL connection to remote database installed at|
| website or some other web server. In some cases this type of connection does |
| not work - your hosting provider may not allow direct connections or your    |
| LAN settings/firewall prevent connection from being established.             |
| Bridge allows you to work with remote database with                          |
| no direct MySQL connection established.                                      |
|                                                                              |
|                                                                              |
| Developed by MagneticOne,                                                    |
| Copyright (C) 2005-2009                                                      |
+-----------------------------------------------------------------------------*/



// Please change immediately
// it is security threat to leave these values as is!
$username = "root";
$password = "";



// Please create this directory or change to any existing temporary directory
// temporary directory should be writable by php script (chmod 0777)
//$temporary_dir = "./tmp"; // on some systems if you get output with 0 size, try to use some local temporary folder 

// Please enter your email address here to receive notifications
//$user_email = 'YOUR@EMAIL-HERE.com';


$allow_compression = true;

$version = '$Revision: 49 $';
//$version = '3.7'; // 29.11.2007
//$version = '3.6'; // 22.11.2007
//$version = '3.5'; // 28.08.2007
//$version = '3.4'; // 14.08.2007
//$version = '3.3'; //10.08.2007
//$version = '3.1'; //16.05.2007
//$version = '3.0'; //10.05.2007
//$version = '2.9'; //12.04.2007


// You can define table prefix here - only tables with names starting with these characters will be stored by bridge and transferred to Store Manager.
// Empty this value to tell bridge to use all tables except for those specified in $exclude_db_tables below
// $include_db_tables = '';

// Do not store tables specified below. Use this variable to reduce size of the data retrieved from bridge
// Specify table names delimited by semicolon ;
$exclude_db_tables = 'log_*;dataflow_*;xcart_sessions_data;xcart_session_history;xcart_stats_shop;xcart_stats_pages_views;xcart_stats_pages;xcart_stats_pages_paths';

/* 
   Please uncomments following database connection information if you need to connect to some 
   specific database or with some specific database login information.
   By default PHP MySQL Bridge is getting login information from your shopping cart.
   This option should be used on non-standard configuration, we assume you know what you are doing
*/

/*
define('USER_DB_SERVER','localhost'); // database host to connect
define('USER_DB_SERVER_USERNAME','');         // database user login to connect
define('USER_DB_SERVER_PASSWORD','');         // database user pasword to connect
define('USER_DB_DATABASE','');  // database name
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE); //good (and pretty enough) for most hostings

if (!ini_get('safe_mode')) {
        @set_time_limit(0); //no time limiting for script, doesn't work in safe mode

} else {
        // no time limiting for script, works in SAFE mode
        @ini_set('max_execution_time', '0');
}

// In case if you have problems with data retrieving change this to a single quote
define('QOUTE_CHAR', '"');



#############################################################################################
#!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!#
#!                                                                                         !#
#! Don't change anything below this line! You should REALLY understand what are you doing! !#
#!                                                                                         !#
#!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!#
#############################################################################################

#
# X-Cart removes all global vars, but do not change any vars in HTTP_*_VARS arrays
# we will hide you technical vars in HTTP_GET_VARS array
# put here any var you need to be saved
#
define('TEST_POST_STRING', '////AjfiIkllsomsdjUNNLkdsuinmJNFIkmsiidmfmiOKSFKMI/////');

define('TEST_OK', '<font color="green">Ok</font>');
define('TEST_FAIL', '<font color="red">Fail</font>');
define('TEST_YES', '<font color="green">Yes</font>');
define('TEST_SKIP', '<font color="gray">Skiped</font>');
define('TEST_NO', '<font color="red">Fail</font>');

if (isset($_REQUEST['phpinfo'])) {
	echo "<a href='" . $_SERVER['HTTP_REFERER'] . "'>back</a><br>";
	phpinfo();
	echo "<br><a href='" . $_SERVER['HTTP_REFERER'] . "'>back</a>";
	die();
}

if ($_REQUEST['task'] == 'self_test')
{
    run_self_test();
    die;
}

if ($_REQUEST['task'] == 'test_post') 
{
    echo TEST_POST_STRING;
    die;
}

if (!(function_exists("gzopen") && function_exists("gzread") && function_exists("gzwrite") && function_exists("gzclose")))
{
    $allow_compression = false;
}

$errors = array(
"authentification" => "PHP MySQL Bridge: Authentication Error",
"cart_type" => "PHP MySQL Bridge: Unknown Cart Type",
"create_tmp_file" => "PHP MySQL Bridge: Can't Create Temporary File",
"open_tmp_file" => "PHP MySQL Bridge: Can't Open Temporary File",
//"not_writeable_dir" => "PHP MySQL Bridge - You temporary dir " .$temporary_dir. " doesn't exist or is not writeable",
"not_writeable_dir" => "PHP MySQL Bridge: Your Temporary Directory specified in bridge.php doesn't exist or is not writeable",
"temporary_file_exist_not" => "PHP MySQL Bridge: Temporary File doesn't exist",
"temporary_file_readable_not" => "PHP MySQL Bridge: Temporary File isn't readable",
"file_uid_mismatch" => "PHP MySQL Bridge: SAFE MODE Restriction in effect. The script uid is not allowed to access tmp folder owned by other uid. If you don't understand this error, please contact your hosting provider for help", 
"open_basedir" => "PHP MySQL Bridge: Please create local Temporary Directory, see \$temporary_dir variable in bridge.php"
// now works in safe mode :)
//"php_safe_mode_detected"                      => "PHP MySQL Bridge - PHP safe mode detected. PHP MySQL bridge will not work properly in safe mode!",
);

//Detecting open_basedir - required for temporary file storage
if( ini_get('open_basedir') && null == $temporary_dir ){
        generate_error($errors['open_basedir']);
}

//Detecting safe_mode
/*
// now works in safe mode :)
if( ini_get('safe_mode') ){
        generate_error($errors['php_safe_mode_detected']);
}
*/

// Detecting system temporary directory
if (empty($temporary_dir)) {
        // Get temporary directory
        $temporary_dir = m1BridgeGetTempDir();
}

// checking temporary directory
if (!is_dir($temporary_dir) || !is_writable($temporary_dir))
{
   generate_error($errors['not_writeable_dir']);
}

$tmpFileStat = stat($temporary_dir);
if ((ini_get('safe_mode') && getmyuid() != intval($tmpFileStat['uid']))) 
{
        generate_error($errors['file_uid_mismatch']);
}


if (md5($username.$password) != $_REQUEST["hash"]) {
    if ( isset($user_email) && !empty($user_email)) {
        mail($user_email, '[ bridge.php ] Bad authetification try', 'Bad login or password was used to login into bridge.php from ' . $_SERVER['REMOTE_ADDR']);
    }
    generate_error($errors['authentification']);
}



$xcartbackuparray = array (
                               'username',
                               'password',
                               'temporary_dir',
                               'allow_compression',
                               'version',
                               'errors',
                               'xcartbackuparray' // should be ALWAYS for restore
                         );

$g_iCartType = getCartType();
$g_sCartHost = '';
ob_start();
if (!defined('USER_DB_SERVER') || !defined('USER_DB_SERVER_USERNAME') || 
  !defined('USER_DB_SERVER_PASSWORD') || !defined('USER_DB_DATABASE')) {
  if ($g_iCartType == -1)
  {
      generate_error($errors['cart_type']);
  }
    
  // osCommerce, CRE Loaded, Zen Cart 
  if ($g_iCartType == 0)
  {
      require('./includes/configure.php');
      $g_sCartHost = substr(HTTP_SERVER,7);
  }
  // X-Cart
  elseif ($g_iCartType == 1)
  {
      // workaround for X-Cart cleaner
      // saving values to HTTP_GET_VARS
      foreach ($xcartbackuparray as $v) {
          $HTTP_GET_VARS['xcartbackuparray=' . $v] = $$v;
      }
      define('XCART_START', 1);
      $xcart_dir = dirname(__FILE__);
      require('./config.php');
      $g_sCartHost = $GLOBALS['xcart_http_host'];
      $g_iCartType = getCartType();
  }
  // Pinnacle Cart
  elseif ($g_iCartType == 2)
  {
      require_once(dirname(__FILE__) . '/content/engine/engine_config.php');
      define('DB_SERVER', DB_HOST);
      define('DB_DATABASE', DB_NAME);
      define('DB_SERVER_USERNAME', DB_USER);
      define('DB_SERVER_PASSWORD', DB_PASSWORD);
      $g_sCartHost = $_SERVER['SERVER_NAME'];
  }
  // Magento
  elseif ($g_iCartType == 3)
  {
      parseMagentoDbConfig();
      $g_sCartHost = $_SERVER['SERVER_NAME'];
  }
  // CubeCart
  elseif ($g_iCartType == 4) 
  {
	  require_once(dirname(__FILE__) . '/includes/global.inc.php');
	  define('DB_SERVER', $glob['dbhost']);
      define('DB_DATABASE', $glob['dbdatabase']);
      define('DB_SERVER_USERNAME', $glob['dbusername']);
      define('DB_SERVER_PASSWORD', $glob['dbpassword']);
      $g_sCartHost = $_SERVER['SERVER_NAME'];
  }
}
ob_end_clean();
function parseMagentoDbConfig()
{
    $config_file = file_get_contents(dirname(__FILE__) . '/app/etc/local.xml');
    $p = xml_parser_create();
    xml_parse_into_struct($p, $config_file, $vals, $index);
    xml_parser_free($p);
    
    foreach($index['ACTIVE'] as $k => $ind) {
        if (intval($vals[$ind]['value']) == 1) {
            define('DB_SERVER', $vals[ $index['HOST'][$k] ]['value']);
            define('DB_SERVER_USERNAME', $vals[ $index['USERNAME'][$k] ]['value']);
            define('DB_SERVER_PASSWORD', $vals[ $index['PASSWORD'][$k] ]['value']);
            define('DB_DATABASE', $vals[ $index['DBNAME'][$k] ]['value']);          
            break;
        }
    }
    
    
}

if ($g_iCartType == 1)
{
    // workaround for X-Cart cleaner
    foreach ($HTTP_GET_VARS['xcartbackuparray=xcartbackuparray'] as $k => $v) {
            $k_real = 'xcartbackuparray=' . $v;
            $$v = $HTTP_GET_VARS[$k_real];
            unset($HTTP_GET_VARS[$k_real]);
        }
        
    $_REQUEST = array_merge(
		isset($HTTP_GET_VARS) && is_array($HTTP_GET_VARS) ? $HTTP_GET_VARS : array(),  
		isset($HTTP_POST_VARS) && is_array($HTTP_POST_VARS) ? $HTTP_POST_VARS : array(), 
		isset($HTTP_COOKIE_VARS) && is_array($HTTP_COOKIE_VARS) ? $HTTP_COOKIE_VARS : array() );

}


// Disabling magic quotes at runtime
if (get_magic_quotes_runtime() || get_magic_quotes_gpc()) {
   function stripslashes_deep($value)
   {
       $value = is_array($value) ?
                   array_map('stripslashes_deep', $value) :
                   stripslashes($value);

       return $value;
   }

    if ($g_iCartType == 0)
    {
            $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    }

   $_POST = array_map('stripslashes_deep', $_POST);
   $_GET = array_map('stripslashes_deep', $_GET);
   $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}

if ($g_iCartType == 1)
{
    $_REQUEST = $_GET + $_POST + $_COOKIE;

    define('DB_DATABASE', $sql_db);
    define('DB_SERVER_USERNAME', $sql_user);
    define('DB_SERVER_PASSWORD', $sql_password);
    define('DB_SERVER', $sql_host);
    define('XCART_START', true );
}


$task = $_REQUEST["task"];
$sql = $_REQUEST["sql"];
$filename = $_REQUEST["filename"];
$position = $_REQUEST["position"];
$package_size = 1048576;

switch ($task) {
    case "get_sql":
        get_sql();
    break;
    
    case "get_sql_file":
        get_sql_file($filename, $position);
    break;

    case "put_sql":
        put_sql($import_handle);
    break;

    case "get_version":
        get_version($version);
    break;

    case "get_config":
        get_config();
    break;
    
    default:
        die();
    break;
}



/************************************************************************
* Returns dump
************************************************************************/
function get_sql() {
    global $temporary_dir,$allow_compression, $package_size;
    $dump = new cMySQLBackUp($temporary_dir,'',$allow_compression, $package_size);
//    $dump->sDBName = DB_DATABASE;

    $dump->_clearAll("m1bridge_");

    $dump->create();

    $dump->download($dump->sBackUpFile.'.'.$dump->sBackUpFileExt);

    $dump->_clearAll("m1bridgetmp_");

    //@unlink($dump->sBackUpDir.'/'.$dump->sBackUpFile);    
}

/**
 * **********************************************************************
 *
 * @param string $filename shows filename what to read from tmp directory;
 * @param integer $position shows position of package needed in filename;
 */
function get_sql_file($filename, $position)
{
        global $errors, $temporary_dir;
        
        $filename = $temporary_dir . "/" . $filename;
        
        if (!file_exists($filename))
    {                
        generate_error($errors['temporary_file_exist_not']); // generating error
    }

    if (!is_readable($filename))
    {
        generate_error($errors['temporary_file_readable_not']); // generating error
    }
        
        get_file_part($filename, $position);
        
}

/************************************************************************
* Function try to determine current cart type (X-Cart or osCommerce)
*
* Return values:
*    0  - osCommerce
*    1  - X-Cart
*    2  - Pinnacle
*   -1  - unknown cart type
************************************************************************/
function getCartType()
{
    $iCartType = -1;
    // Determine if exists file './includes/configure.php'
    if ( is_dir("./includes") && is_file("./includes/configure.php") )
    // osCommerce cart type
    {
        $iCartType = 0;
    }
    elseif ( file_exists("./config.php") )
    // X-Cart cart type
    {
        $iCartType = 1;
    }
    // Pinnacle Cart (alexerm)
    elseif ( file_exists(dirname(__FILE__) . "/content/engine/engine_config.php") )
    {
        $iCartType = 2; 
    }
    // Magneto (alexerm)
    elseif ( file_exists(dirname(__FILE__) . "/app/Mage.php" ) )
    {
        $iCartType = 3;
    }
	// CubeCart 
	elseif ( file_exists(dirname(__FILE__) . '/includes/global.inc.php') ) {
	    $iCartType = 4;
	}
    return $iCartType;
}

/************************************************************************
* Executes SQL
************************************************************************/
function get_config()
{
    global $temporary_dir;
    $sConf = array(
                                'database_host' => ((DB_SERVER == 'localhost') ? $GLOBALS['g_sCartHost'] : DB_SERVER ),
                                'database_name' => DB_DATABASE,
                                'database_username' => DB_SERVER_USERNAME,
                                'database_password' => DB_SERVER_PASSWORD,
                                'store_url' => HTTP_SERVER.DIR_WS_HTTP_CATALOG,
                                'php_version' => phpversion(),
                                'mysql_server_version' => mysql_get_server_info(),
                                'gzip' => intval(extension_loaded('zlib')),
                                'temp_dir_writable' => intval(is_writeable($temporary_dir)),
    );

    echo "0\r\n";
    foreach ($sConf as $key=>$val) {
        echo "$key=$val<br>\r\n";
    }
}

/************************************************************************
* Executes SQL
************************************************************************/
function put_sql($import_handle) {
    global $db_link, $import_handle, $sql, $allow_compression, $temporary_dir, $errors;
    
    global $temporary_dir,$allow_compression;

    $dump = new cMySQLBackUp($temporary_dir,'', $allow_compression);
    $f_name = tempnam($temporary_dir, 'm1bridge');

    if (!$f_name)
    {
        generate_error($errors['create_tmp_file']);
    }

    $import_handle = fopen($f_name, "w+");
        
    $ind = $import_handle;

    if (!$ind)
    {
        generate_error($errors['open_tmp_file']);
    }

    if ($ind)
    {
        $ind = (boolean)fputs($import_handle,$sql);
        $ind &= fclose($import_handle);
    }

    $import_handle = fopen($f_name, "r");

    if (!$import_handle)
    {
        generate_error($errors['open_tmp_file']);
    }
    
    if ($ind) 
    {
//        echo "0\r\n";
    }
    else
    {
        generate_error();
    }
        
    $content = running($import_handle);    
    
        if (strlen($content)>0) {
                echo $content;
        } else {
                echo "0\r\n";
        }
    fclose($import_handle);
    unlink($f_name);
}

        function get_file_part($fname, $position)
    {
        global $package_size, $temporary_dir;
        
        $outpustr = "";
        $fsize = filesize($fname);
        $fsize = $fsize - $position * $package_size;
        if ($fsize > $package_size)
        {
                $fsize = $package_size;
        }
        if ($fsize < 0)
        {
                $fsize = 0;
        }

        if ($fsize < $package_size)
        {
                $del = true;
        }
        else
        {
                $del = false;
        }

        if (!headers_sent())
        {
            header('Content-Length: ' . (strlen($outpustr) + $fsize));
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Pragma: no-cache');
        }
        echo $outpustr;
        
        $fp = fopen($fname, "rb");
        fseek($fp,$package_size * $position);
        $read_size = 2048;
        while ( ($read_size > 0 ) && ($package_size > 0) )
        {
                if ($package_size >= $read_size)
            {
                $package_size -= $read_size;
            }
            else
            {
                $read_size = $package_size;
                $package_size = 0;
            }
                if ($read_size == 0)
                {
                        break;
                }
            $str = fread($fp, $read_size);
            
            echo $str;
        }
        @fclose($fp);

        if ($del === true)
        {
                @unlink($fname);
        }
    }


/************************************************************************
* Returns version etc
************************************************************************/
function get_version($version) {
    echo "0\r\n";
    echo $version;
}


function generate_error($err_text = '1', $class = NULL)
{
    if ($class)
    {
        $class->_del_tmp();
    }
    echo "1";
    echo "\r\n";
    echo $err_text 
         . "
         <br>
         <a href='?task=self_test' target='_blank' style='color: #0000dd;'>Click here to run Self Test</a> or 
         <a name='error' style='color: #0000dd;' href='http://support.magneticone.com/faq.php?error=".$err_text."' target='_blank'>Click here for help</a>
         ";
    die();
}

define ('CBEL_DEBUG', 30);
define ('CBEL_WARN', 20);
define ('CBEL_ERROR', 10);
class cMySQLBackUp{

    var $sBackUpDir = '/tmp';     // directory to put back up
    var $iCurrTime;
    var $iTimeout = 15;
    var $sUniqDel = "{2C6ADA679885438688E6C9329B347146}";

    var $sBackUpFile = '';  // file name to make back up
    var $sBackUpFileExt = 'sql';// file extention to make back up
    var $bOverWrite = false;    // overwrite existing file ?

    var $bCompressed = false;   // use compression for back up
    var $sCompressor = 'gz';    // compressor type: 'bz', 'gz' ... (anything else supported by your system)
    var $iPackageSize = 0; //uses to gve files by packages

    var $bSturcture = true;  // include 'Create table definition'

    var $bData = true;      // include table data

    var $bFullInsert = false;   // create full form of 'INSERT INTO' query

    var $bDeleteBefore = true;  // insert 'DELETE TABLE' directive

    var $sDBHost = DB_SERVER; // database host to connect
    var $sDBUser = DB_SERVER_USERNAME;      // database user login to connect
    var $sDBPwd  = DB_SERVER_PASSWORD;      // database user pasword to connect
    var $rLink   = 0;         // DB link id

    var $sDBName = DB_DATABASE;      // database to back up

    var $aTables = array();  // tables to back up

    var $sReportLevel = CBEL_ERROR;  // reporting level (CBEL_DEBUG | CBEL_WARN | CBEL_ERROR)

    var $sDateFrmt = 'm/d/y H:i:s';  // date format for get_list function
    var $_tmpfname = '';        // temporary file name
    var $_tmpfd = '';         // temporary file descriptor

    var $exclude_files = array ('index\.htm',
    'index\.php',
    '\.htaccess',
    '.\.tmp'); // file names to ignore

    var $sError = '';         // error messages
        
    /**
 * Class constructor
 *
 *
 * @param   string   back up directory
 * @param   string   back up file name
 * @param   int   log level
 *
 * @return  boolean  always true
 *
 * @access  public
 */

    function cMySQLBackUp($dir = '', $file = '', $allow_compression = false, $package_size = 0)
    {
        global $db_link;
        $this->sReportLevel = ($log_level != '' ? $log_level : $this->sReportLevel);
        $this->_report('Intializing', CBEL_DEBUG);
        $this->iCurrTime = mktime();
        
        if ($dir != '')
        {
            $this->sBackUpDir = $dir;
        }
        if ($file != '')
        {
            $this->sBackUpFile = $file;
        }
        if ($allow_compression)
        {
            $this->bCompressed = true;
            $this->sBackUpFileExt = 'gz';
        }
        if ($package_size > 0)
        {
                $this->iPackageSize = $package_size;
        }
        $this->_report('Creating tmp file', CBEL_DEBUG);
        $this->_tmpfname = tempnam($dir, "m1bridgetmp_");
                             
        if (!$this->_tmpfname)
        {                     
            generate_error('Error creating tmp file', $this);
        }

        $this->_report('Openning tmp file', CBEL_DEBUG);
            $this->_tmpfd = fopen($this->_tmpfname, "w+b");
  
         //   $import_handle = fopen($f_name, "w+");
            
        if (!$this->_tmpfd)
        {
            generate_error('Error openning tmp file. ' . $php_errormsg, $this);
        }
//        $this->sDBHost = DB_SERVER; // database host to connect
//        $this->sDBUser = DB_SERVER_USERNAME;         // database user login to connect
//        $this->sDBPwd  = DB_SERVER_PASSWORD;         // database user pasword to connect
//        $this->sDBName = DB_DATABASE;         // database to back up
        
        $this->sDBHost = (defined('USER_DB_SERVER')) ? USER_DB_SERVER : DB_SERVER; // database host to connect
        $this->sDBUser = (defined('USER_DB_SERVER_USERNAME')) ? USER_DB_SERVER_USERNAME : DB_SERVER_USERNAME;         // database user login to connect
        $this->sDBPwd  = (defined('USER_DB_SERVER_PASSWORD')) ? USER_DB_SERVER_PASSWORD : DB_SERVER_PASSWORD;         // database user pasword to connect
        $this->sDBName = (defined('USER_DB_DATABASE')) ? USER_DB_DATABASE : DB_DATABASE;

        $this->_connect();
        $db_link = $this->rLink;
        return true;
    } // end of the function


    /*******************************************************************

    public methods:
    create();
    download();

    *******************************************************************/


    /**
 * Creating a back up. This is a general function for creation of archive
 *
 *
 * @param   none
 *
 * @return  none
 *
 * @access  public
 */

    function create()
    {
        global $db_link, $errors;
        $this->rLink = $db_link;
        $this->sError = ''; // clearing error msg
        if (empty($this->sBackUpDir)) // is back up dir is set?
        { // NO
            generate_error( 'Destination directory is not set.' , $this);
        }

        if (empty($this->sBackUpFile))  // is back up file name is set?
        { // NO
            $this->_report( 'Destination file is not set. Making auto generation.' , CBEL_WARN);

            // generating name for back up file
            $this->sBackUpFile = $this->_generate_fname();
        }

        $this->_connect(); // connecting to DB

        if (sizeof($this->aTables) == 0) // do we have a list of tables to work with?
        { // NO
            $this->_get_tables(); // if not - creating it
        }

		$res = mysql_query('select @@character_set_database as charset' );
		if ($res) {
			$row = mysql_fetch_array($res);
			$this->_write_to_output("ALTER DATABASE CHARACTER SET '" . $row['charset'] . "';\n");
			$this->_write_to_output("SET NAMES 'utf8';\n\n");
		}

        for ($i=0; $i < sizeof($this->aTables); $i++)
        {                               

            if ($this->bSturcture) // should we generate structure ?
            { // YES
                $this->_generate_structure($this->aTables[$i]);
            }
            
            if ($this->bData) // should we generate data ?
            { // YES

                $this->_generate_data($this->aTables[$i]);
            }

        }               
        if (($this->bCompressed) && (function_exists("gzopen")) && (function_exists("gzread")) && (function_exists("gzwrite")) && (function_exists("gzclose"))) // should we make an archive
        { // YES
            fclose($this->_tmpfd);
            $this->_tmpfd = fopen($this->_tmpfname, "rb");
            $fp = gzopen ($this->sBackUpDir . '/' . $this->sBackUpFile . '.' . $this->sBackUpFileExt, "wb");
/*            while ($str = fread($this->_tmpfd, filesize($this->_tmpfname)))
            { 
                gzwrite($fp, $str);
            }
*/                      
            while(!feof($this->_tmpfd)) {
               gzwrite($fp,fread($this->_tmpfd,1024*512));
            }
            gzclose($fp);
        }
        else
        { // NO - creating output file
        
            if ($this->bCompressed)
            {
                $this->sBackUpFileExt = 'sql';
            }
            fclose($this->_tmpfd);
            $this->_tmpfd = fopen($this->_tmpfname, "rb");
            $fp = @fopen ($this->sBackUpDir . '/' . $this->sBackUpFile . '.' . $this->sBackUpFileExt, "w+b");
            if (!$fp)
            {
                generate_error($errors['open_tmp_file'].$php_errormsg,$this);
            }
            while ($str = fread($this->_tmpfd, 1024))
            { 
                @fwrite($fp, $str);
            }
            @fclose($fp);

        }
        $this->_exit(); // closing
    }

 

    /**
 * Download back up function
 *
 *
 * @param   string   backup file name
 *
 * @return  none
 *
 * @access  public
 */

    function download($fname)
    {
        if ($fname != '')
        {
            $this->sBackUpFile = $fname;
        }
        $fname = $this->sBackUpDir . '/' . $this->sBackUpFile;
        
        if (!file_exists($fname))
        {                
            $this->_report( 'File not exists. ', CBEL_WARN); // generating warning
        }

        if (!is_readable($fname))
        {
            $this->_report( 'File is not readable. ', CBEL_WARN); // generating warning
        }
        
        $file_size = filesize($fname);
        
        if ($this->iPackageSize > 0)
        {
                $this->_file_parts($file_size, $this->sBackUpFile);
                return true;
        }
        
        $outpustr = "0\r\n";
        if ($this->sBackUpFileExt == 'gz')
        {
            $outpustr .= '1'."\r\n";
        }
        else 
        {
            $outpustr .= '0'."\r\n";
        }
        if (!headers_sent())
        {
            header('Content-Length: ' . (strlen($outpustr) + $file_size));
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Pragma: no-cache');
        }
        echo $outpustr;
        
        $fp = fopen($fname, "rb");
        while ($str = fread($fp, 2048))
        {
            echo $str;
        }
        @fclose($fp);
        
        $this->_exit();
    }

    /*******************************************************************

    private methods:
    _report();
    _connect();
    _exit();
    _write_to_output();
    _get_tables();

    *******************************************************************/


    /**
 * Reporting function
 *
 *
 * @param   string   report string
 * @param   string   report level
 *
 * @return  none
 *
 * @access  private
 */
	var $resetLog = false;
    function _report($str, $level)
    {
		if (!$this->resetLog) {
			$log = @fopen(m1BridgeGetTempDir() . '/bridge_log.txt', 'w');
			$this->resetLog = true;
		} else {
			$log = @fopen(m1BridgeGetTempDir() . '/bridge_log.txt', 'a');
		}
		if ($log) {
			fwrite($log, "[" . date('r') ."]" . $str . "\n");
			fclose($log);
		}
        if ($level <= $this->sReportLevel)
        { 
            $this->sError .= "$str<br />\r\n";
            flush();
        } // end if			
    }
    
    function _wake_server()//sometimes prints dull info to show that sript is not dead
    {
        $curr_time = mktime();
        if ($curr_time - $this->iCurrTime > $this->iTimeout)
        {
                echo $this->sUniqDel;
                $this->iCurrTime = $curr_time;
        }
    }

    function _clearAll($prefix)//clears old files
    {
        $dir = dir($this->sBackUpDir);

        while (false !== ($entry = $dir->read())) 
        {     
                        if ($entry != '.' && $entry != '..' && substr($entry, 0, strlen($prefix)) == $prefix) {
                                @unlink($this->sBackUpDir. '/' . $entry);
                    }
                }
    }
    
    
    function _file_parts($file_size, $file_name)//get part of big file
    {
        $outpustr = "0\r\n";
        if ($this->sBackUpFileExt == 'gz')
        {
            $outpustr .= '1';
        }
        else 
        {
            $outpustr .= '0';
        }
        $outpustr .= "|";
        $div_last = $file_size % $this->iPackageSize;
        
        if ($div_last == 0)
        {
                $outpustr .= (($file_size - $div_last) / $this->iPackageSize);
        }
        else 
        {
                $outpustr .= (($file_size - $div_last) / $this->iPackageSize + 1);
        }
        $outpustr .=  "|" . $file_size;
		$res = mysql_query('select @@character_set_database as charset' );
		if ($res) {
			$row = mysql_fetch_array($res);
			$outpustr .=  "|" . $row['charset'];
		}
        $outpustr .= "\r\n" . $file_name . "\r\n";
        if (!headers_sent())
        {
            header('Content-Length: ' . (strlen($outpustr)));
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Pragma: no-cache');
        }
        echo $outpustr;
    }


    /**
 * Connection to database. This release works with MySQL
 *
 *
 * @param   none
 *
 * @return  none
 *
 * @access  private
 */

    function _connect()
    {

        $this->_report('Connecting to database', CBEL_DEBUG);
        if ($allow_pconnect = ini_get("mysql.allow_persistent"))
        {
            $this->rLink = mysql_pconnect($this->sDBHost, $this->sDBUser, $this->sDBPwd); // connecting to MySQL
        }
        else 
        {
            $this->rLink = mysql_connect($this->sDBHost, $this->sDBUser, $this->sDBPwd); // connecting to MySQL
        }

        if (!$this->rLink) // is everything OK (check for connection)?
        { // NO
            $str = sprintf("Error connecting to database. MySQL said: %s, error #%s", mysql_error(), mysql_errno());
            generate_error($str,$this);
        }
        else
        { // YES
            $this->_report('Selecting database', CBEL_DEBUG); // selecting DB
            if (!(mysql_select_db($this->sDBName, $this->rLink)))  // is everything is OK?
            { // NO
                $str = sprintf("Error selecting database. MySQL said: %s, error #%s", mysql_error(), mysql_errno());
                generate_error($str,$this);
            } // end if
        } // end if..else (check for  connection)
        
		mysql_query("set names 'utf8'");
    }


    function _exit()
    {
        $this->_del_tmp();
        //  exit;
    }

    function _del_tmp()
    {
        if (!is_null($this->_tmpfd))
        {
            @fclose($this->_tmpfd);
            @unlink($this->_tmpfname);
        }
    }


    /**
 * Writes string to temporary file
 *
 *
 * @param   none
 *
 * @return  none
 *
 * @access  private
 */

    function _write_to_output($out)
    {
        $rez = fwrite($this->_tmpfd, $out);
    }


    /**
 * Getting list of tables in database and putting it into $this->aTables array
 *
 *
 * @param   none
 *
 * @return  none
 *
 * @access  private
 */

    function _get_tables()
    {

        $this->_report('Selecting tables', CBEL_DEBUG);
        $res = @mysql_query("SELECT VERSION() as mysql_version");
        if ($res)
            $rowVersion = mysql_fetch_assoc($res);
            
        if (version_compare($row['mysql_version'], '5.0.1', '<'))
        {
            $result = @mysql_list_tables($this->sDBName); // getting list of tables
        } else { 
            $result = @mysql_query("SHOW FULL TABLES FROM " . $this->sDBName . " WHERE Table_type = 'BASE TABLE'");
        }

        $arr_exclude_db_tables = explode(';', (string)$GLOBALS['exclude_db_tables']);
        $quoted_tbls = array();
        foreach($arr_exclude_db_tables as $tbl)
        {
            $tbl = preg_quote($tbl, '/');
            $tbl = str_replace('\*', '.*', $tbl);
            $tbl = str_replace('\?', '?', $tbl);
            $quoted_tbls[] = '^' . $tbl . '$';
        }
        $tables_exclude_pattern = implode('|', $quoted_tbls);
        
        if ($GLOBALS['include_db_tables'] != '') {
            $tbl_prefix = preg_quote($GLOBALS['include_db_tables'], '/');
            $tbl_prefix = str_replace('\*', '.*', $tbl_prefix);
            $tbl_prefix = str_replace(';', '|', $tbl_prefix);
            $tbl_prefix = str_replace('\?', '?', $tbl_prefix);
            $tables_include_pattern = '^' . $tbl_prefix .'$';
        } else {
            $tables_include_pattern = '.*';
        }
        
        if (!$result)  // is everything is OK? (we got list)
        { // NO
            generate_error('Error selecting tables. ' . mysql_error(),$this);
        }
        else
        { // YES
            while ($row = @mysql_fetch_row($result))
            {
                if (preg_match('/' . $tables_exclude_pattern . '/', $row[0]))
                    continue;
                    
                if (preg_match('/' . $tables_include_pattern . '/', $row[0]))
                    $this->aTables[] = $row[0];
            }
        } // end if..else (we got list)

        @mysql_free_result($result); // freeing result
    }


    /**
 * Creating a structure definition of a table
 *
 *
 * @param   sting   table name for creating stucture
 *
 * @return  none
 *
 * @access  public
 */

    function _generate_structure($tblname)
    {
        $aKeys = array();
        //  !!! Retrieve general information !!!
        $this->_report( 'Generating structure for ' . $tblname , CBEL_DEBUG);
        $result = @mysql_query("DESCRIBE `$tblname`", $this->rLink); // getting general
        // info about table
        if (!$result) // query is successfull ?
        { // NO
            $this->_connect();
            
            $this->_report( 'Generating structure for ' . $tblname , CBEL_DEBUG);
            $result = @mysql_query("DESCRIBE `$tblname`", $this->rLink); // getting general
            if (!$result) {
                generate_error('Can`t get main strucutre from ' . $tblname . '. ' . mysql_error($this->rLink),$this);
            }
        }
        $result = mysql_query("SHOW CREATE  TABLE `$tblname`", $this->rLink);
        $aRow = mysql_fetch_array($result);
        $sOutput = "#\r\n### TABLE STRUCTURE FOR `$tblname`###\r\n#\n\n";
        if ($this->bDeleteBefore)
        {
            $sOutput .= "DROP TABLE IF EXISTS `$tblname`;\r\n";
        }
        $sOutput .= $aRow[1] . "; \r\n";
        $this->_write_to_output($sOutput); // writing to output file
    }

    /**
 * Generates data query for a table
 *
 *
 * @param   string table name
 *
 * @return  none
 *
 * @access  private
 */


    function _generate_data($tblname){

        $aKeys = array();

        $this->_report( 'Inserting data to '.$tblname , CBEL_DEBUG);
        $sOutput = "\r\n\n#\r\n### TABLE DATA FOR `$tblname`###\r\n#\r\n\n";
        $result = mysql_query('SELECT * FROM `' . $tblname . '`', $this->rLink);
        if (!$result)
        {
            $this->_report( 'Error selecting data' , CBEL_ERROR);
            $this->_exit();
        }

        $fieldCount = mysql_num_fields($result);
        for ($i = 0; $i < $fieldCount; $i++)
        {
            $aFields[] = mysql_field_name($result, $i);
        }

        static $iCounter = 0;
        
        while ($aRow = mysql_fetch_array($result))
        {
            $iCounter++;
            if ($iCounter % 1000 == 0) {
                $this->_wake_server();
            }

            $aQData = array();

            $aData = array();
            $aKeys = array();;
            foreach ($aFields as $sField)
            {
                if (function_exists('mysql_real_escape_string')) {
                    $aData[] = " " . QOUTE_CHAR . mysql_real_escape_string($aRow[$sField]) . QOUTE_CHAR . " ";
                } else {
                   $aData[] = " " . QOUTE_CHAR . mysql_escape_string($aRow[$sField]) . QOUTE_CHAR . " ";
                }
                if ($this->bFullInsert)
                {
                    $aKeys[] = "`" . $sField . "`";
                }
            }
            $aQData[] = "(" . implode($aData, ',') . ")";
            if (sizeof($aQData) > 0)
            {
                $sOutput = "INSERT INTO `$tblname` " . (sizeof($aKeys) > 0 ? '(' . implode($aKeys, ',') . ')' : ''  ) . ' VALUES ' . implode($aQData, ",\r\n") . ";\r\n";
            }
            $this->_write_to_output($sOutput); // writing to output file    
        }

        return true;
    }


    /*******************************************************************

    (de) commpression functions

    *******************************************************************/


    function _generate_fname()
    {
        return strtolower("m1bridge_" . $this->sDBName . '_' . date('H_i_s-d_M_Y'));
    }

}



function PMA_importRunQuery($sql = '', $full = '')
{
    global $db_link,$import_run_buffer, $go_sql, $complete_query, $display_query, $sql_query, $cfg, $my_die, $error, $reload, $finished, $timeout_passed, $skip_queries, $executed_queries, $max_sql_len, $read_multiply, $cfg, $sql_query_disabled, $db, $run_query, $is_superuser, $message, $show_error_header;
    $run_query = true;
    $read_multiply = 1;
    $import_run_buffer['sql'] = $sql;
    $import_run_buffer['full'] = $full;
    if (isset($import_run_buffer)) {
        // Should we skip something?
        if ($skip_queries > 0) {
            $skip_queries--;
        } else {
            if (!empty($import_run_buffer['sql']) && trim($import_run_buffer['sql']) != '') {
                $max_sql_len = max($max_sql_len, strlen($import_run_buffer['sql']));
                if (!$sql_query_disabled) {
                    $sql_query .= $import_run_buffer['full'];
                }
                if (!$cfg['AllowUserDropDatabase']
                    && !$is_superuser
                    && preg_match('@^[[:space:]]*DROP[[:space:]]+(IF EXISTS[[:space:]]+)?DATABASE @i', $import_run_buffer['sql'])) {
                    $message = $GLOBALS['strNoDropDatabases'];
                    $show_error_header = TRUE;
                    $error = TRUE;
                } else {
                    $executed_queries++;

                    if ($run_query) {
                        $result = mysql_query($import_run_buffer['sql'],$db_link);
                        if (!$result) {
                            $st = str_replace(';',' ',mysql_error($db_link));
                            $ret .= '<font color="#000000"><b>'.mysql_errno($db_link).'; '.$st.'</b></font>;'.$import_run_buffer['sql'].'<br>';
                        }
                        $msg = '# ';

                        if ($result === FALSE) { // execution failed
//                          if (mysql_errno() != 0) {
                            if (!isset($my_die)) {
                                $my_die = array();
                            }

                            if ($cfg['VerboseMultiSubmit']) {
                                $msg .= $GLOBALS['strError'];
                            }

                            if (!$cfg['IgnoreMultiSubmitErrors']) {
                                $error = TRUE;
                                return $ret;
                            }
                        } elseif ($cfg['VerboseMultiSubmit']) {
                            if ($a_num_rows > 0) {
                                $msg .= $GLOBALS['strRows'] . ': ' . $a_num_rows;
                            } elseif ($a_aff_rows > 0) {
                                $a_rows =
                                $msg .= $GLOBALS['strAffectedRows'] . ' ' . $a_aff_rows;
                            } else {
                                $msg .= $GLOBALS['strEmptyResultSet'];
                            }
                        }
                        if (!$sql_query_disabled) {
                            $sql_query .= $msg . "\r\n";
                        }

                        // If a 'USE <db>' SQL-clause was found and the query succeeded, set our current $db to the new one
                        if ($result != FALSE && preg_match('@^[\s]*USE[[:space:]]*([\S]+)@i', $import_run_buffer['sql'], $match)) {
                            $db = trim($match[1]);
                            $db = trim($db,';'); // for example, USE abc;
                            $reload = TRUE;
                        }

                        if ($result != FALSE && preg_match('@^[\s]*(DROP|CREATE)[\s]+(IF EXISTS[[:space:]]+)?(TABLE|DATABASE)[[:space:]]+(.+)@im', $import_run_buffer['sql'])) {
                            $reload = TRUE;
                        }
                    } // end run query
                } // end if not DROP DATABASE
            } // end non empty query
            elseif (!empty($import_run_buffer['full'])) {
                if ($go_sql) {
                    $complete_query .= $import_run_buffer['full'];
                    $display_query .= $import_run_buffer['full'];
                } else {
                    if (!$sql_query_disabled) {
                        $sql_query .= $import_run_buffer['full'];
                    }
                }
            }
            // check length of query unless we decided to pass it to sql.php
            if (!$go_sql) {
                if ($cfg['VerboseMultiSubmit'] && !empty($sql_query)) {
                    if (strlen($sql_query) > 50000 || $executed_queries > 50 || $max_sql_len > 1000) {
                        $sql_query = '';
                        $sql_query_disabled = TRUE;
                    }
                } else {
                    if (strlen($sql_query) > 10000 || $executed_queries > 10 || $max_sql_len > 500) {
                        $sql_query = '';
                        $sql_query_disabled = TRUE;
                    }
                }
            }
        } // end do query (no skip)
    } // end buffer exists

    // Do we have something to push into buffer?
    if (!empty($sql) || !empty($full)) {
        $import_run_buffer = array('sql' => $sql, 'full' => $full);
    } else {
        unset($GLOBALS['import_run_buffer']);
    }
    
    return $ret;
}

function PMA_importGetNextChunk($size = 32768)
{
    global $import_file, $import_text, $finished, $compression, $import_handle, $offset, $charset_conversion, $charset_of_file, $charset, $read_multiply, $read_limit;
    
    $compression = 'none';
    
    // Add some progression while reading large amount of data

    // We can not read too much
    if ($finished) {
        return TRUE;
    }

    switch ($compression) {
        case 'none':
            $result = fread($import_handle, $size);
            $finished = feof($import_handle);
            break;
    }
    $offset += $size;

    if ($charset_conversion) {
        return PMA_convert_string($charset_of_file, $charset, $result);
    } else {
        // Skip possible byte order marks (I do not think we need more
        // charsets, but feel free to add more, you can use wikipedia for
        // reference: <http://en.wikipedia.org/wiki/Byte_Order_Mark>)
        // @TODO: BOM could be used for charset autodetection
        if ($offset == $size) {
            // UTF-8
            if (strncmp($result, "\xEF\xBB\xBF", 3) == 0) {
                $result = substr($result, 3);
            // UTF-16 BE, LE
            } elseif (strncmp($result, "\xFE\xFF", 2) == 0 || strncmp($result, "\xFF\xFE", 2) == 0) {
                $result = substr($result, 2);
            }
        }
        return $result;
    }
}

function running($import_handle)
{
    global $finished;
    $buffer = '';
    // Defaults for parser
    $sql = '';
    $start_pos = 0;
    $i = 0;
    
    if (isset($_POST['sql_delimiter'])) {
        $sql_delimiter = $_POST['sql_delimiter'];
    } else {
        $sql_delimiter = '/*DELIMITER*/';
        //$sql_delimiter = ';/*DELIMITER*/'; // Ruslan - should be without leading ;
        //$sql_delimiter = ';';
    }

    // Handle compatibility option
    if (isset($_REQUEST['sql_compatibility'])) {
        PMA_DBI_try_query('SET SQL_MODE="' . $_REQUEST['sql_compatibility'] . '"');
    }
    while (!($finished && $i >= $len) && !$error && !$timeout_passed) {
        $data = PMA_importGetNextChunk();
        
        if ($data === FALSE) {
            // subtract data we didn't handle yet and stop processing
            $offset -= strlen($buffer);
            break;
        } elseif ($data === TRUE) {
            // Handle rest of buffer
        } else {
            // Append new data to buffer
            $buffer .= $data;
            
            // Do not parse string when we're not at the end and don't have ; inside
            if ((strpos($buffer, $sql_delimiter) === FALSE) && !$finished) {
                continue;
            }
            $pos = strrpos($buffer,$sql_delimiter);
                $sql_queries = explode($sql_delimiter,$buffer);  
                $c_queries = count($sql_queries);
                if (!$finished) {
                        $buffer = $sql_queries[$c_queries-1];
                        $sql_queries = array_splice($sql_queries,0,$c_queries-1);
                }
                    
                foreach ($sql_queries as $query)
                {
                        if (strlen($query)!=0) {
                                $ret .= PMA_importRunQuery($query,'');
                        }
                }
        }

    }
    
    return $ret;
}



/**
 * Temporary directory path autodiscovery
 *
 * @author alexerm
 * @return string|bool Temporary directory path or false if can't determine it 
 */
function m1BridgeGetTempDir()
{
		$commonTempDir = dirname(__FILE__) . '/tmp/';
		if (is_dir($commonTempDir) && is_writable($commonTempDir) && is_executable($commonTempDir)) {
			return $commonTempDir;
		}

        global $xcart_dir;
        $dir_tmp1 = './tmp';
        $dir_tmp2 = './temp';
        $dir_tmp3 = '../tmp';
        $dir_tmp4 = '../temp';
        $dir_oscommerce = DIR_FS_CATALOG . 'images/';
        $dir_creloaded = DIR_FS_CATALOG . 'tmp/';
        $dir_zencart = DIR_FS_CATALOG . 'cache/';
        if ( null != $xcart_dir) {
                $dir_xcart1 = $xcart_dir . '/var/tmp/';
                $dir_xcart2 = $xcart_dir . '/templates_c/';
        }

        if (is_dir($dir_oscommerce) && is_writable($dir_oscommerce)) {
                $temporaryDir = $dir_oscommerce;
        } elseif (is_dir($dir_tmp1) && is_writable($dir_tmp1)) {
                $temporaryDir = $dir_tmp1;
        } elseif (is_dir($dir_tmp2) && is_writable($dir_tmp2)) {
                $temporaryDir = $dir_tmp2;
        } elseif (is_dir($dir_tmp3) && is_writable($dir_tmp3)) {
                $temporaryDir = $dir_tmp3;
        } elseif (is_dir($dir_tmp4) && is_writable($dir_tmp4)) {
                $temporaryDir = $dir_tmp4;
        } elseif (is_dir($dir_creloaded) && is_writable($dir_creloaded)) {
                $temporaryDir = $dir_creloaded;
        } elseif (is_dir($dir_zencart) && is_writable($dir_zencart)) {
                $temporaryDir = $dir_zencart;
        } elseif (is_dir($dir_xcart1) && is_writable($dir_xcart1)) {
                $temporaryDir = $dir_xcart1;
        } elseif (is_dir($dir_xcart2) && is_writable($dir_xcart2)) {
                $temporaryDir = $dir_xcart2;
        } elseif (ini_get('open_basedir') == null) {
                // Get global temporary directory
                if (!empty($_ENV['TMP'])) {
                        $temporaryDir = $_ENV['TMP'];
                } elseif (!empty($_ENV['TMPDIR'])) {
                        $temporaryDir = $_ENV['TMPDIR'];
                } elseif (!empty($_ENV['TEMP'])) {
                        $temporaryDir = $_ENV['TEMP'];
                } elseif (!empty($_ENV['windir'])) { // temporary dir under windows
                        $temporaryDir = $_ENV['windir'];
                } elseif (ini_get('session.save_path') != null && is_dir(ini_get('session.save_path')) && is_writable(ini_get('session.save_path')) ) {
                        $temporaryDir = ini_get('session.save_path');           
                } elseif (is_writable('/tmp') && is_dir('/tmp')) {
                        $temporaryDir = '/tmp';
                } elseif (is_dir(dirname(tempnam('', 'na'))) && is_writable(dirname(tempnam('', 'na')))) {
                        $temporaryDir = dirname(tempnam('', 'na'));
                } else {
                        $temporaryDir = '/tmp';
                }
        }
        return realpath($temporaryDir);
}

/**
 * Run Tests of main bridge functionality
 *
 */
function run_self_test()
{
    if (!function_exists('curl_init')) {
        echo '<b>PHP CURL extension is disabled.</b><br />Please contact your hosting provider to enable PHP CURL extension. It is necessary for bridge self test only.';
        return;
    }

    $html .= '<h2>Bridge.php Self Test Tool</h2>';
    $html .= '<div style="padding: 5px; margin: 10px 0;">This tool checks your website to make sure there are no problems in your hosting configuration.<br />
                                        Your hosting support can solve all problems found here.</div>';
    $html .= '<table cellpadding=2>'
           . '<tr>'
           . '<th>Test Title</th>'
           . '<th>Result</th>'
           . '</tr>';
    
	$html .= '<tr><td>Bridge Version</td><td>' . $GLOBALS['version'] . '</td><td></td></tr>';
	
    $html .= '<tr><td>Temporary Directory Exists and Writable</td><td>'
           . (( $res = test_temp_directory() ) ? TEST_YES : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td width=450>Create temporary dir ' . dirname(__FILE__) . '/tmp and set permissions to write</td>';

    $html .= '<tr><td>Temporary Directory has enough free space </td><td>'
           . (( $res = test_temp_free_space() ) ? TEST_YES : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td>Delete unused and temporary files or request more disk space from your hosting provider.</td>';
            
    $html .= '<tr><td><a href="http://www.google.com/search?hl=en&q=post_max_size+php+&aq=f&aqi=&aql=&oq=&gs_rfai=" target="_blank">Post Maximum Size</a> </td><td>'
           . ini_get('post_max_size') . '</td>'; 

    if (!$res)
        $html .= '<td>Delete unused and temporary files or request more disk space from your hosting provider.</td>';    
        
    $html .= '<tr><td><a href="http://php.net/manual/en/book.zlib.php" target="_blank">Zlib PHP Extension</a> Loaded</td><td>'
           . (( $res = test_is_gz_avaliable() ) ? TEST_YES : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td>Ask your hosting provider to enable Zlib php extension</td>';
        
    $html .= '<tr><td><a href="http://www.modsecurity.org/documentation/modsecurity-apache/2.5.12/modsecurity2-apache-reference.html" target="_blank">Apache mod_security</a> Disabled</td><td>'
           . (( $res = test_apache_mod_security() ) ? TEST_YES : test_is_cgi_mode() == true ? TEST_SKIP : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td>Ask your hosting provider to disable mod_security extension for bridge.php</td>';

    $html .= '<tr><td><a href="http://www.hardened-php.net/suhosin/" target="_blank">Suhosin PHP extension</a> Disabled</td><td>'
           . (( $res = !test_suhosin_extension_loaded() ) ? TEST_YES : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td>Ask your hosting provider to disable Suhosin extension for bridge.php 
                        or set suhosin.post.max_value_length equal 1048576 (currently ' . intval(ini_get('suhosin.post.max_value_length')) . ')
        </td>';
               
    $html .= '<tr><td>Default Login and Password Changed</td><td>'
           . (( $res = test_default_password_is_changed() ) ? TEST_YES : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td>Change your login credentials in bridge.php to make your connection secure</td>';
        
    $html .= '<tr><td><a href="http://php.net/manual/en/security.magicquotes.php" target="_blank">Magic Quotes GPC/Runtime</a> disabled</td><td>'
           . (( $res = test_magic_quotes() ) ? TEST_YES : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td>Ask your hosting provider to disable Magic Quotes php extension</td>';
           
    $html .= '<tr><td><a href="http://www.google.com/search?hl=en&q=post+multipart+form-data&aq=f&aqi=g2g-m8&aql=&oq=&gs_rfai=" target="_blank">Post (multipart/form-data)</a> Allowed</td><td>'
           . (( $res = test_post_to_self() ) ? TEST_YES : TEST_NO) . '</td>'; 

    if (!$res)
        $html .= '<td>Method POST is not allowed. Please check your Apache configuration or contact your hosting provider to solve this problem.</td>';       
    
        
        $html .= '<tr><td><b>Database Permissions Check</b></td><td></td></tr>';
    
    
    $html .= '<tr><td>Create Table</td><td>'
           . (( $res = test_create_table() ) ? TEST_OK : TEST_FAIL) . '</td>'; 

    if (!$res)
        $html .= '<td>Error details: "' . $GLOBALS['testResult'] . '"</td>';       
           
    $html .= '<tr><td>Insert Data Row</td><td>'
           . (( $res = test_insert_row() ) ? TEST_OK : TEST_FAIL) . '</td>'; 

    if (!$res)
        $html .= '<td>Error details: "' . $GLOBALS['testResult'] . '"</td>';       
           
    $html .= '<tr><td>Update Data Row</td><td>'
           . (( $res = test_update_row() ) ? TEST_OK : TEST_FAIL) . '</td>'; 

    if (!$res)
        $html .= '<td>Error details: "' . $GLOBALS['testResult'] . '"</td>';       
           
    $html .= '<tr><td>Delete Data Row</td><td>'
           . (( $res = test_delete_row() ) ? TEST_OK : TEST_FAIL) . '</td>'; 

    if (!$res)
        $html .= '<td>Error details: "' . $GLOBALS['testResult'] . '"</td>';       
           
    $html .= '<tr><td>Drop Table</td><td>'
           . (( $res = test_drop_table() ) ? TEST_OK : TEST_FAIL) . '</td>'; 

    if (!$res)
        $html .= '<td>Error details: "' . $GLOBALS['testResult'] . '"</td>';       
           
    $html .= '</table><br /><br />';
	$html .= '<a href="?phpinfo">More information about your PHP configuration</a><br /><br />';
	$html .= 'Log file path: ' . m1BridgeGetTempDir() . '/bridge_log.txt <br /><br />';
    $html .= '<div style="margin-top: 15px; font-size: 13px;">PHP MySQL Bridge by <a href="http://magneticone.com" target="_blank" style="color: #15428B">MagneticOne</a></div>';
    echo $html;
}

function test_magic_quotes() {
    $test_magic_quotes_gpc      = true;
    $test_magic_quotes_runtime  = true;

    if (function_exists('get_magic_quotes_gpc')) {
      $test_magic_quotes_gpc =  !get_magic_quotes_gpc();
    }

    if (function_exists('get_magic_quotes_runtime')) {
      $test_magic_quotes_runtime =  !get_magic_quotes_runtime();
    }

    return $test_magic_quotes_runtime && $test_magic_quotes_runtime;
}

/**
 * Check if temporary dir is directory and is writable
 *
 * @return bool
 */
function test_temp_directory()
{
    $temp_dir = m1BridgeGetTempDir();
    $GLOBALS['testResult'] = $temp_dir;
    return (is_dir($temp_dir) && is_writable($temp_dir) && @dir($temp_dir)) && is_executable($temp_dir);
}

/**
 * Check if temporary dir disk has enough
 *
 * @return bool
 */
function test_temp_free_space()
{
    $temp_dir = m1BridgeGetTempDir();
    $freespace = (int)(disk_free_space($temp_dir)/1024/1024); // in MB
    $GLOBALS['testResult'] = $freespace;
    return $freespace >= 0;
}

/**
 * Check if mod_security is not enabled
 *
 * @return bool
 */
function test_apache_mod_security()
{
    if (function_exists('apache_get_modules'))
    {
        $apache_modules = apache_get_modules();
        return !in_array('mod_security', $apache_modules);
    } else {
        return false;
    }
}


function test_is_cgi_mode() {
    if ((function_exists('apache_get_modules') && apache_get_modules() == null) || (!function_exists('apache_get_modules'))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check if Suhosin Hardened-PHP security extension is loaded
 *
 * @return unknown
 */
function test_suhosin_extension_loaded()
{
    return in_array('suhosin', get_loaded_extensions()) && intval(ini_get('suhosin.post.max_value_length')) < 1048576;
}

/**
 * Try to create table
 * Using post to self script
 * 
 * @return bool True if table created, false if not
 */
function test_create_table()
{
    $result = st_post(array('sql' => 'CREATE TABLE bridge_some_new_table (id INT PRIMARY KEY AUTO_INCREMENT, val INT)'), 'task=put_sql&hash=' . st_gethash());
    $GLOBALS['testResult'] = $result;
    return trim($result) === '0';
}

/**
 * Try to insert data into table
 * Using post to self script
 * 
 * @return bool True if data inserted, false if not
 */
function test_insert_row()
{
    $result = st_post(array('sql' => 'INSERT INTO bridge_some_new_table (`val`) VALUES (15)'), 'task=put_sql&hash=' . st_gethash());
    $GLOBALS['testResult'] = $result;
    return trim($result) === '0';
}

/**
 * Try to update data in table
 * Using post to self script
 * 
 * @return bool True if data updated, false if not
 */
function test_update_row()
{
    $result = st_post(array('sql' => 'UPDATE bridge_some_new_table SET val = 21'), 'task=put_sql&hash=' . st_gethash());
    $GLOBALS['testResult'] = $result;
    return trim($result) === '0';
}

/**
 * Try to delete data from table
 * Using post to self script
 * 
 * @return bool True if data deleted, false if not
 */
function test_delete_row()
{
    $result = st_post(array('sql' => 'DELETE FROM bridge_some_new_table'), 'task=put_sql&hash=' . st_gethash());
    $GLOBALS['testResult'] = $result;
    return trim($result) === '0';
}

/**
 * Try to drop table
 * Using post to self script
 * 
 * @return bool True if table dropped, false if not
 */
function test_drop_table()
{
    $result = st_post(array('sql' => 'DROP TABLE bridge_some_new_table'), 'task=put_sql&hash=' . st_gethash());
    $GLOBALS['testResult'] = $result;
    return trim($result) === '0';
}

/**
 * Try to post request to self script
 * 
 * @return bool True if data successfully posted and response recieved, false if not
 */
function test_post_to_self()
{
    $result = st_post(array(), 'task=test_post');
    return $result === TEST_POST_STRING;
}

function test_is_gz_avaliable()
{
    return extension_loaded('zlib');
}

/**
 * Check if default password was changed to another one
 *
 * @return bool True if login or password defer with default
 */
function test_default_password_is_changed()
{
    return ! ( $GLOBALS['username'] == '1' && $GLOBALS['password'] == '1' );
}

/**
 * Get auth hash
 *
 * @return string MD5 encoded hash key
 */
function st_gethash() 
{
    return md5($GLOBALS['username'].$GLOBALS['password']);
}

/**
 * Send multipart post to current script
 *
 * @param array $data POST parameters
 * @param string $params GET parameters
 * @return string Response string
 */
function st_post($data, $params)
{
     $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?' . $params);  
     curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     $postResult = curl_exec($ch);
     curl_close($ch);
     return $postResult;
}
?>
