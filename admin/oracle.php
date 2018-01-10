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
            $start_sample = $_REQUEST['start_sample'];
            $end_sample = $_REQUEST['end_sample'];

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
    sqlarea.sql_id sql_id,
    case
    when s.module != s.program then s.module || ' ' || s.program || ' ' || s.client_info || '' || slo.message
    else s.module || ' ' || s.client_info || '' || slo.message
    end info,
    round(100*NVL(slo.sofar,0)/NVL(slo.totalwork,1)) pct,
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
WHERE STATUS = '" . $status . "' ";

            if(isset($start_sample) && !empty($start_sample) && isset($end_sample) && !empty($end_sample))
            {
                $query = $query . " and sql_id IN (select sql_id from v\$active_session_history where sample_id between " . $start_sample . " and " . $end_sample . ")";
            }

            $query = $query . " AND TYPE != 'BACKGROUND' ORDER BY seconds_in_wait DESC nulls last,pct desc";

            //var_dump($query);

            //$background = 'BACKGROUND';
            //oci_bind_by_name($s, ":status", $status);
            //oci_bind_by_name($s, ":start", $start_sample);
            //oci_bind_by_name($s, ":end", $end_sample);
            //oci_bind_by_name($s, ":background", $background);

            $records = array();

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
            }
            else
            {
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);

                    $records [] = array('sid' => 0,
                        'sql_text' => 'Could not parse statement: ' . $e['message'],
                        'sql_id' => '',
                        'serial' => '',
                        'username' => '',
                        'command' => '',
                        'state' => '',
                        'client_info' => '',
                        'event' => '',
                        'wait_time' => 0,
                        'seconds_in_wait' => 0,
                        'logon_time' => '',
                        'schemaname' => '',
                        'osuser' => '',
                        'machine' => '',
                        'terminal' => '',
                        'action' => '',
                        'pct' => '',
                        'pct_pga' => ''
                    );
                }
                else
                {
                    //$background = 'BACKGROUND';
                    //oci_bind_by_name($s, ":status", $status);
                    //oci_bind_by_name($s, ":start", $start_sample);
                    //oci_bind_by_name($s, ":end", $end_sample);
                    //oci_bind_by_name($s, ":background", $background);

                    $r = oci_execute($s);

                    if (!$r) {
                        $e = oci_error($s);

                        $records [] = array('sid' => 0,
                            'sql_text' => 'Could not execute statement: ' . $e['message'],
                            'sql_id' => '',
                            'serial' => '',
                            'username' => '',
                            'command' => '',
                            'state' => '',
                            'client_info' => '',
                            'event' => '',
                            'wait_time' => 0,
                            'seconds_in_wait' => 0,
                            'logon_time' => '',
                            'schemaname' => '',
                            'osuser' => '',
                            'machine' => '',
                            'terminal' => '',
                            'action' => '',
                            'pct' => '',
                            'pct_pga' => ''
                        );
                    }
                    else
                    {
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                            $records [] = array('sid' => $row['SID'],
                                'sql_text' => $row['SQL_TEXT'],
                                'sql_id' => $row['SQL_ID'],
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
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);

            $response = array(EXT_JSON_READER_TOTAL => count($records),
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
         WHERE wait_class# = :wait_class_other)
          other,
          (SELECT NVL (COUNT (*), 0)
                              FROM v\$session
                             WHERE wait_time != :cpu_wait_time and status = :cpu_status AND TYPE != :background) cpu,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_application)
          application,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_configuration)
          configuration,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_administrative)
          administrative,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_concurrency)
          concurrency,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_commit)
          commit,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_network)
          network,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_userio)
          userio,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_systemio)
          systemio,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_scheduler)
          scheduler,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_clustering)
          clustering,
       (SELECT NVL (COUNT (*), 0)
          FROM v\$session_wait
         WHERE wait_class# = :wait_class_queueing)
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
                    'sample_id' => '',
                    'configuration' => 0,
                    'administrative' => 0,
                    'concurrency' => 0,
                    'commit' => 0,
                    'network' => 0,
                    'userio' => 0,
                    'cpu' => 0,
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
                        'sample_id' => '',
                        'administrative' => 0,
                        'concurrency' => 0,
                        'commit' => 0,
                        'network' => 0,
                        'userio' => 0,
                        'cpu' => 0,
                        'systemio' => 0,
                        'scheduler' => 0,
                        'clustering' => 0,
                        'queueing' => 0,
                        'comments' => $e['message']);
                } else {
                    //WHERE wait_time != :cpu_wait_time and status = :cpu_status) cpu,
                    $background = 'BACKGROUND';
                    oci_bind_by_name($s, ":background", $background);

                    $cpu_wait_time = 0;
                    oci_bind_by_name($s, ":cpu_wait_time", $cpu_wait_time);

                    $cpu_status = 'ACTIVE';
                    oci_bind_by_name($s, ":cpu_status", $cpu_status);

                    $wait_class_other = 0;
                    oci_bind_by_name($s, ":wait_class_other", $wait_class_other);

                    $wait_class_application = 1;
                    oci_bind_by_name($s, ":wait_class_application", $wait_class_application);

                    $wait_class_configuration = 2;
                    oci_bind_by_name($s, ":wait_class_configuration", $wait_class_configuration);

                    $wait_class_administrative = 3;
                    oci_bind_by_name($s, ":wait_class_administrative", $wait_class_administrative);

                    $wait_class_concurrency = 4;
                    oci_bind_by_name($s, ":wait_class_concurrency", $wait_class_concurrency);

                    $wait_class_commit = 5;
                    oci_bind_by_name($s, ":wait_class_commit", $wait_class_commit);

                    $wait_class_network = 7;
                    oci_bind_by_name($s, ":wait_class_network", $wait_class_network);

                    $wait_class_userio = 8;
                    oci_bind_by_name($s, ":wait_class_userio", $wait_class_userio);

                    $wait_class_systemio = 9;
                    oci_bind_by_name($s, ":wait_class_systemio", $wait_class_systemio);

                    $wait_class_scheduler = 10;
                    oci_bind_by_name($s, ":wait_class_scheduler", $wait_class_scheduler);

                    $wait_class_clustering = 11;
                    oci_bind_by_name($s, ":wait_class_clustering", $wait_class_clustering);

                    $wait_class_queueing = 12;
                    oci_bind_by_name($s, ":wait_class_queueing", $wait_class_queueing);

                    $r = oci_execute($s);
                    if (!$r) {
                        $e = oci_error($s);

                        $records[] = array(
                            'date' => $start_date,
                            'other' => 0,
                            'application' => 0,
                            'configuration' => 0,
                            'sample_id' => '',
                            'administrative' => 0,
                            'concurrency' => 0,
                            'commit' => 0,
                            'network' => 0,
                            'userio' => 0,
                            'cpu' => 0,
                            'systemio' => 0,
                            'scheduler' => 0,
                            'clustering' => 0,
                            'queueing' => 0,
                            'comments' => $e['message']);
                    } else {
                        $index = 0;
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                            $records[] = array(
                                'date' => $start_date,
                                'sample_id' => $index,
                                'other' => $row['OTHER'],
                                'application' => $row['APPLICATION'],
                                'configuration' => $row['CONFIGURATION'],
                                'administrative' => $row['ADMINISTRATIVE'],
                                'concurrency' => $row['CONCURRENCY'],
                                'commit' => $row['COMMIT'],
                                'network' => $row['NETWORK'],
                                'userio' => $row['USERIO'],
                                'cpu' => $row['CPU'],
                                'systemio' => $row['SYSTEMIO'],
                                'scheduler' => $row['SCHEDULER'],
                                'clustering' => $row['CLUSTERING'],
                                'queueing' => $row['QUEUEING'],
                                'comments' => '');

                            $index++;
                        }
                    }
                }

                oci_free_statement($s);
            }

            oci_close($c);

            $response = array(EXT_JSON_READER_TOTAL => count($records),
                EXT_JSON_READER_ROOT => $records);
            break;

        case 'ash_waits':
            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $databases_id = $_REQUEST['databases_id'];
            $time = $_REQUEST['sample_time'];

            $start_date = date("Y-m-d H:i:s");

            $sum = 0;

            $query = "SELECT ash.sample_id,ash.sample_time,
         NVL (userio.nbre, 0) userio,
         NVL (systemio.nbre, 0) systemio,
         NVL (application.nbre, 0) application,
         NVL (other.nbre, 0) other,
         NVL (configuration.nbre, 0) configuration,
         NVL (administrative.nbre, 0) administrative,
         NVL (concurrency.nbre, 0) concurrency,
         NVL (comm.nbre, 0) comm,
         NVL (cpu.nbre, 0) cpu,
         NVL (net.nbre, 0) net,
         NVL (sched.nbre, 0) sched
    FROM v\$active_session_history ash
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_userio
                          GROUP BY sample_time) userio
            ON (ash.sample_time = userio.sample_time)
            LEFT OUTER JOIN (SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class is null
                          GROUP BY sample_time) cpu
            ON (ash.sample_time = cpu.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_systemio
                          GROUP BY sample_time) systemio
            ON (ash.sample_time = systemio.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_application
                          GROUP BY sample_time) application
            ON (ash.sample_time = application.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_other
                          GROUP BY sample_time) other
            ON (ash.sample_time = other.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_configuration
                          GROUP BY sample_time) configuration
            ON (ash.sample_time = configuration.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_administrative
                          GROUP BY sample_time) administrative
            ON (ash.sample_time = administrative.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_concurrency
                          GROUP BY sample_time) concurrency
            ON (ash.sample_time = concurrency.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_commit
                          GROUP BY sample_time) comm
            ON (ash.sample_time = comm.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_network
                          GROUP BY sample_time) net
            ON (ash.sample_time = net.sample_time)
         LEFT OUTER JOIN (  SELECT sample_time, NVL (COUNT (*), 0) nbre
                              FROM v\$active_session_history
                             WHERE wait_class_id = :wait_class_scheduler
                          GROUP BY sample_time) sched
            ON (ash.sample_time = sched.sample_time)";

            if(isset($time) && !empty($time))
            {
                $query = $query . " where ash.sample_time >= '" . $time . "'";
            }
            else
            {
                $query = $query . " where ash.sample_time >= sysdate - 1/24";
            }

            $query = $query . " GROUP BY ash.sample_time,ash.sample_id,
         userio.nbre,
         systemio.nbre,
         application.nbre,
         other.nbre,
         configuration.nbre,
         administrative.nbre,
         concurrency.nbre,
         comm.nbre,
         net.nbre,
         sched.nbre,
         cpu.nbre
ORDER BY ash.sample_time";

            $records = array();

            $sample_time = null;

            $c = oci_connect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();

                $records[] = array(
                    'date' => $start_date,
                    'other' => 0,
                    'application' => 0,
                    'configuration' => 0,
                    'sample_id' => '',
                    'administrative' => 0,
                    'concurrency' => 0,
                    'commit' => 0,
                    'network' => 0,
                    'userio' => 0,
                    'cpu' => 0,
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
                        'sample_id' => '',
                        'administrative' => 0,
                        'concurrency' => 0,
                        'commit' => 0,
                        'network' => 0,
                        'userio' => 0,
                        'cpu' => 0,
                        'systemio' => 0,
                        'scheduler' => 0,
                        'clustering' => 0,
                        'queueing' => 0,
                        'comments' => $e['message']);
                } else {
                    //oci_bind_by_name($s, ":sample", $stime);

                    $wait_class_other = 1893977003;
                    oci_bind_by_name($s, ":wait_class_other", $wait_class_other);

                    $wait_class_application = 4217450380;
                    oci_bind_by_name($s, ":wait_class_application", $wait_class_application);

                    $wait_class_configuration = 3290255840;
                    oci_bind_by_name($s, ":wait_class_configuration", $wait_class_configuration);

                    $wait_class_administrative = 4166625743;
                    oci_bind_by_name($s, ":wait_class_administrative", $wait_class_administrative);

                    $wait_class_concurrency = 3875070507;
                    oci_bind_by_name($s, ":wait_class_concurrency", $wait_class_concurrency);

                    $wait_class_commit = 3386400367;
                    oci_bind_by_name($s, ":wait_class_commit", $wait_class_commit);

                    $wait_class_network = 2000153315;
                    oci_bind_by_name($s, ":wait_class_network", $wait_class_network);

                    $wait_class_userio = 1740759767;
                    oci_bind_by_name($s, ":wait_class_userio", $wait_class_userio);

                    $wait_class_systemio = 4108307767;
                    oci_bind_by_name($s, ":wait_class_systemio", $wait_class_systemio);

                    $wait_class_scheduler = 2396326234;
                    oci_bind_by_name($s, ":wait_class_scheduler", $wait_class_scheduler);

                    $r = oci_execute($s);
                    if (!$r) {
                        $e = oci_error($s);

                        $records[] = array(
                            'date' => $start_date,
                            'other' => 0,
                            'sample_id' => '',
                            'application' => 0,
                            'configuration' => 0,
                            'administrative' => 0,
                            'concurrency' => 0,
                            'commit' => 0,
                            'network' => 0,
                            'userio' => 0,
                            'systemio' => 0,
                            'cpu' => 0,
                            'scheduler' => 0,
                            'clustering' => 0,
                            'queueing' => 0,
                            'comments' => $e['message']);

                        //var_dump($stime);
                    } else {
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                            $records[] = array(
                                'date' => $row['SAMPLE_TIME'],
                                'sample_id' => $row['SAMPLE_ID'],
                                'other' => $row['OTHER'],
                                'application' => $row['APPLICATION'],
                                'configuration' => $row['CONFIGURATION'],
                                'administrative' => $row['ADMINISTRATIVE'],
                                'concurrency' => $row['CONCURRENCY'],
                                'commit' => $row['COMM'],
                                'network' => $row['NET'],
                                'cpu' => $row['CPU'],
                                'userio' => $row['USERIO'],
                                'systemio' => $row['SYSTEMIO'],
                                'scheduler' => $row['SCHED'],
                                'clustering' => $row['CLUSTERING'],
                                'queueing' => 0,
                                'comments' => '');

                            $sample_time = $row['SAMPLE_TIME'];
                        }
                    }
                }

                oci_free_statement($s);
            }

            oci_close($c);

            $response = array(EXT_JSON_READER_TOTAL => count($records),
                EXT_JSON_READER_ROOT => $records,'sample_time' => $sample_time);
            break;

        case 'list_sql':
            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $user_id = $_REQUEST['user_id'];
            $databases_id = $_REQUEST['databases_id'];

            $start_date = date("Y-m-d H:i:s");

            $query = "SELECT buffer_gets,
         executions,
         disk_reads,
         ROUND (disk_reads / executions) as reads_exec,
         ROUND (buffer_gets / executions) as gets_exec,
         rows_processed,
         round(rows_processed / executions) as rows_exec,
         parse_calls,
         version_count,
         ROUND (cpu_time / 1000000/60) cpu_time,
         ROUND (elapsed_time / 1000000/60) elapsed_time,
         sorts,
         sql_text,
         sql_id
    FROM v\$sqlarea where executions > 0 ";
   //WHERE ROUND (elapsed_time / 1000000/60) >= 5 AND executions > 0
//ORDER BY elapsed_time DESC";

            if(is_numeric($user_id))
            {
                $query = $query . " and PARSING_USER_ID = " . $user_id . " order by FIRST_LOAD_TIME";
            }
            else
            {
                $query = $query . " and ROUND (elapsed_time / 1000000/60) >= 1 AND executions > 0 ORDER BY elapsed_time DESC";
            }

            $records = array();

            $c = oci_connect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();

                $records[] = array(
                    'buffer_gets' => '',
                    'executions' => '',
                    'disk_reads' => '',
                    'reads_exec' => '',
                    'gets_exec' => '',
                    'rows_processed' => '',
                    'rows_exec' => '',
                    'parse_calls' => '',
                    'version_count' => '',
                    'cpu_time' => '',
                    'elapsed_time' => '',
                    'sorts' => '',
                    'sql_text' => $e['message'],
                    'sql_id' => '');
            } else {
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);

                    $records[] = array(
                        'buffer_gets' => '',
                        'executions' => '',
                        'disk_reads' => '',
                        'reads_exec' => '',
                        'gets_exec' => '',
                        'rows_processed' => '',
                        'rows_exec' => '',
                        'parse_calls' => '',
                        'version_count' => '',
                        'cpu_time' => '',
                        'elapsed_time' => '',
                        'sorts' => '',
                        'sql_text' => $e['message'],
                        'sql_id' => '');
                } else {
                    $r = oci_execute($s);
                    if (!$r) {
                        $e = oci_error($s);

                        $records[] = array(
                            'buffer_gets' => '',
                            'executions' => '',
                            'disk_reads' => '',
                            'reads_exec' => '',
                            'gets_exec' => '',
                            'rows_processed' => '',
                            'rows_exec' => '',
                            'parse_calls' => '',
                            'version_count' => '',
                            'cpu_time' => '',
                            'elapsed_time' => '',
                            'sorts' => '',
                            'sql_text' => $e['message'],
                            'sql_id' => '');
                    } else {
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                            $records[] = array(
                                'buffer_gets' => $row['BUFFER_GETS'],
                                'executions' => $row['EXECUTIONS'],
                                'disk_reads' => $row['DISK_READS'],
                                'reads_exec' => $row['READS_EXEC'],
                                'gets_exec' => $row['GETS_EXEC'],
                                'rows_processed' => $row['ROWS_PROCESSED'],
                                'rows_exec' => $row['ROWS_EXEC'],
                                'parse_calls' => $row['PARSE_CALLS'],
                                'version_count' => $row['VERSION_COUNT'],
                                'cpu_time' => $row['CPU_TIME'],
                                'elapsed_time' => $row['ELAPSED_TIME'],
                                'sorts' => $row['SORTS'],
                                'sql_text' => $row['SQL_TEXT'],
                                'sql_id' => $row['SQL_ID']
                            );
                        }
                    }
                }

                oci_free_statement($s);
            }

            oci_close($c);

            $response = array(EXT_JSON_READER_TOTAL => count($records),
                EXT_JSON_READER_ROOT => $records);
            break;

        case 'list_histo':
            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $event = strtolower($_REQUEST['event']);
            $databases_id = $_REQUEST['databases_id'];

            $start_date = date("Y-m-d H:i:s");

            $sum = 0;
            $query = "SELECT event,round(wait_time_milli/1000) wait_time,wait_count,last_update_time FROM v\$event_histogram WHERE last_update_time is not null and lower(event) = '" . $event . "' and wait_time_milli > 1000 order by last_update_time";

            $records = array();

            $c = oci_connect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();

                $records[] = array(
                    'date' => $start_date,
                    'event' => '',
                    'wait_time_milli' => 0,
                    'wait_count' => 0,
                    'last_update_time' => '',
                    'comments' => $e['message']);
            } else {
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);

                    $records[] = array(
                        'date' => $start_date,
                        'event' => '',
                        'wait_time_milli' => 0,
                        'wait_count' => 0,
                        'last_update_time' => '',
                        'comments' => $e['message']);
                } else {
                    $r = oci_execute($s);
                    if (!$r) {
                        $e = oci_error($s);

                        $records[] = array(
                            'date' => $start_date,
                            'event' => '',
                            'wait_time_milli' => 0,
                            'wait_count' => 0,
                            'last_update_time' => '',
                            'comments' => $e['message']);
                    } else {
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                            $records[] = array(
                                'date' => $row['LAST_UPDATE_TIME'],
                                'event' => $row['EVENT'],
                                'wait_time' => $row['WAIT_TIME'],
                                'wait_count' => $row['WAIT_COUNT'],
                                'last_update_time' => $row['LAST_UPDATE_TIME'],
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

        case 'list_memhisto':
            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $databases_id = $_REQUEST['databases_id'];

            $start_date = date("Y-m-d H:i:s");

            $sum = 0;
            $query = "select sga.allo sga, pga.allo pga,SN.END_INTERVAL_TIME time
  from
(select snap_id,INSTANCE_NUMBER,round(sum(bytes)/1024/1024) allo
   from DBA_HIST_SGASTAT
  group by snap_id,INSTANCE_NUMBER) sga
,(select snap_id,INSTANCE_NUMBER,round(sum(value)/1024/1024) allo
    from DBA_HIST_PGASTAT where name = 'total PGA allocated'
   group by snap_id,INSTANCE_NUMBER) pga
, dba_hist_snapshot sn
where sn.snap_id=sga.snap_id
  and sn.INSTANCE_NUMBER=sga.INSTANCE_NUMBER
  and sn.snap_id=pga.snap_id
  and sn.INSTANCE_NUMBER=pga.INSTANCE_NUMBER and SN.END_INTERVAL_TIME >= ADD_MONTHS(sysdate,-1)
order by sn.snap_id";

            $records = array();

            $c = oci_connect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();

                $records[] = array(
                    'time' => $start_date,
                    'sga' => 0,
                    'pga' => 0,
                    'comments' => $e['message']);
            } else {
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);

                    $records[] = array(
                        'time' => $start_date,
                        'sga' => 0,
                        'pga' => 0,
                        'comments' => $e['message']);
                } else {
                    $r = oci_execute($s);
                    if (!$r) {
                        $e = oci_error($s);

                        $records[] = array(
                            'time' => $start_date,
                            'sga' => 0,
                            'pga' => 0,
                            'comments' => $e['message']);
                    } else {
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                            $records[] = array(
                                'time' => $row['TIME'],
                                'sga' => $row['SGA'],
                                'pga' => $row['PGA'],
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

        case 'list_sgahisto':
            $db_user = $_REQUEST['db_user'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $databases_id = $_REQUEST['databases_id'];

            $start_date = date("Y-m-d H:i:s");

            $sum = 0;
            $query = "SELECT other.taille other,
         jav.taille AS jav,
         strm.taille strm,
         sharedd.taille sharedd,
         large.taille large,
         SN.END_INTERVAL_TIME time
    FROM (  SELECT snap_id, ROUND (SUM (bytes) / 1024 / 1024) taille
              FROM DBA_HIST_SGASTAT sga
             WHERE pool IS NULL
          GROUP BY snap_id) other,
         (  SELECT snap_id, ROUND (SUM (bytes) / 1024 / 1024) taille
              FROM DBA_HIST_SGASTAT sga
             WHERE pool = 'java pool'
          GROUP BY snap_id) jav,
         (  SELECT snap_id, ROUND (SUM (bytes) / 1024 / 1024) taille
              FROM DBA_HIST_SGASTAT sga
             WHERE pool = 'streams pool'
          GROUP BY snap_id) strm,
         (  SELECT snap_id, ROUND (SUM (bytes) / 1024 / 1024) taille
              FROM DBA_HIST_SGASTAT sga
             WHERE pool = 'shared pool'
          GROUP BY snap_id) sharedd,
         (  SELECT snap_id, ROUND (SUM (bytes) / 1024 / 1024) taille
              FROM DBA_HIST_SGASTAT sga
             WHERE pool = 'large pool'
          GROUP BY snap_id) large,
         dba_hist_snapshot sn
   WHERE     sn.snap_id = other.snap_id
         AND sn.snap_id = strm.snap_id
         AND sn.snap_id = sharedd.snap_id
         AND sn.snap_id = large.snap_id
         AND sn.snap_id = jav.snap_id
         AND SN.END_INTERVAL_TIME >= ADD_MONTHS (SYSDATE, -1)
ORDER BY sn.snap_id";

            $records = array();

            $c = oci_connect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();

                $records[] = array(
                    'time' => $start_date,
                    'other' => 0,
                    'java' => 0,
                    'streams' => 0,
                    'shared' => 0,
                    'large' => 0,
                    'comments' => $e['message']);
            } else {
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);

                    $records[] = array(
                        'time' => $start_date,
                        'other' => 0,
                        'java' => 0,
                        'streams' => 0,
                        'shared' => 0,
                        'large' => 0,
                        'comments' => $e['message']);
                } else {
                    $r = oci_execute($s);
                    if (!$r) {
                        $e = oci_error($s);

                        $records[] = array(
                            'time' => $start_date,
                            'other' => 0,
                            'java' => 0,
                            'streams' => 0,
                            'shared' => 0,
                            'large' => 0,
                            'comments' => $e['message']);
                    } else {
                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                            $records[] = array(
                                'time' => $row['TIME'],
                                'other' => $row['OTHER'],
                                'java' => $row['JAV'],
                                'streams' => $row['STRM'],
                                'shared' => $row['SHAREDD'],
                                'large' => $row['LARGE'],
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