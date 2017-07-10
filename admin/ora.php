<html>
<head>
<title>TinySlider - JavaScript Slideshow</title>
<meta http-equiv="Refresh" content="0; URL=<?php echo $redirectUrl; ?>">
</head>
<body>
<?php

$db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
$db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
$db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
$db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

if ($conn = oci_pconnect($db_user,$db_pass,$db_host . "/" . $db_sid)) {
    print 'Successfully connected to Oracle Database XE!';
    oci_close($conn);
}
else {
    $errmsg = oci_error();
    print 'Oracle connect error: ' . $errmsg['message'];
}
?>
</body>
</html>