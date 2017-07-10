<?php
/*
  $Id: Servers.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    include('includes/modules/Net/SSH2.php');
    if (!class_exists('content')) {
        include('includes/classes/content.php');
    }
class toC_Servers_Admin
{
    function getData($id)
    {
        global $osC_Database;

        $QServers = $osC_Database->query('select a.* from :table_servers a where servers_id = :servers_id');

        $QServers->bindTable(':table_servers', TABLE_SERVERS);
        $QServers->bindInt(':servers_id', $id);
        $QServers->execute();

        $data = $QServers->toArray();

        $QServers->freeResult();

//        $description = content::getContentDescription($id, 'servers');
//        $data = array_merge($data, $description);
//
//        $product_categories_array = content::getContentCategories($id, 'servers');
//        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

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

    function getLog($host,$user,$pass,$port,$url,$lines)
    {
        $error = false;

        $ssh = new Net_SSH2($host,$port);

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter à ce serveur, veuillez contacter votre administrateur systeme";
            return false;
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
                return false;
            }
            else
            {
                $ssh->disableQuietMode();
                $cmd = "ls " . $url;
                $resp = trim($ssh->exec($cmd));

                if($resp != $url)
                {
                    $_SESSION['LAST_ERROR'] = 'Fichier inexistant sur ce serveur';
                    return false;
                }

                $cmd = "du -m " . $url . " |awk '{print $1'}";
                $resp = trim($ssh->exec($cmd));

                $size = (int)$resp;

                if($size <= 10)
                {
                    $cmd = "wc -l " . $url . " |awk '{print $1'}";
                    $resp = trim($ssh->exec($cmd));

                    $lc = (int)$resp;
                }
                else
                {
                    $lc = 1000;
                }

                $tail = $lc - $lines;

                $cmd = "tail -" . $tail . " " . $url . " > /tmp/out.log";
                $resp = $ssh->exec($cmd);

                $cmd = "cat /tmp/out.log";
                $resp = $ssh->exec($cmd);

                $data = array('lines' => $lc,'content' => $resp,'size' => $size);

                $ssh->disconnect();

                return $data;
            }
        }
    }

    function saveDb($id = null, $data)
    {
        global $osC_Database;

        $error = false;

        //we check the connection first
        $db_user = $data['user'];
        $db_pass = $data['pass'];
        $db_host = $data['host'];
        $db_port = $data['port'];
        $db_sid = $data['sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . ":" . $db_port . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $_SESSION['LAST_ERROR'] = 'Could not connect to database: ' . $e['message'];
            return false;
        } else {
            $osC_Database->startTransaction();

            if (is_numeric($id)) {
                $Qserver = $osC_Database->query('update :table_databases set servers_id = :servers_id,label=:label,port = :port,sid = :sid,user = :user,pass = :pass,category = :category where databases_id = :databases_id');
                $Qserver->bindInt(':databases_id', $id);
            } else {
                $Qserver = $osC_Database->query('insert into :table_databases (servers_id,label,port,sid,user,pass,category) values (:servers_id,:label,:port,:sid,:user,:pass,:category)');
            }

            $Qserver->bindTable(':table_databases', TABLE_DATABASES);
            $Qserver->bindValue(':servers_id', $data['servers_id']);
            $Qserver->bindValue(':label', $data['label']);
            $Qserver->bindInt(':port', $data['port']);
            $Qserver->bindValue(':sid', $data['sid']);
            $Qserver->bindValue(':user', $data['user']);
            $Qserver->bindValue(':category', $data['category']);
            $Qserver->bindValue(':pass', $data['pass']);
            $Qserver->setLogging($_SESSION['module'], $id);
            $Qserver->execute();

            if ($osC_Database->isError()) {
                $error = true;
            } else {
                if (is_numeric($id)) {
                    $databases_id = $id;
                } else {
                    $databases_id = $osC_Database->nextID();
                }
            }

            //content
            if ($error === false) {
                $error = !content::saveContent($id, $databases_id, 'databases', $data);
            }

            //Process Languages
            if ($error === false) {
                $error = !content::saveServerDescription($id, $databases_id, 'databases', $data);
            }

            //content_to_categories
            if ($error === false) {
                $error = !content::saveContentToCategories($id, $databases_id, 'databases', $data);
            }

            //images
            if ($error === false) {
                $error = !content::saveImages($databases_id, 'databases');
            }

            if ($error === false) {
                $osC_Database->commitTransaction();
                osC_Cache::clear('sefu-databases');
                return true;
            }

            $osC_Database->rollbackTransaction();

            $_SESSION['LAST_ERROR'] = $osC_Database->error;

            return false;
        }
    }

    function saveFsState($data)
    {
        global $osC_Database;

        $query = "INSERT INTO delta_fs_usage (snaps_id,servers_id,fs_name,space_total_mb,space_used_mb,space_dispo_mb,start_date,end_date) VALUES (:snaps_id,:servers_id,:fs_name,:space_total_mb,:space_used_mb,:space_dispo_mb,:start_date,:end_date)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindTable(':table_databases', TABLE_DATABASES);
        $Qserver->bindValue(':servers_id', $data['servers_id']);
        $Qserver->bindValue(':snaps_id', $data['snaps_id']);
        $Qserver->bindInt(':fs_name', $data['fs_name']);
        $Qserver->bindValue(':space_total_mb', $data['space_total_mb']);
        $Qserver->bindValue(':space_used_mb', $data['space_used_mb']);
        $Qserver->bindValue(':space_dispo_mb', $data['space_dispo_mb']);
        $Qserver->bindValue(':start_date', $data['start_date']);
        $Qserver->bindValue(':end_date', $data['end_date']);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        return true;
    }

    function saveServerSpaceUsage($data)
    {
        global $osC_Database;

        $query = "INSERT INTO delta_space_usage (snaps_id,servers_id,space_total_gb,space_used_gb,space_dispo_gb,start_date,end_date) VALUES (:snaps_id,:servers_id,:space_total_gb,:space_used_gb,:space_dispo_gb,:start_date,end_date)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':servers_id', $data['servers_id']);
        $Qserver->bindValue(':snaps_id', $data['snaps_id']);
        $Qserver->bindInt(':space_total_gb', $data['space_total_gb']);
        $Qserver->bindValue(':space_used_gb', $data['space_used_gb']);
        $Qserver->bindValue(':space_dispo_gb', $data['space_dispo_gb']);
        $Qserver->bindValue(':start_date', $data['start_date']);
        $Qserver->bindValue(':end_date', $data['end_date']);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        return true;
    }

    function saveServerState($data)
    {
        global $osC_Database;

        $query = "INSERT INTO delta_server_state (servers_id,state,comments,start_date) VALUES (:servers_id,:state,:comments,:start_date)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':servers_id', $data['servers_id']);
        $Qserver->bindValue(':state', $data['state']);
        $Qserver->bindInt(':comments', $data['comments']);
        $Qserver->bindValue(':start_date', $data['start_date']);

        if ($osC_Database->isError()) {
            return false;
        }

        return true;
    }

    function saveOgg($id = null, $data)
    {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qserver = $osC_Database->query('UPDATE delta_ogg_config SET id = id,datapump_name = datapump_name,dest_database = dest_database,extract_name = extract_name,oggdir_dest = oggdir_dest,oggdir_src = oggdir_src,replicat_name = replicat_name,src_database = src_database WHERE id = :id');
            $Qserver->bindInt(':id', $id);
        } else {
            $Qserver = $osC_Database->query('INSERT INTO delta_ogg_config(datapump_name,dest_database,extract_name,oggdir_dest,oggdir_src,replicat_name,src_database) VALUES (:datapump_name,:dest_database,:extract_name,:oggdir_dest,:oggdir_src,:replicat_name,:src_database)');
        }

        $Qserver->bindValue(':datapump_name', $data['datapump_name']);
        $Qserver->bindInt(':dest_database', $data['dest_database']);
        $Qserver->bindValue(':extract_name', $data['extract_name']);
        $Qserver->bindValue(':oggdir_dest', $data['oggdir_dest']);
        $Qserver->bindValue(':oggdir_src', $data['oggdir_src']);
        $Qserver->bindValue(':replicat_name', $data['replicat_name']);
        $Qserver->bindInt(':src_database', $data['src_database']);
        $Qserver->setLogging($_SESSION['module'], $id);
        $Qserver->execute();

        $error = $osC_Database->isError();

        if ($error === false) {
            $osC_Database->commitTransaction();
            osC_Cache::clear('sefu-databases');
            return true;
        }

        $osC_Database->rollbackTransaction();

        $_SESSION['LAST_ERROR'] = $osC_Database->error;

        return false;
    }

    function save($id = null, $data)
    {
        global $osC_Database;

        $error = false;

        //we check the connection first
        $user = $data['user'];
        $pass = $data['pass'];
        $host = $data['host'];
        $port = $data['port'];
        $typ = $data['typ'];

        switch($typ)
        {
            case 'win':
                //$command = "C:\\xampp\\htdocs\\dev\\tools\\psexec.exe /accepteula \\\\". $host . " -u " . $user . " -p " . $pass . " ipconfig ";
                $command = "C:\\xampp\\htdocs\\dev\\tools\\psexec.exe \\\\" . $host . " -u " . $user . " -p " . $pass . " copy 2>&1";
                $resp=shell_exec($command);
                if($resp == null)
                {
                    return false;
                }

                if(strpos($resp, "Couldn't access"))
                {
                    $_SESSION['LAST_ERROR'] = "Impossible de se connecter à ce serveur, veuillez contacter votre administrateur systeme";
                    return false;
                }

                break;
            case 'lin':
                $ssh = new Net_SSH2($host,$port);

                if (empty($ssh->server_identifier)) {
                    $_SESSION['LAST_ERROR'] = "Impossible de se connecter à ce serveur, veuillez contacter votre administrateur systeme";
                    return false;
                } else {
                    if (!$ssh->login($user, $pass)) {
                        $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
                        return false;
                    }
                }

                $ssh->disconnect();
                break;
            case 'aix':
                return false;
        }

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qserver = $osC_Database->query('update :table_servers set host = :host,label=:label,port = :port,typ = :typ,user = :user,pass = :pass where servers_id = :servers_id');
            $Qserver->bindInt(':servers_id', $id);
        } else {
            $Qserver = $osC_Database->query('insert into :table_servers (host,label,port,typ,user,pass) values (:host,:label,:port,:typ,:user,:pass)');
        }

        $Qserver->bindTable(':table_servers', TABLE_SERVERS);
        $Qserver->bindValue(':host', $data['host']);
        $Qserver->bindValue(':label', $data['label']);
        $Qserver->bindInt(':port', $data['port']);
        $Qserver->bindValue(':typ', $data['typ']);
        $Qserver->bindValue(':user', $data['user']);
        $Qserver->bindValue(':pass', $data['pass']);
        $Qserver->setLogging($_SESSION['module'], $id);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            $error = true;
        } else {
            if (is_numeric($id)) {
                $servers_id = $id;
            } else {
                $servers_id = $osC_Database->nextID();
            }
        }

        //content
        if ($error === false) {
            $error = !content::saveContent($id, $servers_id, 'servers', $data);
        }

        //Process Languages
        if ($error === false) {
            $error = !content::saveServerDescription($id, $servers_id, 'servers', $data);
        }

        //content_to_categories
        if ($error === false) {
            $error = !content::saveContentToCategories($id, $servers_id, 'servers', $data);
        }

        //images
        if ($error === false) {
            $error = !content::saveImages($servers_id, 'servers');
        }

        if ($error === false) {
            $osC_Database->commitTransaction();
            osC_Cache::clear('sefu-servers');
            return true;
        }

        $osC_Database->rollbackTransaction();

        $_SESSION['LAST_ERROR'] = $osC_Database->error;

        return false;
    }

    function saveLog($id = null, $data)
    {
        global $osC_Database;

        $error = false;

        //we check the connection first
        $user = $data['user'];
        $pass = $data['pass'];
        $host = $data['host'];
        $port = $data['port'];

        $ssh = new Net_SSH2($host,$port);

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter à ce serveur, veuillez contacter votre administrateur systeme";
            return false;
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
                return false;
            }
            else
            {
                $ssh->disableQuietMode();
                $cmd = "ls " . $data['url'];
                $resp = trim($ssh->exec($cmd));
                $ssh->disconnect();

                if($resp != $data['url'])
                {
                    $_SESSION['LAST_ERROR'] = 'Fichier inexistant sur ce serveur';
                    return false;
                }

                $osC_Database->startTransaction();

                if (is_numeric($id)) {
                    $Qserver = $osC_Database->query('update :table_logs set servers_id = :servers_id,url = :url,content_type=:content_type,content_id = :content_id where logs_id = :logs_id');
                    $Qserver->bindInt(':logs_id', $id);
                } else {
                    $Qserver = $osC_Database->query('insert into :table_logs (servers_id,url,content_type,content_id) values (:servers_id,:url,:content_type,:content_id)');
                }

                $Qserver->bindTable(':table_logs', TABLE_LOGS);
                $Qserver->bindValue(':url', $data['url']);
                $Qserver->bindValue(':content_type', $data['content_type']);
                $Qserver->bindInt(':servers_id', $data['servers_id']);
                $Qserver->bindInt(':content_id', $data['content_id']);
                $Qserver->setLogging($_SESSION['module'], $id);
                $Qserver->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                } else {
                    if (is_numeric($id)) {
                        $logs_id = $id;
                    } else {
                        $logs_id = $osC_Database->nextID();
                    }
                }

                //content
                if ($error === false) {
                    $error = !content::saveContent($id, $logs_id, 'logs', $data);
                }

                //Process Languages
                if ($error === false) {
                    $error = !content::saveServerDescription($id, $logs_id, 'logs', $data);
                }

                //content_to_categories
                if ($error === false) {
                    $error = !content::saveContentToCategories($id, $logs_id, 'logs', $data);
                }

                //images
                if ($error === false) {
                    $error = !content::saveImages($logs_id, 'logs');
                }

                if ($error === false) {
                    $osC_Database->commitTransaction();
                    osC_Cache::clear('sefu-logs');
                    return true;
                }

                $osC_Database->rollbackTransaction();

                $_SESSION['LAST_ERROR'] = $osC_Database->error;

                return false;
            }
        }
    }

    function delete($id)
    {
        global $osC_Database;
        $error = false;

        $osC_Database->startTransaction();

        $error = !content::deleteContent($id,'servers');

        if ($error === false) {
            $QServers = $osC_Database->query('delete from :table_servers where servers_id = :servers_id');
            $QServers->bindTable(':table_servers', table_servers);
            $QServers->bindInt(':servers_id', $id);
            $QServers->setLogging($_SESSION['module'], $id);
            $QServers->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }
        }

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $osC_Database->commitTransaction();
        osC_Cache::clear('sefu-Servers');
        return true;
    }

    function purge_repo()
    {
        global $osC_Database;
        $error = false;

        $osC_Database->startTransaction();

        $QServers = $osC_Database->query('DELETE FROM delta_tbs_usage WHERE DATEDIFF(SYSDATE(),start_date) >= 90');
        $QServers->execute();

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $QServers = $osC_Database->query('DELETE FROM delta_reports_executions');
        $QServers->execute();

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $QServers = $osC_Database->query('DELETE FROM obs_notification WHERE DATEDIFF(SYSDATE(),CREATED_DATE) >= 90');
        $QServers->execute();

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $QServers = $osC_Database->query('DELETE FROM obs_event_log WHERE DATEDIFF(SYSDATE(),EVENT_TIME) >= 90');
        $QServers->execute();

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $QServers = $osC_Database->query('DELETE FROM delta_fs_usage WHERE DATEDIFF(SYSDATE(),start_date) >= 90');
        $QServers->execute();

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $osC_Database->commitTransaction();
        osC_Cache::clear('sefu-Servers');
        return true;
    }
}

?>
