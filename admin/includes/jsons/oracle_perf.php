<?php

include('includes/modules/Net/SSH2.php');
require('includes/classes/reports.php');
class toC_Json_Oracle_Perf
{
    function listIoevents()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $class = $_REQUEST['class'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "select event,count(*) from v\$event_histogram where last_update_time is not null and wait_time_milli > 1000 and event in (select name from v\$event_name where wait_class = '" . $class . "') group by event having count(*) > 1";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records[] = array('event' => "Impossible d'executer cette requete : " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records[] = array('event' => "Impossible de lister les evenements IO : " . htmlentities($e['message']));
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records[] = array('event' => $row['EVENT']);
                    }
                }

                oci_free_statement($s);
            }

            oci_close($c);
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listSnapshots()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $start_id = $_REQUEST['start_id'];
        $total = 0;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            if (!empty($start_id)) {
                $query = "SELECT count(snap_id) nbre FROM DBA_HIST_SNAPSHOT WHERE dbid = (SELECT DISTINCT dbid FROM DBA_HIST_DATABASE_INSTANCE) AND snap_id > " . $start_id . " AND startup_time <= (SELECT MIN (startup_time) FROM DBA_HIST_SNAPSHOT WHERE snap_id > " . $start_id . ")";
            } else {
                $query = "SELECT count(snap_id) nbre FROM DBA_HIST_SNAPSHOT WHERE dbid = (SELECT DISTINCT dbid FROM DBA_HIST_DATABASE_INSTANCE)";
            }

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les captures AWR de cette base ' . htmlentities($e['message']));
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total = $row['NBRE'];
                    }
                }
            }

            if (!empty($start_id)) {
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT snap_id,TO_CHAR (begin_interval_time, 'yyyy/mm/dd hh24:mi:ss') begin_interval_time,TO_CHAR (end_interval_time, 'yyyy/mm/dd hh24:mi:ss') end_interval_time,TO_CHAR (startup_time, 'yyyy/mm/dd hh24:mi:ss') startup_time FROM DBA_HIST_SNAPSHOT WHERE     dbid = (SELECT DISTINCT dbid FROM DBA_HIST_DATABASE_INSTANCE) AND snap_id > " . $start_id . " AND startup_time <= (SELECT MIN (startup_time) FROM DBA_HIST_SNAPSHOT WHERE snap_id > " . $start_id . ") ORDER BY 1) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            } else {
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT snap_id,TO_CHAR (begin_interval_time, 'yyyy/mm/dd hh24:mi:ss') begin_interval_time,TO_CHAR (end_interval_time, 'yyyy/mm/dd hh24:mi:ss') end_interval_time,TO_CHAR (startup_time, 'yyyy/mm/dd hh24:mi:ss') startup_time FROM DBA_HIST_SNAPSHOT WHERE     dbid = (SELECT DISTINCT dbid FROM DBA_HIST_DATABASE_INSTANCE) ORDER BY 1) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            }

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                //oci_bind_by_name($s, ":tablespace_name", $tbs);
                $fin = $start + $limit;
                oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les captures AWR de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('snap_id' => $row['SNAP_ID'], 'begin_interval_time' => $row['BEGIN_INTERVAL_TIME'], 'end_interval_time' => $row['END_INTERVAL_TIME'], 'startup_time' => $row['STARTUP_TIME']);
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array('success' => true, 'feedback' => $total . ' captures', EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function runAsh()
    {
        global $toC_Json;

        $ssh = new Net_SSH2(REPORT_RUNNER, '22');

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
        } else {
            if (!$ssh->login("guyfomi", "12345")) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
            } else {
                $ssh->disableQuietMode();

                $data = $_REQUEST;
                $characters = '0123456789';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 10; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=oracle_perf&action=ash_report&startValue=" . $data['startValue'] . "&endValue=" . $data['endValue'] . "&db_host=" . $data['db_host'] . "&db_user=" . $data['db_user'] . "&db_pass=" . $data['db_pass'] . "&db_sid=" . $data['db_sid'] . "&databases_id=" . $data['databases_id'] . "&task_id=" . $randomString . "' &";
                $ssh->exec($cmd);
                //var_dump($cmd);

                $ssh->disconnect();

                $detail = array('task_id' => $randomString, 'status' => 'run', 'comments' => 'Job cree avec succes');
                toC_Reports_Admin::addJobDetail($detail);

                $response = array('success' => true, 'msg' => 'Tache creee avec succes', 'task_id' => $randomString, 'status' => 'run');
            }
        }

        echo $toC_Json->encode($response);
    }

    function getSqltext()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $sql_id = $_REQUEST['sql_id'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']),'sql_text' => '');
        } else {
            $query = "SELECT to_char(REPLACE(sql_text, CHR(00), ' ')) sql_text
FROM v\$sqlarea
WHERE sql_id = '" . $sql_id . "'
UNION
SELECT to_char(REPLACE(sql_text, CHR(00), ' ')) sql_text
FROM dba_hist_sqltext
WHERE sql_id  = '" . $sql_id . "'
AND sql_text IS NOT NULL";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']),'sql_text' => '');
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => htmlentities($e['message']),'sql_text' => '');
                } else {
                    $sql_text ='';
                    $index = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC)) && $index == 0) {
                        $sql_text = $row['SQL_TEXT'];
                        $index ++;
                    }

                    $response = array('success' => true, 'feedback' => '','sql_text' => $sql_text);
                }

                oci_free_statement($s);
            }
            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function ashReport()
    {
        error_reporting(E_ALL);
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $endValue = $_REQUEST['endValue'];
        $startValue = $_REQUEST['startValue'];
        $data = $_REQUEST;

        $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Connexion à la BD ...');
        toC_Reports_Admin::addJobDetail($detail);

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Could not connect to database: ' . htmlentities($e['message']));
            toC_Reports_Admin::addJobDetail($detail);
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Recuperation des metadonnees ...');
            toC_Reports_Admin::addJobDetail($detail);
            $minutes = 15;

            parse:
            if(isset($endValue) && !empty($endValue) && isset($startValue) && !empty($startValue))
            {
                $query = "select TO_char(min(SAMPLE_TIME),'YYYY/MM/DD HH24:MI:SS') START_SNAP,TO_char(max(SAMPLE_TIME),'YYYY/MM/DD HH24:MI:SS') END_SNAP from v\$active_session_history where sample_time between '" . $startValue . "' and '" . $endValue . "'";
            }
            else
            {
                $query = "select TO_char(min(SAMPLE_TIME) + $minutes/1440,'YYYY/MM/DD HH24:MI:SS') START_SNAP,TO_char(max(SAMPLE_TIME),'YYYY/MM/DD HH24:MI:SS') END_SNAP from v\$active_session_history";
            }

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de charger les metadonnees " . htmlentities($e['message']));
                toC_Reports_Admin::addJobDetail($detail);
                $response = array('success' => false, 'feedback' => "Impossible de charger les snap id " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                    toC_Reports_Admin::addJobDetail($detail);
                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                } else {
                    $start_snap = 0;
                    $end_snap = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $start_snap = $row['START_SNAP'];
                        $end_snap = $row['END_SNAP'];
                    }

                    oci_free_statement($s);

                    $query = "SELECT DISTINCT dbid, db_name, instance_number  FROM DBA_HIST_DATABASE_INSTANCE";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                        toC_Reports_Admin::addJobDetail($detail);
                        $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                            toC_Reports_Admin::addJobDetail($detail);
                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                        } else {
                            $dbid = -1;
                            $instance_number = 0;
                            $db_name = '???????????';

                            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                                $dbid = $row['DBID'];
                                $db_name = $row['DB_NAME'];
                                $instance_number = $row['INSTANCE_NUMBER'];
                            }

                            oci_free_statement($s);

                            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Generation du rapport ...');
                            toC_Reports_Admin::addJobDetail($detail);

                            $query = "Select * from table(dbms_workload_repository.ASH_REPORT_HTML(l_dbid => " . $dbid . ",l_inst_num => " . $instance_number . ", l_btime => " . " TO_DATE('" . $start_snap . "','YYYY/MM/DD HH24:MI:SS')," . "l_etime => " . " TO_DATE('" . $end_snap . "','YYYY/MM/DD HH24:MI:SS')," . "l_options => 0,l_slot_width => 0,l_sid => null,l_sql_id => null,l_wait_class => null,l_service_hash => null,l_module => null,l_action => null,l_client_id => null,l_plsql_entry => null))";
                            $s = oci_parse($c, $query);

                            if (!$s) {
                                $e = oci_error($c);
                                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de generer ASH " . htmlentities($e['message']));
                                toC_Reports_Admin::addJobDetail($detail);
                                $response = array('success' => false, 'feedback' => "Impossible de generer ASH " . htmlentities($e['message']));
                            } else {
                                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                if (!$r) {
                                    $e = oci_error($s);
                                    if (strpos($e['message'], '01843') !== false)
                                    {
                                        $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => htmlentities($e['message']));
                                        toC_Reports_Admin::addJobDetail($detail);
                                        $minutes = $minutes + 15;
                                        goto parse;
                                    }
                                    else
                                    {
                                        $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                                        toC_Reports_Admin::addJobDetail($detail);
                                        $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                    }
                                } else {
                                    oci_fetch_all($s, $output);

                                    oci_free_statement($s);
                                    oci_close($c);

                                    $out = '';

                                    foreach ($output as $col) {
                                        foreach ($col as $item) {
                                            $out = $out . $item;
                                        }
                                    }

                                    $dir = realpath(DIR_WS_REPORTS) . '/';
                                    if (!file_exists($dir)) {
                                        mkdir($dir, 0777, true);
                                    }

                                    $report = 'ash_' . $data['task_id'] . '.html';
                                    $file_name = $dir . '/' . $report;

                                    $b = file_put_contents($file_name, $out);

                                    if ($b > 0) {
                                        $detail = array('task_id' => $data['task_id'], 'status' => 'complete', 'comments' => $report);
                                        toC_Reports_Admin::addJobDetail($detail);

                                        if (isset($data['to']) && !empty($data['to']) && isset($data['subject']) && !empty($data['subject'])) {
                                            $data['body'] = $out;
                                            toC_Reports_Admin::sendEmail($data);
                                        }
                                    } else {
                                        $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de creer le fichier de rapport");
                                        toC_Reports_Admin::addJobDetail($detail);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function runAddm()
    {
        global $toC_Json;

        $ssh = new Net_SSH2(REPORT_RUNNER, '22');

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
        } else {
            if (!$ssh->login("guyfomi", "12345")) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
            } else {
                $ssh->disableQuietMode();

                $data = $_REQUEST;
                $characters = '0123456789';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 10; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=oracle_perf&action=addm_report&db_host=" . $data['db_host'] . "&db_user=" . $data['db_user'] . "&db_pass=" . $data['db_pass'] . "&db_sid=" . $data['db_sid'] . "&databases_id=" . $data['databases_id'] . "&task_id=" . $randomString . "' &";
                $ssh->exec($cmd);
                //var_dump($cmd);

                $ssh->disconnect();

                $detail = array('task_id' => $randomString, 'status' => 'run', 'comments' => 'Job cree avec succes');
                toC_Reports_Admin::addJobDetail($detail);

                $response = array('success' => true, 'msg' => 'Tache creee avec succes', 'task_id' => $randomString, 'status' => 'run');
            }
        }

        echo $toC_Json->encode($response);
    }

    function addmReport()
    {
        error_reporting(E_ALL);
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $data = $_REQUEST;

        $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'recuperation des parametres ...');
        toC_Reports_Admin::addJobDetail($detail);

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Could not connect to database: ' . htmlentities($e['message']));
            toC_Reports_Admin::addJobDetail($detail);
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "SELECT (SELECT MIN (snap_id)
          FROM DBA_HIST_SNAPSHOT
         WHERE startup_time =
                  (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT))
          start_snap,
       (SELECT MAX (snap_id) FROM DBA_HIST_SNAPSHOT) end_snap
  FROM DUAL";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de charger les snap id " . htmlentities($e['message']));
                toC_Reports_Admin::addJobDetail($detail);
                $response = array('success' => false, 'feedback' => "Impossible de charger les snap id " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                    toC_Reports_Admin::addJobDetail($detail);
                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                } else {
                    $start_snap = 0;
                    $end_snap = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $start_snap = $row['START_SNAP'];
                        $end_snap = $row['END_SNAP'];
                    }

                    oci_free_statement($s);

                    $query = "SELECT DISTINCT dbid, db_name, instance_number  FROM DBA_HIST_DATABASE_INSTANCE";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                        toC_Reports_Admin::addJobDetail($detail);
                        $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                            toC_Reports_Admin::addJobDetail($detail);
                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                        } else {
                            $dbid = -1;
                            $instance_number = 0;
                            $db_name = '???????????';

                            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                                $dbid = $row['DBID'];
                                $db_name = $row['DB_NAME'];
                                $instance_number = $row['INSTANCE_NUMBER'];
                            }

                            oci_free_statement($s);

                            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Generation du rapport ...');
                            toC_Reports_Admin::addJobDetail($detail);

                            $characters = '0123456789';
                            $charactersLength = strlen($characters);
                            $randomString = '';
                            for ($i = 0; $i < 5; $i++) {
                                $randomString .= $characters[rand(0, $charactersLength - 1)];
                            }

                            $task_name = 'addm_report_' . $randomString;

                            $query = "DECLARE
  tname  VARCHAR2 (60);
  taskid  NUMBER;
  BEGIN
  tname := '" . $task_name . "'; dbms_advisor.create_task('ADDM', taskid, tname); dbms_advisor.set_task_parameter(tname, 'START_SNAPSHOT'," . $start_snap . "); dbms_advisor.set_task_parameter(tname, 'END_SNAPSHOT', " . $end_snap . "); dbms_advisor.set_task_parameter(tname, 'INSTANCE', " . $instance_number . "); dbms_advisor.set_task_parameter(tname, 'DB_ID', " . $dbid . "); dbms_advisor.execute_task(tname); END;";

                            $s = oci_parse($c, $query);
                            if (!$s) {
                                $e = oci_error($c);
                                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de generer ASH " . htmlentities($e['message']));
                                toC_Reports_Admin::addJobDetail($detail);
                                $response = array('success' => false, 'feedback' => "Impossible de generer ADDM " . htmlentities($e['message']));
                            } else {
                                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                if (!$r) {
                                    $e = oci_error($s);
                                    $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                                    toC_Reports_Admin::addJobDetail($detail);
                                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                } else {
                                    oci_free_statement($s);

                                    $query = "SELECT DBMS_ADVISOR.get_task_report ('" . $task_name . "','TEXT','ALL','ALL') report FROM DBA_ADVISOR_TASKS t WHERE     t.task_name = '" . $task_name . "' AND t.owner = SYS_CONTEXT ('USERENV', 'session_user')";
                                    $s = oci_parse($c, $query);

                                    if (!$s) {
                                        $e = oci_error($c);
                                        $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                                        toC_Reports_Admin::addJobDetail($detail);
                                        $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                        $response = array('success' => false, 'feedback' => "Impossible de generer ADDM REPORT " . htmlentities($e['message']));
                                    } else {
                                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                        if (!$r) {
                                            $e = oci_error($s);
                                            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                                            toC_Reports_Admin::addJobDetail($detail);
                                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                        } else {
                                            //oci_fetch_all($s, $output);

                                            $out = '';
                                            while ($row = oci_fetch_array($s, OCI_ASSOC + OCI_RETURN_LOBS)) {
                                                $out = $out . $row['REPORT'];
                                            }

                                            $dir = realpath(DIR_WS_REPORTS) . '/';
                                            if (!file_exists($dir)) {
                                                mkdir($dir, 0777, true);
                                            }

                                            $report = 'addm_' . '_' . $start_snap . '_' . $end_snap . '.txt';
                                            $file_name = $dir . '/' . $report;
                                            $b = file_put_contents($file_name, $out);

                                            oci_free_statement($s);
                                            oci_close($c);

                                            if ($b > 0) {
                                                $detail = array('task_id' => $data['task_id'], 'status' => 'complete', 'comments' => $report);
                                                toC_Reports_Admin::addJobDetail($detail);

                                                if (isset($data['to']) && !empty($data['to']) && isset($data['subject']) && !empty($data['subject'])) {
                                                    $data['body'] = $out;
                                                    toC_Reports_Admin::sendEmail($data);
                                                }
                                            } else {
                                                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de creer le fichier de rapport");
                                                toC_Reports_Admin::addJobDetail($detail);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listSqloptenv()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $sql_id = $_REQUEST['sql_id'];

        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('id' => '',
                'isdefault' => '',
                'name' => 'error',
                'value' => $e['message']);
        } else {
            $query = "SELECT id,isdefault,name,value
  FROM v\$sql_optimizer_env
 WHERE sql_id = '" . $sql_id . "' order by name";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('id' => '',
                    'isdefault' => '',
                    'name' => 'error',
                    'value' => $e['message']);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('id' => '',
                        'isdefault' => '',
                        'name' => 'error',
                        'value' => $e['message']);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('id' => $row['ID'],
                            'isdefault' => $row['ISDEFAULT'],
                            'name' => $row['NAME'],
                            'value' => $row['VALUE']);
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listSqltablestats()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $sql_id = $_REQUEST['sql_id'];

        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('owner' => '',
                'table_name' => $e['message'],
                'num_rows' => '',
                'blocks' => '',
                'empty_blocks' => '',
                'avg_free_space' => '',
                'chain_cnt' => '',
                'avg_row_len' => '',
                'sample_size' => '',
                'last_analyzed' => '',
                'stale_stats' => ''
            );
        } else {
            $query = "WITH object AS (
SELECT
       object_owner owner, object_name name
  FROM gv\$sql_plan
 WHERE sql_id = '" . $sql_id . "'
   AND object_owner IS NOT NULL
   AND object_name IS NOT NULL
 UNION
SELECT object_owner owner, object_name name
  FROM dba_hist_sql_plan
 WHERE sql_id = '" . $sql_id . "'
   AND object_owner IS NOT NULL
   AND object_name IS NOT NULL
)
SELECT t.owner, t.table_name,t.num_rows,t.BLOCKS,t.EMPTY_BLOCKS,t.AVG_SPACE AVG_FREE_SPACE,t.CHAIN_CNT,t.AVG_ROW_LEN,
t.SAMPLE_SIZE,t.LAST_ANALYZED,t.STALE_STATS
  FROM dba_tab_statistics t, -- include fixed objects
       object o
 WHERE t.owner = o.owner
   AND t.table_name = o.name
 UNION
SELECT i.table_owner, i.table_name,t1.num_rows,t1.BLOCKS,t1.EMPTY_BLOCKS,t1.AVG_SPACE AVG_FREE_SPACE,t1.CHAIN_CNT,t1.AVG_ROW_LEN,
t1.SAMPLE_SIZE,t1.LAST_ANALYZED,t1.STALE_STATS
  FROM dba_indexes i,dba_tab_statistics t1,
       object o
 WHERE i.owner = o.owner
   AND i.index_name = o.name
   AND t1.table_name = i.table_name";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('owner' => '',
                    'table_name' => $e['message'],
                    'num_rows' => '',
                    'blocks' => '',
                    'empty_blocks' => '',
                    'avg_free_space' => '',
                    'chain_cnt' => '',
                    'avg_row_len' => '',
                    'sample_size' => '',
                    'last_analyzed' => '',
                    'stale_stats' => ''
                );
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('owner' => '',
                        'table_name' => $e['message'],
                        'num_rows' => '',
                        'blocks' => '',
                        'empty_blocks' => '',
                        'avg_free_space' => '',
                        'chain_cnt' => '',
                        'avg_row_len' => '',
                        'sample_size' => '',
                        'last_analyzed' => '',
                        'stale_stats' => ''
                    );
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('owner' => $row['OWNER'],
                            'table_name' =>$row['TABLE_NAME'],
                            'num_rows' => $row['NUM_ROWS'],
                            'blocks' => $row['BLOCKS'],
                            'empty_blocks' => $row['EMPTY_BLOCKS'],
                            'avg_free_space' => $row['AVG_FREE_SPACE'],
                            'chain_cnt' => $row['CHAIN_CNT'],
                            'avg_row_len' => $row['AVG_ROW_LEN'],
                            'sample_size' => $row['SAMPLE_SIZE'],
                            'last_analyzed' => $row['LAST_ANALYZED'],
                            'stale_stats' => $row['STALE_STATS']
                        );
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listSqlindexestats()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $sql_id = $_REQUEST['sql_id'];

        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('owner' => '',
                'index_name' => $e['message'],
                'index_type' => '',
                'uniqueness' => '',
                'compression' => '',
                'pct_free' => '',
                'blevel' => '',
                'clustering_factor' => '',
                'last_analyzed' => ''
            );
        } else {

            $query = "SELECT object_owner owner, object_name index_name,i.INDEX_TYPE,i.UNIQUENESS,i.COMPRESSION,i.PCT_FREE,i.BLEVEL,i.CLUSTERING_FACTOR,i.LAST_ANALYZED
  FROM gv\$sql_plan,dba_indexes i
 WHERE sql_id = '" . $sql_id . "'
 and i.INDEX_NAME = object_name
   AND object_owner IS NOT NULL
   AND object_name IS NOT NULL
   AND (object_type LIKE '%INDEX%' OR operation LIKE '%INDEX%')
 UNION
SELECT object_owner owner, object_name index_name,i1.INDEX_TYPE,i1.UNIQUENESS,i1.COMPRESSION,i1.PCT_FREE,i1.BLEVEL,i1.CLUSTERING_FACTOR,i1.LAST_ANALYZED
  FROM dba_hist_sql_plan,dba_indexes i1
 WHERE sql_id = '" . $sql_id . "'
 and i1.INDEX_NAME = object_name
   AND object_owner IS NOT NULL
   AND object_name IS NOT NULL
   AND (object_type LIKE '%INDEX%' OR operation LIKE '%INDEX%')";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('owner' => '',
                    'index_name' => $e['message'],
                    'index_type' => '',
                    'uniqueness' => '',
                    'compression' => '',
                    'pct_free' => '',
                    'blevel' => '',
                    'clustering_factor' => '',
                    'last_analyzed' => ''
                );
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('owner' => '',
                        'index_name' => $e['message'],
                        'index_type' => '',
                        'uniqueness' => '',
                        'compression' => '',
                        'pct_free' => '',
                        'blevel' => '',
                        'clustering_factor' => '',
                        'last_analyzed' => ''
                    );
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('owner' => $row['OWNER'],
                            'index_name' =>$row['INDEX_NAME'],
                            'index_type' => $row['INDEX_TYPE'],
                            'uniqueness' => $row['UNIQUENESS'],
                            'compression' => $row['COMPRESSION'],
                            'pct_free' => $row['PCT_FREE'],
                            'blevel' => $row['BLEVEL'],
                            'clustering_factor' => $row['CLUSTERING_FACTOR'],
                            'last_analyzed' => $row['LAST_ANALYZED']
                        );
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function sqlareaUsage()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('username' => $e['message'],
                'user_id' => '',
                'sharable' => '',
                'persistent' => '',
                'runtime' => '',
                'areas' => '',
                'mem_sum' => '');
        } else {
            $query = "SELECT username,user_id,
         round(SUM (sharable_mem)/1024/1024) sharable,
         round(SUM (persistent_mem)/1024/1024) persistent,
         round(SUM (runtime_mem)/1024/1024) runtime,
         COUNT (*) areas,
         round(SUM (sharable_mem + persistent_mem + runtime_mem)/1024/1024) mem_sum
    FROM (SELECT username,user_id,
                 sharable_mem,
                 persistent_mem,
                 runtime_mem
            FROM sys.v_\$sqlarea a, dba_users b
           WHERE a.parsing_user_id = b.user_id) s
GROUP BY username,user_id
ORDER BY 7 desc";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('username' => $e['message'],
                    'user_id' => '',
                    'sharable' => '',
                    'persistent' => '',
                    'runtime' => '',
                    'areas' => '',
                    'mem_sum' => '');
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('username' => $e['message'],
                        'user_id' => '',
                        'sharable' => '',
                        'persistent' => '',
                        'runtime' => '',
                        'areas' => '',
                        'mem_sum' => '');
                } else {

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('username' => $row['USERNAME'],
                            'user_id' => $row['USER_ID'],
                            'sharable' => $row['SHARABLE'], 'persistent' => $row['PERSISTENT'], 'runtime' => $row['RUNTIME'], 'areas' => $row['AREAS'], 'mem_sum' => $row['MEM_SUM']);
                    }
                }
            }
            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function memoryResize()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $entry_icon = osc_icon_from_filename('xxxxxxxx.error');
            $records [] = array('icon' => $entry_icon,
                'index' => 0,
                'component' => htmlentities($e['message']),
                'parameter' => '',
                'status' => 'ERROR',
                'initial_size' => '',
                'target_size' => '',
                'final_size' => '',
                'start_time' => '',
                'end_time' => '',
                'duree' => '');
        } else {
            $query = "SELECT component,
       parameter,
       round(initial_size/1024/1024) initial_size,
       round(target_size/1024/1024) target_size,
       round(final_size/1024/1024) final_size,
       status,
       start_time,
       end_time,
       EXTRACT(SECOND FROM(end_time - start_time) DAY TO SECOND) as duree
    from V\$MEMORY_RESIZE_OPS order by start_time desc";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $entry_icon = osc_icon_from_filename('xxxxxxxx.error');
                $records [] = array('icon' => $entry_icon,
                    'index' => 0,
                    'component' => htmlentities($e['message']),
                    'parameter' => '',
                    'status' => 'ERROR',
                    'initial_size' => '',
                    'target_size' => '',
                    'final_size' => '',
                    'start_time' => '',
                    'end_time' => '',
                    'duree' => '');
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $entry_icon = osc_icon_from_filename('xxxxxxxx.error');
                    $records [] = array('icon' => $entry_icon,
                        'index' => 0,
                        'component' => htmlentities($e['message']),
                        'parameter' => '',
                        'status' => 'ERROR',
                        'initial_size' => '',
                        'target_size' => '',
                        'final_size' => '',
                        'start_time' => '',
                        'end_time' => '',
                        'duree' => '');
                } else {
                    $index = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $entry_icon = osc_icon_from_filename('xxxxxxxx.' . $row['STATUS']);
                        $records [] = array('icon' => $entry_icon,
                            'index' => $index,
                            'component' => $row['COMPONENT'],
                            'parameter' => $row['PARAMETER'],
                            'status' => $row['STATUS'],
                            'initial_size' => $row['INITIAL_SIZE'],
                            'target_size' => $row['TARGET_SIZE'],
                            'final_size' => $row['FINAL_SIZE'],
                            'start_time' => $row['START_TIME'],
                            'end_time' => $row['END_TIME'],
                            'duree' => $row['DUREE']);
                        $index++;
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listLatch()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('name' => $e['message'],
                'ind' => '',
                'misses' => '',
                'immediate_gets' => '',
                'immediate_misses' => '',
                'misses_ratio' => '',
                'gets' => '');
        }
        else
        {
            $query = "SELECT LATCH# ind,
         name,
         gets,
         misses,
         immediate_gets,
         round(wait_time/1000000) wait_time,
         immediate_misses,
         DECODE (gets, 0, 0, ROUND (misses / gets * 100)) misses_ratio
    FROM v\$latch
   WHERE misses > 0
ORDER BY misses_ratio DESC";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('name' => $e['message'],
                    'ind' => '',
                    'misses' => '',
                    'wait_time' => '',
                    'immediate_gets' => '',
                    'immediate_misses' => '',
                    'misses_ratio' => '',
                    'gets' => '');
            }
            else
            {
                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);

                    $records [] = array('name' => $e['message'],
                        'ind' => '',
                        'misses' => '',
                        'wait_time' => '',
                        'immediate_gets' => '',
                        'immediate_misses' => '',
                        'misses_ratio' => '',
                        'gets' => '');
                }
                else
                {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('name' => $row['NAME'],
                            'ind' => $row['IND'],
                            'misses' => $row['MISSES'],
                            'wait_time' => $row['WAIT_TIME'],
                            'immediate_gets' => $row['IMMEDIATE_GETS'],
                            'immediate_misses' => $row['IMMEDIATE_MISSES'],
                            'misses_ratio' => $row['MISSES_RATIO'],
                            'gets' => $row['GETS']);
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function awrReport()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $start_snap = $_REQUEST['start_snap'];
        $end_snap = $_REQUEST['end_snap'];
        $data = $_REQUEST;

        $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Connexion à la BD ...');
        toC_Reports_Admin::addJobDetail($detail);

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Could not connect to database: ' . htmlentities($e['message']));
            toC_Reports_Admin::addJobDetail($detail);

            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Recuperation des Parametres ...');
            toC_Reports_Admin::addJobDetail($detail);
            $query = "SELECT DISTINCT dbid, db_name, instance_number  FROM DBA_HIST_DATABASE_INSTANCE";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                toC_Reports_Admin::addJobDetail($detail);
                $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                    toC_Reports_Admin::addJobDetail($detail);

                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                } else {
                    $dbid = -1;
                    $instance_number = 0;
                    $db_name = '???????????';

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $dbid = $row['DBID'];
                        $db_name = $row['DB_NAME'];
                        $instance_number = $row['INSTANCE_NUMBER'];
                    }

                    oci_free_statement($s);

                    $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Execution du Script ...');
                    toC_Reports_Admin::addJobDetail($detail);

                    $query = "Select * from table(dbms_workload_repository.AWR_REPORT_HTML(" . $dbid . "," . $instance_number . "," . $start_snap . "," . $end_snap . ",8))";
                    $s = oci_parse($c, $query);

                    if (!$s) {
                        $e = oci_error($c);
                        $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de generer AWR_REPORT_HTML " . htmlentities($e['message']));
                        toC_Reports_Admin::addJobDetail($detail);
                        $response = array('success' => false, 'feedback' => "Impossible de generer AWR_REPORT_HTML " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                            toC_Reports_Admin::addJobDetail($detail);
                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                        } else {
                            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Generation du rapport ...');
                            toC_Reports_Admin::addJobDetail($detail);

                            $output = array();

                            oci_fetch_all($s, $output);

                            oci_free_statement($s);
                            oci_close($c);

                            $out = '';

                            foreach ($output as $col) {
                                foreach ($col as $item) {
                                    $out = $out . $item;
                                }
                            }

                            $dir = realpath(DIR_WS_REPORTS);
                            if (!file_exists($dir)) {
                                mkdir($dir, 0777, true);
                            }

                            $report = 'awr_' . $data['task_id'] . '.html';
                            $file_name = $dir . '/' . $report;

                            $b = file_put_contents($file_name, $out);

                            if ($b > 0) {
                                $detail = array('task_id' => $data['task_id'], 'status' => 'complete', 'comments' => $report);
                                toC_Reports_Admin::addJobDetail($detail);

                                if (isset($data['to']) && !empty($data['to']) && isset($data['subject']) && !empty($data['subject'])) {
                                    $data['body'] = $output;
                                    toC_Reports_Admin::sendEmail($data);
                                }

                                $response = array('success' => true);
                            } else {
                                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de creer le fichier de rapport : " . $query);
                                toC_Reports_Admin::addJobDetail($detail);

                                $response = array('success' => false);
                            }
                        }
                    }
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function runAwr()
    {
        global $toC_Json;

        $ssh = new Net_SSH2(REPORT_RUNNER, '22');

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
        } else {
            if (!$ssh->login("guyfomi", "12345")) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
            } else {
                $ssh->disableQuietMode();

                $data = $_REQUEST;
                $characters = '0123456789';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 10; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=databases&action=awr_report&db_host=" . $data['db_host'] . "&start_snap=" . $data['PARAM_START_SNAP'] . "&end_snap=" . $data['PARAM_END_SNAP'] . "&db_user=" . $data['db_user'] . "&db_pass=" . $data['db_pass'] . "&db_sid=" . $data['db_sid'] . "&databases_id=" . $data['databases_id'] . "&task_id=" . $randomString . "' &";
                $ssh->exec($cmd);
                //var_dump($cmd);

                $ssh->disconnect();

                $detail = array('task_id' => $randomString, 'status' => 'run', 'comments' => 'Job cree avec succes');
                toC_Reports_Admin::addJobDetail($detail);

                $response = array('success' => true, 'msg' => 'Tache creee avec succes', 'task_id' => $randomString, 'status' => 'run');
            }
        }

        echo $toC_Json->encode($response);
    }

    function listLibrarycache()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('namespace' => $e['message'],
                'reloads' => '',
                'gethits' => '',
                'pins' => '',
                'pinhits' => '',
                'invalidations' => '',
                'gets' => '',
                'get' => '',
                'pin' => '');
        }
        else
        {
            $query = "SELECT gets,gethits,pins,pinhits,namespace,reloads,invalidations,round(gethitratio * 100) get,round(pinhitratio * 100) pin FROM V\$LIBRARYCACHE ORDER BY namespace";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('namespace' => $e['message'],
                    'reloads' => '',
                    'gethits' => '',
                    'pins' => '',
                    'pinhits' => '',
                    'invalidations' => '',
                    'gets' => '',
                    'get' => '',
                    'pin' => '');
            }
            else
            {
                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('namespace' => $e['message'],
                        'reloads' => '',
                        'gethits' => '',
                        'pins' => '',
                        'pinhits' => '',
                        'invalidations' => '',
                        'gets' => '',
                        'get' => '',
                        'pin' => '');
                }
                else
                {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('namespace' => $row['NAMESPACE'],
                            'reloads' => $row['RELOADS'],
                            'invalidations' => $row['INVALIDATIONS'],
                            'gethits' => $row['GETHITS'],
                            'pins' => $row['PINS'],
                            'pinhits' => $row['PINHITS'],
                            'gets' => $row['GETS'],
                            'get' => $row['GET'],
                            'pin' => $row['PIN']);
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function sgaResize()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $entry_icon = osc_icon_from_filename('xxxxxxxx.error');
            $records [] = array('icon' => $entry_icon,
                'index' => 0,
                'component' => htmlentities($e['message']),
                'parameter' => '',
                'status' => 'ERROR',
                'initial_size' => '',
                'target_size' => '',
                'final_size' => '',
                'start_time' => '',
                'end_time' => '',
                'duree' => '');
        } else {
            $query = "SELECT component,
       parameter,
       round(initial_size/1024/1024) initial_size,
       round(target_size/1024/1024) target_size,
       round(final_size/1024/1024) final_size,
       status,
       start_time,
       end_time,
       EXTRACT(SECOND FROM(end_time - start_time) DAY TO SECOND) as duree
    from v\$sga_resize_ops  order by start_time desc";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $entry_icon = osc_icon_from_filename('xxxxxxxx.error');
                $records [] = array('icon' => $entry_icon,
                    'index' => 0,
                    'component' => htmlentities($e['message']),
                    'parameter' => '',
                    'status' => 'ERROR',
                    'initial_size' => '',
                    'target_size' => '',
                    'final_size' => '',
                    'start_time' => '',
                    'end_time' => '',
                    'duree' => '');
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $entry_icon = osc_icon_from_filename('xxxxxxxx.error');
                    $records [] = array('icon' => $entry_icon,
                        'index' => 0,
                        'component' => htmlentities($e['message']),
                        'parameter' => '',
                        'status' => 'ERROR',
                        'initial_size' => '',
                        'target_size' => '',
                        'final_size' => '',
                        'start_time' => '',
                        'end_time' => '',
                        'duree' => '');
                } else {
                    $index = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $entry_icon = osc_icon_from_filename('xxxxxxxx.' . $row['STATUS']);
                        $records [] = array('icon' => $entry_icon,
                            'index' => $index,
                            'component' => $row['COMPONENT'],
                            'parameter' => $row['PARAMETER'],
                            'status' => $row['STATUS'],
                            'initial_size' => $row['INITIAL_SIZE'],
                            'target_size' => $row['TARGET_SIZE'],
                            'final_size' => $row['FINAL_SIZE'],
                            'start_time' => $row['START_TIME'],
                            'end_time' => $row['END_TIME'],
                            'duree' => $row['DUREE']);
                        $index++;
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function pgaStats()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "SELECT name,
       CASE unit WHEN 'bytes' THEN to_char(round(VALUE / 1024 / 1024)) || ' MB' ELSE to_char(VALUE) END val
  FROM v\$PGASTAT";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $index = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('index' => $index, 'name' => $row['NAME'], 'val' => $row['VAL']);
                        $index++;
                    }

                    oci_free_statement($s);

                    $response = array('success' => true, EXT_JSON_READER_TOTAL => count($records),
                        EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function listPxsessions()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $scope = $_REQUEST['scope'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array(
                'index' => 0,
                'degree' => '',
                'req_degree' => '',
                'no_of_processes' => '',
                'sql_text' => $e['message'],
                'username' => '');
        } else {
            $query = "WITH px_session AS (  SELECT   qcsid, qcserial#, MAX (degree) degree,
                               MAX (req_degree) req_degree,
                               COUNT ( * ) no_of_processes
                        FROM   v\$px_session p
                    GROUP BY   qcsid, qcserial#)
SELECT   s.sid, s.username, degree, req_degree, no_of_processes,
         substr(sql_text,0,60) sql_text
  FROM   v\$session s  JOIN px_session p
           ON (s.sid = p.qcsid AND s.serial# = p.qcserial#)
         JOIN v\$sql sql
           ON (sql.sql_id = s.sql_id
               AND sql.child_number = s.sql_child_number)";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array(
                    'index' => 0,
                    'degree' => '',
                    'req_degree' => '',
                    'no_of_processes' => '',
                    'sql_text' => $e['message'],
                    'username' => '');
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array(
                        'index' => 0,
                        'degree' => '',
                        'req_degree' => '',
                        'no_of_processes' => '',
                        'sql_text' => $e['message'],
                        'username' => '');
                } else {
                    $index = 0;
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array(
                            'index' => $index,
                            'degree' => $row['DEGREE'],
                            'req_degree' => $row['REQ_DEGREE'],
                            'no_of_processes' => $row['NO_OF_PROCESSES'],
                            'sql_text' => $row['SQL_TEXT'],
                            'username' => $row['USERNAME']);

                        $index++;
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listPxbufferadvice()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $scope = $_REQUEST['scope'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('value' => '',
                'statistic' => $e['message']);
        } else {
            $query = "select * from V\$PX_BUFFER_ADVICE";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('value' => '',
                    'statistic' => $e['message']);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('value' => '',
                        'statistic' => $e['message']);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('value' => $row['VALUE'], 'statistic' => $row['STATISTIC']);
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function cboRecos()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $sql_id = $_REQUEST['sql_id'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array(
                'index' => 0,
                'reco' => $e['message']);
        } else {
            $query = "SELECT 'STATISTICS_LEVEL = ALL provides valuable metrics like A-Rows.<br>Be aware of Bug 5969780 (CPU overhead).<br>Use a value of ALL only at the session level.<br>You could use CBO hint /*+ gather_plan_statistics */ to accomplish the same.'
         as reco
  FROM v\$system_parameter2
 WHERE UPPER(name) = 'STATISTICS_LEVEL'
   AND UPPER(value) = 'ALL'
union all
SELECT 'CBO initialization parameter '||upper(name)||' with a non-default value of '||upper(value)||' .'
  FROM (
SELECT DISTINCT name, value
  FROM v\$sql_optimizer_env
 WHERE sql_id = '" . $sql_id . "'
   AND isdefault = 'NO')
union all
SELECT 'CBO initialization parameter '||g.name||' with a non-default value of '||g.value||'.<br>Review the correctness of this non-default value '||g.value||'.<br>Unset this parameter unless there is a strong reason for keeping its current value.<br>Default value is '||g.default_value||'.'
  FROM v\$sys_optimizer_env g
 WHERE g.isdefault = 'NO'
   AND NOT EXISTS (
SELECT NULL
  FROM v\$sql_optimizer_env s
 WHERE s.sql_id = '" . $sql_id . "'
   AND s.isdefault = 'NO'
   AND s.name = g.name
   AND s.value = g.value)
   union all
   SELECT 'MBRC Parameter is set to '||value||' overriding its default value.<br>The default value of this parameter is a value that corresponds to the maximum I/O size that can be performed efficiently.<br>This value is platform-dependent and is 1MB for most platforms.<br>Because the parameter is expressed in blocks, it will be set to a value that is equal to the maximum I/O size that can be performed efficiently divided by the standard block size.'
  FROM v\$system_parameter2
 WHERE UPPER(name) = 'DB_FILE_MULTIBLOCK_READ_COUNT'
   AND (isdefault = 'FALSE' OR ismodified <> 'FALSE')
union all
 SELECT 'NLS_SORT Session Parameter is set to '||value||'.<br>Setting NLS_SORT to anything other than BINARY causes a sort to use a full table scan, regardless of the path chosen by the optimizer.'
  FROM v\$nls_parameters
 WHERE UPPER(parameter) = 'NLS_SORT'
   AND UPPER(value) <> 'BINARY'
union all
SELECT 'NLS_SORT Instance Parameter is set to '||value||'.<br>Setting NLS_SORT to anything other than BINARY causes a sort to use a full table scan, regardless of the path chosen by the optimizer.'
  FROM v\$system_parameter
 WHERE UPPER(name) = 'NLS_SORT'
   AND UPPER(value) <> 'BINARY'
union all
SELECT 'NLS_SORT Global Parameter is set to '||value||'.<br>Setting NLS_SORT to anything other than BINARY causes a sort to use a full table scan, regardless of the path chosen by the optimizer.'
  FROM nls_database_parameters
 WHERE UPPER(parameter) = 'NLS_SORT'
   AND UPPER(value) <> 'BINARY'
union all
SELECT 'SQL Area references '||COUNT(DISTINCT optimizer_env_hash_value)||' distinct CBO Environments for this one SQL.<br>Distinct CBO Environments may produce different Plans.'
  FROM gv\$sqlarea_plan_hash
 WHERE sql_id = '" . $sql_id . "'
HAVING COUNT(*) > 1
union all
SELECT 'GV\$SQL references '||COUNT(DISTINCT optimizer_env_hash_value)||' distinct CBO Environments for this one SQL.<br>Distinct CBO Environments may produce different Plans.'
  FROM gv\$sql
 WHERE sql_id = '" . $sql_id . "'
HAVING COUNT(*) > 1
union all
SELECT 'AWR references '||COUNT(DISTINCT optimizer_env_hash_value)||' distinct CBO Enviornments for this one SQL.<br>Distinct CBO Environments may produce different Plans.'
  FROM dba_hist_sqlstat
 WHERE sql_id = '" . $sql_id . "'
HAVING COUNT(*) > 1
union all
SELECT 'There are plans with same PHV '||v.plan_hash_value||' but different predicate ordering.<br>Different ordering in the predicates for '||v.plan_hash_value||' can affect the performance of this SQL,<br>focus on Step ID '||v.id||' predicates '||v.predicates||'.'
  FROM (
WITH d AS (
SELECT sql_id,
       plan_hash_value,
       id,
       COUNT(DISTINCT access_predicates) distinct_access_predicates,
       COUNT(DISTINCT filter_predicates) distinct_filter_predicates
  FROM gv\$sql_plan_statistics_all
 WHERE sql_id = '" . $sql_id . "'
 GROUP BY
       sql_id,
       plan_hash_value,
       id
HAVING MIN(NVL(access_predicates, 'X')) != MAX(NVL(access_predicates, 'X'))
    OR MIN(NVL(filter_predicates, 'X')) != MAX(NVL(filter_predicates, 'X'))
)
SELECT v.plan_hash_value,
       v.id,
       'access' type,
       v.inst_id,
       v.child_number,
       v.access_predicates predicates
  FROM d,
       gv\$sql_plan_statistics_all v
 WHERE v.sql_id = d.sql_id
   AND v.plan_hash_value = d.plan_hash_value
   AND v.id = d.id
   AND d.distinct_access_predicates > 1
 UNION ALL
SELECT v.plan_hash_value,
       v.id,
       'filter' type,
       v.inst_id,
       v.child_number,
       v.filter_predicates predicates
  FROM d,
       gv\$sql_plan_statistics_all v
 WHERE v.sql_id = d.sql_id
   AND v.plan_hash_value = d.plan_hash_value
   AND v.id = d.id
   AND d.distinct_filter_predicates > 1
 ORDER BY
       1, 2, 3, 6, 4, 5) v
union all
SELECT 'Plan '||v.plan_hash_value||' may have implicit data_type conversion functions in Filter Predicates.<br>Review Execution Plans.<br>If Filter Predicates for '||v.plan_hash_value||' include unexpected INTERNAL_FUNCTION to perform an implicit data_type conversion,<br>be sure it is not preventing a column from being used as an Access Predicate.'
  FROM (
SELECT DISTINCT plan_hash_value
  FROM gv\$sql_plan
 WHERE inst_id IN (SELECT inst_id FROM gv\$instance)
   AND sql_id = '" . $sql_id . "'
   AND filter_predicates LIKE '%INTERNAL_FUNCTION%'
 ORDER BY 1) v
 union all
 SELECT 'Plan '||v.plan_hash_value||' has operations with Cost 0 and Card 1. Possible incorrect Selectivity.<br>Review Execution Plans.<br>Look for Plan operations in '||v.plan_hash_value||' where Cost is 0 and Estimated Cardinality is 1.<br>Suspect predicates out of range or incorrect statistics.'
  FROM (
SELECT plan_hash_value
  FROM gv\$sql_plan
 WHERE sql_id = '" . $sql_id . "'
   AND cost = 0
   AND cardinality = 1
 UNION
SELECT plan_hash_value
  FROM dba_hist_sql_plan
 WHERE sql_id = '" . $sql_id . "'
   AND cost = 0
   AND cardinality = 1) v
union all
SELECT 'This SQL shows evidence of high version count of '||MAX(v.version_count)||',Review Execution Plans for details.'
  FROM (
SELECT MAX(version_count) version_count
  FROM gv\$sqlarea_plan_hash
 WHERE sql_id = '" . $sql_id . "'
 UNION
SELECT MAX(version_count) version_count
  FROM dba_hist_sqlstat
 WHERE sql_id = '" . $sql_id . "' ) v
HAVING MAX(v.version_count) > 20
union all
SELECT 'OPTIMIZER_MODE was set to FIRST_ROWS in '||v.pln_count||' Plan(s).<br>The optimizer uses a mix of cost and heuristics to find a best plan for fast delivery of the first few rows.<br>Using heuristics sometimes leads the query optimizer to generate a plan with a cost that is significantly larger than the cost of a plan without applying the heuristic.<br>FIRST_ROWS is available for backward compatibility and plan stability; use FIRST_ROWS_n instead.'
FROM (
SELECT COUNT(*) pln_count
  FROM (
SELECT plan_hash_value
  FROM gv\$sql
 WHERE sql_id = '" . $sql_id . "'
   AND optimizer_mode = 'FIRST_ROWS'
 UNION
SELECT plan_hash_value
  FROM dba_hist_sqlstat
 WHERE sql_id = '" . $sql_id . "'
   AND optimizer_mode = 'FIRST_ROWS') v) v
 WHERE v.pln_count > 0
 union all
 SELECT 'There exist(s) '||v.tbl_count||' Fixed Object(s) without CBO statistics.<br>Consider gathering statistics for fixed objects using DBMS_STATS.GATHER_FIXED_OBJECTS_STATS.'
FROM (
SELECT COUNT(*) tbl_count
  FROM dba_tab_statistics t
 WHERE t.object_type = 'FIXED TABLE'
   AND NOT EXISTS (
SELECT NULL
  FROM dba_tab_cols c
 WHERE t.owner = c.owner
   AND t.table_name = c.table_name )) v
 WHERE v.tbl_count > 0
 union all
 SELECT 'Workload CBO System Statistics are not gathered.<br>CBO is using default values.<br>Consider gathering workload system statistics using DBMS_STATS.GATHER_SYSTEM_STATS'
  FROM sys.aux_stats$
 WHERE sname = 'SYSSTATS_MAIN'
   AND pname = 'CPUSPEED'
   AND pval1 IS NULL
   union all
   SELECT 'Multi-block read time of '||a1.pval1||'ms seems too small compared to single-block read time of '||a2.pval1||'ms.<br>Consider gathering workload system statistics using DBMS_STATS.GATHER_SYSTEM_STATS or adjusting SREADTIM and MREADTIM using DBMS_STATS.SET_SYSTEM_STATS.'
  FROM sys.aux_stats$ a1, sys.aux_stats$ a2
 WHERE a1.sname = 'SYSSTATS_MAIN'
   AND a1.pname = 'MREADTIM'
   AND a2.sname = 'SYSSTATS_MAIN'
   AND a2.pname = 'SREADTIM'
   AND (1.2 * a2.pval1) > a1.pval1
   AND a1.pval1 > a2.pval1
   union all
   SELECT 'Single-block read time of '||pval1||' milliseconds seems too small.<br>Consider gathering workload system statistics using DBMS_STATS.GATHER_SYSTEM_STATS or adjusting SREADTIM using DBMS_STATS.SET_SYSTEM_STATS.'
  FROM sys.aux_stats$
 WHERE sname = 'SYSSTATS_MAIN'
   AND pname = 'SREADTIM'
   AND pval1 < 2
   union all
   SELECT 'Multi-block read time of '||pval1||' milliseconds seems too small.<br>Consider gathering workload system statistics using DBMS_STATS.GATHER_SYSTEM_STATS or adjusting MREADTIM using DBMS_STATS.SET_SYSTEM_STATS.'
  FROM sys.aux_stats$
 WHERE sname = 'SYSSTATS_MAIN'
   AND pname = 'MREADTIM'
   AND pval1 < 3
   union all
   SELECT 'Single-block read time of '||pval1||' milliseconds seems too large.<br>Consider gathering workload system statistics using DBMS_STATS.GATHER_SYSTEM_STATS or adjusting SREADTIM using DBMS_STATS.SET_SYSTEM_STATS.'
  FROM sys.aux_stats$
 WHERE sname = 'SYSSTATS_MAIN'
   AND pname = 'SREADTIM'
   AND pval1 > 18
  union all
            SELECT 'AutoDOP is enable but there are no IO Calibration stats.<br>AutoDOP requires IO Calibration stats, consider collecting them using DBMS_RESOURCE_MANAGER.CALIBRATE_IO.'
  FROM v\$parameter
 WHERE UPPER(name) = 'PARALLEL_DEGREE_POLICY'
   AND UPPER(value) IN ('AUTO','LIMITED')
   AND NOT EXISTS (SELECT 1
                     FROM dba_rsrc_io_calibrate)";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array(
                    'index' => 0,
                    'reco' => $e['message']);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array(
                        'index' => 0,
                        'reco' => $e['message']);
                } else {
                    $index = 0;
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array(
                            'index' => $index,
                            'reco' => $row['RECO']);

                        $index++;
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listPxstats()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('value' => '',
                'statistic' => $e['message']);
        } else {
            $query = "select * from V\$PX_PROCESS_SYSSTAT";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('value' => '',
                    'statistic' => $e['message']);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('value' => '',
                        'statistic' => $e['message']);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('value' => $row['VALUE'], 'statistic' => $row['STATISTIC']);
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listPxsessstats()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array(
                'last_query' => '',
                'session_total' => '',
                'statistic' => $e['message']);
        } else {
            $query = "select * from V\$PQ_SESSTAT";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array(
                    'last_query' => '',
                    'session_total' => '',
                    'statistic' => $e['message']);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array(
                        'last_query' => '',
                        'session_total' => '',
                        'statistic' => $e['message']);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array(
                            'last_query' => $row['LAST_QUERY'],
                            'session_total' => $row['SESSION_TOTAL'],
                            'statistic' => $row['STATISTIC']);
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }
}

?>