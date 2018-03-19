<?php

require('includes/classes/databases.php');
require('includes/classes/servers.php');
require('includes/classes/email_account.php');
require('includes/classes/email_accounts.php');
require('includes/classes/reports.php');
require('includes/classes/sms.php');

class toC_Json_Databases
{
    function loadDatabase()
    {
        global $toC_Json;

        $data = toC_Databases_Admin::getDb($_REQUEST['databases_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function watchOgg()
    {
        global $toC_Json, $osC_Database;
        $data = $_REQUEST;
        $recs = array();

        $query = "INSERT INTO delta_snaps (job_id,status,running_host,created_date) VALUES (:job_id,:status,:running_host,:created_date)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':job_id', "watch_ogg");
        $Qserver->bindValue(':status', "ready");
        $Qserver->bindValue(':running_host', "10.100.18.19");
        $Qserver->bindValue(':created_date', date("Y-m-d H:i:s"));
        $Qserver->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'msg' => "Impossible de creer un job de collection de l'utilisation des espaces");
            //var_dump($osC_Database);
        } else {
            $snaps_id = $osC_Database->nextID();

            $ssh = new Net_SSH2(REPORT_RUNNER, '22');

            if (empty($ssh->server_identifier)) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
            } else {
                if (!$ssh->login("guyfomi", "12345")) {
                    $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
                } else {
                    $ssh->disableQuietMode();

                    $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=databases&action=watch_capture&db_host=" . $data['src_db_host'] . "&config_id=" . $data['config_id'] . "&os_user=" . $data['src_os_user'] . "&os_pass=" . $data['src_os_pass'] . "&src_home=" . $data['src_home'] . "&oggdir_src=" . $data['oggdir_src'] . "&extract_name=" . $data['extract_name'] . "&snaps_id=" . $snaps_id . "' &";
                    $ssh->exec($cmd);

                    $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=databases&action=watch_propagation&db_host=" . $data['src_db_host'] . "&config_id=" . $data['config_id'] . "&os_user=" . $data['src_os_user'] . "&os_pass=" . $data['src_os_pass'] . "&src_home=" . $data['src_home'] . "&oggdir_src=" . $data['oggdir_src'] . "&datapump_name=" . $data['datapump_name'] . "&snaps_id=" . $snaps_id . "' &";
                    $ssh->exec($cmd);

                    $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=databases&action=watch_replication&db_host=" . $data['dest_db_host'] . "&config_id=" . $data['config_id'] . "&os_user=" . $data['dest_os_user'] . "&os_pass=" . $data['dest_os_pass'] . "&dest_home=" . $data['dest_home'] . "&oggdir_dest=" . $data['oggdir_dest'] . "&replicat_name=" . $data['replicat_name'] . "&snaps_id=" . $snaps_id . "' &";
                    $ssh->exec($cmd);

                    $ssh->disconnect();
                }

                $query = "SELECT
  0 AS 'index',
  'Capture' AS 'process',
  lag AS latence,
  state AS 'status',
  '' AS 'network'
FROM
  delta_ogg_capture_state
WHERE config_id= " . $data['config_id'] . " and snaps_id =
  (SELECT
    MAX(snaps_id)
  FROM
    delta_ogg_capture_state where snaps_id < " . $snaps_id . " and config_id= " . $data['config_id'] . ")
  UNION
  SELECT
    1 AS 'index', 'Propagation' AS 'process', lag AS latence, state AS 'status', net AS 'network'
  FROM
    delta_ogg_propagation_state
  WHERE config_id= " . $data['config_id'] . " and snaps_id =
    (SELECT
      MAX(snaps_id)
    FROM
      delta_ogg_propagation_state where snaps_id < " . $snaps_id . " and config_id= " . $data['config_id'] . ")
    UNION
    SELECT
      2 AS 'index', 'Replication' AS 'process', lag AS latence, state AS 'status', '' AS 'network'
    FROM
      delta_ogg_replication_state
    WHERE config_id= " . $data['config_id'] . " and snaps_id =
      (SELECT
        MAX(snaps_id)
      FROM
        delta_ogg_replication_state where snaps_id < " . $snaps_id . " and config_id= " . $data['config_id'] . ")";

                //var_dump($query);

                $QServers = $osC_Database->query($query);

                while ($QServers->next()) {

                    $recs[] = array('index' => $QServers->ValueInt('index'),
                        'process' => $QServers->Value('process'),
                        'latence' => $QServers->Value('latence'),
                        'network' => $QServers->Value('network'),
                        'status' => $QServers->Value('status')
                    );

                }
            }

            $response = array(EXT_JSON_READER_TOTAL => 3,
                EXT_JSON_READER_ROOT => $recs);
        }

        echo $toC_Json->encode($response);
    }

    function monOgg_orig()
    {
        global $toC_Json, $osC_Database;
        $data = $_REQUEST;
        $recs = array();

        $query = "SELECT
  0 AS 'index',
  extract_name AS 'process',
  lag AS latence,
  state AS 'status',
  fs_state AS 'pct',
  trail_seqno AS 'seqno',
  trail_rba AS 'rba',
  '' AS 'network'
FROM
  delta_ogg_capture_state,delta_ogg_config
WHERE delta_ogg_config.id = delta_ogg_capture_state.config_id and config_id= " . $data['config_id'] . " and ogg_state_id =
  (SELECT
    MAX(ogg_state_id)
  FROM
    delta_ogg_capture_state WHERE config_id= " . $data['config_id'] . ")
  UNION
  SELECT
    1 AS 'index',datapump_name AS 'process', lag AS latence, state AS 'status','0%' AS 'pct',trail_seqno AS 'seqno',
  trail_rba AS 'rba', net AS 'network'
  FROM
    delta_ogg_propagation_state,delta_ogg_config
  WHERE delta_ogg_config.id = delta_ogg_propagation_state.config_id and config_id= " . $data['config_id'] . " and ogg_state_id =
    (SELECT
      MAX(ogg_state_id)
    FROM
      delta_ogg_propagation_state WHERE config_id= " . $data['config_id'] . ")
    UNION
    SELECT
      2 AS 'index',replicat_name AS 'process', lag AS latence, state AS 'status',fs_state AS 'pct',trail_seqno AS 'seqno',
  trail_rba AS 'rba', '' AS 'network'
    FROM
      delta_ogg_replication_state,delta_ogg_config
    WHERE delta_ogg_config.id = delta_ogg_replication_state.config_id and config_id= " . $data['config_id'] . " and ogg_state_id =
      (SELECT
        MAX(ogg_state_id)
      FROM
        delta_ogg_replication_state WHERE config_id= " . $data['config_id'] . ")";

        //var_dump($query);

        $QServers = $osC_Database->query($query);

        while ($QServers->next()) {

            $size = explode(' ', $QServers->Value('network'));
            $net = toC_Servers_Admin::formatSizeUnits($size[0]);
            $sec = explode(' ', $QServers->Value('latence'));

            $recs[] = array('index' => $QServers->ValueInt('index'),
                'process' => $QServers->Value('process'),
                'pct' => $QServers->Value('pct'),
                'seqno' => $QServers->Value('seqno'),
                'rba' => $QServers->Value('rba'),
                'latence' => $sec[0] . ' s',
                'network' => empty($size[0]) ? $size[0] : $net . '/s',
                'status' => $QServers->Value('status')
            );

        }

        $response = array(EXT_JSON_READER_TOTAL => 3,
            EXT_JSON_READER_ROOT => $recs);

        echo $toC_Json->encode($response);
    }

    function monOgg()
    {
        global $toC_Json;
        $data = $_REQUEST;
        $recs = array();

        $file1 = "/dev/shm/capture_" . $data['config_id'];
        $capture = file_get_contents($file1);
        $capture = $toC_Json->decode($capture);

        //var_dump($capture);

        $file2 = "/dev/shm/datapump_" . $data['config_id'];
        $datapump = file_get_contents($file2);
        $datapump = $toC_Json->decode($datapump);

        //var_dump($datapump);

        $file3 = "/dev/shm/replicat_" . $data['config_id'];
        $replicat = file_get_contents($file3);
        $replicat = $toC_Json->decode($replicat);

        //var_dump($replicat);
        //$sec = explode(' ', $capture['lag']);

        $recs[] = array('index' => 0,
            'process' => 'extract',
            'pct' => $capture->pct,
            'seqno' => $capture->seqno,
            'rba' => $capture->rba,
            'latence' => $capture->lag,
            'network' => '',
            'status' => $capture->state
        );

        $size = explode(' ', $datapump->net);
        $net = toC_Servers_Admin::formatSizeUnits($size[0]);
        $sec = explode(' ', $datapump->lag);

        $recs[] = array('index' => 1,
            'process' => 'datapump',
            'pct' => '0%',
            'seqno' => $datapump->seqno,
            'rba' => $datapump->rba,
            'latence' => $sec[0] . ' s',
            'network' => empty($size[0]) ? $size[0] : $net . '/s',
            'status' => $datapump->state
        );

        $size = explode(' ', $replicat->net);
        $net = toC_Servers_Admin::formatSizeUnits($size[0]);
        $sec = explode(' ', $replicat->lag);

        $recs[] = array('index' => 2,
            'process' => 'replicat',
            'pct' => $replicat->pct,
            'seqno' => $replicat->seqno,
            'rba' => $replicat->rba,
            'latence' => $sec[0] . ' s',
            'network' => empty($size[0]) ? $size[0] : $net . '/s',
            'status' => $replicat->state
        );

        //var_dump($datapump);

        $response = array(EXT_JSON_READER_TOTAL => 3,
            EXT_JSON_READER_ROOT => $recs);

        echo $toC_Json->encode($response);

        //unlink($file1);
        //unlink($file2);
        //unlink($file3);
    }

    function watchOgg_orig()
    {
        global $toC_Json;

        $records = array();

        $data = $_REQUEST;

        //$data['src_home'] = toC_Databases_Admin::getOracleHome($data['src_db_user'], $data['src_db_pass'], $data['src_db_host'], $data['src_db_sid']);

        $records[] = array('index' => 0, 'process' => 'Capture', 'latence' => '00000000', 'status' => $data['src_home'], 'network' => '');
        $records[] = array('index' => 1, 'process' => 'Propagation', 'latence' => '00000000', 'status' => $data['src_home'], 'network' => '');

        //$data['dest_home'] = toC_Databases_Admin::getOracleHome($data['dest_db_user'], $data['dest_db_pass'], $data['dest_db_host'], $data['dest_db_sid']);
        $records[] = array('index' => 2, 'process' => 'Application', 'latence' => '00000000', 'status' => $data['dest_home'], 'network' => '');

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['src_db_host']);
        if (!$ssh->login($data['src_os_user'], $data['src_os_pass'])) {
            $records[0]['status'] = 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user'];
            $records[1]['status'] = 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user'];
        } else {
            $ssh->disableQuietMode();
            $file = "/dev/shm/capture" . $randomString;

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo status extract " . $data['extract_name'] . " >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "chmod +x " . $file;
            $ssh->exec($cmd);

            $cmd = "sh " . $file . " | grep -i " . $data['extract_name'] . "|awk -F \":\" '{print \$2}'";
            $state = $ssh->exec($cmd);

            $records[0]['status'] = trim($state);;

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo lag extract " . $data['extract_name'] . " >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "chmod +x " . $file;
            $ssh->exec($cmd);

            $cmd = "sh " . $file . " |grep record|awk -F \":\" '{if (NR =1) print \$2}'";
            $lag = $ssh->exec($cmd);

            $records[0]['latence'] = $lag;

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo status extract " . $data['datapump_name'] . " >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "chmod +x " . $file;
            $ssh->exec($cmd);

            $cmd = "sh " . $file . " | grep -i " . $data['datapump_name'] . "|awk -F \":\" '{print \$2}'";
            $state = $ssh->exec($cmd);

            $records[1]['status'] = trim($state);

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo lag extract " . $data['datapump_name'] . " >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "chmod +x " . $file;
            $ssh->exec($cmd);

            $cmd = "sh " . $file . " |grep record|awk -F \":\" '{if (NR =1) print \$2}'";
            $lag = $ssh->exec($cmd);

            $records[1]['latence'] = $lag;

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo send extract " . $data['datapump_name'] . " GETTCPSTATS >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ! >> " . $file;
            $ssh->exec($cmd);

            $cmd = "chmod +x " . $file;
            $ssh->exec($cmd);

            $cmd = "sh " . $file . " |grep -i Outbound|awk '{print \$6\" \"\$7}'";
            $net = $ssh->exec($cmd);

            $records[1]['network'] = $net;

            $cmd = "rm -f " . $file;
            $ssh->exec($cmd);

            $ssh->disconnect();
        }

        $ssh = new Net_SSH2($data['dest_db_host']);
        if (!$ssh->login($data['dest_os_user'], $data['dest_os_pass'])) {
            $records[2]['status'] = 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user'];
        } else {
            $ssh->disableQuietMode();
            $file = "/dev/shm/capture" . $randomString;

            $cmd = "echo export ORACLE_HOME=" . $data['dest_home'] . $file;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['dest_home'] . "/bin:/sbin/:bin >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['dest_home'] . "/lib >> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_dest'] . ">> " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo status replicat " . $data['replicat_name'] . " >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ! >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "chmod +x  " . $file;
            $ssh->exec($cmd);

            $cmd = "sh  " . $file . " | grep -i " . $data['replicat_name'] . "|awk -F \":\" '{print \$2}'";
            $state = $ssh->exec($cmd);

            $records[2]['status'] = trim($state);

            $cmd = "echo export ORACLE_HOME=" . $data['dest_home'] . ">  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['dest_home'] . "/bin:/sbin/:bin >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['dest_home'] . "/lib >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_dest'] . ">>  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo lag replicat " . $data['replicat_name'] . " >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "echo ! >>  " . $file;
            $ssh->exec($cmd);

            $cmd = "chmod +x  " . $file;
            $ssh->exec($cmd);

            $cmd = "sh  " . $file . " |grep record|awk -F \":\" '{if (NR =1) print \$2}'";
            $lag = $ssh->exec($cmd);

            $records[2]['latence'] = $lag;

            $cmd = "rm -f " . $file;
            $ssh->exec($cmd);

            $ssh->disconnect();
        }

        $response = array(EXT_JSON_READER_TOTAL => 3,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listTransactions()
    {

        //osC_Sms_Admin::addDetail("Chargement de la liste des Transactions ...");

        $db_user = IRISDB_USER;
        $db_pass = IRISDB_PASS;
        $db_host = IRISDB_HOST;
        $db_sid = IRISDB_SID;

        $records = array();

        $response = array('success' => true, 'msg' => 'OK');

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            //$response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            //echo htmlentities($e['message']);
            var_dump($e);
        } else {
            $query = "select * from trans";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                //$response = array('success' => false, 'feedback' => "Impossible de charger la listes des transactions " . htmlentities($e['message']));
                //echo htmlentities($e['message']);
                var_dump($c);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    //$response = array('success' => false, 'feedback' => 'Impossible de charger la listes des transactions ' . htmlentities($e['message']));
                    //echo htmlentities($e['message']);
                    var_dump($e);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records[] = array(
                            'AUTOMATE' => $row['AUTOMATE'],
                            'PHONE' => $row['PHONE'],
                            'BRANCH_CODE' => $row['BRANCH_CODE'],
                            'CIVILITE' => $row['CIVILITE'],
                            'CUSTOMERID' => $row['CUSTOMERID'],
                            'FIRSTNAME' => $row['FIRSTNAME'],
                            'LASTNAME' => $row['LASTNAME'],
                            'MONTANT' => $row['MONTANT'],
                            'TIME_LOC_TRAN' => $row['TIME_LOC_TRAN'],
                            'DATE_LOC_TRAN' => $row['DATE_LOC_TRAN'],
                            'TRXLOGID' => $row['TRXLOGID'],
                            'TYPE_COMPTE' => $row['TYPE_COMPTE'],
                            'TYPE_TRANSACTION' => $row['TYPE_TRANSACTION'],
                            'LIEU' => $row['LIEU'],
                            'LANG' => $row['LANG']
                        );
                    }
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);

        //$ssh = new Net_SSH2(CURL_RUNNER, '22');
        //osC_Sms_Admin::addDetail("Connexion SSH ...");
        $ssh = new Net_SSH2(IRISDB_HOST, '22');

        if (empty($ssh->server_identifier)) {
            //$response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
            var_dump($ssh);
        } else {
            if (!$ssh->login("oracle", "J*w6_MyD(ZJ7W=8L")) {
                //$response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
                var_dump($ssh);
            } else {
                $ssh->disableQuietMode();

                // osC_Sms_Admin::addDetail("Envoi requetes CURL ...");

                foreach ($records as $data) {
                    $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=sms&action=send_sms&" .
                        "customerid=" . $data['CUSTOMERID'] . "&civilite=" . $data['CIVILITE'] . "&firstname=" . $data['FIRSTNAME'] . "&trxlogid=" . $data['TRXLOGID'] .
                        "&lastname=" . $data['LASTNAME'] . "&type_compte=" . $data['TYPE_COMPTE'] . "&montant=" . $data['MONTANT'] . "&lang=" . $data['LANG'] .
                        "&date_loc_tran=" . $data['DATE_LOC_TRAN'] . "&time_loc_tran=" . $data['TIME_LOC_TRAN'] . "&automate=" . $data['AUTOMATE'] . "&phone=" . $data['PHONE'] . "&lieu=" . $data['LIEU'] . "' &";

                    $ssh->exec($cmd);

                    //var_dump($cmd);
                }

                $ssh->disconnect();
            }
        }

        //echo $response;
    }

    function listAlertesgab()
    {

        //osC_Sms_Admin::addDetail("Chargement de la liste des Transactions ...");

        $db_user = IRISDB_USER;
        $db_pass = IRISDB_PASS;
        $db_host = IRISDB_HOST;
        $db_sid = IRISDB_SID;

        $records = array();

        $response = array('success' => true, 'msg' => 'OK');

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            //$response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            //echo htmlentities($e['message']);
//            var_dump($e);
        } else {
            $query = "select to_char(DB_DATE_TIME,'dd/mm/yyyy HH24:MM') DB_DATE_TIME,DISPLAYID,DRAPEAU,EVENTID,MOTIF,PHONE,PHONE2 from atm_event where drapeau in (0,1)";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                //$response = array('success' => false, 'feedback' => "Impossible de charger la listes des transactions " . htmlentities($e['message']));
                //echo htmlentities($e['message']);
                //var_dump($c);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    //$response = array('success' => false, 'feedback' => 'Impossible de charger la listes des transactions ' . htmlentities($e['message']));
                    //echo htmlentities($e['message']);
                    //var_dump($e);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records[] = array(
                            'DB_DATE_TIME' => $row['DB_DATE_TIME'],
                            'DISPLAYID' => $row['DISPLAYID'],
                            'DRAPEAU' => $row['DRAPEAU'],
                            'EVENTID' => $row['EVENTID'],
                            'MOTIF' => $row['MOTIF'],
                            'PHONE' => $row['PHONE'],
                            'PHONE2' => $row['PHONE2']
                        );
                    }
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);

        //$ssh = new Net_SSH2(CURL_RUNNER, '22');
        //osC_Sms_Admin::addDetail("Connexion SSH ...");
        $ssh = new Net_SSH2(IRISDB_HOST, '22');

        if (empty($ssh->server_identifier)) {
            //$response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
            var_dump($ssh);
        } else {
            if (!$ssh->login("oracle", "J*w6_MyD(ZJ7W=8L")) {
                //$response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
                var_dump($ssh);
            } else {
                $ssh->disableQuietMode();

                // osC_Sms_Admin::addDetail("Envoi requetes CURL ...");

                foreach ($records as $data) {
                    $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=sms&action=send_alertgab&" .
                        "DB_DATE_TIME=" . $data['DB_DATE_TIME'] . "&DISPLAYID=" . $data['DISPLAYID'] . "&DRAPEAU=" . $data['DRAPEAU'] . "&EVENTID=" . $data['EVENTID'] .
                        "&MOTIF=" . $data['MOTIF'] . "&PHONE=" . $data['PHONE'] . "&PHONE2=" . $data['PHONE2'] . "' &";

                    $ssh->exec($cmd);

                    //var_dump($cmd);
                }

                $ssh->disconnect();
            }
        }

        //echo $response;
    }

    function stopCapture()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['src_db_host']);
        if (!$ssh->login($data['src_os_user'], $data['src_os_pass'])) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user']);
        } else {
            $ssh->disableQuietMode();

            $cmd = "ps -ef |grep LISTENER | grep -v grep | awk '{print \$8}' | awk 'BEGIN {FS=\"bin\"} {print \$1}'";
            $str = $ssh->exec($cmd);
            $data['src_home'] = substr($str, 0, strlen($str) - 1);

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo stop extract " . $data['extract_name'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString;
            $state = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $state);

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function stopDatapump()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['src_db_host']);
        if (!$ssh->login($data['src_os_user'], $data['src_os_pass'])) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user']);
        } else {
            $ssh->disableQuietMode();

            $cmd = "ps -ef |grep LISTENER | grep -v grep | awk '{print \$8}' | awk 'BEGIN {FS=\"bin\"} {print \$1}'";
            $str = $ssh->exec($cmd);
            $data['src_home'] = substr($str, 0, strlen($str) - 1);

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo stop extract " . $data['datapump_name'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString;
            $state = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $state);

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function startCapture()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['src_db_host']);
        if (!$ssh->login($data['src_os_user'], $data['src_os_pass'])) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user']);
        } else {
            $ssh->disableQuietMode();

            $cmd = "ps -ef |grep LISTENER | grep -v grep | awk '{print \$8}' | awk 'BEGIN {FS=\"bin\"} {print \$1}'";
            $str = $ssh->exec($cmd);
            $data['src_home'] = substr($str, 0, strlen($str) - 1);

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo start extract " . $data['extract_name'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString;
            $state = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $state);

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function loadGroupsTree()
    {
        global $osC_Database, $toC_Json;

        $Qtree = $osC_Database->query('SELECT COUNT(ur.databases_id) AS count, ur.databases_id,r.group_id, r.group_name FROM delta_database_groups r LEFT OUTER JOIN delta_database_to_groups ur ON (r.group_id = ur.group_id) GROUP BY r.group_name, r.group_id ORDER BY r.group_name,r.group_id ASC');
        $Qtree->execute();

        $records = array();

        while ($Qtree->next()) {
            $records [] = array('group_id' => $Qtree->value('group_id'), 'id' => $Qtree->value('group_id'), 'text' => $Qtree->value('group_name') . ' (' . $Qtree->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/database_icon.jpg', 'leaf' => true);
        }

        $Qtree->freeResult();

        echo $toC_Json->encode($records);
    }

    function loadLayoutTree()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT a.*,s.HOST,s.servers_id,s.typ,s.USER AS server_user,s.pass AS server_pass,s.PORT AS server_port FROM delta_databases a INNER JOIN delta_servers s ON a.servers_id = s.servers_id";
        $Qdatabases = $osC_Database->query($query);
        $Qdatabases->appendQuery('order by a.label ');
        $Qdatabases->execute();

        $records = array();
        while ($Qdatabases->next()) {
            $records [] = array(
                'host' => $Qdatabases->Value('HOST'),
                'server_user' => $Qdatabases->Value('server_user'),
                'servers_id' => $Qdatabases->Value('servers_id'),
                'server_pass' => $Qdatabases->Value('server_pass'),
                'server_port' => $Qdatabases->Value('server_port'),
                'label' => $Qdatabases->Value('label'),
                'sid' => $Qdatabases->Value('sid'),
                'port' => $Qdatabases->Value('port'),
                'db_port' => $Qdatabases->Value('port'),
                'db_user' => $Qdatabases->Value('user'),
                'typ' => $Qdatabases->Value('typ'),
                'db_pass' => $Qdatabases->Value('pass'),
                'databases_id' => $Qdatabases->ValueInt('databases_id'),
                'id' => $Qdatabases->ValueInt('databases_id'),
                'text' => $Qdatabases->value('label'),
                'icon' => 'templates/default/images/icons/16x16/database_icon.jpg',
                'leaf' => true
            );
        }

        $Qdatabases->freeResult();

        echo $toC_Json->encode($records);
    }

    function listDatabaseGroups()
    {
        global $toC_Json, $osC_Database;

        $query = "select * from delta_database_groups order by group_name";
        $Qgroups = $osC_Database->query($query);
        $Qgroups->execute();

        $records = array();

        $count = 0;

        while ($Qgroups->next()) {
            $records[] = array(
                'group_id' => $Qgroups->valueInt('group_id'),
                'group_name' => $Qgroups->value('group_name')
            );

            $count++;
        }

        $Qgroups->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadGroup()
    {
        global $toC_Json;

        $data = toC_Databases_Admin::getGroup($_REQUEST['group_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function deleteGroup()
    {
        if (isset($_REQUEST['group_id']) && !empty($_REQUEST['group_id'])) {
            global $toC_Json, $osC_Language;

            if (toC_Databases_Admin::deleteGroup($_REQUEST['group_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['last_error']);
            }
        } else {
            $response = array('success' => false, 'feedback' => 'Veuillez selectionner !!');
        }

        echo $toC_Json->encode($response);
    }

    function saveGroup()
    {
        global $toC_Json, $osC_Language;

        if (toC_Databases_Admin::saveGroup((isset($_REQUEST['group_id']) && is_numeric($_REQUEST['group_id'])
            ? $_REQUEST['group_id']
            : null), $_REQUEST)
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
        }

        echo $toC_Json->encode($response);
    }

    function startDatapump()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['src_db_host']);
        if (!$ssh->login($data['src_os_user'], $data['src_os_pass'])) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user']);
        } else {
            $ssh->disableQuietMode();

            $cmd = "ps -ef |grep LISTENER | grep -v grep | awk '{print \$8}' | awk 'BEGIN {FS=\"bin\"} {print \$1}'";
            $str = $ssh->exec($cmd);
            $data['src_home'] = substr($str, 0, strlen($str) - 1);

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo start extract " . $data['datapump_name'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString;
            $state = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $state);

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function startReplicat()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['dest_db_host']);
        if (!$ssh->login($data['dest_os_user'], $data['dest_os_pass'])) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH : dest_db_host = ' . $data['dest_db_host'] . '  dest_os_user = ' . $data['dest_os_user']);
        } else {
            $ssh->disableQuietMode();

            $cmd = "ps -ef | grep LISTENER | grep -v grep | awk '{print \$8}' | awk 'BEGIN {FS=\"bin\"} {print \$1}'";
            $str = $ssh->exec($cmd);
            $data['dest_home'] = substr($str, 0, strlen($str) - 1);

            //var_dump($data['dest_home']);

            $cmd = "echo export ORACLE_HOME=" . $data['dest_home'] . " > /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['dest_home'] . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['dest_home'] . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_dest'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo start replicat " . $data['replicat_name'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString;
            $state = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $state);

            //$cmd = "rm -f /tmp/capture" . $randomString;
            //$ssh->exec($cmd);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function stopReplicat()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['dest_db_host']);
        if (!$ssh->login($data['dest_os_user'], $data['dest_os_pass'])) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user']);
        } else {
            $ssh->disableQuietMode();

            $cmd = "ps -ef |grep LISTENER | grep -v grep | awk '{print \$8}' | awk 'BEGIN {FS=\"bin\"} {print \$1}'";
            $str = $ssh->exec($cmd);
            $data['dest_home'] = substr($str, 0, strlen($str) - 1);

            $cmd = "echo export ORACLE_HOME=" . $data['dest_home'] . " > /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['dest_home'] . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['dest_home'] . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_dest'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo stop replicat " . $data['replicat_name'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString;
            $state = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $state);

            //$cmd = "rm -f /tmp/capture" . $randomString;
            //$ssh->exec($cmd);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function detailCapture()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($data['src_db_host']);
        if (!$ssh->login($data['src_os_user'], $data['src_os_pass'])) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH : src_db_host = ' . $data['src_db_host'] . '  src_os_user = ' . $data['src_os_user']);
        } else {
            $ssh->disableQuietMode();

            $cmd = "echo export ORACLE_HOME=" . $data['src_home'] . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $data['src_home'] . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $data['src_home'] . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $data['oggdir_src'] . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo start extract " . $data['extract_name'] . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString;
            $state = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $state);

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function listFreq()
    {
        global $toC_Json;

        $records = array();

        $i = 0;

        while ($i < 120) {
            $records[] = array('index' => $i, 'value' => ($i + 1) * 1000, 'display' => ($i + 1) . ' sec');
            $i++;
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listDest()
    {
        global $toC_Json;

        $records = array();

        $i = 2;

        while ($i < 32) {
            $records[] = array('index' => $i, 'value' => $i, 'display' => 'Destination ' . $i);
            $i++;
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function setTbstatus()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $tbs = $_REQUEST['tbs'];
        $flag = $_REQUEST['flag'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $proc_name = 'proc_set_tbs_' . $randomString;
            $job_name = 'job_set_tbs_' . $randomString;

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AS BEGIN execute immediate ''alter tablespace " . $tbs . " " . $flag . "'';END;';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure cree avec succes ...", 'proc_name' => $proc_name);
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . ";END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'proc_name' => $proc_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function listTbsmap()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $table = $_REQUEST['table_name'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {

            $total = 0;
            $query = "SELECT count(*) NBRE from " . $table;

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

                    //$query = "select * from " . $table;
                    $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (select * from " . $table . " order by block_id ) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                    } else {

                        $fin = $start + $limit;

                        oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                        oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => htmlentities($e['message']));
                        } else {
                            $records = array();
                            $index = 0;

                            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                                $size = toC_Servers_Admin::formatSizeUnits(($row['TAILLE']));
                                $records [] = array('index' => $index, 'file_id' => $row['FILE_ID'], 'block_id' => $row['BLOCK_ID'], 'end_block' => $row['END_BLOCK'], 'size' => $size, 'owner' => $row['OWNER'], 'segment_name' => $row['SEGMENT_NAME'], 'partition_name' => $row['PARTITION_NAME'], 'segment_type' => $row['SEGMENT_TYPE']);
                                $index++;
                            }

                            $response = array('success' => false, EXT_JSON_READER_TOTAL => $total,
                                EXT_JSON_READER_ROOT => $records);
                        }
                    }
                }

                oci_free_statement($s);
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function saveGgconfig()
    {
        global $toC_Json;

        $data = array('datapump_name' => $_REQUEST['datapump_name'],
            'content_description' => $_REQUEST['label'],
            'dest_database' => $_REQUEST['dest_database'],
            'extract_name' => $_REQUEST['extract_name'],
            'oggdir_dest' => $_REQUEST['oggdir_dest'],
            'oggdir_src' => $_REQUEST['oggdir_src'],
            'replicat_name' => $_REQUEST['replicat_name'],
            'src_database' => $_REQUEST['src_database']);

        if (toC_Servers_Admin::saveOgg((isset($_REQUEST['id']) && ($_REQUEST['id'] != -1)
            ? $_REQUEST['id'] : null), $data)
        ) {
            $response = array('success' => true, 'feedback' => 'Configuration enregistree ...');
        } else {
            $response = array('success' => false, 'feedback' => "Erreur survenue lors de l'enregistrement de la configuration : " . $_SESSION['LAST_ERROR']);
        }

        header('Content-type: application/json');
        echo $toC_Json->encode($response);
    }

    function tbsMap()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $tbs = $_REQUEST['tbs'];
        $file_id = $_REQUEST['file_id'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $job_name = 'tbs_map_' . $randomString;
            $table_name = 'table_map_' . $randomString;
            $proc_name = 'proc_map_' . $randomString;

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN execute immediate ''CREATE TABLE " . $table_name . " NOLOGGING PARALLEL AS SELECT file_id, block_id, block_id + blocks - 1 end_block, ( ( (block_id + blocks - 1) - block_id) * 8192) taille, owner, segment_name, partition_name, segment_type FROM dba_extents WHERE tablespace_name = ''''" . $tbs . "'''' UNION ALL SELECT file_id, block_id, block_id + blocks - 1 end_block, ( ( (block_id + blocks - 1) - block_id) * 8192) taille, ''''free'''' owner, ''''free'''' segment_name, NULL partition_name, NULL segment_type FROM dba_free_space WHERE tablespace_name = ''''" . $tbs . "'''' ORDER BY 1, 2'';end;';END;";

            if ($file_id != "-1") {
                $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN execute immediate ''CREATE TABLE " . $table_name . " NOLOGGING PARALLEL AS SELECT file_id, block_id, block_id + blocks - 1 end_block, ( ( (block_id + blocks - 1) - block_id) * 8192) taille, owner, segment_name, partition_name, segment_type FROM dba_extents WHERE file_id = " . $file_id . " and tablespace_name = ''''" . $tbs . "'''' UNION ALL SELECT file_id, block_id, block_id + blocks - 1 end_block, ( ( (block_id + blocks - 1) - block_id) * 8192) taille, ''''free'''' owner, ''''free'''' segment_name, NULL partition_name, NULL segment_type FROM dba_free_space WHERE file_id = " . $file_id . " and tablespace_name = ''''" . $tbs . "'''' ORDER BY 1, 2'';end;';END;";
            }

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible d executer la requete de creation de la procedure de redimensionnement ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                } else {
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . "; END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'table_name' => $table_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function moveTable()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $table_script = $_REQUEST['table_script'];
        $indexes_script = $_REQUEST['indexes_script'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $index_action = "";
            $proc_name = 'proc_move_table_' . $randomString;
            $job_name = 'job_move_table_' . $randomString;

            $rows = explode(";", $indexes_script);

            foreach ($rows as $row) {
                if (!empty($row)) {
                    $index_action = $index_action . "execute immediate ''" . $row . "'';";
                }
            }

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN execute immediate ''alter session force parallel dml'';execute immediate ''alter session force parallel ddl'';execute immediate ''" . $table_script . "'';" . $index_action . "END;';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible d executer la requete de creation de la procedure de redimensionnement ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure cree avec succes ...", 'proc_name' => $proc_name);
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . ";END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'proc_name' => $proc_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function moveLob()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $lob_script = $_REQUEST['lob_script'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $proc_name = 'proc_move_lob_' . $randomString;
            $job_name = 'job_move_lob_' . $randomString;

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN execute immediate ''alter session force parallel dml'';execute immediate ''alter session force parallel ddl'';execute immediate ''" . $lob_script . "'' ;END;';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible d executer la requete de creation de la procedure de redimensionnement ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure cree avec succes ...", 'proc_name' => $proc_name);
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . ";END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'proc_name' => $proc_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function moveIndex()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $table_script = $_REQUEST['table_script'];
        $indexes_script = $_REQUEST['indexes_script'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $index_action = "";
            $proc_name = 'proc_move_index_' . $randomString;
            $job_name = 'job_move_index_' . $randomString;

            $rows = explode(";", $indexes_script);

            foreach ($rows as $row) {
                if (!empty($row)) {
                    $index_action = $index_action . "execute immediate ''" . $row . "'';";
                }
            }

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN execute immediate ''alter session force parallel dml'';execute immediate ''alter session force parallel ddl'';" . $index_action . "END;';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible d executer la requete de creation de la procedure de redimensionnement ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure cree avec succes ...", 'proc_name' => $proc_name);
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . ";END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'proc_name' => $proc_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function gatherTablestats()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $schema = $_REQUEST['owner'];
        $table = $_REQUEST['segment_name'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $proc_name = 'proc_gather_stats_' . $randomString;
            $job_name = 'job_gather_stats_' . $randomString;
            $method = 'FOR ALL COLUMNS';

            $args = "''" . $schema . "'',''" . $table . "'',METHOD_OPT => ''" . $method . "''";

            $action = "DBMS_STATS.GATHER_TABLE_STATS(" . $args . ");";

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN " . $action . "END;';END;";

            //var_dump($query);
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de parser la requete ' . $query . ' ... raison : ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible d executer la requete ' . $query . ' ... raison : ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure cree avec succes ...", 'proc_name' => $proc_name);
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . " ;execute immediate ''DROP PROCEDURE " . $proc_name . "'';END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        var_dump($query);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            var_dump($query);
                            $response = array('success' => false, 'feedback' => "Impossible d'executer ce job : " . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'proc_name' => $proc_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function gatherIndexstats()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $schema = $_REQUEST['owner'];
        $table = $_REQUEST['segment_name'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $proc_name = 'proc_gather_stats_' . $randomString;
            $job_name = 'job_gather_stats_' . $randomString;

            $args = "''" . $schema . "'',''" . $table . "''";

            $action = "DBMS_STATS.GATHER_INDEX_STATS(" . $args . ");";

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN " . $action . "END;';END;";

            //var_dump($query);
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de parser la requete ' . $query . ' ... raison : ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible d executer la requete ' . $query . ' ... raison : ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure creee avec succes ...", 'proc_name' => $proc_name);
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . ";END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'proc_name' => $proc_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function dropProcedure()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $proc_name = $_REQUEST['proc_name'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {
            $query = "begin execute immediate 'DROP PROCEDURE " . $proc_name . "';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de supprimer cette procedure ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de supprimer cette procedure ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure suppprimee avec succes ...");
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function dropTable()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $table_name = $_REQUEST['segment_name'];
        $owner = $_REQUEST['owner'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {
            $query = "begin execute immediate 'DROP TABLE " . $owner . '.' . $table_name . " purge';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de supprimer cette table ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de supprimer cette table ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Table supprimee avec succes ...");
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function dropIndex()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $table_name = $_REQUEST['segment_name'];
        $owner = $_REQUEST['owner'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {
            $query = "begin execute immediate 'DROP INDEX " . $owner . '.' . $table_name . "';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de supprimer cet Index ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de supprimer cet Index ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Index supprime avec succes ...");
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function renameDatafile()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $src = $_REQUEST['src'];
        $dest = $_REQUEST['dest'];
        $tbs = $_REQUEST['tbs'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {
            $query = "select status from dba_tablespaces where tablespace_name = '" . $tbs . "'";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de determiner le status du tablespace ' . $tbs . ' : ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de determiner le status du tablespace ' . $tbs . ' : ' . htmlentities($e['message']));
                } else {
                    $status = 'offline';

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $status = strtolower($row['STATUS']);
                    }

                    if ($status != 'offline') {
                        $response = array('success' => false, 'feedback' => 'Impossible de renommer ce fichier ');
                    } else {
                        $query = "begin execute immediate 'ALTER DATABASE rename file ''" . $src . "'' to ''" . $dest . "''';END;";

                        $s = oci_parse($c, $query);
                        if (!$s) {
                            $e = oci_error($c);
                            $response = array('success' => false, 'feedback' => 'Impossible de renommer ce fichier de donnees ' . htmlentities($e['message']) . ' query : ' . $query);
                        } else {
                            $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                            if (!$r) {
                                $e = oci_error($s);
                                $response = array('success' => false, 'feedback' => 'Impossible de renommer ce fichier de donnees ' . htmlentities($e['message']));
                            } else {
                                $response = array('success' => true, 'feedback' => "Fichier de donnees renomme avec succes ...");
                            }
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function dropTablespace()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $tablespace_name = $_REQUEST['tablespace_name'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {
            $query = "begin execute immediate 'purge recyclebin';execute immediate 'DROP TABLESPACE " . $tablespace_name . " including contents and datafiles cascade constraints';END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de supprimer cet espace logique ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de supprimer cet espace logique ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "espace logique supprime avec succes ...");
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function watchJob()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $job_name = $_REQUEST['job_name'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "select state status,null err,event info from v\$session where module = 'DBMS_SCHEDULER' union all SELECT status, error# err, ADDITIONAL_INFO info FROM DBA_SCHEDULER_JOB_RUN_DETAILS WHERE lower(job_name) = '" . $job_name . "'";

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
                        $records [] = array('log_id' => $index, 'status' => $row['STATUS'], 'err' => $row['ERR'], 'info' => $row['INFO']);
                        $index++;
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array('success' => true, EXT_JSON_READER_TOTAL => count($records),
                        EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function awrReportTFJO()
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
            $query = "select (SELECT MAX (snap_id) FROM DBA_HIST_SNAPSHOT WHERE startup_time = (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT WHERE startup_time < (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT))) END_SNAP,(SELECT MIN (snap_id) FROM DBA_HIST_SNAPSHOT WHERE startup_time = (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT WHERE startup_time < (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT))) START_SNAP from dual";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible de charger les snap id " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
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
                        $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
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

                            $query = "Select OUTPUT from table(dbms_workload_repository.AWR_REPORT_HTML(" . $dbid . "," . $instance_number . "," . $start_snap . "," . $end_snap . ",8))";
                            $s = oci_parse($c, $query);

                            if (!$s) {
                                $e = oci_error($c);
                                $response = array('success' => false, 'feedback' => "Impossible de generer AWR_REPORT_HTML " . htmlentities($e['message']));
                            } else {
                                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                if (!$r) {
                                    $e = oci_error($s);
                                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
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

                                    $data = $_REQUEST;
                                    $data['body'] = $out;

                                    $response = toC_Reports_Admin::sendEmail($data);
                                }
                            }
                        }
                    }
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function addmReportTFJO()
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
            $query = "select (SELECT MAX (snap_id) FROM DBA_HIST_SNAPSHOT WHERE startup_time = (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT WHERE startup_time < (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT))) END_SNAP,(SELECT MIN (snap_id) FROM DBA_HIST_SNAPSHOT WHERE startup_time = (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT WHERE startup_time < (SELECT MAX (startup_time) FROM DBA_HIST_SNAPSHOT))) START_SNAP from dual";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible de charger les snap id " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
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
                        $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
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
                                $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                            } else {
                                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                if (!$r) {
                                    $e = oci_error($s);
                                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                } else {
                                    oci_free_statement($s);

                                    $query = "SELECT DBMS_ADVISOR.get_task_report ('" . $task_name . "','TEXT','ALL','ALL') report FROM DBA_ADVISOR_TASKS t WHERE     t.task_name = '" . $task_name . "' AND t.owner = SYS_CONTEXT ('USERENV', 'session_user')";
                                    $s = oci_parse($c, $query);

                                    if (!$s) {
                                        $e = oci_error($c);
                                        $response = array('success' => false, 'feedback' => "Impossible de generer ADDM REPORT " . htmlentities($e['message']));
                                    } else {
                                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                        if (!$r) {
                                            $e = oci_error($s);
                                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                        } else {
                                            //oci_fetch_all($s, $output);

                                            $out = '';
                                            while ($row = oci_fetch_array($s, OCI_ASSOC + OCI_RETURN_LOBS)) {
                                                $out = $out . $row['REPORT'];
                                            }

                                            //var_dump($output);
                                            $mailBodyTextFile = '/tmp/' . 'addm_report_' . $randomString . '.txt';
                                            file_put_contents($mailBodyTextFile, $out);

                                            oci_free_statement($s);
                                            oci_close($c);

                                            //var_dump($out);
                                            $data = $_REQUEST;
                                            //$data['body'] = "<table border='0' cellpadding='1' cellspacing='1' style='width:100%;'><tbody><tr><td>" . $out . "</td></tr></tbody></table>";
                                            $data['body'] = "Ci joint le rapport ADDM du dernier TFJO";
                                            $data['attachments'] = $mailBodyTextFile;

                                            $response = toC_Reports_Admin::sendEmail($data);
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

    function addmReportOLTP()
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
                $response = array('success' => false, 'feedback' => "Impossible de charger les snap id " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
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
                        $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
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
                                $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                            } else {
                                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                if (!$r) {
                                    $e = oci_error($s);
                                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                } else {
                                    oci_free_statement($s);

                                    $query = "SELECT DBMS_ADVISOR.get_task_report ('" . $task_name . "','TEXT','ALL','ALL') report FROM DBA_ADVISOR_TASKS t WHERE     t.task_name = '" . $task_name . "' AND t.owner = SYS_CONTEXT ('USERENV', 'session_user')";
                                    $s = oci_parse($c, $query);

                                    if (!$s) {
                                        $e = oci_error($c);
                                        $response = array('success' => false, 'feedback' => "Impossible de generer ADDM REPORT " . htmlentities($e['message']));
                                    } else {
                                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                        if (!$r) {
                                            $e = oci_error($s);
                                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                        } else {
                                            //oci_fetch_all($s, $output);

                                            $out = '';
                                            while ($row = oci_fetch_array($s, OCI_ASSOC + OCI_RETURN_LOBS)) {
                                                $out = $out . $row['REPORT'];
                                            }

                                            //var_dump($output);
                                            $mailBodyTextFile = '/tmp/' . 'addm_report_' . $randomString . '.txt';
                                            file_put_contents($mailBodyTextFile, $out);

                                            oci_free_statement($s);
                                            oci_close($c);

                                            //var_dump($out);
                                            $data = $_REQUEST;
                                            //$data['body'] = "<table border='0' cellpadding='1' cellspacing='1' style='width:100%;'><tbody><tr><td>" . $out . "</td></tr></tbody></table>";
                                            $data['body'] = "Ci joint le rapport ADDM des dernieres transactions OLTP";
                                            $data['attachments'] = $mailBodyTextFile;

                                            $response = toC_Reports_Admin::sendEmail($data);
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

    function awrReportOLTP()
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
                $response = array('success' => false, 'feedback' => "Impossible de charger les snap id " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
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
                        $response = array('success' => false, 'feedback' => "Impossible de charger les metatada de cette base " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
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

                            $query = "Select OUTPUT from table(dbms_workload_repository.AWR_REPORT_HTML(" . $dbid . "," . $instance_number . "," . $start_snap . "," . $end_snap . ",8))";
                            $s = oci_parse($c, $query);

                            if (!$s) {
                                $e = oci_error($c);
                                $response = array('success' => false, 'feedback' => "Impossible de generer AWR_REPORT_HTML " . htmlentities($e['message']));
                            } else {
                                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                if (!$r) {
                                    $e = oci_error($s);
                                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                                } else {
                                    //$output = '';

                                    $characters = '0123456789';
                                    $charactersLength = strlen($characters);
                                    $randomString = '';
                                    for ($i = 0; $i < 5; $i++) {
                                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                                    }

                                    $file = '/tmp/awr_' . $randomString . 'html';

                                    oci_fetch_all($s, $output);

                                    //var_dump($output);

                                    oci_free_statement($s);
                                    oci_close($c);

                                    $out = '';

                                    foreach ($output as $col) {
                                        foreach ($col as $item) {
                                            $out = $out . $item;
                                        }
                                    }

                                    $data = $_REQUEST;
                                    $data['body'] = $out;
                                    //$data['attachments'] = $file;
                                    //var_dump($out);

                                    $response = toC_Reports_Admin::sendEmail($data);
                                }
                            }
                        }
                    }
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function downloadReport()
    {
        global $toC_Json;

        $response = array('success' => true, 'file_name' => HTTP_SERVER . '/' . DIR_REPORT_HTTP_CATALOG . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $_REQUEST['comments']);
        echo $toC_Json->encode($response);
    }

    function getJobStatus()
    {
        global $osC_Database, $toC_Json;
        $task_id = $_REQUEST['task_id'];

        $query = "SELECT * FROM :table_logs where task_id = :task_id ORDER BY LOGS_ID DESC LIMIT 0, 1";
        $Qreports = $osC_Database->query($query);

        $Qreports->bindTable(':table_logs', TABLE_LOGS);
        $Qreports->bindValue(':task_id', $task_id);
        $Qreports->execute();

        $records = array();
        while ($Qreports->next()) {
            $records[] = array('task_id' => $task_id,
                'status' => $Qreports->Value('status'),
                'comments' => $Qreports->Value('comment')
            );
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function sqlReport()
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

                $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=databases&action=sql_rep&db_host=" . $data['db_host'] . "&sql_id=" . $data['sql_id'] . "&db_user=" . $data['db_user'] . "&db_pass=" . $data['db_pass'] . "&db_sid=" . $data['db_sid'] . "&databases_id=" . $data['databases_id'] . "&task_id=" . $randomString . "' &";
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

    function sqlTune()
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

                $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=databases&action=sql_tuning&db_host=" . $data['db_host'] . "&sql_id=" . $data['sql_id'] . "&db_user=" . $data['db_user'] . "&db_pass=" . $data['db_pass'] . "&db_sid=" . $data['db_sid'] . "&databases_id=" . $data['databases_id'] . "&task_id=" . $randomString . "' &";
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

    function getAlertlog()
    {
        global $toC_Json;

        $host = $_REQUEST['db_host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $instance_name = '';
        $background_dump_dest = '';
        $records = array();

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records[] = array(
                'success' => false,
                'feedback' => 'Could not connect to database: ' . htmlentities($e['message']),
                'url' => '',
                'lines' => 0,
                'size' => 0,
                'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
            );
        } else {
            $query = "SELECT (SELECT SYS_CONTEXT ('USERENV', 'INSTANCE_NAME') FROM DUAL)           instance_name,       (SELECT VALUE           FROM v\$parameter          WHERE name = 'background_dump_dest')           background_dump_dest   FROM DUAL";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);

                $records[] = array(
                    'success' => false,
                    'feedback' => "Impossible d'executer le scrip de chargement des parametres : " . htmlentities($e['message']),
                    'url' => '',
                    'lines' => 0,
                    'size' => 0,
                    'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                    'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                );
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records[] = array(
                        'success' => false,
                        'feedback' => htmlentities($e['message']),
                        'url' => '',
                        'lines' => 0,
                        'size' => 0,
                        'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                        'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                        'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                        'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                    );
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $instance_name = $row['INSTANCE_NAME'];
                        $background_dump_dest = $row['BACKGROUND_DUMP_DEST'];
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        if (!empty($instance_name) && !empty($background_dump_dest)) {
            $ssh = new Net_SSH2($host, $port);

            if (empty($ssh->server_identifier)) {
                $records[] = array(
                    'success' => false,
                    'feedback' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme",
                    'url' => '',
                    'lines' => 0,
                    'size' => 0,
                    'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                    'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                );
            } else {
                if (!$ssh->login($user, $pass)) {
                    $records[] = array(
                        'success' => false,
                        'feedback' => "Compte ou mot de passe invalide",
                        'url' => '',
                        'lines' => 0,
                        'size' => 0,
                        'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                        'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                        'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                        'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                    );
                } else {
                    $ssh->disableQuietMode();

                    $url = $background_dump_dest . '/alert_' . $instance_name . '.log';
                    $cmd = "du -m " . $url . " |awk '{print $1'}";
                    $resp = trim($ssh->exec($cmd));

                    $size = (int)$resp;

                    $file = '/dev/shm/' . substr(md5(rand()), 0, 7) . '.log';
                    $cmd = 'strings ' . $url . ' > ' . $file;
                    $ssh->exec($cmd);
                    $url = $file;

                    $cmd = "wc -l " . $url . " |awk '{print $1'}";
                    $resp = trim($ssh->exec($cmd));

                    $lc = (int)$resp;

                    if (isset($_REQUEST['permissions'])) {
                        $permissions = explode(',', $_REQUEST['permissions']);

                        $records[] = array(
                            'success' => true,
                            'feedback' => "OK",
                            'url' => $url,
                            'lines' => $lc,
                            'size' => $size,
                            'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[1],
                            'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : $permissions[2],
                            'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[3]
                        );
                    } else {
                        $records[] = array(
                            'success' => true,
                            'feedback' => "OK",
                            'url' => $url,
                            'lines' => $lc,
                            'size' => $size,
                            'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                            'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                            'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                            'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                        );
                    }

                    $ssh->disconnect();
                }
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function sqlRep()
    {
        error_reporting(E_ALL);
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $sql_id = $_REQUEST['sql_id'];
        $data = $_REQUEST;

        $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Connexion à la base ...');
        toC_Reports_Admin::addJobDetail($detail);

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Could not connect to database: ' . htmlentities($e['message']));
            toC_Reports_Admin::addJobDetail($detail);
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Generation du rapport ...');
            toC_Reports_Admin::addJobDetail($detail);

            $query = "SELECT DBMS_SQLTUNE.REPORT_SQL_MONITOR(sql_id=> '" . $sql_id . "',type=>'HTML') FROM dual";
            var_dump($query);
            $s = oci_parse($c, $query);

            if (!$s) {
                $e = oci_error($c);
                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de generer le rapport SQL : " . htmlentities($e['message']));
                toC_Reports_Admin::addJobDetail($detail);
                $response = array('success' => false, 'feedback' => "Impossible de generer le rapport SQL : " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                    toC_Reports_Admin::addJobDetail($detail);
                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                } else {
                    oci_fetch_all($s, $output);

                    //var_dump($output);

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

                    $report = 'sql_report_' . $data['task_id'] . '.html';
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

        echo $toC_Json->encode($response);
    }

    function sqlTuning()
    {
        error_reporting(E_ALL);
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $sql_id = $_REQUEST['sql_id'];
        $data = $_REQUEST;

        $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'connexion BD ...');
        toC_Reports_Admin::addJobDetail($detail);

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Could not connect to database: ' . htmlentities($e['message']));
            toC_Reports_Admin::addJobDetail($detail);
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {

            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Creation du Job ...');
            toC_Reports_Admin::addJobDetail($detail);

            $query = "DECLARE ret_val VARCHAR2 (4000);BEGIN ret_val := DBMS_SQLTUNE.CREATE_TUNING_TASK(sql_id => '" . $sql_id . "'," . " plan_hash_value => NULL,scope => 'COMPREHENSIVE',time_limit  => 1800," . " task_name   => '" . $data['task_id'] . "',description => '" . $data['task_id'] . "');" . "END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de Creer le Job " . htmlentities($e['message']));
                toC_Reports_Admin::addJobDetail($detail);
                $response = array('success' => false, 'feedback' => "Impossible de Creer le Job " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                    toC_Reports_Admin::addJobDetail($detail);
                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                } else {

                    oci_free_statement($s);

                    $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Analyse de la requete ...');
                    toC_Reports_Admin::addJobDetail($detail);

                    $query = "Begin Dbms_Sqltune.EXECUTE_TUNING_TASK('" . $data['task_id'] . "'); End;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible d'analyser cette requete " . htmlentities($e['message']));
                        toC_Reports_Admin::addJobDetail($detail);
                        $response = array('success' => false, 'feedback' => "Impossible d'analyser cette requete " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                            toC_Reports_Admin::addJobDetail($detail);
                            $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
                        } else {

                            oci_free_statement($s);

                            $detail = array('task_id' => $data['task_id'], 'status' => 'run', 'comments' => 'Generation du rapport ...');
                            toC_Reports_Admin::addJobDetail($detail);

                            $query = "select Dbms_Sqltune.REPORT_TUNING_TASK('" . $data['task_id'] . "', 'TEXT', 'ALL') report from dual";
                            $s = oci_parse($c, $query);

                            if (!$s) {
                                $e = oci_error($c);
                                $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => "Impossible de generer le Rapport " . htmlentities($e['message']));
                                toC_Reports_Admin::addJobDetail($detail);
                                $response = array('success' => false, 'feedback' => "Impossible de generer le Rapport " . htmlentities($e['message']));
                            } else {
                                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                                if (!$r) {
                                    $e = oci_error($s);
                                    $detail = array('task_id' => $data['task_id'], 'status' => 'error', 'comments' => 'Erreur : ' . htmlentities($e['message']));
                                    toC_Reports_Admin::addJobDetail($detail);
                                    $response = array('success' => false, 'feedback' => 'Erreur : ' . htmlentities($e['message']));
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

                                    $report = 'sql_tuning_' . $data['task_id'] . '.txt';
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

    function watchCapture()
    {
        $db_host = $_REQUEST['db_host'];
        $os_user = $_REQUEST['os_user'];
        $os_pass = $_REQUEST['os_pass'];
        $src_home = $_REQUEST['src_home'];
        $oggdir_src = $_REQUEST['oggdir_src'];
        $extract_name = $_REQUEST['extract_name'];

        global $toC_Json;

        $response = array('success' => false, 'feedback' => "NOK");

        $data = $_REQUEST;

        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($db_host);
        if (!$ssh->login($os_user, $os_pass)) {
            $data['comments'] = "Impossible d etablir une connexion SSH : src_db_host = " . $db_host . "  src_os_user = " . $os_user;
            $data['state'] = "UNKNOWN";
        } else {
            $ssh->disableQuietMode();

            $cmd = "echo export ORACLE_HOME=" . $src_home . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $src_home . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $src_home . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $oggdir_src . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo info " . $extract_name . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString . " > /tmp/" . $extract_name . "_" . $randomString . ".info";
            $ssh->exec($cmd);

            $cmd = "cat /tmp/" . $extract_name . "_" . $randomString . ".info | awk '{if (NR ==11) print \$8}'";
            $state = $ssh->exec($cmd);

            $data['state'] = trim($state);

            $cmd = "cat /tmp/" . $extract_name . "_" . $randomString . ".info";
            $data['comments'] = $ssh->exec($cmd);

            $cmd = "cat /tmp/" . $extract_name . "_" . $randomString . ".info | awk '{if (NR ==12) print \$3}'";
            $lag = $ssh->exec($cmd);

            $data['lag'] = $lag;

            $cmd = "cat /tmp/" . $extract_name . "_" . $randomString . ".info | awk '{if (NR ==13) print \$5}'";
            $trail = $ssh->exec($cmd);

            $data['trail'] = $trail;

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "rm -f /tmp/" . $extract_name . "_" . $randomString . ".info";
            $ssh->exec($cmd);

            $success = toC_Databases_Admin::saveCaptureState($data);

            $response = array('success' => $success, 'feedback' => $success ? "OK" : "NOK");
        }

        $ssh->disconnect();
        echo $toC_Json->encode($response);
    }

    function monCapture()
    {
        global $toC_Json, $osC_Database;

        $data = $_REQUEST;

        $file = "/dev/shm/capture_" . $data['config_id'];
        $json = $toC_Json->encode($data);
        $success = file_put_contents($file, $json) > 0;

        //$success = toC_Databases_Admin::saveCaptureState($data);

        $response = array('success' => $success, 'feedback' => $success ? "OK" : "NOK");

        echo $toC_Json->encode($response);
    }

    function monOgglog()
    {
        global $toC_Json, $osC_Database;

        $data = $_REQUEST;

        $success = toC_Databases_Admin::saveOggLog($data);

        $response = array('success' => $success, 'feedback' => $success ? "OK" : "NOK");

        echo $toC_Json->encode($response);
    }

    function monDatapump()
    {
        global $toC_Json;

        $data = $_REQUEST;

        $file = "/dev/shm/datapump_" . $data['config_id'];
        $json = $toC_Json->encode($data);
        $success = file_put_contents($file, $json) > 0;

        //$success = toC_Databases_Admin::savePropagationState($data);

        $response = array('success' => $success, 'feedback' => $success ? "OK" : "NOK");

        echo $toC_Json->encode($response);
    }

    function monReplicat()
    {
        global $toC_Json, $osC_Database;

        $data = $_REQUEST;

        $file = "/dev/shm/replicat_" . $data['config_id'];
        $json = $toC_Json->encode($data);
        $success = file_put_contents($file, $json) > 0;

        //$success = toC_Databases_Admin::saveReplicationState($data);

        $response = array('success' => $success, 'feedback' => $success ? "OK" : "NOK");

        echo $toC_Json->encode($response);
    }

    function watchPropagation()
    {
        $db_host = $_REQUEST['db_host'];
        $config_id = $_REQUEST['config_id'];
        $os_user = $_REQUEST['os_user'];
        $os_pass = $_REQUEST['os_pass'];
        $src_home = $_REQUEST['src_home'];
        $oggdir_src = $_REQUEST['oggdir_src'];
        $datapump_name = $_REQUEST['datapump_name'];

        global $toC_Json;

        $response = array('success' => false, 'feedback' => "NOK");

        $data = $_REQUEST;

        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($db_host);
        if (!$ssh->login($os_user, $os_pass)) {
            $data['comments'] = 'Impossible d etablir une connexion SSH : src_db_host = ' . $db_host . '  src_os_user = ' . $os_user;
            $data['state'] = "UNKNOWN";
        } else {
            $ssh->disableQuietMode();

            $cmd = "echo export ORACLE_HOME=" . $src_home . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $src_home . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $src_home . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $oggdir_src . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo info " . $datapump_name . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo send extract " . $datapump_name . " gettcpstats >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString . " > /tmp/" . $datapump_name . "_" . $randomString . ".info";
            $ssh->exec($cmd);

            $cmd = "cat /tmp/" . $datapump_name . "_" . $randomString . ".info | awk '{if (NR ==11) print \$8}'";
            $state = $ssh->exec($cmd);

            $data['state'] = trim($state);

            $cmd = "cat /tmp/" . $datapump_name . "_" . $randomString . ".info";
            $data['comments'] = $ssh->exec($cmd);

            $cmd = "cat /tmp/" . $datapump_name . "_" . $randomString . ".info | awk '{if (NR ==12) print \$3}'";
            $lag = $ssh->exec($cmd);

            $data['lag'] = $lag;

            $cmd = "cat /tmp/" . $datapump_name . "_" . $randomString . ".info | awk '{if (NR ==13) print \$5}'";
            $trail = $ssh->exec($cmd);

            $data['trail'] = $trail;

            $cmd = "cat /tmp/" . $datapump_name . "_" . $randomString . ".info |grep -i Outbound|awk '{print \$6\" \"\$7}'";
            $net = $ssh->exec($cmd);

            $data['net'] = $net;

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "rm -f /tmp/" . $datapump_name . "_" . $randomString . ".info";
            $ssh->exec($cmd);

            $success = toC_Databases_Admin::savePropagationState($data);

            $response = array('success' => $success, 'feedback' => $success ? "OK" : "NOK");
        }

        $ssh->disconnect();

        echo $toC_Json->encode($response);
    }

    function watchReplication()
    {
        $db_host = $_REQUEST['db_host'];
        $config_id = $_REQUEST['config_id'];
        $os_user = $_REQUEST['os_user'];
        $os_pass = $_REQUEST['os_pass'];
        $dest_home = $_REQUEST['dest_home'];
        $oggdir_dest = $_REQUEST['oggdir_dest'];
        $replicat_name = $_REQUEST['replicat_name'];

        global $toC_Json;

        $response = array('success' => false, 'feedback' => "NOK");

        $data = $_REQUEST;

        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($db_host);
        if (!$ssh->login($os_user, $os_pass)) {
            $data['comments'] = 'Impossible d etablir une connexion SSH : src_db_host = ' . $db_host . '  src_os_user = ' . $os_user;
            $data['state'] = "UNKNOWN";
        } else {
            $ssh->disableQuietMode();

            $cmd = "ps -ef |grep LISTENER | grep -v grep | awk '{print \$8}' | awk 'BEGIN {FS=\"bin\"} {print $1}'";
            $data['dest_home'] = $ssh->exec($cmd);

            $cmd = "echo export ORACLE_HOME=" . $dest_home . "> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export PATH=" . $dest_home . "/bin:/sbin/:bin >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo export LD_LIBRARY_PATH=" . $dest_home . "/lib >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo cd " . $oggdir_dest . ">> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ./ggsci \<\<! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo info " . $replicat_name . " >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "echo ! >> /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "chmod +x /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "sh /tmp/capture" . $randomString . " > /tmp/" . $replicat_name . "_" . $randomString . ".info";
            $ssh->exec($cmd);

            $cmd = "cat /tmp/" . $replicat_name . "_" . $randomString . ".info | awk '{if (NR ==11) print \$8}'";
            $state = $ssh->exec($cmd);

            $data['state'] = trim($state);

            $cmd = "cat /tmp/" . $replicat_name . "_" . $randomString . ".info";
            $data['comments'] = $ssh->exec($cmd);

            $cmd = "cat /tmp/" . $replicat_name . "_" . $randomString . ".info | awk '{if (NR ==12) print \$3}'";
            $lag = $ssh->exec($cmd);

            $data['lag'] = $lag;

            $cmd = "cat /tmp/" . $replicat_name . "_" . $randomString . ".info | awk '{if (NR ==13) print \$5}'";
            $trail = $ssh->exec($cmd);

            $data['trail'] = $trail;

            $cmd = "rm -f /tmp/capture" . $randomString;
            $ssh->exec($cmd);

            $cmd = "rm -f /tmp/" . $replicat_name . "_" . $randomString . ".info";
            $ssh->exec($cmd);

            $success = toC_Databases_Admin::saveReplicationState($data);

            $response = array('success' => $success, 'feedback' => $success ? "OK" : "NOK");
        }

        $ssh->disconnect();
        echo $toC_Json->encode($response);
    }

    function loadSegment()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $segment_type = $_REQUEST['segment_type'];
        $segment_name = $_REQUEST['segment_name'];
        $spartition_name = $_REQUEST['partition_name'];
        $owner = $_REQUEST['owner'];

        if (strtolower($segment_type) != 'table' && strtolower($segment_type) != 'index' && strtolower($segment_type) != 'table partition' && strtolower($segment_type) != 'table subpartition') {
            $response = array('success' => false, 'feedback' => "Ce type de segment n'est pas supporté");
        } else {
            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                $query = "";

                switch ($segment_type) {
                    case "TABLE":
                        $query = "select i.index_name,tablespace_name,(select count(*) from dba_ind_columns d where d.index_name = i.index_name and d.table_name = i.table_name and d.table_owner = '" . strtoupper($owner) . "') cols from dba_indexes i where lower(i.table_name) = '" . strtolower($segment_name) . "' and i.owner = '" . strtoupper($owner) . "'";
                        break;

                    case "TABLE PARTITION":
                        $query = "select i.index_name,tablespace_name,(select count(*) from dba_ind_columns d where d.index_name = i.index_name and d.table_name = i.table_name and d.table_owner = '" . strtoupper($owner) . "') cols from dba_indexes i where lower(i.table_name) = '" . strtolower($segment_name) . "' and i.owner = '" . strtoupper($owner) . "'";
                        break;

                    case "INDEX":
                        $in = "(";

                        $batchs = explode(',', $_REQUEST['segment_name']);
                        foreach ($batchs as $batch) {
                            $in = $in . "'" . $batch . "',";
                        }

                        $in = $in . "'DUMMY')";

                        $query = "select i.index_name,tablespace_name,(select count(*) from dba_ind_columns d where d.index_name = i.index_name and d.table_name = i.table_name) cols from dba_indexes i where lower(i.index_name) in " . strtolower($in) . " and i.owner = '" . strtoupper($owner) . "'";
                        break;
                    case "LOBSEGMENT":
                        $query = "select i.index_name,tablespace_name,0 cols from dba_lobs i where lower(i.table_name) = '" . strtolower($segment_name) . "' and i.owner = '" . strtoupper($owner) . "'";
                        break;
                }

                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));
                    } else {
                        $records = array();

                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                            $records [] = array('tablespace_name' => $row['TABLESPACE_NAME'], 'index_name' => $row['INDEX_NAME'], 'cols' => $row['COLS']);
                        }

                        $response = array('success' => true, 'indexes' => $records);
                    }

                    oci_free_statement($s);
                    oci_close($c);
                }
            }
        }

        echo $toC_Json->encode($response);
    }


    function loadLob()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $segment_type = $_REQUEST['segment_type'];
        $segment_name = $_REQUEST['segment_name'];
        $owner = $_REQUEST['owner'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "SELECT i.index_name,i.table_name,i.tablespace_name,i.column_name,0 cols FROM dba_lobs i where lower(i.segment_name) = '" . strtolower($segment_name) . "' and i.owner = '" . strtoupper($owner) . "'";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('tablespace_name' => $row['TABLESPACE_NAME'], 'column_name' => $row['COLUMN_NAME'], 'index_name' => $row['INDEX_NAME'], 'cols' => $row['COLS'], 'table_name' => $row['TABLE_NAME']);
                    }

                    $response = array('success' => true, 'indexes' => $records);
                }

                oci_free_statement($s);
                oci_close($c);
            }
        }

        echo $toC_Json->encode($response);
    }

    function listDatabasesconnexions()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT  CONCAT('host=',`delta_servers`.`host`,'#server_port=',`delta_servers`.`PORT`,'#server_user=',`delta_servers`.`user`,'#server_pass=',`delta_servers`.`pass`,'#servers_id=',`delta_servers`.`servers_id`,'#user=',`delta_databases`.`user`,'#pass=',`delta_databases`.`pass`,'#sid=',`delta_databases`.`sid`,'#port=',`delta_databases`.`port`,'#databases_id=',`delta_databases`.`databases_id`) AS `oracle_connexion`,`delta_databases`.`label` AS label_database FROM `delta_databases` INNER JOIN `delta_servers` ON (`delta_databases`.`servers_id` = `delta_servers`.`servers_id`) order by `delta_databases`.`label`";

        $QServers = $osC_Database->query($query);
        $QServers->execute();

        $i = 0;
        $records = array();
        while ($QServers->next()) {
            $records[] = array('oracle_connexion' => $QServers->Value('oracle_connexion'), 'label_database' => $QServers->Value('label_database'),
                'id' => $i
            );

            $i++;
        }

        $response = array(EXT_JSON_READER_TOTAL => $i,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listTbscombo()
    {
        global $toC_Json;

        $query = "SELECT ts.tablespace_name,
       size_info.megs_used,
       size_info.MAX
  FROM (SELECT a.tablespace_name,
               ROUND ( (a.bytes_alloc - NVL (b.bytes_free, 0)) / 1024 / 1024)
                  megs_used,
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
         WHERE a.tablespace_name = b.tablespace_name(+) and a.tablespace_name not in (SELECT TABLESPACE_NAME FROM DBA_TABLESPACES where CONTENTS in ('UNDO','TEMPORARY')) and a.tablespace_name not in ('SYSTEM','SYSAUX')
        UNION ALL
          SELECT h.tablespace_name,
                 ROUND (SUM (NVL (p.bytes_used, 0)) / 1048576) megs_used,
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
 WHERE ts.tablespace_name = size_info.tablespace_name and ts.tablespace_name not in (SELECT TABLESPACE_NAME FROM DBA_TABLESPACES where CONTENTS in ('UNDO','TEMPORARY')) and ts.tablespace_name not in ('SYSTEM','SYSAUX') order by 1";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;
                        $max = (int)($row['MAX']);
                        $used = (int)($row['MEGS_USED']);
                        //$total_percent_used = (int)($used * 100 / $max);
                        $free = $max - $used;

                        $records [] = array('tablespace_name' => $row['TABLESPACE_NAME'], 'content' => $row['TABLESPACE_NAME'] . ' (' . $free . ' MB libre)');
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array(EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listTablespacecombo()
    {
        global $toC_Json;

        $query = "select tablespace_name from dba_tablespaces order by 1";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $records = array();
        $total = 0;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();

            $records [] = array('tablespace_name' => 'err', 'label' => 'Could not connect to database: ' . htmlentities($e['message']));

            //$response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('tablespace_name' => 'err', 'label' => "Impossible d'executer cette requete " . htmlentities($e['message']));

                //$response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('tablespace_name' => 'err', 'label' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));

                    //$response = array('success' => false, 'feedback' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));
                } else {

                    $records [] = array('tablespace_name' => 'all', 'label' => "Tous les Tablespaces");

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;

                        $records [] = array('tablespace_name' => $row['TABLESPACE_NAME'], 'label' => $row['TABLESPACE_NAME']);
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listTemptbs()
    {
        global $toC_Json;

        $query = "SELECT ts.tablespace_name,
       size_info.megs_used,
       size_info.MAX
  FROM (SELECT a.tablespace_name,
               ROUND ( (a.bytes_alloc - NVL (b.bytes_free, 0)) / 1024 / 1024)
                  megs_used,
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
         WHERE a.tablespace_name = b.tablespace_name(+) and a.tablespace_name in (SELECT TABLESPACE_NAME FROM DBA_TABLESPACES where CONTENTS in ('TEMPORARY'))
        UNION ALL
          SELECT h.tablespace_name,
                 ROUND (SUM (NVL (p.bytes_used, 0)) / 1048576) megs_used,
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
 WHERE ts.tablespace_name = size_info.tablespace_name and ts.tablespace_name in (SELECT TABLESPACE_NAME FROM DBA_TABLESPACES where CONTENTS in ('TEMPORARY')) order by 1";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;
                        $max = (int)($row['MAX']);
                        $used = (int)($row['MEGS_USED']);
                        //$total_percent_used = (int)($used * 100 / $max);
                        $free = $max - $used;

                        $records [] = array('tablespace_name' => $row['TABLESPACE_NAME'], 'content' => $row['TABLESPACE_NAME'] . ' (' . $free . ' MB libre)');
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array(EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listProfiles()
    {
        global $toC_Json;

        $query = "SELECT distinct profile FROM dba_profiles ORDER BY 1";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les profiles de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;

                        $records [] = array('profile' => $row['PROFILE']);
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array(EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listRoles()
    {
        global $toC_Json;

        $query = "SELECT role FROM sys.DBA_ROLES r WHERE role NOT IN ('AQ_ADMINISTRATOR_ROLE','APEX_ADMINISTRATOR_ROLE','AUTHENTICATEDUSER', 'ADM_PARALLEL_EXECUTE_TASK', 'DBFS_ROLE', 'HS_ADMIN_EXECUTE_ROLE', 'HS_ADMIN_SELECT_ROLE', 'AQ_USER_ROLE', 'CONNECT', 'CSW_USR_ROLE', 'CTXAPP', 'CWM_USER', 'DATAPUMP_EXP_FULL_DATABASE', 'DATAPUMP_IMP_FULL_DATABASE', 'DBA', 'DELETE_CATALOG_ROLE', 'DMUSER_ROLE', 'DM_CATALOG_ROLE', 'EJBCLIENT', 'EXECUTE_CATALOG_ROLE', 'EXP_FULL_DATABASE', 'GATHER_SYSTEM_STATISTICS', 'GLOBAL_AQ_USER_ROLE', 'HS_ADMIN_ROLE', 'IMP_FULL_DATABASE', 'JAVADEBUGPRIV', 'JAVAIDPRIV', 'JAVASYSPRIV', 'JAVAUSERPRIV', 'JAVA_ADMIN', 'JAVA_DEPLOY', 'JMXSERVER', 'LOGSTDBY_ADMINISTRATOR', 'MGMT_USER', 'OEM_ADVISOR', 'OEM_MONITOR', 'OLAPI_TRACE_USER', 'OLAP_DBA', 'OLAP_USER', 'OLAP_XS_ADMIN', 'ORDADMIN', 'OWB\$CLIENT', 'OWB_DESIGNCENTER_VIEW', 'OWB_USER', 'PLUSTRACE', 'RECOVERY_CATALOG_OWNER', 'RESOURCE', 'SCHEDULER_ADMIN', 'SELECT_CATALOG_ROLE', 'SPATIAL_CSW_ADMIN', 'SPATIAL_WFS_ADMIN', 'WFS_USR_ROLE', 'WKUSER', 'WM_ADMIN_ROLE', 'XDBADMIN', 'XDB_SET_INVOKER', 'XDB_WEBSERVICES', 'XDB_WEBSERVICES_OVER_HTTP', 'XDB_WEBSERVICES_WITH_PUBLIC') ORDER BY ROLE";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les roles de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $records [] = array('role' => 'PUBLIC');
                    $total = 1;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;

                        $records [] = array('role' => $row['ROLE']);
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array(EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listDb()
    {
        global $toC_Json, $osC_Database;

        $group_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        //$QServers = $osC_Database->query('select a.*, cd.*,c.*, atoc.*,s.label,s.host,s.servers_id,s.typ,s.user as server_user,s.pass as server_pass,s.port as server_port,st.* from :table_databases a left join :table_content c on a.databases_id = c.content_id left join  :table_content_description cd on a.databases_id = cd.content_id left join  :table_servers s on a.servers_id = s.servers_id left join :table_content_to_categories atoc on atoc.content_id = a.databases_id LEFT JOIN delta_database_state st ON st.databases_id = a.databases_id where cd.language_id = :language_id and atoc.content_type = "databases" and c.content_type = "databases" and cd.content_type = "databases" AND st.start_date = (SELECT MAX(start_date) FROM delta_database_state WHERE databases_id = a.databases_id) ');
        $query = "SELECT a.*,s.label,s.HOST,s.servers_id,s.typ,s.USER AS server_user,s.pass AS server_pass,s.PORT AS server_port FROM delta_databases a INNER JOIN delta_servers s ON a.servers_id = s.servers_id";
        $Qdatabases = $osC_Database->query($query);

        if (!empty($_REQUEST['search'])) {
            $Qdatabases->appendQuery('and a.label like :content_name');
            $Qdatabases->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        if (!empty($_REQUEST['category']) && $_REQUEST['category'] != 'all') {
            $Qdatabases->appendQuery('and a.category = :category');
            $Qdatabases->bindValue(':category', $_REQUEST['category']);
        }

        if ($group_id != 0 && $group_id != -1) {
            $Qdatabases->appendQuery('and a.databases_id IN (SELECT databases_id FROM delta_database_to_groups WHERE group_id = :group_id)');
            $Qdatabases->bindInt(':group_id', $group_id);
        }

        $Qdatabases->appendQuery('order by a.label ');
        $Qdatabases->execute();

        $records = array();
        while ($Qdatabases->next()) {
            if (isset($_REQUEST['permissions'])) {
                $permissions = explode(',', $_REQUEST['permissions']);
                $records[] = array('databases_id' => $Qdatabases->ValueInt('databases_id'),
                    'host' => $Qdatabases->Value('HOST'),
                    'server_user' => $Qdatabases->Value('server_user'),
                    'servers_id' => $Qdatabases->Value('servers_id'),
                    'server_pass' => $Qdatabases->Value('server_pass'),
                    'server_port' => $Qdatabases->Value('server_port'),
                    'label' => $Qdatabases->Value('label'),
                    'sid' => $Qdatabases->Value('sid'),
                    'port' => $Qdatabases->Value('port'),
                    'db_user' => $Qdatabases->Value('user'),
                    'typ' => $Qdatabases->Value('typ'),
                    'db_pass' => $Qdatabases->Value('pass')
                );
            } else {
                $records[] = array('databases_id' => $Qdatabases->ValueInt('databases_id'),
                    'host' => $Qdatabases->Value('HOST'),
                    'server_user' => $Qdatabases->Value('server_user'),
                    'servers_id' => $Qdatabases->Value('servers_id'),
                    'server_pass' => $Qdatabases->Value('server_pass'),
                    'server_port' => $Qdatabases->Value('server_port'),
                    'label' => $Qdatabases->Value('label'),
                    'sid' => $Qdatabases->Value('sid'),
                    'port' => $Qdatabases->Value('port'),
                    'db_user' => $Qdatabases->Value('user'),
                    'typ' => $Qdatabases->Value('typ'),
                    'db_pass' => $Qdatabases->Value('pass')
                );
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listDatabases()
    {
        global $toC_Json, $osC_Database;

        $group_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        //$QServers = $osC_Database->query('select a.*, cd.*,c.*, atoc.*,s.label,s.host,s.servers_id,s.typ,s.user as server_user,s.pass as server_pass,s.port as server_port,st.* from :table_databases a left join :table_content c on a.databases_id = c.content_id left join  :table_content_description cd on a.databases_id = cd.content_id left join  :table_servers s on a.servers_id = s.servers_id left join :table_content_to_categories atoc on atoc.content_id = a.databases_id LEFT JOIN delta_database_state st ON st.databases_id = a.databases_id where cd.language_id = :language_id and atoc.content_type = "databases" and c.content_type = "databases" and cd.content_type = "databases" AND st.start_date = (SELECT MAX(start_date) FROM delta_database_state WHERE databases_id = a.databases_id) ');
        $query = "SELECT a.*,s.HOST,s.servers_id,s.typ,s.USER AS server_user,s.pass AS server_pass,s.PORT AS server_port,st.* FROM delta_databases a LEFT JOIN delta_servers s ON a.servers_id = s.servers_id LEFT JOIN delta_database_state st ON st.databases_id = a.databases_id WHERE st.start_date = (SELECT MAX(start_date) FROM delta_database_state WHERE databases_id = a.databases_id)";
        $QServers = $osC_Database->query($query);

        if (!empty($_REQUEST['search'])) {
            $QServers->appendQuery('and a.label like :content_name');
            $QServers->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        if (!empty($_REQUEST['category']) && $_REQUEST['category'] != 'all') {
            $QServers->appendQuery('and a.category = :category');
            $QServers->bindValue(':category', $_REQUEST['category']);
        }

        if ($group_id != 0 && $group_id != -1) {
            $QServers->appendQuery('and a.databases_id IN (SELECT databases_id FROM delta_database_to_groups WHERE group_id = :group_id)');
            $QServers->bindTable(':table_users_roles', TABLE_USERS_ROLES);
            $QServers->bindInt(':group_id', $group_id);
        }

        $QServers->appendQuery('order by a.label ');
        $QServers->execute();

        $records = array();
        while ($QServers->next()) {
            $entry_icon = osc_icon_from_filename('xxxxxxxxxx.' . $QServers->value('state'), $QServers->Value('comments'));

            if (isset($_REQUEST['permissions'])) {
                $permissions = explode(',', $_REQUEST['permissions']);
                $records[] = array('databases_id' => $QServers->ValueInt('databases_id'),
                    'icon' => $entry_icon,
                    'status' => $QServers->Value('state'),
                    'tbs' => $QServers->Value('tbs'),
                    'comments' => $QServers->Value('comments'),
                    'host' => $QServers->Value('HOST'),
                    'server_user' => $QServers->Value('server_user'),
                    'servers_id' => $QServers->Value('servers_id'),
                    'server_pass' => $QServers->Value('server_pass'),
                    'server_port' => $QServers->Value('server_port'),
                    'label' => $QServers->Value('label'),
                    'sid' => $QServers->Value('sid'),
                    'port' => $QServers->Value('port'),
                    'db_user' => $QServers->Value('user'),
                    'typ' => $QServers->Value('typ'),
                    'db_pass' => $QServers->Value('pass'),
                    'fs' => $QServers->Value('fs'),
                    'startup_time' => $QServers->Value('startup_time'),
                    'version' => $QServers->Value('version'),
                    'db_size' => $QServers->Value('db_size'),
                    'last_backup' => $QServers->Value('last_backup'),
                    'flashback_time' => $QServers->Value('flashback_time'),
                    'percent_free_fra' => $QServers->Value('percent_free_fra'),
                    'last_backup_size' => $QServers->Value('last_backup_size'),
                    'last_backup_status' => $QServers->Value('last_backup_status'),
                    'archiver' => $QServers->Value('archiver'),
                    'log_archived' => $QServers->Value('log_archived'),
                    'log_applied' => $QServers->Value('log_applied'),
                    'applied_time' => $QServers->Value('applied_time'),
                    'log_gap' => $QServers->Value('log_gap'),
                    'role' => $QServers->Value('role')
                );
            } else {
                $records[] = array('databases_id' => $QServers->ValueInt('databases_id'),
                    'icon' => $entry_icon,
                    'status' => $QServers->Value('state'),
                    'tbs' => $QServers->Value('tbs'),
                    'comments' => $QServers->Value('comments'),
                    'host' => $QServers->Value('HOST'),
                    'server_user' => $QServers->Value('server_user'),
                    'servers_id' => $QServers->Value('servers_id'),
                    'server_pass' => $QServers->Value('server_pass'),
                    'server_port' => $QServers->Value('server_port'),
                    'label' => $QServers->Value('label'),
                    'sid' => $QServers->Value('sid'),
                    'port' => $QServers->Value('port'),
                    'db_user' => $QServers->Value('user'),
                    'typ' => $QServers->Value('typ'),
                    'db_pass' => $QServers->Value('pass'),
                    'fs' => $QServers->Value('fs'),
                    'startup_time' => $QServers->Value('startup_time'),
                    'version' => $QServers->Value('version'),
                    'db_size' => $QServers->Value('db_size'),
                    'last_backup' => $QServers->Value('last_backup'),
                    'flashback_time' => $QServers->Value('flashback_time'),
                    'percent_free_fra' => $QServers->Value('percent_free_fra'),
                    'last_backup_size' => $QServers->Value('last_backup_size'),
                    'last_backup_status' => $QServers->Value('last_backup_status'),
                    'archiver' => $QServers->Value('archiver'),
                    'log_archived' => $QServers->Value('log_archived'),
                    'log_applied' => $QServers->Value('log_applied'),
                    'applied_time' => $QServers->Value('applied_time'),
                    'log_gap' => $QServers->Value('log_gap'),
                    'role' => $QServers->Value('role')
                );
            }
        }

        $recs = array();
        foreach ($records as $rec) {
            if ($rec['status'] == 'down') {
                $recs[] = $rec;
            }
        }

        foreach ($records as $rec) {
            if ($rec['status'] == 'warning' || $rec['status'] == 'warni') {
                $recs[] = $rec;
            }
        }

        foreach ($records as $rec) {
            if ($rec['status'] == 'up') {
                $recs[] = $rec;
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($recs),
            EXT_JSON_READER_ROOT => $recs);

        echo $toC_Json->encode($response);
    }

    function listDatabasesperf()
    {
        global $toC_Json, $osC_Database;

        $group_id = empty($_REQUEST['category']) ? 0 : $_REQUEST['category'];

        $query = "SELECT a.*,s.HOST,s.servers_id,s.typ,s.USER AS server_user,s.pass AS server_pass,s.PORT AS server_port FROM delta_databases a LEFT OUTER JOIN delta_servers s ON a.servers_id = s.servers_id WHERE 1 = 1 ";
        $QServers = $osC_Database->query($query);

        if (!empty($_REQUEST['search'])) {
            $QServers->appendQuery('and a.label like :content_name');
            $QServers->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        if (!empty($_REQUEST['where'])) {
            $QServers->appendQuery('and ' . $_REQUEST['where']);
        }

        if ($group_id != 0 && $group_id != -1) {
            $QServers->appendQuery('and a.databases_id IN (SELECT databases_id FROM delta_database_to_groups WHERE group_id = :group_id)');
            $QServers->bindInt(':group_id', $group_id);
        }

        $QServers->appendQuery('order by a.label ');
        $QServers->execute();

        $records = array();
        while ($QServers->next()) {
            if (isset($_REQUEST['permissions'])) {
                $permissions = explode(',', $_REQUEST['permissions']);
                $records[] = array('databases_id' => $QServers->ValueInt('databases_id'),
                    'host' => $QServers->Value('HOST'),
                    'server_user' => $QServers->Value('server_user'),
                    'servers_id' => $QServers->Value('servers_id'),
                    'server_pass' => $QServers->Value('server_pass'),
                    'server_port' => $QServers->Value('server_port'),
                    'label' => $QServers->Value('label'),
                    'sid' => $QServers->Value('sid'),
                    'port' => $QServers->Value('port'),
                    'db_user' => $QServers->Value('user'),
                    'typ' => $QServers->Value('typ'),
                    'db_pass' => $QServers->Value('pass')
                );
            } else {
                $records[] = array('databases_id' => $QServers->ValueInt('databases_id'),
                    'host' => $QServers->Value('HOST'),
                    'server_user' => $QServers->Value('server_user'),
                    'servers_id' => $QServers->Value('servers_id'),
                    'server_pass' => $QServers->Value('server_pass'),
                    'server_port' => $QServers->Value('server_port'),
                    'label' => $QServers->Value('label'),
                    'sid' => $QServers->Value('sid'),
                    'port' => $QServers->Value('port'),
                    'db_user' => $QServers->Value('user'),
                    'typ' => $QServers->Value('typ'),
                    'db_pass' => $QServers->Value('pass')
                );
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function watchTopevents()
    {
        global $toC_Json, $osC_Database;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $databases_id = $_REQUEST['databases_id'];

        $data = $_REQUEST;

        $categories = array();
        $other = array();
        $application = array();
        $configuration = array();
        $administrative = array();
        $concurrency = array();
        $commit = array();
        $network = array();
        $userio = array();
        $systemio = array();
        $clustering = array();
        $queueing = array();

        $ssh = new Net_SSH2(REPORT_RUNNER, '22');

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
        } else {
            if (!$ssh->login("guyfomi", "12345")) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
            } else {
                $ssh->disableQuietMode();

                $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=databases&action=snap_events&db_host=" . $data['db_host'] . "&db_user=" . $data['db_user'] . "&db_pass=" . $data['db_pass'] . "&db_sid=" . $data['db_sid'] . "&databases_id=" . $data['databases_id'] . "' &";
                $ssh->exec($cmd);
                //var_dump($cmd);

                $ssh->disconnect();
            }

            $query = "select * from delta_oracle_events where databases_id = " . $databases_id . " order by date";

            $QServers = $osC_Database->query($query);
            $records = array();
            while ($QServers->next()) {
                $records[] = array(
                    'date' => $QServers->Value('date'),
                    'other' => $QServers->Value('other'),
                    'application' => $QServers->Value('application'),
                    'configuration' => $QServers->Value('configuration'),
                    'administrative' => $QServers->Value('administrative'),
                    'concurrency' => $QServers->Value('concurrency'),
                    'commit' => $QServers->Value('commit'),
                    'network' => $QServers->Value('network'),
                    'userio' => $QServers->Value('userio'),
                    'systemio' => $QServers->Value('systemio'),
                    'scheduler' => $QServers->Value('scheduler'),
                    'clustering' => $QServers->Value('clustering'),
                    'queueing' => $QServers->Value('queueing'));
            }

//            while ($QServers->next()) {
//
//                $categories[] = $QServers->Value('date');
//                $other[] = $QServers->Value('other');
//                $application[] = $QServers->Value('application');
//                $configuration[] = $QServers->Value('configuration');
//                $administrative[] = $QServers->Value('administrative');
//                $concurrency[] = $QServers->Value('concurrency');
//                $commit[] = $QServers->Value('commit');
//                $network[] = $QServers->Value('network');
//                $userio[] = $QServers->Value('userio');
//                $systemio[] = $QServers->Value('systemio');
//                $clustering[] = $QServers->Value('clustering');
//                $queueing[] = $QServers->Value('queueing');
//            }
//        }
//
//        $response = array('date' => $categories,
//            'other' => $other,
//            'application' => $application,
//            'configuration' => $configuration,
//            'administrative' => $administrative,
//            'concurrency' => $concurrency,
//            'commit' => $commit,
//            'userio' => $userio,
//            'systemio' => $systemio,
//            'clustering' => $clustering,
//            'queueing' => $queueing,
//            'network' => $network
//        );

            $response = $records;

            echo $toC_Json->encode($response);
        }
    }

    function listTopevents()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $records = array();

        $sum = 0;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            //trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
            $records [] = array('event' => $e['message'],
                'time_waited' => 1, 'wait_class' => 'error');

            $sum = $sum + 1;
        } else {
            //$query = "SELECT * FROM ( SELECT event,time_waited,wait_class FROM v\$system_event WHERE event NOT LIKE 'SQL*Net%' AND event NOT IN ('pmon timer','rdbms ipc message','dispatcher timer','smon timer') and wait_class != 'Idle' ORDER BY time_waited DESC) WHERE ROWNUM < 6";
            $query = "SELECT event,wait_class,COUNT (*) time_waited FROM v\$session_wait WHERE wait_class != 'Idle'  GROUP BY event,wait_class ORDER BY 3 DESC";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                //trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
                $records [] = array('event' => $e['message'],
                    'time_waited' => 1, 'wait_class' => 'error');

                $sum = $sum + 1;
            } else {
                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);
                    //trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);

                    $records [] = array('event' => $e['message'],
                        'time_waited' => 1, 'wait_class' => 'error');

                    $sum = $sum + 1;
                }

                while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                    $records [] = array('event' => $row['EVENT'],
                        'time_waited' => $row['TIME_WAITED'], 'wait_class' => $row['WAIT_CLASS']);

                    $sum = $sum + $row['TIME_WAITED'];
                }
            }
        }

        oci_free_statement($s);
        oci_close($c);

        $recs = array();
        $i = 0;
        foreach ($records as $rec) {
            $pct = 100 * $rec['time_waited'] / $sum;

            $recs [] = array('id' => $i, 'event' => $rec['event'], 'wait_class' => $rec['wait_class'],
                'pct_used' => $rec['event'] . '#' . $pct . '#' . $rec['wait_class']);

            $i++;
        }

        $response = array(EXT_JSON_READER_TOTAL => count($recs),
            EXT_JSON_READER_ROOT => $recs);

        echo $toC_Json->encode($response);
    }

    function listWaits()
    {
        global $toC_Json;

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

        echo $toC_Json->encode($response);
    }

    function snapEvents()
    {
        global $osC_Database;

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

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();

            $query = "INSERT INTO delta_oracle_events
            (date,
             databases_id,
             other,
             application,
             configuration,
             administrative,
             concurrency,
             commit,
             network,
             userio,
             systemio,
             scheduler,
             clustering,
             queueing,comments)
             VALUES
             (" . $start_date . "," . $databases_id . "," . 0 . "," . 0 . "," . 0 .
                "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . 0 .
                "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . $e['message'] . ")";

            $sum = $sum + 1;
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);

                $query = "INSERT INTO delta_oracle_events
            (date,
             databases_id,
             other,
             application,
             configuration,
             administrative,
             concurrency,
             commit,
             network,
             userio,
             systemio,
             scheduler,
             clustering,
             queueing;comments)
             VALUES
             (" . $start_date . "," . $databases_id . "," . 0 . "," . 0 . "," . 0 .
                    "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . 0 .
                    "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . $e['message'] . ")";

                $sum = $sum + 1;
            } else {
                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);

                    $query = "INSERT INTO delta_oracle_events
            (date,
             databases_id,
             other,
             application,
             configuration,
             administrative,
             concurrency,
             commit,
             network,
             userio,
             systemio,
             scheduler,
             clustering,
             queueing;comments)
             VALUES
             (" . $start_date . "," . $databases_id . "," . 0 . "," . 0 . "," . 0 .
                        "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . 0 .
                        "," . 0 . "," . 0 . "," . 0 . "," . 0 . "," . $e['message'] . ")";

                    $sum = $sum + 1;
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                        $query = "INSERT INTO delta_oracle_events
            (date,
             databases_id,
             other,
             application,
             configuration,
             administrative,
             concurrency,
             commit,
             network,
             userio,
             systemio,
             scheduler,
             clustering,
             queueing)
             VALUES
             ('" . $start_date . "'," . $databases_id . "," . $row['OTHER'] . "," . $row['APPLICATION'] . "," . $row['CONFIGURATION'] .
                            "," . $row['ADMINISTRATIVE'] . "," . $row['CONCURRENCY'] . "," . $row['COMMIT'] . "," . $row['NETWORK'] . "," . $row['USERIO'] .
                            "," . $row['SYSTEMIO'] . "," . $row['SCHEDULER'] . "," . $row['CLUSTERING'] . "," . $row['QUEUEING'] . ")";

                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        //var_dump($query);

        $Qserver = $osC_Database->query($query);

        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        $id = $osC_Database->nextID();

        $query = "delete from delta_oracle_events where id < " . ($id - 500);

        $Qserver = $osC_Database->query($query);
        $Qserver->execute();
        //echo $toC_Json->encode($response);
    }

    function listToptbs()
    {
        global $toC_Json;

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
                        $tip = toC_Servers_Admin::formatSizeUnits(($free * 1024 * 1024)) . " libre sur " . toC_Servers_Admin::formatSizeUnits(($max * 1024 * 1024));
                        $records [] = array('tbs' => strtolower($row['TABLESPACE_NAME']), 'status' => $row['STATUS'], 'contents' => $row['CONTENTS'], 'extent_management' => $row['EXTENT_MANAGEMENT'], 'bigfile' => $row['BIGFILE'], 'megs_alloc' => $row['MEGS_ALLOC'], 'megs_free' => $row['MEGS_FREE'], 'megs_used' => $row['MEGS_USED'], 'pct_used' => $total_percent_used, 'max' => $row['MAX'], 'free' => $row['FREE'], 'rest' => $total_percent_used . ';' . $row['MEGS_FREE'] . ';' . $row['MAX'], 'qtip' => $tip);
                    }

                    $response = array('success' => true, 'feedback' => $total . ' espaces logiques', EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function listOggconfig()
    {
        global $osC_Database, $toC_Json;

        $query = "SELECT
  g.id,
  g.datapump_name,
  g.dest_database,
  g.extract_name,
  g.oggdir_dest,
  g.oggdir_src,
  g.replicat_name,
  g.src_database,
  (SELECT
    delta_databases.USER
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.src_database) src_db_user,
  (SELECT
    delta_databases.pass
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.src_database) src_db_pass,
  (SELECT
    HOST
  FROM
    delta_servers
    WHERE servers_id =
    (SELECT
      servers_id
    FROM
      delta_databases
    WHERE delta_databases.databases_id = g.src_database)) src_db_host,
  (SELECT
    USER
  FROM
    delta_servers
  WHERE servers_id =
    (SELECT
      servers_id
    FROM
      delta_databases
    WHERE delta_databases.databases_id = g.src_database)) src_os_user,
 (SELECT
    pass
  FROM
    delta_servers
  WHERE servers_id =
    (SELECT
      servers_id
    FROM
      delta_databases
    WHERE delta_databases.databases_id = g.src_database)) src_os_pass,
  (SELECT
    delta_databases.label
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.src_database) src_label,
  (SELECT
    delta_databases.sid
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.src_database) src_db_sid,
  (SELECT
    delta_databases.label
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.dest_database) dest_label,
  (SELECT
    delta_databases.USER
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.dest_database) dest_db_user,
  (SELECT
    delta_databases.pass
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.dest_database) dest_db_pass,
  (SELECT
    HOST
  FROM
    delta_servers
    WHERE servers_id =
    (SELECT
      servers_id
    FROM
      delta_databases
    WHERE delta_databases.databases_id = g.dest_database)) dest_db_host,
  (SELECT
    delta_databases.sid
  FROM
    delta_databases
  WHERE delta_databases.databases_id = g.dest_database) dest_db_sid,
 (SELECT
    USER
  FROM
    delta_servers
  WHERE servers_id =
    (SELECT
      servers_id
    FROM
      delta_databases
    WHERE delta_databases.databases_id = g.dest_database)) dest_os_user,
 (SELECT
    pass
  FROM
    delta_servers
  WHERE servers_id =
    (SELECT
      servers_id
    FROM
      delta_databases
    WHERE delta_databases.databases_id = g.dest_database)) dest_os_pass
FROM
  delta_ogg_config g ";

        if (!empty($_REQUEST['where'])) {
            $query = $query . ' where ' . $_REQUEST['where'];
        }

        $Qdatabases = $osC_Database->query($query);
        $Qdatabases->execute();

        $records = array();
        while ($Qdatabases->next()) {
            //$src_home = toC_Databases_Admin::getOracleHome($Qdatabases->Value('src_db_user'), $Qdatabases->Value('src_db_pass'), $Qdatabases->Value('src_db_host'), $Qdatabases->Value('src_db_sid'));
            //$dest_home = toC_Databases_Admin::getOracleHome($Qdatabases->Value('dest_db_user'), $Qdatabases->Value('dest_db_pass'), $Qdatabases->Value('dest_db_host'), $Qdatabases->Value('dest_db_sid'));

            if (isset($_REQUEST['permissions'])) {
                $permissions = explode(',', $_REQUEST['permissions']);
                $records[] = array('id' => $Qdatabases->ValueInt('id'),
                    'datapump_name' => $Qdatabases->Value('datapump_name'),
                    'dest_database' => $Qdatabases->ValueInt('dest_database'),
                    'extract_name' => $Qdatabases->Value('extract_name'),
                    'oggdir_dest' => $Qdatabases->Value('oggdir_dest'),
                    'oggdir_src' => $Qdatabases->Value('oggdir_src'),
                    'replicat_name' => $Qdatabases->Value('replicat_name'),
                    'src_database' => $Qdatabases->ValueInt('src_database'),
                    'src_label' => $Qdatabases->Value('src_label'),
                    'dest_label' => $Qdatabases->Value('dest_label'),
                    'src_home' => '',
                    'src_db_user' => $Qdatabases->Value('src_db_user'),
                    'src_db_pass' => $Qdatabases->Value('src_db_pass'),
                    'src_db_host' => $Qdatabases->Value('src_db_host'),
                    'src_db_sid' => $Qdatabases->Value('src_db_sid'),
                    'dest_home' => '',
                    'dest_db_user' => $Qdatabases->Value('dest_db_user'),
                    'dest_db_pass' => $Qdatabases->Value('dest_db_pass'),
                    'dest_db_host' => $Qdatabases->Value('dest_db_host'),
                    'dest_db_sid' => $Qdatabases->Value('dest_db_sid'),
                    'src_os_user' => $Qdatabases->Value('src_os_user'),
                    'src_os_pass' => $Qdatabases->Value('src_os_pass'),
                    'dest_os_user' => $Qdatabases->Value('dest_os_user'),
                    'dest_os_pass' => $Qdatabases->Value('dest_os_pass'),
                    'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[1],
                    'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : $permissions[2],
                    'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[3]
                );
            } else {
                $records[] = array('id' => $Qdatabases->ValueInt('id'),
                    'datapump_name' => $Qdatabases->Value('datapump_name'),
                    'dest_database' => $Qdatabases->ValueInt('dest_database'),
                    'extract_name' => $Qdatabases->Value('extract_name'),
                    'oggdir_dest' => $Qdatabases->Value('oggdir_dest'),
                    'oggdir_src' => $Qdatabases->Value('oggdir_src'),
                    'replicat_name' => $Qdatabases->Value('replicat_name'),
                    'src_database' => $Qdatabases->ValueInt('src_database'),
                    'src_label' => $Qdatabases->Value('src_label'),
                    'dest_label' => $Qdatabases->Value('dest_label'),
                    'src_home' => '',
                    'src_db_user' => $Qdatabases->Value('src_db_user'),
                    'src_db_pass' => $Qdatabases->Value('src_db_pass'),
                    'src_db_host' => $Qdatabases->Value('src_db_host'),
                    'src_db_sid' => $Qdatabases->Value('src_db_sid'),
                    'dest_home' => '',
                    'dest_db_user' => $Qdatabases->Value('dest_db_user'),
                    'dest_db_pass' => $Qdatabases->Value('dest_db_pass'),
                    'dest_db_host' => $Qdatabases->Value('dest_db_host'),
                    'dest_db_sid' => $Qdatabases->Value('dest_db_sid'),
                    'src_os_user' => $Qdatabases->Value('src_os_user'),
                    'src_os_pass' => $Qdatabases->Value('src_os_pass'),
                    'dest_os_user' => $Qdatabases->Value('dest_os_user'),
                    'dest_os_pass' => $Qdatabases->Value('dest_os_pass'),
                    'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                    'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                );
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listPerf()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $query = "";

        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();
        //$sum = 0;
        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('namespace' => $row['NAMESPACE'],
                'reloads' => $row['RELOADS'], 'invalidations' => $row['INVALIDATIONS'], 'get' => $row['GET'], 'pin' => $row['PIN']);

            $count++;
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function lockTree()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $query = "WITH sessions AS
   (SELECT
           sid,serial# serial,osuser,machine, blocking_session,username, row_wait_obj#, sql_id
      FROM v\$session)
SELECT LEVEL,username,osuser,machine,sid,serial,object_name,
       sql_text
  FROM sessions s
  LEFT OUTER JOIN dba_objects
       ON (object_id = row_wait_obj#)
  LEFT OUTER JOIN v\$sql
       USING (sql_id)
 WHERE sid IN (SELECT blocking_session FROM sessions)
    OR blocking_session IS NOT NULL
 CONNECT BY PRIOR sid = blocking_session
 START WITH blocking_session IS NULL";

        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();
        //$sum = 0;
        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('sid' => $row['SID'],
                'serial' => $row['SERIAL'],
                'level' => $row['LEVEL'],
                'username' => $row['USERNAME'],
                'osuser' => $row['OSUSER'],
                'machine' => $row['MACHINE'],
                'sql_text' => $row['SQL_TEXT'],
                'object_name' => $row['OBJECT_NAME']
            );

            $count++;
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function lockObj()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $query = "SELECT s.SID,
  s.serial# serial,
  s.username,
  ROUND(CTIME/60) AS duree,
  do.object_name  AS locked_object,
  s.status,
  s.osuser,
  (select description from V\$LOCK_TYPE where type = l.type) description
FROM v\$lock l
JOIN v\$session s
ON l.sid=s.sid
JOIN v\$process p
ON p.addr = s.paddr
JOIN v\$locked_object lo
ON l.SID = lo.SESSION_ID
JOIN dba_objects DO
ON lo.OBJECT_ID = do.OBJECT_ID
where 1 = 1 ";

        if (!empty($_REQUEST['search'])) {
            $query = $query . " and (lower(s.username) like '%" . strtolower($_REQUEST['search']) . "%' or lower(do.object_name) like '%" . strtolower($_REQUEST['search']) . "%')";
        }

        $query = $query . " ORDER BY 5";

        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();
        //$sum = 0;
        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('sid' => $row['SID'],
                'serial' => $row['SERIAL'],
                'username' => $row['USERNAME'],
                'osuser' => $row['OSUSER'],
                'duree' => $row['DUREE'],
                'locked_object' => $row['LOCKED_OBJECT'],
                'status' => $row['STATUS'],
                'description' => $row['DESCRIPTION']
            );

            $count++;
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function killSession()
    {
        global $toC_Json;

        $sid = empty($_REQUEST['sid']) ? '' : $_REQUEST['sid'];
        $serial = empty($_REQUEST['serial']) ? '' : $_REQUEST['serial'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            //$query = "BEGIN FOR x IN (SELECT Sid,Serial# FROM v\$session WHERE sid = '" . $sid . "' and serial# = '" . $serial . "' LOOP EXECUTE IMMEDIATE 'Alter System Kill Session ''' || x.Sid || ','|| x.Serial# || ''' IMMEDIATE'; END LOOP; END;";

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $job_name = 'job_kill_session_' . $randomString;
            $proc_name = 'proc_kill_session_' . $randomString;

            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE " . $proc_name . " AUTHID CURRENT_USER AS BEGIN EXECUTE IMMEDIATE ''Alter System Kill Session ''''" . $sid . "," . $serial . "'''' IMMEDIATE'';END;'; end;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de tuer cette session ' . htmlentities($e['message']) . '\n' . $query);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']) . '\n' . $query);
                } else {
                    $response = array('success' => true, 'feedback' => "Procedure cree avec succes ...", 'proc_name' => $proc_name);
                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'BEGIN " . $proc_name . ";END;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer ce job : ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name, 'proc_name' => $proc_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function loadTbsTree()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $tbs = $_REQUEST['tbs'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $query = "SELECT ora_hash(segment_type, 999) category,segment_type TYPE, COUNT (*) NBRE FROM dba_segments WHERE tablespace_name = '" . $tbs . "' GROUP BY ora_hash(segment_type, 999),segment_type UNION SELECT ora_hash('DATAFILE', 999) category,'DATAFILE' TYPE, COUNT (*) FROM dba_data_files WHERE tablespace_name = '" . $tbs . "' ORDER BY 2";
        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();

        //$records [] = array('roles_id' => -1, 'id' => -1, 'text' => 'Tout le monde', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('roles_id' => $row['CATEGORY'], 'id' => $row['CATEGORY'], 'count' => $row['NBRE'], 'text' => $row['TYPE'] . ' ( ' . $row['NBRE'] . ' )', 'icon' => 'templates/default/images/icons/16x16/classes.png', 'leaf' => true);
        }

        oci_free_statement($s);
        oci_close($c);

        echo $toC_Json->encode($records);
    }

    function listTbs()
    {
        global $toC_Json;

        $query = "Select ts.tablespace_name, ts.status, ts.contents, ts.extent_management, ts.bigfile,
       size_info.megs_alloc, size_info.megs_free, size_info.megs_used,
       size_info.pct_free, size_info.pct_used, size_info.max,(size_info.MAX - size_info.megs_used) free
From
      (
      select  a.tablespace_name,
             round(a.bytes_alloc / 1024 / 1024) megs_alloc,
             round(nvl(b.bytes_free, 0) / 1024 / 1024) megs_free,
             round((a.bytes_alloc - nvl(b.bytes_free, 0)) / 1024 / 1024) megs_used,
             round((nvl(b.bytes_free, 0) / a.bytes_alloc) * 100) Pct_Free,
            100 - round((nvl(b.bytes_free, 0) / a.bytes_alloc) * 100) Pct_used,
             round(maxbytes/1048576) Max
      from  ( select  f.tablespace_name,
                     sum(f.bytes) bytes_alloc,
                     sum(decode(f.autoextensible, 'YES',f.maxbytes,'NO', f.bytes)) maxbytes
              from dba_data_files f
              group by tablespace_name) a,
            ( select  f.tablespace_name,
                     sum(f.bytes)  bytes_free
              from dba_free_space f
              group by tablespace_name) b
      where a.tablespace_name = b.tablespace_name (+)
      union all
      select h.tablespace_name,
             round(sum(h.bytes_free + h.bytes_used) / 1048576) megs_alloc,
             round(sum((h.bytes_free + h.bytes_used) - nvl(p.bytes_used, 0)) / 1048576) megs_free,
             round(sum(nvl(p.bytes_used, 0))/ 1048576) megs_used,
             round((sum((h.bytes_free + h.bytes_used) - nvl(p.bytes_used, 0)) / sum(h.bytes_used + h.bytes_free)) * 100) Pct_Free,
             100 - round((sum((h.bytes_free + h.bytes_used) - nvl(p.bytes_used, 0)) / sum(h.bytes_used + h.bytes_free)) * 100) pct_used,
             round(sum(decode(f.autoextensible, 'YES', f.maxbytes, 'NO', f.bytes) / 1048576)) max
      from   sys.v_\$TEMP_SPACE_HEADER h, sys.v_\$Temp_extent_pool p, dba_temp_files f
      where  p.file_id(+) = h.file_id
      and    p.tablespace_name(+) = h.tablespace_name
      and    f.file_id = h.file_id
      and    f.tablespace_name = h.tablespace_name
      group by h.tablespace_name
      ) size_info,
      sys.dba_tablespaces ts
where ts.tablespace_name = size_info.tablespace_name
order by tablespace_name";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;
                        $max = (int)($row['MAX']);
                        $used = (int)($row['MEGS_USED']);
                        $total_percent_used = (int)($used * 100 / $max);
                        $records [] = array('tablespace_name' => $row['TABLESPACE_NAME'], 'status' => $row['STATUS'], 'contents' => $row['CONTENTS'], 'extent_management' => $row['EXTENT_MANAGEMENT'], 'bigfile' => $row['BIGFILE'], 'megs_alloc' => $row['MEGS_ALLOC'], 'megs_free' => $row['MEGS_FREE'], 'megs_used' => $row['MEGS_USED'], 'total_pct_used' => $total_percent_used, 'max' => $row['MAX'], 'free' => $row['FREE']);
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array('success' => false, 'feedback' => $total . ' espaces logiques', EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listTbsusage()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT " .
            "  delta_databases.*," .
            "  delta_db_space_usage.snaps_id," .
            "  delta_servers.host, " .
            "  delta_servers.user as server_user, " .
            "  delta_servers.pass as server_pass, " .
            "  delta_servers.port as server_port, " .
            "  delta_servers.typ as server_typ, " .
            "  delta_db_space_usage.databases_id," .
            "  delta_db_space_usage.space_total_gb as size," .
            "  delta_db_space_usage.space_used_gb as used," .
            "  delta_db_space_usage.space_dispo_gb as dispo," .
            "  ROUND(space_used_gb/space_total_gb * 100) AS pct_used," .
            "  delta_db_space_usage.start_date," .
            "  delta_db_space_usage.end_date " .
            "FROM" .
            "  delta_db_space_usage " .
            "  INNER JOIN" .
            "  delta_databases " .
            "  ON (" .
            "    delta_db_space_usage.databases_id = delta_databases.databases_id" .
            "  ) INNER JOIN delta_servers ON (delta_servers.servers_id = delta_databases.servers_id) " .
            "WHERE delta_db_space_usage.snaps_id = " .
            "  (SELECT " .
            "    MAX(snaps_id) " .
            "  FROM" .
            "    delta_snaps where job_id = 'db_space_usage') order by delta_databases.label";

        $QServers = $osC_Database->query($query);

        $QServers->execute();

        $records = array();
        while ($QServers->next()) {
            $records[] = array('databases_id' => $QServers->ValueInt('databases_id'),
                'snaps_id' => $QServers->ValueInt('snaps_id'),
                'label' => $QServers->Value('label'),
                'sid' => $QServers->Value('sid'),
                'size' => $QServers->ValueInt('size'),
                'used' => $QServers->ValueInt('used'),
                'dispo' => $QServers->ValueInt('dispo'),
                'pct_used' => $QServers->ValueInt('pct_used'),
                'start_date' => $QServers->Value('start_date'),
                'end_date' => $QServers->Value('end_date'),
                'host' => $QServers->Value('host'),
                'server_user' => $QServers->Value('server_user'),
                'server_pass' => $QServers->Value('server_pass'),
                'server_port' => $QServers->Value('server_port'),
                'server_typ' => $QServers->Value('server_typ'),
                'port' => $QServers->ValueInt('port'),
                'user' => $QServers->Value('user'),
                'pass' => $QServers->Value('pass')
            );
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listDatafiles()
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
            $query = "SELECT round(d.maxbytes/1024/1024) maxsize, d.maxblocks, d.status,d.blocks, d.autoextensible,nvl(d.increment_by, 0) increment_by,d.file_id,d.file_name,d.tablespace_name,round(MAX (d.bytes/1024/1024)) taille,NVL (round(SUM (f.Bytes/1024/1024)), 0) free_mb FROM DBA_FREE_SPACE f, DBA_DATA_FILES d WHERE f.tablespace_name(+) = d.tablespace_name AND f.file_id(+) = d.file_id ";
            if (!empty($_REQUEST['tbs'])) {
                $tbs = strtolower($_REQUEST['tbs']);
                $query = $query . " and lower(d.tablespace_name) = '" . $tbs . "'";
            }

            $query = $query . " GROUP BY d.file_id,d.file_name,d.tablespace_name,d.blocks, d.autoextensible,nvl(d.increment_by, 0),round(d.maxbytes/1024/1024), d.maxblocks, d.status";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                oci_bind_by_name($s, ":tablespace_name", $tbs);

                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les espaces logiques de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;
                        $max = (int)($row['MAXSIZE']);
                        $free = (int)($row['FREE_MB']);
                        $size = (int)($row['TAILLE']);
                        $used = $size - $free;
                        $total_percent_used = (int)($used * 100 / $max);
                        $percent_used = (int)($used * 100 / $size);
                        $records [] = array('free_mb' => $row['FREE_MB'], 'file_id' => $row['FILE_ID'], 'tablespace_name' => $row['TABLESPACE_NAME'], 'file_name' => $row['FILE_NAME'], 'status' => $row['STATUS'], 'autoextensible' => $row['AUTOEXTENSIBLE'], 'size' => $size, 'blocks' => $row['BLOCKS'], 'maxsize' => $max, 'increment_by' => $row['INCREMENT_BY'], 'maxblocks' => $row['MAXBLOCKS'], 'pct_used' => $percent_used, 'total_pct_used' => $total_percent_used);
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array('success' => false, 'feedback' => $total . ' datafiles', EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listParameters()
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
            $records [] = array('num' => 0,
                'name' => 'error',
                'display_value' => '',
                'description' => $e['message']);
        } else {
            switch($scope)
            {
                case "instance":
                $query = "select num,name,display_value,description from v\$parameter where 1 = 1 ";
                    break;

                case "px":
                    $query = "select num,name,display_value,description from v\$parameter where lower(name) like 'parallel%' or lower(name) in('shared_pool_size','memory_target','memory_max_target','pga_aggregate_target','sga_target','transactions','fast_start_parallel_rollback','cpu_count','dml_locks') ";
                    break;

                case "shared_pool":
                    $query = "SELECT n.indx num,
         n.ksppinm name,
         n.ksppdesc description,
         v.KSPPSTVL display_value
    FROM x\$ksppi n, x\$ksppsv v
   WHERE     n.indx = v.indx
         AND (   n.ksppinm LIKE '%shared_pool%'
              OR n.ksppinm IN ('_kghdsidx_count',
                               '_ksmg_granule_size',
                               '_memory_imm_mode_without_autosga'))";
                    break;

                default:
                    $query = "select num,name,display_value,description from v\$parameter where 1 = 1 ";
                    break;
            }

            if (!empty($_REQUEST['search'])) {
                $search = strtolower($_REQUEST['search']);
                $query = $query . " and (lower(name) like '%" . $search . "%' or lower(display_value) like '%" . $search . "%' or lower(description) like '%" . $search . "%')";
            }

            $query = $query . " order by 2";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('num' => 0,
                    'name' => 'error',
                    'display_value' => '',
                    'description' => $e['message']);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('num' => 0,
                        'name' => 'error',
                        'display_value' => '',
                        'description' => $e['message']);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('num' => $row['NUM'], 'name' => $row['NAME'], 'display_value' => $row['DISPLAY_VALUE'], 'description' => $row['DESCRIPTION']);
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

    function compBicec()
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
            var_dump($e);
        } else {
            $query = "SELECT upper(c.OBJECT_NAME) OBJECT_NAME,
       S.ROOT_SCAN_ID,
       s.current_dif_count difference,
       s.count_rows,
       s.last_update_time
  FROM DBA_COMPARISON c, DBA_COMPARISON_SCAN s
 WHERE     c.COMPARISON_NAME = s.COMPARISON_NAME
       AND c.OWNER = s.OWNER
       AND c.object_name = c.remote_object_name
       AND s.parent_scan_id IS NULL
UNION
SELECT upper(REMOTE_TABLE) OBJECT_NAME,
       0 ROOT_SCAN_ID,
       (SOURCE_NUM_ROWS - REMOTE_NUM_ROWS) difference,
       SOURCE_NUM_ROWS count_rows,
       LAST_UPDATE last_update_time
  FROM GM_FOMI.VOL_COMPARISON
ORDER BY 1";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                var_dump($e);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les Top programmes : ' . htmlentities($e['message']));
                    var_dump($e);
                } else {
                    $output = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%'><tbody><tr><td style='text-align: center; background-color: rgb(0, 0, 51);'>";

                    $output = $output . "<strong><span style='font-size:16px;'><span style='color:#fff0f5;'>" . $_REQUEST['subject'] . "</span></span></strong></td></tr><tr><td>";

                    $output = $output . "<table border='0' cellpadding='1' cellspacing='1' style='width: 100%'><tbody><tr><td style='text-align: center; background-color: rgb(102, 102, 102);'>";

                    $output = $output . "<strong><span style='color:#fff0f5;'>Table</span></strong></td><td style='text-align: center; background-color: rgb(102, 102, 102);'><strong>";

                    $output = $output . "<span style='color:#fff0f5;'>Nombre Lignes</span></strong></td><td style='text-align: center; background-color: rgb(102, 102, 102);'><strong>";

                    $output = $output . "<span style='color:#fff0f5;'>Difference</span></strong></td><td style='text-align: center; background-color: rgb(102, 102, 102);'><strong>";

                    $output = $output . "<span style='color:#fff0f5;'>Date</span></strong></td></tr>";

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        if ($row['DIFFERENCE'] != 0) {
                            $output = $output . "<tr><td style='text-align: center; background-color: red;'><strong><span style='color:#fff0f5;'>" . $row['OBJECT_NAME'] . "</span></strong></td><td style='text-align: center; background-color: red;'><strong><span style='color:#fff0f5;'>" . $row['COUNT_ROWS'] . "</span></strong></td><td style='text-align: center; background-color: red;'><strong><span style='color:#fff0f5;'>" . $row['DIFFERENCE'] . "</span></strong></td><td style='text-align: center; background-color: red;'><strong><span style='color:#fff0f5;'>" . $row['LAST_UPDATE_TIME'] . "</span></strong></td></tr>";
                        } else {
                            $output = $output . "<tr><td style='text-align: center; background-color: rgb(0, 51, 0);'><strong><span style='color:#fff0f5;'>" . $row['OBJECT_NAME'] . "</span></strong></td><td style='text-align: center; background-color: rgb(0, 51, 0);'><strong><span style='color:#fff0f5;'>" . $row['COUNT_ROWS'] . "</span></strong></td><td style='text-align: center; background-color: rgb(0, 51, 0);'><strong><span style='color:#fff0f5;'>" . $row['DIFFERENCE'] . "</span></strong></td><td style='text-align: center; background-color: rgb(0, 51, 0);'><strong><span style='color:#fff0f5;'>" . $row['LAST_UPDATE_TIME'] . "</span></strong></td></tr>";
                        }
                    }

                    $output = $output . "</tbody></table></td></tr></tbody></table></body></html>";

                    $data = $_REQUEST;
                    $data['body'] = $output;

                    $response = toC_Reports_Admin::sendEmail($data);
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function ggsException()
    {
        global $toC_Json;

        $output = "";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $label = $_REQUEST['label'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            var_dump($e);
        } else {
            $query = "select rep_name,table_name,errno,optype,count(*) occurences from gm_fomi.EXCEPTIONS group by rep_name,table_name,errno,optype order by 1,2";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                var_dump($e);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les exceptions : ' . htmlentities($e['message']));
                } else {
                    $output = $output . "<table border='0' cellpadding='1' cellspacing='1' style='width: 100%'><tbody><tr><td style='text-align: center; background-color: rgb(0, 0, 102);'><strong><span style='color:#fff0f5;'>" . $label . "</span></strong></td></tr>";

                    $output = $output . "<tr><td><table border='0' cellpadding='1' cellspacing='1' style='width: 100%'><tbody><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'><strong>REP_NAME</strong></td><td style='text-align: center; background-color: rgb(204, 204, 204);'><strong>TABLE_NAME</strong></td><td style='text-align: center; background-color: rgb(204, 204, 204);'><strong>ERRNO</strong></td><td style='text-align: center; background-color: rgb(204, 204, 204);'><strong>OCCURENCES</strong></td><td style='text-align: center; background-color: rgb(204, 204, 204);'><strong>OPTYPE</strong></td><td style='text-align: center; background-color: rgb(204, 204, 204);'><strong>WHEN</strong></td></tr>";

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $output = $output . "<tr><td style='text-align: center;'>" . $row['REP_NAME'] . "</td><td style='text-align: center;'>" . $row['TABLE_NAME'] . "</td><td style='text-align: center;'>" . $row['ERRNO'] . "</td><td>" . $row['OCCURENCES'] . "</td><td>" . $row['OPTYPE'] . "</td></tr>";
                    }

                    $output = $output . "</tbody></table></td></tr></tbody></table><p></p>";

                    /*$query = "truncate table gm_fomi.GGS_EXCEPTIONS";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => "Impossible de vider la table des exception " . htmlentities($e['message']));
                        var_dump($e);
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de vider la table des exception : ' . htmlentities($e['message']));
                        }
                    }*/
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $data = $_REQUEST;
        $data['body'] = $output;

        $response = toC_Reports_Admin::sendEmail($data);

        echo $toC_Json->encode($response);
    }

    function topProgrammes()
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
            $query = "SELECT a.*,
           round((  TO_DATE (datf || ' ' || SUBSTR (heurf, 1, 8),
                       'DD/MM/RR HH24:MI:SS')
            - TO_DATE (datd || ' ' || SUBSTR (heurd, 1, 8),
                       'DD/MM/RR HH24:MI:SS'))
         * 24
         * 60)
            duree1
    FROM BANK.FJO_STAT1 a
   WHERE     dco = (SELECT MAX (dco) FROM BANK.FJO_STAT1)
         AND   (  TO_DATE (datf || ' ' || SUBSTR (heurf, 1, 8),
                           'DD/MM/RR HH24:MI:SS')
                - TO_DATE (datd || ' ' || SUBSTR (heurd, 1, 8),
                           'DD/MM/RR HH24:MI:SS'))
             * 24
             * 60 >= 1
ORDER BY duree1 DESC";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les Top programmes : ' . htmlentities($e['message']));
                } else {
                    $output = "<table align='left' border='0' cellpadding='0' cellspacing='0' style='height:0px;width:100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; height: 50px; background-color: rgb(0, 0, 51);'><span style='font-size:24px;'><span style='color:#FFF0F5;'>Top Programmes TFJO</span></span></td></tr><tr><td></td></tr>";

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $output = $output . "<tr><td style='vertical-align: middle; text-align: left;'><table cellpadding='1' cellspacing='1'><tbody><tr><td style='width: 115px; text-align: left; vertical-align: middle;'>" . $row['PROG'] . "</td><td style='width: 50px; text-align: center; vertical-align: middle;'>" . $row['DUREE1'] . " Min</td><td style='width: " . $row['DUREE1'] * 3 . "px; background-color: rgb(0, 0, 102);'></td></tr></tbody></table></td></tr>";
                    }

                    $output = $output . "</tbody></table>";

                    $data = $_REQUEST;
                    $data['body'] = $output;

                    $response = toC_Reports_Admin::sendEmail($data);
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function statsTfjos()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $norm = $_REQUEST['norme'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "SELECT DCO,
         T_TOT,
         T_HI,
         NMVT
    FROM BANK.FJO_STAT2
   WHERE dco >= SYSDATE - 60
ORDER BY 1 DESC NULLS LAST";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les durees des TFJOs : ' . htmlentities($e['message']));
                } else {
                    $output = "<table border='0' cellpadding='1' cellspacing='0' style='width:100%;'><tbody><tr><td style='text-align: center; background-color: rgb(0, 0, 51);'><span style='color:#FFF0F5;'><span style='font-size:20px;'>Statistiques TFJO</span></span></td></tr><tr><td><table border='0' cellpadding='1' cellspacing='0' style='width:100%;'><tbody><tr><td style='text-align: center; width: 15%; background-color: rgb(153, 153, 153);'><span style='color:#000080;'><strong>DCO</strong></span></td><td style='text-align: center; width: 15%; background-color: rgb(153, 153, 153);'><span style='color:#000080;'><strong>Mvts</strong></span></td><td style='text-align: center; width: 10%; background-color: rgb(153, 153, 153);'><span style='color:#000080;'><strong>Duree</strong></span></td><td style='background-color: rgb(153, 153, 153);'>&nbsp;</td></tr>";

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {

                        $tokens = explode(":", $row['T_HI']);

                        $minutes = (int)($tokens[1]);
                        $hoursToM = (int)($tokens[0]) * 60;
                        $total = $minutes + $hoursToM;
                        $total = $total / 2;

                        $norme = $total <= $norm ? $total : $norm;
                        $extra = $total > $norm ? $total - $norm : 0;
                        $extra_norme = $norme <= $norm ? $norm - $norme : 0;

                        //$output = $output . "<tr><td style='text-align: center; vertical-align: middle; width: 100%;'><table border='0' cellpadding='1' cellspacing='1' style='width:100%;'><tbody><tr><td style='text-align: left; width: 30%;'>" . $row['DCO'] . "</td><td style='text-align: left; width: 70%;'>" . substr($row['T_HI'],0,5) . "</td></tr><tr><td style='text-align: left; width: 30%;'>" . number_format($row['NMVT'],0," "," ") . " Mvts</td><td style='text-align: left; width: 70%;'>";

                        $output = $output . "<tr><td style='text-align: center;font-size:10px;'>" . substr($row['DCO'], 0, 6) . "</td><td style='text-align: center;font-size:10px;'>" . number_format($row['NMVT'], 0, " ", " ") . "</td><td style='text-align: center;font-size:10px;'>" . substr($row['T_HI'], 0, 5) . "</td><td style='text-align: left; width: 60%;'>";

                        $output = $output . "<table border='0' cellpadding='0' cellspacing='0'><tbody><tr><td style='width: " . $norme . "px; background-color: green;'>&nbsp;</td>";

                        if ($extra_norme > 0) {
                            $output = $output . "<td style='width: " . $extra_norme . "px;background-color: rgb(204, 204, 204);'>&nbsp;</td>";
                        }

                        if ($extra > 0) {
                            $output = $output . "<td style='width: " . $extra . "px;background-color: red;'>&nbsp;</td>";
                        }

                        $output = $output . "</tr></tbody></table></td>";
                    }

                    $output = $output . "</tbody></table></td></tr></tbody></table>";

                    oci_free_statement($s);
                    oci_close($c);

                    $data = $_REQUEST;
                    $data['body'] = $output;

                    $response = toC_Reports_Admin::sendEmail($data);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listDatabasesTree()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $QServers = $osC_Database->query('select a.*, cd.*,c.*, atoc.*,s.label,s.host,s.servers_id,s.user as server_user,s.pass as server_pass,s.port as server_port  from :table_databases a left join :table_content c on a.databases_id = c.content_id left join  :table_content_description cd on a.databases_id = cd.content_id left join  :table_servers s on a.servers_id = s.servers_id left join :table_content_to_categories atoc on atoc.content_id = a.databases_id  where cd.language_id = :language_id and atoc.content_type = "databases" and c.content_type = "databases" and cd.content_type = "databases"');

        if ($current_category_id != 0) {
            $QServers->appendQuery('and atoc.categories_id = :categories_id ');
            $QServers->bindInt(':categories_id', $current_category_id);
        }

        if (!empty($_REQUEST['search'])) {
            $QServers->appendQuery('and cd.content_name like :content_name');
            $QServers->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        $QServers->appendQuery('order by cd.content_name ');
        $QServers->bindTable(':table_databases', TABLE_DATABASES);
        $QServers->bindTable(':table_servers', TABLE_SERVERS);
        $QServers->bindTable(':table_content', TABLE_CONTENT);
        $QServers->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
        $QServers->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
        $QServers->bindInt(':language_id', $osC_Language->getID());
        $QServers->execute();

        $records = array();
        while ($QServers->next()) {
            if (isset($_REQUEST['permissions'])) {
                $permissions = explode(',', $_REQUEST['permissions']);
                $records[] = array('id' => $QServers->ValueInt('databases_id'),
                    'content_status' => $QServers->ValueInt('content_status'),
                    'content_order' => $QServers->Value('content_order'),
                    'text' => $QServers->Value('content_name'),
                    'host' => $QServers->Value('host'),
                    'leaf' => true,
                    'roles_id' => '',
                    'cls' => '',
                    'icon' => 'templates/default/images/icons/16x16/oracle.jpg',
                    'server_user' => $QServers->Value('server_user'),
                    'servers_id' => $QServers->Value('servers_id'),
                    'server_pass' => $QServers->Value('server_pass'),
                    'server_port' => $QServers->Value('server_port'),
                    'label' => $QServers->Value('label'),
                    'sid' => $QServers->Value('sid'),
                    'port' => $QServers->Value('port'),
                    'db_user' => $QServers->Value('user'),
                    'db_pass' => $QServers->Value('pass'),
                    'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[1],
                    'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : $permissions[2],
                    'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[3]
                );
            } else {
                $records[] = array('id' => $QServers->ValueInt('databases_id'),
                    'content_status' => $QServers->ValueInt('content_status'),
                    'content_order' => $QServers->Value('content_order'),
                    'text' => $QServers->Value('content_name'),
                    'host' => $QServers->Value('host'),
                    'leaf' => true,
                    'roles_id' => '',
                    'cls' => '',
                    'icon' => 'templates/default/images/icons/16x16/oracle.jpg',
                    'server_user' => $QServers->Value('server_user'),
                    'servers_id' => $QServers->Value('servers_id'),
                    'server_pass' => $QServers->Value('server_pass'),
                    'server_port' => $QServers->Value('server_port'),
                    'label' => $QServers->Value('label'),
                    'sid' => $QServers->Value('sid'),
                    'port' => $QServers->Value('port'),
                    'db_user' => $QServers->Value('user'),
                    'db_pass' => $QServers->Value('pass'),
                    'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                    'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                    'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                );
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listUsers()
    {
        global $toC_Json;
        $search = $_REQUEST['search'];
        $exclude = $_REQUEST['exclude'];

        $query = "SELECT USERNAME,ACCOUNT_STATUS,EXPIRY_DATE,CREATED,AUTHENTICATION_TYPE FROM SYS.DBA_USERS where username not in ('SYS','FLOWS_30000','MDDATA','FLOWS_FILES','WK_TEST','WKPROXY','SPATIAL_CSW_ADMIN_USR','SPATIAL_WFS_ADMIN_USR ','XS\$NULL','WMSYS','WKSYS','SI_INFORMTN_SCHEMA','SYSTEM','APPQOSSYS','DBSNMP','DIP','ANONYMOUS','ORACLE_OCM','OUTLN','AWR_STAGE','CSMIG','CTXSYS','OWBSYS','ORDDATA','OLAPSYS','DMSYS','APEX_PUBLIC_USER','DSSYS','EXFSYS','LBACSYS','MDSYS','ORDPLUGINS','ORDSYS','PERFSTAT','TRACESVR','TSMSYS','XDB','BANK','MGMT_VIEW','SYSMAN') ";

        if (!empty($exclude)) {
            $query = $query . " and username not in " . $exclude;
        }

        if (!empty($search)) {
            $query = $query . " and lower(USERNAME) like '%" . strtolower($search) . "%'";
        }

        $query = $query . " order by USERNAME ";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));

        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));

            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les utilisateurs de cette base ' . htmlentities($e['message']));

                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;

                        $file = "xxxxxxxxxxx.user_locked";

                        if (trim(strtolower($row['ACCOUNT_STATUS'])) == "open") {
                            $file = "xxxxxxxxxx.user_open";
                        }

                        $entry_icon = osc_icon_from_filename($file, $row['ACCOUNT_STATUS']);

                        $records[] = array(
                            'icon' => $entry_icon,
                            'status' => $row['ACCOUNT_STATUS'],
                            'username' => $row['USERNAME'],
                            'expiration' => $row['EXPIRY_DATE'],
                            'creation' => $row['CREATED'],
                            'authentication_type' => $row['AUTHENTICATION_TYPE'] == 'EXTERNAL' ? 1 : 0
                        );
                    }

                    oci_free_statement($s);
                    oci_close($c);

                    $response = array(EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listAllusers()
    {
        global $toC_Json;
        $search = $_REQUEST['search'];

        $query = "SELECT USERNAME FROM SYS.DBA_USERS where 1 = 1 ";

        if (!empty($search)) {
            $query = $query . " and lower(USERNAME) like '%" . strtolower($search) . "%'";
        }

        $query = $query . " order by USERNAME ";

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les utilisateurs de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();

                    $records[] = array(
                        'username' => 'all',
                        'label' => 'Tous les Schemas'
                    );

                    $total = 1;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total++;

                        $records[] = array(
                            'username' => $row['USERNAME'],
                            'label' => $row['USERNAME']
                        );
                    }

                    $response = array(EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function listRmanconfig()
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
            $query = "SELECT name,value FROM V\$RMAN_CONFIGURATION order by 1";
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de charger la configuration RMAN de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records[] = array(
                            'name' => $row['NAME'],
                            'value' => $row['VALUE'],
                            'index' => $total
                        );

                        $total++;
                    }

                    $response = array(EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function listBackup()
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
            $query = "SELECT SESSION_KEY key,
         INPUT_TYPE type,
         STATUS,
         TO_CHAR (START_TIME, 'mm/dd/yy hh24:mi') start_time,
         TO_CHAR (END_TIME, 'mm/dd/yy hh24:mi') end_time,
         time_taken_display duree,
         OUTPUT_BYTES_DISPLAY taille,
         round(compression_ratio) ratio
    FROM V\$RMAN_BACKUP_JOB_DETAILS
ORDER BY SESSION_KEY desc";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de lister les sauvegardes RMAN de cette base ' . htmlentities($e['message']));
                } else {
                    $records = array();
                    $total = 0;

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records[] = array(
                            'key' => $row['KEY'],
                            'type' => $row['TYPE'],
                            'status' => $row['STATUS'],
                            'start_time' => $row['START_TIME'],
                            'end_time' => $row['END_TIME'],
                            'duree' => $row['DUREE'],
                            'taille' => $row['TAILLE'],
                            'ratio' => $row['RATIO']
                        );

                        $total++;
                    }

                    $response = array(EXT_JSON_READER_TOTAL => $total,
                        EXT_JSON_READER_ROOT => $records);
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function resizeDatafile()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $file_ids = explode(',', $_REQUEST['file_id']);

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database : ' . htmlentities($e['message']));
        } else {
            $query = "begin execute immediate 'CREATE OR REPLACE PROCEDURE resize_datafile (fileid NUMBER) IS taille NUMBER;block_size   INTEGER;BEGIN execute immediate ''purge recyclebin'';SELECT VALUE INTO block_size FROM V\$PARAMETER WHERE NAME = ''db_block_size'';SELECT CEIL ( (highblock * block_size + block_size) / 1024) INTO taille FROM (  SELECT file_id, MAX (block_id + blocks) highblock FROM dba_extents WHERE file_id = fileid GROUP BY file_id);EXECUTE IMMEDIATE ''alter database datafile '' || fileid || '' resize '' || taille || ''K'';END;'; end;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible d executer la requete de creation de la procedure de redimensionnement ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de creer la procedure de redimensionnement ' . htmlentities($e['message']));
                } else {
                    $characters = '0123456789';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < 5; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }

                    $job_name = 'resize_df_' . $randomString;
                    $action = "";

                    foreach ($file_ids as &$file_id) {
                        if (!empty($file_id)) {
                            $action = $action . "resize_datafile (" . $file_id . ");";
                        }
                    }

                    $query = "BEGIN DBMS_SCHEDULER.create_job (job_name => '" . $job_name . "',job_type => 'PLSQL_BLOCK',job_action => 'begin " . $action . " end;',start_date => SYSTIMESTAMP,enabled => TRUE,auto_drop => TRUE);END;";

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer le job de redimensionnement ' . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => 'Impossible de creer le job de redimensionnement ' . htmlentities($e['message']));
                        } else {
                            $response = array('success' => true, 'feedback' => "Job cree avec succes ...", 'job_name' => $job_name);
                        }
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function setDatafilestatus()
    {
        global $toC_Json;

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $file_id = $_REQUEST['file_id'];
        $flag = $_REQUEST['flag'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "ALTER DATABASE DATAFILE " . $file_id . " AUTOEXTEND " . $flag;
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de changer le status de ce fichier de donnees ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => 'OK');
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function listIndexes()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $schema = strtolower($_REQUEST['schema']);
        $tbs = strtolower($_REQUEST['tbs']);
        $records = array();
        $total = 0;

        $query = "SELECT *
  FROM (SELECT a.*, ROWNUM rnum
          FROM (SELECT DECODE (PARTITION_NAME,
                               NULL, SEGMENT_NAME,
                               SEGMENT_NAME || ':' || PARTITION_NAME)
                          SEGMENT_NAME,
                       DBA_SEGMENTS.OWNER,
                       DBA_SEGMENTS.TABLESPACE_NAME,
                       DBA_SEGMENTS.INITIAL_EXTENT,
                       DBA_SEGMENTS.NEXT_EXTENT,
                       EXTENTS,
                       round(BYTES) TAILLE,
                       DBA_SEGMENTS.PCT_INCREASE,
                       TABLE_NAME,
                       UNIQUENESS,
                       CLUSTERING_FACTOR,
                       (SELECT blocks
                          FROM dba_tables d
                         WHERE     d.table_name = DBA_INDEXES.TABLE_NAME
                               AND d.owner = DBA_SEGMENTS.OWNER)
                          table_blocks,
                       (SELECT num_rows
                          FROM dba_tables t
                         WHERE     t.table_name = DBA_INDEXES.TABLE_NAME
                               AND t.owner = DBA_SEGMENTS.OWNER)
                          table_rows,
                       COMPRESSION,
                       BLEVEL,
                       LEAF_BLOCKS,
                       DISTINCT_KEYS,
                       STATUS,
                       LAST_ANALYZED,
                       LOGGING
                  FROM DBA_SEGMENTS
                       INNER JOIN SYS.DBA_INDEXES
                          ON (SEGMENT_NAME = INDEX_NAME)
                 WHERE SEGMENT_TYPE = 'INDEX' ";

        if (!empty($schema) && isset($schema) && $schema != 'all') {
            $query = $query . " AND lower(DBA_SEGMENTS.owner) = '" . $schema . "'";
        }

        if (!empty($tbs) && isset($tbs) && $tbs != 'all') {
            $query = $query . " AND lower(DBA_SEGMENTS.tablespace_name) = '" . $tbs . "'";
        }

        if (!empty($search)) {
            $query = $query . " and lower(DBA_SEGMENTS.segment_name) like :seg_name ";
        }

        $query = $query . " ) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH ";

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('pct_increase' => '',
                'segment_name' => $e['message'],
                'owner' => '',
                'tablespace_name' => '',
                'initial_extent' => '',
                'next_extent' => '',
                'extents' => '',
                'size' => 0,
                'uniqueness' => '',
                'clustering_factor' => '',
                'table_blocks' => '',
                'table_rows' => '',
                'compression' => '',
                'blevel' => '',
                'leaf_blocks' => '',
                'distinct_keys' => '',
                'status' => '',
                'last_analyzed' => '',
                'logging' => '',
                'table_name' => '');
        } else {
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('pct_increase' => '',
                    'segment_name' => $e['message'],
                    'owner' => '',
                    'tablespace_name' => '',
                    'initial_extent' => '',
                    'next_extent' => '',
                    'extents' => '',
                    'size' => 0,
                    'uniqueness' => '',
                    'clustering_factor' => '',
                    'table_blocks' => '',
                    'table_rows' => '',
                    'compression' => '',
                    'blevel' => '',
                    'leaf_blocks' => '',
                    'distinct_keys' => '',
                    'status' => '',
                    'last_analyzed' => '',
                    'logging' => '',
                    'table_name' => '');
            } else {
                $search = '%' . strtolower($search) . '%';
                oci_bind_by_name($s, ":seg_name", $search);
                $fin = $start + $limit;
                oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('pct_increase' => '',
                        'segment_name' => $e['message'],
                        'owner' => '',
                        'tablespace_name' => '',
                        'initial_extent' => '',
                        'next_extent' => '',
                        'extents' => '',
                        'size' => 0,
                        'uniqueness' => '',
                        'clustering_factor' => '',
                        'table_blocks' => '',
                        'table_rows' => '',
                        'compression' => '',
                        'blevel' => '',
                        'leaf_blocks' => '',
                        'distinct_keys' => '',
                        'status' => '',
                        'last_analyzed' => '',
                        'logging' => '',
                        'table_name' => '');
                } else {

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $size = toC_Servers_Admin::formatSizeUnits($row['TAILLE']);
                        $records [] = array('pct_increase' => $row['PCT_INCREASE'],
                            'segment_name' => $row['SEGMENT_NAME'],
                            'owner' => $row['OWNER'],
                            'tablespace_name' => $row['TABLESPACE_NAME'],
                            'initial_extent' => $row['INITIAL_EXTENT'],
                            'next_extent' => $row['NEXT_EXTENT'],
                            'extents' => $row['EXTENTS'],
                            'size' => $size,
                            'uniqueness' => $row['UNIQUENESS'],
                            'clustering_factor' => $row['CLUSTERING_FACTOR'],
                            'table_blocks' => $row['TABLE_BLOCKS'],
                            'table_rows' => $row['TABLE_ROWS'],
                            'compression' => $row['COMPRESSION'],
                            'blevel' => $row['BLEVEL'],
                            'leaf_blocks' => $row['LEAF_BLOCKS'],
                            'distinct_keys' => $row['DISTINCT_KEYS'],
                            'status' => $row['STATUS'],
                            'last_analyzed' => $row['LAST_ANALYZED'],
                            'logging' => $row['LOGGING'],
                            'table_name' => $row['TABLE_NAME']);
                    }
                }
            }

            oci_free_statement($s);

            $query = "SELECT count(*) nbre FROM DBA_SEGMENTS INNER JOIN SYS.DBA_INDEXES ON (SEGMENT_NAME = INDEX_NAME) WHERE SEGMENT_TYPE = 'INDEX' ";

            if (!empty($schema) && isset($schema) && $schema != 'all') {
                $query = $query . " AND lower(DBA_SEGMENTS.owner) = '" . $schema . "'";
            }

            if (!empty($tbs) && isset($tbs) && $tbs != 'all') {
                $query = $query . " AND lower(DBA_SEGMENTS.tablespace_name) = '" . $tbs . "'";
            }

            if (!empty($search)) {
                $query = $query . " and lower(DBA_SEGMENTS.segment_name) like :seg_name ";
            }

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $total = null;
            } else {
                $search = '%' . strtolower($search) . '%';
                oci_bind_by_name($s, ":seg_name", $search);

                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);
                    $total = null;
                } else {

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total = $row['NBRE'];
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listTablesOld()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search)) {
            $start = 0;
            $limit = 10000;
            $total = 0;

            if (!empty($_REQUEST['tbs'])) {
                $tbs = strtolower($_REQUEST['tbs']);

                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (  SELECT DECODE (partition_name,NULL, segment_name,segment_name || ':' || partition_name) segment_name,owner,tablespace_name,segment_type," .
                    "initial_extent,next_extent,extents,bytes / 1024 / 1024 taille,PCT_INCREASE FROM dba_segments where segment_type = 'TABLE' and lower(segment_name) like :seg_name and lower(tablespace_name) = '" . $tbs . "' ORDER BY bytes desc) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            } else {
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (  SELECT DECODE (partition_name,NULL, segment_name,segment_name || ':' || partition_name) segment_name,owner,tablespace_name,segment_type," .
                    "initial_extent,next_extent,extents,bytes / 1024 / 1024 taille,PCT_INCREASE FROM dba_segments where segment_type = 'TABLE' and lower(segment_name) like :seg_name ORDER BY bytes desc) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            }
        } else {
            if (!empty($_REQUEST['tbs'])) {
                $tbs = strtolower($_REQUEST['tbs']);
                $query = "select count(*) nbre from dba_segments where segment_type = 'TABLE' and lower(tablespace_name) = '" . $tbs . "'";
            } else {
                $query = "select count(*) nbre from dba_segments where segment_type = 'TABLE'";
            }

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
            }

            $r = oci_execute($s);
            if (!$r) {
                $e = oci_error($s);
                trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
            }

            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                $total = (int)($row['NBRE']);
            }

            if (!empty($_REQUEST['tbs'])) {
                $tbs = strtolower($_REQUEST['tbs']);

                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (  SELECT DECODE (partition_name,NULL, segment_name,segment_name || ':' || partition_name) segment_name,owner,tablespace_name,segment_type," .
                    "initial_extent,next_extent,extents,bytes / 1024 / 1024 taille,max_extents,PCT_INCREASE FROM dba_segments where segment_type = 'TABLE' and lower(tablespace_name) = '" . $tbs . "' ORDER BY bytes desc) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            } else {
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (  SELECT DECODE (partition_name,NULL, segment_name,segment_name || ':' || partition_name) segment_name,owner,tablespace_name,segment_type," .
                    "initial_extent,next_extent,extents,bytes / 1024 / 1024 taille,max_extents,PCT_INCREASE FROM dba_segments where segment_type = 'TABLE' ORDER BY bytes desc) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            }
        }

        $fin = $start + $limit;
        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $search = '%' . strtolower($search) . '%';
        oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
        oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);
        oci_bind_by_name($s, ":seg_name", $search);

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('pct_increase' => $row['PCT_INCREASE'], 'segment_name' => $row['SEGMENT_NAME'], 'owner' => $row['OWNER'], 'tablespace_name' => $row['TABLESPACE_NAME'], 'initial_extent' => $row['INITIAL_EXTENT'], 'next_extent' => $row['NEXT_EXTENT'], 'extents' => $row['EXTENTS'], 'size' => $row['TAILLE'], 'max_extents' => $row['MAX_EXTENTS']);
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listTables()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $db_user = $_REQUEST['db_user'];
        $db_pass = $_REQUEST['db_pass'];
        $db_host = $_REQUEST['db_host'];
        $db_sid = $_REQUEST['db_sid'];
        $schema = strtolower($_REQUEST['schema']);
        $tbs = strtolower($_REQUEST['tbs']);
        $records = array();
        $total = 0;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $records [] = array('pct_increase' => '', 'segment_name' => $e['message'], 'owner' => $schema, 'tablespace_name' => $e['message'], 'initial_extent' => '', 'next_extent' => '', 'extents' => '', 'size' => '', 'max_extents' => '');
        } else {

            //$query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT snap_id,TO_CHAR (begin_interval_time, 'yyyy/mm/dd hh24:mi:ss') begin_interval_time,TO_CHAR (end_interval_time, 'yyyy/mm/dd hh24:mi:ss') end_interval_time,TO_CHAR (startup_time, 'yyyy/mm/dd hh24:mi:ss') startup_time FROM DBA_HIST_SNAPSHOT WHERE     dbid = (SELECT DISTINCT dbid FROM DBA_HIST_DATABASE_INSTANCE) AND snap_id > " . $start_id . " AND startup_time <= (SELECT MIN (startup_time) FROM DBA_HIST_SNAPSHOT WHERE snap_id > " . $start_id . ") ORDER BY 1) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            //$query = "SELECT s.segment_type,s.segment_name,DECODE (s.partition_name,NULL, '',s.partition_name) partition_name,s.owner,s.tablespace_name,s.segment_type,s.initial_extent,s.next_extent,s.extents,s.bytes / 1024 / 1024 taille,s.PCT_INCREASE,t.logging,t.blocks,t.num_rows,t.empty_blocks,t.avg_space,t.chain_cnt,t.avg_row_len,t.last_analyzed,t.monitoring,t.compression FROM dba_segments s,dba_tables t where (s.segment_name = t.table_name and s.owner = t.owner) and s.segment_type in ('TABLE','TABLE SUBPARTITION','TABLE PARTITION') ";

            $query = "SELECT *
  FROM (SELECT a.*, ROWNUM rnum
          FROM (SELECT s.segment_type,
                       s.segment_name,
                       DECODE (s.partition_name, NULL, '', s.partition_name)
                          partition_name,
                       s.owner,
                       s.tablespace_name,
                       s.initial_extent,
                       s.next_extent,
                       s.extents,
                       round(s.bytes / 1024 / 1024) taille,
                       s.PCT_INCREASE,
                       t.logging,
                       t.blocks,
                       t.num_rows,
                       t.empty_blocks,
                       t.avg_space,
                       t.chain_cnt,
                       t.avg_row_len,
                       t.last_analyzed,
                       t.monitoring,
                       t.compression
                  FROM dba_segments s, dba_tables t
                 WHERE     (    s.segment_name = t.table_name
                            AND s.owner = t.owner)
                       AND s.segment_type IN ('TABLE',
                                              'TABLE SUBPARTITION',
                                              'TABLE PARTITION') ";

            if (!empty($schema) && isset($schema) && $schema != 'all') {
                $query = $query . " AND lower(s.owner) = '" . $schema . "'";
            }

            if (!empty($tbs) && isset($tbs) && $tbs != 'all') {
                $query = $query . " AND lower(s.tablespace_name) = '" . $tbs . "'";
            }

            if (!empty($search)) {
                $query = $query . " and lower(s.segment_name) like :seg_name ";
            }

            $query = $query . " ) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH ";

            //var_dump($query);

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $records [] = array('compression' => '', 'monitoring' => '', 'last_analyzed' => '', 'avg_row_len' => '', 'chain_cnt' => '', 'avg_space' => '', 'empty_blocks' => '', 'num_rows' => '', 'blocks' => '', 'logging' => '', 'pct_increase' => '', 'segment_name' => $e['message'], 'segment_type' => '', 'partition_name' => '', 'owner' => $schema, 'tablespace_name' => $e['message'], 'initial_extent' => '', 'next_extent' => '', 'extents' => '', 'size' => '', 'max_extents' => '');
            } else {
                $search = '%' . strtolower($search) . '%';
                oci_bind_by_name($s, ":seg_name", $search);
                $fin = $start + $limit;
                oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);
                    $records [] = array('compression' => '', 'monitoring' => '', 'last_analyzed' => '', 'avg_row_len' => '', 'chain_cnt' => '', 'avg_space' => '', 'empty_blocks' => '', 'num_rows' => '', 'blocks' => '', 'logging' => '', 'pct_increase' => '', 'segment_name' => $e['message'], 'segment_type' => '', 'partition_name' => '', 'owner' => $schema, 'tablespace_name' => $e['message'], 'initial_extent' => '', 'next_extent' => '', 'extents' => '', 'size' => '', 'max_extents' => '');
                } else {

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $records [] = array('compression' => $row['COMPRESSION'], 'monitoring' => $row['MONITORING'], 'last_analyzed' => $row['LAST_ANALYZED'], 'avg_row_len' => $row['AVG_ROW_LEN'], 'chain_cnt' => $row['CHAIN_CNT'], 'avg_space' => $row['AVG_SPACE'], 'empty_blocks' => $row['EMPTY_BLOCKS'], 'num_rows' => $row['NUM_ROWS'], 'blocks' => $row['BLOCKS'], 'logging' => $row['LOGGING'], 'pct_increase' => $row['PCT_INCREASE'], 'segment_name' => $row['SEGMENT_NAME'], 'segment_type' => $row['SEGMENT_TYPE'], 'partition_name' => $row['PARTITION_NAME'], 'owner' => $row['OWNER'], 'tablespace_name' => $row['TABLESPACE_NAME'], 'initial_extent' => $row['INITIAL_EXTENT'], 'next_extent' => $row['NEXT_EXTENT'], 'extents' => $row['EXTENTS'], 'size' => $row['TAILLE'], 'max_extents' => $row['MAX_EXTENTS']);
                    }
                }
            }

            $query = "SELECT count(*) nbre FROM dba_segments s,dba_tables t where (s.segment_name = t.table_name and s.owner = t.owner) and s.segment_type in ('TABLE','TABLE SUBPARTITION','TABLE PARTITION') ";

            if (!empty($schema) && isset($schema) && $schema != 'all') {
                $query = $query . " AND lower(s.owner) = '" . $schema . "'";
            }

            if (!empty($tbs) && isset($tbs) && $tbs != 'all') {
                $query = $query . " AND lower(s.tablespace_name) = '" . $tbs . "'";
            }

            if (!empty($search)) {
                $query = $query . " and lower(s.segment_name) like :seg_name ";
            }

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $total = null;
            } else {
                $search = '%' . strtolower($search) . '%';
                oci_bind_by_name($s, ":seg_name", $search);

                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);
                    $total = null;
                } else {

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $total = $row['NBRE'];
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function saveDatabase()
    {
        global $toC_Json;

        $data = array('content_name' => $_REQUEST['label'],
            'content_url' => '',
            'created_by' => $_SESSION['admin']['username'],
            'modified_by' => $_SESSION['admin']['username'],
            'content_description' => $_REQUEST['label'],
            'content_order' => 0,
            'content_status' => $_REQUEST['content_status'],
            'page_title' => $_REQUEST['label'],
            'meta_keywords' => $_REQUEST['label'],
            'servers_id' => $_REQUEST['servers_id'],
            'label' => $_REQUEST['label'],
            'sid' => $_REQUEST['sid'],
            'host' => $_REQUEST['host'],
            'port' => $_REQUEST['port'],
            'user' => $_REQUEST['user'],
            'pass' => $_REQUEST['pass'],
            'meta_descriptions' => $_REQUEST['label']);

        if (isset($_REQUEST['group_id'])) {
            $data['group_id'] = explode(',', $_REQUEST['group_id']);

            if (is_array($data['group_id'])) {

                if (toC_Databases_Admin::save((isset($_REQUEST['databases_id']) && ($_REQUEST['databases_id'] != -1)
                    ? $_REQUEST['databases_id'] : null), $data)
                ) {
                    $response = array('success' => true, 'feedback' => 'Configuration enregistrée ...');
                } else {
                    $response = array('success' => false, 'feedback' => "Erreur survenue lors de l'enregistrement de la configuration : " . $_SESSION['LAST_ERROR']);
                }
            }
        } else {
            $response = array('success' => false, 'feedback' => 'Vous devez selectionner au moins un groupe pour cette base');
        }

        header('Content-type: application/json');
        echo $toC_Json->encode($response);
    }

    function deleteDatabase()
    {
        global $toC_Json, $osC_Language;

        if (toC_Databases_Admin::delete($_REQUEST['databases_id'])) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function deleteDatabases()
    {
        global $toC_Json, $osC_Language;

        $error = false;

        $batchs = explode(',', $_REQUEST['batch']);
        foreach ($batchs as $batch) {
            list($Databases_id, $filename) = explode(':', $batch);
            if (!toC_Databases_Admin::delete($Databases_id, $filename)) {
                $error = true;
                break;
            }
        }

        if ($error === false) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function setStatus()
    {
        global $toC_Json, $osC_Language;

        if (isset($_REQUEST['Databases_id']) && toC_Databases_Admin::setStatus($_REQUEST['Databases_id'], (isset($_REQUEST['flag'])
            ? $_REQUEST['flag'] : null))
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function getCurrentuser()
    {
        global $toC_Json;

        $username = $_SESSION[admin][username];

        $response = array('success' => true, 'feedback' => '','username' => $username);

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter','username' => '');
        }

        echo $toC_Json->encode($response);
    }
}

?>