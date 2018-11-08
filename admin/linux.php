<?php

define('EXT_JSON_READER_ROOT', 'records');
define('EXT_JSON_READER_TOTAL', 'total');
error_reporting(0);
include('includes/modules/Net/SSH2.php');

function GetCoreInformation($data) {
    $cores = array();
    foreach( $data as $line ) {
        if( preg_match('/^cpu[0-9]/', $line) )
        {
            $info = explode(' ', $line );
            $cores[] = array(
                'user' => $info[1],
                'nice' => $info[2],
                'sys' => $info[3],
                'idle' => $info[4],
                'iowait' => $info[5]
            );
        }
    }
    return $cores;
}

function GetNetInformation($data) {
    $cores = array();
    $rec = 0;
    $trans = 0;
    foreach( $data as $line ) {
        $info = explode(';', $line );
        $rec = $rec + $info[0];
        $trans = $trans + $info[1];
    }

    $cores[] = array(
        'rec' => $rec,
        'trans' => $trans
    );

    return $cores;
}

function GetDisksInformation($data) {
    $disks = array();
    foreach( $data as $line ) {
        $info = explode(';', $line );
        //var_dump($info);
        if(substr( $info[0], 0, 3 ) != "ram" && substr( $info[0], 0, 4 ) != "loop")
        {
            $disks[] = array(
                'name' => $info[0],
                'read' => $info[1],
                'write' => $info[2]
            );
        }
    }
    return $disks;
}

function GetNetUsage($stat1, $stat2) {
    $start_date = date("Y-m-d H:i:s");
    if( count($stat1) !== count($stat2) ) {
        return;
    }

    $rec = 0;
    $trans = 0;
    for( $i = 0, $l = count($stat1); $i < $l; $i++) {
        $rec = $stat2[$i]['rec'] - $stat1[$i]['rec'];
        $trans = $stat2[$i]['trans'] - $stat1[$i]['trans'];
    }

    $values = array(
        'rec' => round($rec/1024/1024),
        'trans' => round($trans/1024/1024),
        'category' => $start_date
    );

    return $values;
}

function GetCpuPercentages($stat1, $stat2) {
    $start_date = date("Y-m-d H:i:s");
    if( count($stat1) !== count($stat2) ) {
        return;
    }
    $cpus = array();
    for( $i = 0, $l = count($stat1); $i < $l; $i++) {
        $dif = array();
        $dif['user'] = $stat2[$i]['user'] - $stat1[$i]['user'];
        $dif['nice'] = $stat2[$i]['nice'] - $stat1[$i]['nice'];
        $dif['sys'] = $stat2[$i]['sys'] - $stat1[$i]['sys'];
        $dif['idle'] = $stat2[$i]['idle'] - $stat1[$i]['idle'];
        $dif['iowait'] = $stat2[$i]['iowait'] - $stat1[$i]['iowait'];
        $total = array_sum($dif);

        $cpu = array();
        foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 1);
        $cpus[$i] = $cpu;
        //$cpus[$i]['category'] = $i;
    }

    $values = array(
        'user' => 0,
        'nice' => 0,
        'sys' => 0,
        'idle' => 0,
        'iowait' => 0
    );
    foreach ($cpus as $item) {
        $values['user'] += $item['user'];
        $values['nice'] += $item['nice'];
        $values['sys'] += $item['sys'];
        $values['idle'] += $item['idle'];
        $values['iowait'] += $item['iowait'];
    }

    $values['user'] = $values['user'] / count($cpus);
    $values['nice'] = $values['nice'] / count($cpus);
    $values['sys'] = $values['sys'] / count($cpus);
    $values['idle'] = $values['idle'] / count($cpus);
    $values['iowait'] = $values['iowait'] / count($cpus);
    $values['category'] = $start_date;

    return $values;
}

function GetDiskActivity($stat1, $stat2) {
    if( count($stat1) !== count($stat2) ) {
        return;
    }
    $disks = array();
    for( $i = 0, $l = count($stat1); $i < $l; $i++) {
        $dif = array();
        $dif['name'] = $stat2[$i]['name'];
        $dif['read'] = $stat2[$i]['read'] - $stat1[$i]['read'];
        //$dif['read'] = round($dif['read']/1000);
        $dif['write'] = $stat2[$i]['write'] - $stat1[$i]['write'];
        //$dif['write'] = round($dif['write']/1000);

        $read = $dif['read'];
        $write = $dif['write'];
        $total = 1000;
        $dif['read'] = $read*100/$total;
        $dif['write'] = $write*100/$total;

        $disks[$i] = $dif;
    }
    return $disks;
}

function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
}

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    $response = null;

    switch(strtolower($action))
    {
        case 'mem_usage';
            $db_host = $_REQUEST['db_host'];
            $os_user = $_REQUEST['server_user'];
            $os_pass = $_REQUEST['server_pass'];

            $usage = array();
            $pct = 0;

            $ssh = new Net_SSH2($db_host,22,5);
            if (empty($ssh->server_identifier)) {
                $comment = 'Impossible de se connecter au serveur ' . $db_host;
                $pct = null;
            }
            else
            {
                if (!$ssh->login($os_user, $os_pass)) {
                    $comment = 'Impossible de se connecter au serveur ' . $db_host;
                    $pct = null;
                } else {
                    $ssh->disableQuietMode();
                    $ssh->setTimeout(5);

                    $start_date = date("Y-m-d H:i:s");

                    //$cmd = "cat /proc/meminfo | grep ^MemTotal | awk '{print $2}'";
                    $cmd = "cat /proc/meminfo | grep ^SwapTotal | awk '{print $2}'";
                    $total = $ssh->exec($cmd);

                    //$cmd = "cat /proc/meminfo | grep ^MemFree | awk '{print $2}'";
                    $cmd = "cat /proc/meminfo | grep ^SwapFree | awk '{print $2}'";
                    $free = $ssh->exec($cmd);
                    $used = $total - $free;
                    $pct = (100 * $used) / $total;

                    $usage = array(
                        'total' => $total,
                        'free' => $free,
                        'pct' => $pct,
                        'category' => $start_date
                    );

                    $comment = '';
                }
            }

            $ssh->disconnect();

            $response = array(EXT_JSON_READER_TOTAL => count($usage),
                EXT_JSON_READER_ROOT => $usage, 'comment' => $comment, 'pct' => $pct);

            break;

        case 'memory_usage';
            $db_host = $_REQUEST['db_host'];
            $os_user = $_REQUEST['server_user'];
            $os_pass = $_REQUEST['server_pass'];

            $usage = array();


            $ssh = new Net_SSH2($db_host,22,5);
            if (empty($ssh->server_identifier)) {
                $comment = 'Impossible de se connecter au serveur ' . $db_host;

            }
            else
            {
                if (!$ssh->login($os_user, $os_pass)) {
                    $comment = 'Impossible de se connecter au serveur ' . $db_host;

                } else {
                    $ssh->disableQuietMode();
                    $ssh->setTimeout(5);

                    $start_date = date("Y-m-d H:i:s");

                    $cmd = "cat /proc/meminfo | grep ^MemTotal | awk '{print $2}'";
                    //$cmd = "cat /proc/meminfo | grep ^SwapTotal | awk '{print $2}'";
                    $total = intval($ssh->exec($cmd));

                    $cmd = "cat /proc/meminfo | grep ^MemAvailable | awk '{print $2}'";
                    //$cmd = "cat /proc/meminfo | grep ^SwapFree | awk '{print $2}'";
                    $free = intval($ssh->exec($cmd));
                    $used = $total - $free;

                    $usage = array(
                        'free' => $free,
                        'used' => $used,
                        'category' => $start_date
                    );

                    $comment = '';
                }
            }

            $ssh->disconnect();

            $response = array(EXT_JSON_READER_TOTAL => count($usage),
                EXT_JSON_READER_ROOT => $usage, 'comment' => $comment);

            break;

        case 'cpu_usage';
            $db_host = $_REQUEST['db_host'];
            $os_user = $_REQUEST['server_user'];
            $os_pass = $_REQUEST['server_pass'];

            $pct = array();

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $start_date = date("Y-m-d H:i:s");

            $pct = array(
                'user' => 0,
                'nice' => 0,
                'sys' => 0,
                'idle' => 0,
                'iowait' => 0,
                'category' => $start_date
            );

            $ssh = new Net_SSH2($db_host,22,5);
            if (empty($ssh->server_identifier)) {
                $comment = 'Impossible de se connecter au serveur ' . $db_host;
                //$pct = null;
            }
            else
            {
                if (!$ssh->login($os_user, $os_pass)) {
                    $comment = 'Compte ou Mot de passe invalide ...';
                    //$pct = null;
                } else {
                    $ssh->disableQuietMode();
                    $ssh->setTimeout(5);

                    $file = "/dev/shm/" . $randomString;

                    $cmd = "cat /proc/stat";
                    $data = $ssh->exec($cmd);
                    file_put_contents($file, $data);

                    $content = file($file);

                    $stat1 = GetCoreInformation($content);

                    //var_dump($stat1);

                    sleep(1);

                    $data = $ssh->exec($cmd);
                    file_put_contents($file, $data);

                    $content = file($file);

                    //var_dump($data);

                    $stat2 = GetCoreInformation($content);

                    //var_dump($stat2);

                    $pct = GetCpuPercentages($stat1, $stat2);
                    $comment = '';

                    unlink($file);
                }
            }

            $ssh->disconnect();

            $response = array(EXT_JSON_READER_TOTAL => count($pct),
                EXT_JSON_READER_ROOT => $pct, 'comment' => $comment);

            break;

        case 'disk_activity';
            $db_host = $_REQUEST['db_host'];
            $os_user = $_REQUEST['server_user'];
            $os_pass = $_REQUEST['server_pass'];

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $start_date = date("Y-m-d H:i:s");

            $disks = array();
            $dif = array();
            $dif['name'] = "error";
            $dif['read'] = 0;
            //$dif['read'] = round($dif['read']/1000);
            $dif['write'] = 0;
            //$dif['write'] = round($dif['write']/1000);

            $read = $dif['read'];
            $write = $dif['write'];
            $total = 1000;
            $dif['read'] = $read*100/$total;
            $dif['write'] = $write*100/$total;

            $disks[0] = $dif;

            $ssh = new Net_SSH2($db_host,22,5);
            if (empty($ssh->server_identifier)) {
                $comment = 'Impossible de se connecter au serveur ' . $db_host;
                //$disks = null;
            }
            else
            {
                if (!$ssh->login($os_user, $os_pass)) {
                    $comment = 'Compte ou Mot de passe invalide ...';
                    //$disks = null;
                } else {
                    $ssh->disableQuietMode();
                    $ssh->setTimeout(5);

                    $file = "/dev/shm/" . $randomString;

                    $cmd = "cat /proc/diskstats | awk '{print \$3\";\"\$7\";\"\$11}'";
                    $data = $ssh->exec($cmd);
                    file_put_contents($file, $data);

                    $content = file($file);

                    $stat1 = GetDisksInformation($content);

                    sleep(1);

                    $data = $ssh->exec($cmd);
                    file_put_contents($file, $data);

                    $content = file($file);

                    $stat2 = GetDisksInformation($content);

                    $disks = GetDiskActivity($stat1, $stat2);
                    $comment = '';

                    unlink($file);
                }
            }

            $ssh->disconnect();

            $response = array(EXT_JSON_READER_TOTAL => count($disks),
                EXT_JSON_READER_ROOT => $disks, 'comment' => $comment);

            break;

        case 'net_usage':
            $db_host = $_REQUEST['db_host'];
            $os_user = $_REQUEST['server_user'];
            $os_pass = $_REQUEST['server_pass'];

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $start_date = date("Y-m-d H:i:s");

            $net = $values = array(
                'rec' => 0,
                'trans' => 0,
                'category' => $start_date
            );

            $ssh = new Net_SSH2($db_host,22,5);
            if (empty($ssh->server_identifier)) {
                $comment = 'Impossible de se connecter au serveur ' . $db_host;
//                $net = null;
            }
            else
            {
                if (!$ssh->login($os_user, $os_pass)) {
                    $comment = 'Compte ou Mot de passe invalide ...';
                    //$net = null;
                } else {
                    $ssh->disableQuietMode();
                    $ssh->setTimeout(5);

                    $file = "/dev/shm/" . $randomString;

                    $cmd = "cat /proc/net/dev | awk '{if (NR > 2) print \$1\";\"\$9}'|awk -F \":\" '{if($1 != \"bond0\") print \$2}'";
                    $data = $ssh->exec($cmd);
                    file_put_contents($file, $data);

                    $content = file($file);

                    $stat1 = GetNetInformation($content);

                    //var_dump($stat1);

                    sleep(1);

                    $data = $ssh->exec($cmd);
                    file_put_contents($file, $data);

                    $content = file($file);

                    //var_dump($data);

                    $stat2 = GetNetInformation($content);

                    //var_dump($stat2);

                    $net = GetNetUsage($stat1, $stat2);
                    $comment = '';

                    //var_dump($pct);
                    unlink($file);
                }
            }

            $ssh->disconnect();

            $response = array(EXT_JSON_READER_TOTAL => count($net),
                EXT_JSON_READER_ROOT => $net, 'comment' => $comment);

            break;
        case 'list_topfs':
            $host = $_REQUEST['host'];
            $port = $_REQUEST['port'];
            $user = $_REQUEST['user'];
            $pass = $_REQUEST['pass'];
            $typ = $_REQUEST['typ'];

            $records [] = array('fs' => '',
                'typ' => '',
                'size' => 100,
                'used' => 100,
                'dispo' => 0,
                'pct_used' => 100,
                'mount' => 'Connecting ...',
                'qtip' => 'Connecting ...',
                'rest' => 100 . ';' . 100 . ';' . 0);

            $response = array(EXT_JSON_READER_TOTAL => 1,
                EXT_JSON_READER_ROOT => $records);

            $records = array();
            $recs = array();

            switch ($typ) {
                case "win":
                    $command = "C:\\xampp\\htdocs\\dev\\tools\\psexec.exe \\\\" . $host . " -u " . $user . " -p " . $pass . " -c C:\\xampp\\htdocs\\dev\\tools\\psinfo.exe -d ";
                    $resp = shell_exec($command);
                    if ($resp != null) {
                        $rows = explode("\n", $resp);

                        $vol_index = 0;
                        $index = 0;
                        foreach ($rows as $row) {
                            if ($vol_index > 0) {
                                $record = explode(" ", $row);
                                $cnt = count($record);

                                $free = $record[$cnt - 4];
                                $size = $record[$cnt - 7];
                                $volume = $record[4];
                                $type = $record[5];
                                $format = $record[11];
                                $used = $size - $free;
                                $pct_used = $used / $size * 100;

                                $records[] = array('fs' => $volume,
                                    'typ' => $format,
                                    'size' => (int)($size) / 1024,
                                    'used' => (int)($used) / 1024,
                                    'dispo' => (int)($free) / 1024,
                                    'pct_used' => $pct_used,
                                    'mount' => $volume
                                );
                            } else {
                                $record = explode(" ", $row);

                                if ($record[0] == "Volume") {
                                    $vol_index = $index + 1;
                                }
                            }
                            $index++;
                        }

                        break;
                    }

                    return false;
                case "aix":
                    return false;
                case "lin":
                    $ssh = new Net_SSH2($host, $port,15);

                    if (empty($ssh->server_identifier)) {
                        $records [] = array('fs' => '',
                            'typ' => '',
                            'size' => 100,
                            'used' => 100,
                            'dispo' => 0,
                            'pct_used' => 100,
                            'mount' => 'Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme',
                            'qtip' => 'Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme',
                            'rest' => 100 . ';' . 100 . ';' . 0);

                        $response = array(EXT_JSON_READER_TOTAL => 1,
                            EXT_JSON_READER_ROOT => $records);
                    } else {
                        if (!$ssh->login($user, $pass)) {
                            $records [] = array('fs' => '',
                                'typ' => '',
                                'size' => 100,
                                'used' => 100,
                                'dispo' => 0,
                                'pct_used' => 100,
                                'mount' => 'Compte ' . $user . 'ou mot de passe invalide',
                                'qtip' => 'Compte ' . $user . 'ou mot de passe invalide',
                                'rest' => 100 . ';' . 100 . ';' . 0);

                            $response = array(EXT_JSON_READER_TOTAL => 1,
                                EXT_JSON_READER_ROOT => $records);
                        } else {
                            $ssh->disableQuietMode();
                            $ssh->setTimeout(5);

                            $cmd = "df -PT|awk '{if (NR > 1) {print \$1\";\"\$2\";\"\$3\";\"\$4\";\"\$5\";\"\$6\";\"\$7}}'";
                            $resp = $ssh->exec($cmd);
                            $ssh->disconnect();

                            $rows = explode("\n", $resp);

                            $index = 0;
                            foreach ($rows as $row) {
                                $record = explode(";", $row);
                                $free = (int)($record[4]) * 1024;
                                $size = (int)($record[2]) / 1024;

                                $records[] = array('fs' => $record[0],
                                    'typ' => $record[1],
                                    'size' => $size,
                                    'used' => (int)($record[3]) / 1024,
                                    'dispo' => (int)($record[4]) / 1024,
                                    'pct_used' => $record[5],
                                    'mount' => $record[6],
                                    'qtip' => formatSizeUnits(($free)) . " libre sur " . formatSizeUnits(((int)($record[2]) * 1024))
                                );

                                $index++;
                            }

                            $top = 0;
                            foreach ($records as $rec) {
                                $pcts = explode("%", $rec['pct_used']);
                                if ($pcts[0] > $top) {
                                    $top = $pcts[0];

                                    $recs[0] = array('fs' => $rec['fs'],
                                        'typ' => $rec['typ'],
                                        'size' => $rec['size'],
                                        'used' => $rec['used'],
                                        'dispo' => $rec['dispo'],
                                        'pct_used' => $pcts[0],
                                        'mount' => $rec['mount'],
                                        'qtip' => $rec['qtip'],
                                        'rest' => $rec['pct_used'] . ';' . $rec['pct_used'] . ';' . $rec['dispo']
                                    );
                                }
                            }

                            $top = 0;
                            foreach ($records as $rec) {
                                $pcts = explode("%", $rec['pct_used']);
                                if ($pcts[0] > $top && $pcts[0] < $recs[0]['pct_used']) {
                                    $top = $pcts[0];

                                    $recs[1] = array('fs' => $rec['fs'],
                                        'typ' => $rec['typ'],
                                        'size' => $rec['size'],
                                        'used' => $rec['used'],
                                        'dispo' => $rec['dispo'],
                                        'pct_used' => $pcts[0],
                                        'mount' => $rec['mount'],
                                        'qtip' => $rec['qtip'],
                                        'rest' => $rec['pct_used'] . ';' . $rec['pct_used'] . ';' . $rec['dispo']
                                    );
                                }
                            }

                            $top = 0;
                            foreach ($records as $rec) {
                                $pcts = explode("%", $rec['pct_used']);
                                if ($pcts[0] > $top && $pcts[0] < $recs[1]['pct_used']) {
                                    $top = $pcts[0];

                                    $recs[2] = array('fs' => $rec['fs'],
                                        'typ' => $rec['typ'],
                                        'size' => $rec['size'],
                                        'used' => $rec['used'],
                                        'dispo' => $rec['dispo'],
                                        'pct_used' => $pcts[0],
                                        'mount' => $rec['mount'],
                                        'qtip' => $rec['qtip'],
                                        'rest' => $rec['pct_used'] . ';' . $rec['size'] . ';' . $rec['dispo']
                                    );
                                }
                            }

                            $response = array(EXT_JSON_READER_TOTAL => 3,
                                EXT_JSON_READER_ROOT => $recs);
                        }
                    }

                    $ssh->disconnect();

                    break;
            }
            break;
    }

    echo json_encode($response);
}

?>