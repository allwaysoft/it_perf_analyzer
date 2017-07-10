<?php

$TKEEPALIVE_ENABLE = "1";
$TKEEPALIVE_INTERVAL = "10";
$LOGLEVEL = "5";
$LOGFILE = "httptunnel_server.log";
$MAXLOGSIZE = "4000000";

if (!isset($_REQUEST["a"])) {

	echo "Now you can establish connections through the HTTP tunnel.";
	exit;
}

$useunix=in_array("unix", stream_get_transports());

if (version_compare("5.0.0",phpversion())==1) die ("Only PHP 5 or above supported");

error_reporting(0);
set_error_handler("myErrorHandler");

function myErrorHandler($errno, $errstr, $errfile, $errline) {
	switch ($errno) {
	case E_ERROR:
		$errfile=preg_replace('|^.*[\\\\/]|','',$errfile);
		echo "l:1 ERROR in line $errline of file $errfile: [$errno] $errstr\n";
		exit;
	}
}	

function shutdown () {
	global $ipsock, $rmsock, $outcount, $incount, $td, $te, $sockname, $useunix;

	if (connection_status() & 1) { # ABORTED
		logline (1, $_SERVER["REMOTE_ADDR"].": Irregular tunnel disconnect -> disconnecting server");
		logline (2, $_SERVER["REMOTE_ADDR"].": Sent ".$outcount." bytes, received ".$incount." bytes");
	} elseif (connection_status() & 2) { # TIMEOUT
		logline (1, $_SERVER["REMOTE_ADDR"].": PHP script timeout -> disconnecting server");
		logline (2, $_SERVER["REMOTE_ADDR"].": Sent ".$outcount." bytes, received ".$incount." bytes");
	}
	
	if ($ipsock) fclose($ipsock);
	if ($rmsock) fclose($rmsock);
	if ($_REQUEST["a"]=="c" && $useunix && $sockname && file_exists($sockname)) {unlink ($sockname);}
}

function openRemote ($http_user, $http_pass) {
	global $copts,$dad,$dpo,$bad,$bpo,$usock, $rmsock, $ident;

	$s=$dad;
	$p=$dpo;
	$rmsock = stream_socket_client("tcp://$s:$p", $errno, $errstr);
	if (!$rmsock) return "TCP stream_socket_client(tcp://$s:$p) failed: reason: $errstr";
	$bad=preg_replace('/:.*$/','',stream_socket_get_name($rmsock,false));
	$bpo=preg_replace('/^.*?:/','',stream_socket_get_name($rmsock,false));
	stream_set_blocking($rmsock,1);

	return "";
}

function logline ($ll,$msg) {
	global $LOGLEVEL, $LOG, $MAXLOGSIZE, $LOGFILE;	
	if ($LOGLEVEL and $ll<=$LOGLEVEL) {
		$LOG=fopen ($LOGFILE, "a");
		if ($LOG) {			
			fwrite ($LOG, date("d.m.Y H:i:s")." - $msg\r\n");
			$lstat=fstat($LOG);
			if ($lstat["size"]>$MAXLOGSIZE) rotatelog();
			fclose($LOG);
		}
	}
}

function rotatelog() {
	global $MAXLOGSIZE, $LOGFILE, $LOG, $LOGLEVEL,$logrtry;
	fwrite ($LOG, date("d.m.Y H:i:s")." - Logfile reached maximum size ($MAXLOGSIZE)- rotating.\r\n");
	fclose ($LOG);
	rename ($LOGFILE,"$LOGFILE.old");
	$LOG=fopen ($LOGFILE, "a");
	if (!$LOG) $LOGLEVEL=0;
	else fwrite ($LOG, date("d.m.Y H:i:s")." - Opening new Logfile.\r\n");
}

function bin2txt ($ret) {
	global $LOGLEVEL;
	if ($LOGLEVEL>=4)
		return preg_replace_callback('/[[:cntrl:]\x80-\xFF]/','rep_cb',$ret);
	else
		return preg_replace('/([[:cntrl:]\x80-\xFF])/','.',$ret);
}

function myfwrite ($fd,$buf) {
	$i=0;
	while ($buf != "") {
		$i=strlen($buf);
		$i=fwrite ($fd,$buf,$i);
		if ($i==false) {
			if (!feof($fd)) continue;
			break;
		}
		$buf=substr($buf,$i);
	}
	return $i;
}

// this is for outbound data connections only - this part has been moved to the front for speed
// send data client connect?
// need the following REQUEST vars:
// a: "s"
// d: control data in the format:
//		><ipcname>\n<base64enc data>\n...
if ($_REQUEST["a"]=="s") {
	$ident='';
	$ipsock=0;
	$ret="";
	
	// we need to split these up
	
	$time_start = microtime(1);
	
	foreach (preg_split('/\n/',$_REQUEST["d"]) as $i) {
		$i=trim($i);
		if ($i == '') continue;
		if (preg_match('/^>(.*)$/',$i,$arr)) {
			// open a new IPC socket to send the next data to
			if ($ident == $arr[1]) continue;
			if ($ipsock) {					
				fclose($ipsock);
			}
			$ident = $arr[1];			
			preg_match('/^(([^:]+):)?([^:]+)$/',$ident,$matches);
			$port=$matches[3];
			$addr=(isset($matches[2]) && $matches[2])?$matches[2]:"127.0.0.1";
			$sockopen = "tcp://$addr:$port";				

			$time_start1 = microtime(1);
			
			while (!($ipsock = stream_socket_client($sockopen, $errno, $errstr)) &&
					preg_match('/temporarily/',$errstr)) {usleep(rand(1,200000));}

			$time_end1 = microtime(1);
			$time1 = $time_end1 - $time_start1;
			logline(4,"Line 139: while usleep: $time1");
			
			if (!$ipsock) {
				$ret.="$ident ER stream_socket_client($sockopen) failed: reason: $errstr\n";
				$ident='';
				continue;
			}
		} else {
			if (!$ipsock) continue;
			
			$time_myfwrite_start = microtime(1);

			myfwrite ($ipsock,$i."\n");

			$time_myfwrite_end = microtime(1);
			$time = $time_myfwrite_end - $time_myfwrite_start;
			logline(4,"Line 148: myfwrite: $time");

			$ret.="$ident OK\n";
		}
	}

	$time_end = microtime(1);
	$time = $time_end - $time_start;
	logline(4,"Line 155: foreach: $time");

	if ($ipsock) fclose($ipsock);
	$ipsock='';
	header("Content-Length: ".strlen($ret));
	echo $ret;
	exit;
}

// start of programm
register_shutdown_function ("shutdown");
set_time_limit(0);
ob_implicit_flush();

// no output buffering
// primary tunnel connect?
// need the following REQUEST vars:
// a: "c"
// s: remote server name
// p: remote server port
// sw: package sequence wrap value
// o: connection options (1 = zlib compressed traffic, 4 = udp)
if ($_REQUEST["a"]=="c") {

	$outcount=0;
	$incount=0;
	$sequence=0;
	$sw=$_REQUEST["sw"];
	$ka=$TKEEPALIVE_ENABLE;
	$ki=$TKEEPALIVE_INTERVAL;
	$dad=$_REQUEST["s"];
	$dpo=$_REQUEST["p"];

	// check connection options
	$copts=$_REQUEST["o"];

	$time_start = microtime(1);
	
	if (!in_array("zlib",get_loaded_extensions())) {			
		$copts &= 254;
	}
	if (!in_array("mcrypt",get_loaded_extensions()) ||
		!in_array("openssl",get_loaded_extensions())) {
		$copts &= 253;
	}

	$time_end = microtime(1);
	$time = $time_end - $time_start;
	logline(4,"Line 203: get_loaded_extensions: $time");

	$time_start = microtime(1);
	
	// open the interprocess socket
	$ipsock = stream_socket_server("tcp://127.0.0.1:0", $errno, $errstr);

	$time_end = microtime(1);
	$time = $time_end - $time_start;
	logline(4,"Line 212: stream_socket_server: $time");

	if (!$ipsock) {
		echo "c:s=ER&msg=".urlencode("stream_socket_server() failed: reason: $errstr")."\n";
		exit;
	}
	$ident=stream_socket_get_name($ipsock,false);
	$ident=preg_replace('/^.*?:/','',$ident);


	$time_start = microtime(1);
	
	stream_set_blocking($ipsock,0);

	$time_end = microtime(1);
	$time = $time_end - $time_start;
	logline(4,"Line 228: stream_set_blocking: $time");

	$time_start = microtime(1);

	// open the remote socket
	$msg=openRemote($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

	$time_end = microtime(1);
	$time = $time_end - $time_start;
	logline(4,"Line 237: openRemote: $time");

	if ($msg) {
		echo "c:s=ER&msg=".urlencode("REMOTE $msg")."\n";exit;}
	if ($rmsock) {

		$time_start = microtime(1);

		stream_set_blocking($rmsock,1);

		$time_end = microtime(1);
		$time = $time_end - $time_start;
		logline(4,"Line 249: stream_set_blocking: $time");
	}
	logline (2,	"$ident: New tunnel established ".$_SERVER["REMOTE_ADDR"]." -> $dad:$dpo");
	echo " ";
	echo "c:s=OK&o=$copts&i=$ident&sn=$bad&sp=$bpo".($copts & 2?"&k=".urlencode($symkey):"")."\n";

	// ok, we created both sockets .. now listen on both
	if ($ka) $nk=time()+$ki;
	$copts &= 239;
	while (true) {
		// set up the handles to listen on
		$rin = array($ipsock);
		if ($rmsock) {
			$rin[]=$rmsock; # listen on tcp socket if applicable
		}
		$ti=time();
		$write = $except = null;			

		$time_start = microtime(1);

		stream_select($rin, $write, $except, $ka?($nk-$ti<=0?0:$nk-$ti):null);

		$time_end = microtime(1);
		$time = $time_end - $time_start;
		logline(4,"Line 273: stream_select: $time");

		if ($ka and time()>=$nk) {
			echo "\n";
			$nk=time()+$ki;	
			continue;
		}
		
		if ($rin[0]==$ipsock) {
			// ok, we got an interprocess connecting, that means were piping the data from $ipsock to $rmsock
			if (($c_ipsock=stream_socket_accept ($ipsock))===false) {
				continue;
			}
			$inbuf='';

			$time_start = microtime(1);
			
			while (!feof($c_ipsock)) {
				$inbuf .= fread($c_ipsock, 8192);
			}

			$time_end = microtime(1);
			$time = $time_end - $time_start;
			logline(4,"Line 296: while fread: $time");

			fclose($c_ipsock);
			$inbuf=preg_replace('/\r/','',$inbuf);
			logline(4,"$ident: Got something from IPC: $inbuf");

			$time_start = microtime(1);
			
			foreach (preg_split('/\n/',$inbuf) as $i) {
				if ($i=="") continue;
				if (preg_match('/^(\d+):(.*)$/',$i,$matches)) {
					# we have data coming in .. check the sequence and send to rserver
					# drop dupes
					if (!isset($sequence_buffer[$matches[1]])) {
						$sequence_buffer[$matches[1]] = $matches[2];
						logline(4, "$ident: Got seq ".$matches[1].", expected seq $sequence");
					
						while(isset($sequence_buffer[$sequence])) {
							if (preg_match('/^c:disconnect/',$sequence_buffer[$sequence])) {
								echo "c:disconnect on request client\n";
								logline (2,"$ident: Disconnect on request client");
								logline (2,"$ident: Sent ".$outcount." bytes, received ".$incount." bytes");
								exit;
							} else {
								$buf = str_replace(' ', '+', $sequence_buffer[$sequence]);
								$buf=base64_decode($buf);
								if ($copts & 1) {
									$buf=gzinflate($buf);
								}
								unset($sequence_buffer[$sequence]);
								unset($sequence_buffer[$sequence+(($sequence-floor($sw/2))<0?$sw:0)-floor($sw/2)]);
								$i=strlen($buf);
								myfwrite($rmsock,$buf);
								logline(3,"$ident: -> ".bin2txt(substr($buf,-$i)));
								$outcount+=$i;
								$sequence++;$sequence %= $sw;
							}
						}
					} else {
						logline(2,"$ident: WARNING - Dupe package received seq ".$matches[1].", expected seq $sequence");
					}
				} else {
					echo "l:1 Got a line I could not understand: '$i'\n";
					if ($ka) $nk=time()+$ki;
				}
			}

			$time_end = microtime(1);
			$time = $time_end - $time_start;
			logline(4,"Line 345: foreach: $time");
		}

		elseif (($rmsock && $rin[0]==$rmsock) || ($usock && $rin[0]==$usock)) {
			// we got data coming in from the remote port, lets dump it to the client
			if (($rmsock && feof($rmsock)) || ($usock && feof($usock))) {
				echo "c:disconnect on request server\n";
				logline (2,"$ident: Disconnect on request server");
				logline (2,"$ident: Sent ".$outcount." bytes, received ".$incount." bytes");
				exit;
			}

			$time_start = microtime(1);

			$buf=fread($rmsock,65536);

			$time_end = microtime(1);
			$time = $time_end - $time_start;
			logline(4,"Line 363: fread: $time");

			$i=strlen($buf);

			if (!empty($buf)) {
				logline(3,"$ident: <- ".bin2txt(substr($buf,-$i)));
				$incount+=$i;
				if ($copts & 1) {
				
					$time_start = microtime(1);
					
					$buf=gzdeflate($buf);

					$time_end = microtime(1);
					$time = $time_end - $time_start;
					logline(4,"Line 386: gzdeflate: $time");
				}
				echo base64_encode($buf)."\n";
				if ($ka) $nk=time()+$ki;
			}
		}
	}
}

?>