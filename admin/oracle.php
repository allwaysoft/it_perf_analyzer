<?php

define('EXT_JSON_READER_ROOT', 'records');
define('EXT_JSON_READER_TOTAL', 'total');
error_reporting(0);

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    $response = null;

    switch (strtolower($action)) {

        case 'list_sessions':
            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $status = $_REQUEST['status'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
            }

            $status = strtoupper($status);

            $query = "SELECT *
FROM
  (SELECT s.sid,
    s.serial#             AS serial,
    s.saddr,
    lower(s.username) AS username,
    s.status,
    s.type,
    s.command,
    sw.state,
    sw.event,
    sw.wait_time,
    sw.seconds_in_wait,
    s.logon_time,
    s.schemaname,
    s.osuser,
    s.machine,
    s.terminal,
    s.action,
    sql_text,
    case
    when s.module != s.program then s.module || ' ' || s.program || ' ' || s.client_info
    else s.module || ' ' || s.client_info
    end info,
    100       *NVL(slo.sofar,0)/NVL(slo.totalwork,1) pct,
    ROUND(100 * p.PGA_USED_MEM / p.pga_max_mem) pct_pga
  FROM v\$session s,
    v\$px_session px,
    v\$session_wait sw,
    v\$process p,
    v\$sqlarea sqlarea,
    (SELECT * FROM v\$session_longops WHERE time_remaining <> 0
    ) slo
  WHERE s.sql_hash_value = sqlarea.hash_value
  AND s.sql_address      = sqlarea.address
  AND s.sid              = sw.sid(+)
  and s.sid != USERENV ('SID')
  AND s.paddr            = p.addr
  AND (s.sid             = slo.sid(+)
  AND s.serial#          = slo.serial#(+))
  AND (s.sid             = px.sid(+)
  AND s.serial#          = px.serial#(+))
  )
WHERE STATUS = '" . $status . "'
AND TYPE    != 'BACKGROUND' ";

            if(!empty($_REQUEST['search']) and isset($_REQUEST['search']))
            {
                $query = $query . " and (lower(s.username) like '" . strtolower($_REQUEST['search']) . "' or lower(s.command) like '" . strtolower($_REQUEST['search']) . "' or lower(sw.event) like '" . strtolower($_REQUEST['search']) . "' or lower(s.schemaname) like '" . strtolower($_REQUEST['search']) . "' or lower(s.osuser) like '" . strtolower($_REQUEST['search']) . "' or lower(s.machine) like '" . strtolower($_REQUEST['search']) . "' or lower(s.action) like '" . strtolower($_REQUEST['search']) . "' or lower(sql_text) like '" . strtolower($_REQUEST['search']) . "' or lower(s.module) like '" . strtolower($_REQUEST['search']) . "' or lower(s.program) like '" . strtolower($_REQUEST['search']) . "' or lower(s.client_info) like '" . strtolower($_REQUEST['search']) . "')";
            }

            $query = $query . " ORDER BY seconds_in_wait DESC";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
                var_dump($s);
            }

            $r = oci_execute($s);
            if (!$r) {
                $e = oci_error($s);
                trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
                var_dump($r);
            }

            $records = array();
            //$sum = 0;
            $count = 0;

            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                $records [] = array('sid' => $row['SID'],
                    'sql_text' => $row['SQL_TEXT'],
                    'serial' => $row['SERIAL'],
                    'username' => $row['USERNAME'],
                    'command' => $row['COMMAND'],
                    'state' => $row['STATE'],
                    'client_info' => $row['INFO'],
                    'event' => $row['EVENT'],
                    'wait_time' => $row['WAIT_TIME'],
                    'seconds_in_wait' => $row['SECONDS_IN_WAIT'],
                    'logon_time' => $row['LOGON_TIME'],
                    'schemaname' => $row['SCHEMANAME'],
                    'osuser' => $row['OSUSER'],
                    'machine' => $row['MACHINE'],
                    'terminal' => $row['TERMINAL'],
                    'action' => $row['ACTION'],
                    'pct' => $row['PCT'],
                    'pct_pga' => $row['PCT_PGA']
                );

                $count++;
            }

            oci_free_statement($s);
            oci_close($c);

            $response = array(EXT_JSON_READER_TOTAL => $count,
                EXT_JSON_READER_ROOT => $records);

            break;
        case 'list_waits':
            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $databases_id = $_REQUEST['databases_id'];

            $start_date = date("Y-m-d H:i:s");

            $sum = 0;
            $query = "SELECT (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 0)
          other,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 1)
          application,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 2)
          configuration,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 3)
          administrative,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 4)
          concurrency,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 5)
          commit,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 7)
          network,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 8)
          userio,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 9)
          systemio,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 10)
          scheduler,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 11)
          clustering,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = 12)
          queueing
  FROM DUAL";

            $records = array();

            $c = oci_connect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();

                $records[] = array(
                    'date' => $start_date,
                    'other' => 0,
                    'application' => 0,
                    'configuration' => 0,
                    'administrative' => 0,
                    'concurrency' => 0,
                    'commit' => 0,
                    'network' => 0,
                    'userio' => 0,
                    'systemio' => 0,
                    'scheduler' => 0,
                    'clustering' => 0,
                    'queueing' => 0,
                    'comments' => $e['message']);
            } else {
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);

                    $records[] = array(
                        'date' => $start_date,
                        'other' => 0,
                        'application' => 0,
                        'configuration' => 0,
                        'administrative' => 0,
                        'concurrency' => 0,
                        'commit' => 0,
                        'network' => 0,
                        'userio' => 0,
                        'systemio' => 0,
                        'scheduler' => 0,
                        'clustering' => 0,
                        'queueing' => 0,
                        'comments' => $e['message']);
                } else {
                    $r = oci_execute($s);
                    if (!$r) {
                        $e = oci_error($s);

                        $records[] = array(
                            'date' => $start_date,
                            'other' => 0,
                            'application' => 0,
                            'configuration' => 0,
                            'administrative' => 0,
                            'concurrency' => 0,
                            'commit' => 0,
                            'network' => 0,
                            'userio' => 0,
                            'systemio' => 0,
                            'scheduler' => 0,
                            'clustering' => 0,
                            'queueing' => 0,
                            'comments' => $e['message']);
                    } else {
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                            $records[] = array(
                                'date' => $start_date,
                                'other' => $row['OTHER'],
                                'application' => $row['APPLICATION'],
                                'configuration' => $row['CONFIGURATION'],
                                'administrative' => $row['ADMINISTRATIVE'],
                                'concurrency' => $row['CONCURRENCY'],
                                'commit' => $row['COMMIT'],
                                'network' => $row['NETWORK'],
                                'userio' => $row['USERIO'],
                                'systemio' => $row['SYSTEMIO'],
                                'scheduler' => $row['SCHEDULER'],
                                'clustering' => $row['CLUSTERING'],
                                'queueing' => $row['QUEUEING'],
                                'comments' => '');
                        }
                    }
                }

                oci_free_statement($s);
            }

            oci_close($c);

            $response = array(EXT_JSON_READER_TOTAL => count($records),
                EXT_JSON_READER_ROOT => $records);
            break;

        case 'list_toptbs':
            $query = "SELECT ts.tablespace_name,
       size_info.megs_alloc,
       size_info.megs_free,
       size_info.megs_used,
       size_info.MAX,
       (size_info.MAX - size_info.megs_used) free
  FROM (SELECT a.tablespace_name,
               ROUND (a.bytes_alloc / 1024 / 1024) megs_alloc,
               ROUND (NVL (b.bytes_free, 0) / 1024 / 1024) megs_free,
               ROUND ( (a.bytes_alloc - NVL (b.bytes_free, 0)) / 1024 / 1024)
                  megs_used,
               ROUND ( (NVL (b.bytes_free, 0) / a.bytes_alloc) * 100)
                  Pct_Free,
               100 - ROUND ( (NVL (b.bytes_free, 0) / a.bytes_alloc) * 100)
                  Pct_used,
               ROUND (maxbytes / 1048576) MAX
          FROM (  SELECT f.tablespace_name,
                         SUM (f.bytes) bytes_alloc,
                         SUM (
                            DECODE (f.autoextensible,
                                    'YES', f.maxbytes,
                                    'NO', f.bytes))
                            maxbytes
                    FROM dba_data_files f
                GROUP BY tablespace_name) a,
               (  SELECT f.tablespace_name, SUM (f.bytes) bytes_free
                    FROM dba_free_space f
                GROUP BY tablespace_name) b
         WHERE a.tablespace_name = b.tablespace_name(+)
        UNION ALL
          SELECT h.tablespace_name,
                 ROUND (SUM (h.bytes_free + h.bytes_used) / 1048576) megs_alloc,
                 ROUND (
                      SUM (
                         (h.bytes_free + h.bytes_used) - NVL (p.bytes_used, 0))
                    / 1048576)
                    megs_free,
                 ROUND (SUM (NVL (p.bytes_used, 0)) / 1048576) megs_used,
                 ROUND (
                      (  SUM (
                              (h.bytes_free + h.bytes_used)
                            - NVL (p.bytes_used, 0))
                       / SUM (h.bytes_used + h.bytes_free))
                    * 100)
                    Pct_Free,
                   100
                 - ROUND (
                        (  SUM (
                                (h.bytes_free + h.bytes_used)
                              - NVL (p.bytes_used, 0))
                         / SUM (h.bytes_used + h.bytes_free))
                      * 100)
                    pct_used,
                 ROUND (
                    SUM (
                         DECODE (f.autoextensible,
                                 'YES', f.maxbytes,
                                 'NO', f.bytes)
                       / 1048576))
                    MAX
            FROM sys.v_\$TEMP_SPACE_HEADER h,
                 sys.v_\$Temp_extent_pool p,
                 dba_temp_files f
           WHERE     p.file_id(+) = h.file_id
                 AND p.tablespace_name(+) = h.tablespace_name
                 AND f.file_id = h.file_id
                 AND f.tablespace_name = h.tablespace_name
        GROUP BY h.tablespace_name) size_info,
       sys.dba_tablespaces ts
 WHERE ts.tablespace_name = size_info.tablespace_name order by (size_info.MAX - size_info.megs_used)";

            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];

            $records = array();
            $total = 0;

            $c = oci_connect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();

                $records [] = array('tbs' => substr($e['message'], 15), 'pct_used' => 100, 'rest' => 100 . ';' . 0 . ';' . 0, 'qtip' => $e['message']);

                $total = $total + 1;

                $response = array('success' => false, 'feedback' => $total . ' espaces logiques', EXT_JSON_READER_TOTAL => $total,
                    EXT_JSON_READER_ROOT => $records);
            } else {
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $records [] = array('tbs' => substr($e['message'], 15), 'pct_used' => 100, 'rest' => 100 . ';' . 0 . ';' . 0, 'qtip' => $e['message']);

                    $total = $total + 1;

                    $response = array('success' => false, 'feedback' => $total . ' espaces logiques', EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $records [] = array('tbs' => substr($e['message'], 15), 'pct_used' => 100, 'rest' => 100 . ';' . 0 . ';' . 0, 'qtip' => $e['message']);

                        $total = $total + 1;

                        $response = array('success' => false, 'feedback' => $total . ' espaces logiques', EXT_JSON_READER_TOTAL => $total,
                            EXT_JSON_READER_ROOT => $records);
                    } else {
                        $total = 0;
                        while (($row = oci_fetch_array($s, OCI_ASSOC)) && $total < 3) {
                            $total++;
                            $max = (int)($row['MAX']);
                            $used = (int)($row['MEGS_USED']);
                            $total_percent_used = (int)($used * 100 / $max);
                            $free = $row['FREE'];

                            $bytes = $free * 1024 * 1024;

                            if ($bytes >= 1073741824) {
                                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
                            } elseif ($bytes >= 1048576) {
                                $bytes = number_format($bytes / 1048576, 2) . ' MB';
                            } elseif ($bytes >= 1024) {
                                $bytes = number_format($bytes / 1024, 2) . ' KB';
                            } elseif ($bytes > 1) {
                                $bytes = $bytes . ' bytes';
                            } elseif ($bytes == 1) {
                                $bytes = $bytes . ' byte';
                            } else {
                                $bytes = '0 bytes';
                            }

                            $bytes_max = $max * 1024 * 1024;

                            if ($bytes_max >= 1073741824) {
                                $bytes_max = number_format($bytes_max / 1073741824, 2) . ' GB';
                            } elseif ($bytes_max >= 1048576) {
                                $bytes_max = number_format($bytes_max / 1048576, 2) . ' MB';
                            } elseif ($bytes_max >= 1024) {
                                $bytes_max = number_format($bytes_max / 1024, 2) . ' KB';
                            } elseif ($bytes_max > 1) {
                                $bytes_max = $bytes_max . ' bytes';
                            } elseif ($bytes_max == 1) {
                                $bytes_max = $bytes_max . ' byte';
                            } else {
                                $bytes_max = '0 bytes';
                            }

                            $tip = $bytes . " libre sur " . $bytes_max;
                            $records [] = array('tbs' => strtolower($row['TABLESPACE_NAME']), 'pct_used' => $total_percent_used, 'rest' => $total_percent_used . ';' . $row['MEGS_FREE'] . ';' . $row['MAX'], 'qtip' => $tip);
                        }

                        $response = array('success' => true, 'feedback' => $total . ' espaces logiques', EXT_JSON_READER_TOTAL => $total,
                            EXT_JSON_READER_ROOT => $records);
                    }
                }

                oci_free_statement($s);
            }

            oci_close($c);

            break;
    }

    echo json_encode($response);
}

?>