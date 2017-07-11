<?php

if (!class_exists('content')) {
    include('includes/classes/content.php');
}
class toC_Databases_Admin
{
    function getData($id)
    {
        global $osC_Database, $osC_Language;

        $Qdatabases = $osC_Database->query('select a.*, ad.* from :table_databases a, :table_databases_description ad where a.databases_id = :databases_id and a.databases_id =ad.databases_id and ad.languages_id = :language_id');

        $Qdatabases->bindTable(':table_databases', TABLE_databaseS);
        $Qdatabases->bindTable(':table_databases_description', TABLE_databaseS_DESCRIPTION);
        $Qdatabases->bindInt(':databases_id', $id);
        $Qdatabases->bindInt(':language_id', $osC_Language->getID());
        $Qdatabases->execute();

        $data = $Qdatabases->toArray();
        $data['html'] = '<a href="../cache/databases/' . $data['filename'] . '" target="_blank">' . $data['filename'] . '</a>';

        $Qdatabases->freeResult();

        return $data;
    }

    function getDb($id)
    {
        global $osC_Database;

        $QServers = $osC_Database->query('select a.*, c.*,s.servers_id,s.host  from :table_databases a left join :table_content c on c.content_id = a.databases_id left join :table_servers s on s.servers_id = a.servers_id  where a.databases_id = :databases_id and c.content_type = "databases"');

        $QServers->bindTable(':table_servers', TABLE_SERVERS);
        $QServers->bindTable(':table_databases', TABLE_DATABASES);
        $QServers->bindTable(':table_content', TABLE_CONTENT);
        $QServers->bindInt(':databases_id', $id);
        $QServers->execute();

        $data = $QServers->toArray();

        $QServers->freeResult();

        $description = content::getContentDescription($id, 'databases');
        $data = array_merge($data, $description);

        $product_categories_array = content::getContentCategories($id, 'databases');
        $data['categories_id'] = implode(',', $product_categories_array);

        return $data;
    }

    function getOracleHome($db_user, $db_pass, $db_host, $db_sid)
    {
        $home = false;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $home = $e['message'];
        } else {
            $query = "SELECT SUBSTR (file_spec, 1, INSTR (file_spec, 'lib') - 2) HOME FROM dba_libraries WHERE library_name = 'DBMS_SUMADV_LIB'";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $home = $e['message'];
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $home = $e['message'];
                } else {
                    $records = array();

                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $home = $row['HOME'];
                    }
                }

                oci_free_statement($s);
                oci_close($c);
            }
        }

        return $home;
    }

    function getOggConfig($id)
    {
        global $osC_Database;

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
  delta_ggconfig g ";

        $Qdatabases = $osC_Database->query($query);
        $Qdatabases->bindInt(':id', $id);
        $Qdatabases->execute();

        $data = $Qdatabases->toArray();
        //$data['html'] = '<a href="../cache/databases/' . $data['filename'] . '" target="_blank">' . $data['filename'] . '</a>';

        $Qdatabases->freeResult();

        return $data;
    }

    function saveCaptureState($data)
    {
        global $osC_Database;
        $start_date = date("Y-m-d H:i:s");

        $query = "INSERT INTO delta_ogg_capture_state (config_id,state,comments,lag,date,db_status,db_sequence,fs_state,capture_sequence,trail_seqno,trail_rba) VALUES (:config_id,:state,:comments,:lag,:when,:db_status,:db_sequence,:fs_state,:capture_sequence,:trail_seqno,:trail_rba)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':config_id', $data['config_id']);
        $Qserver->bindValue(':state', $data['state']);
        $Qserver->bindValue(':comments', $data['comments']);
        $Qserver->bindValue(':lag', $data['lag']);
        $Qserver->bindValue(':db_status', $data['status']);
        $Qserver->bindValue(':db_sequence', $data['seq']);
        $Qserver->bindValue(':fs_state', $data['pct']);
        $Qserver->bindValue(':trail_seqno', $data['seqno']);
        $Qserver->bindValue(':trail_rba', $data['rba']);
        $Qserver->bindValue(':capture_sequence', $data['sequence']);
        $Qserver->bindValue(':when',$start_date);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        $id = $osC_Database->nextID();

        $query = "delete from delta_ogg_capture_state where ogg_state_id < " . ($id - 200);

        $Qserver = $osC_Database->query($query);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        return true;
    }

    function saveOggLog($data)
    {
        global $osC_Database;

        $query = "INSERT INTO delta_ogg_log (process_name,config_id,date,time,type,code,message) VALUES (:process_name,:config_id,:date,:time,:type,:code,:message)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':process_name', $data['process_name']);
        $Qserver->bindValue(':config_id', $data['config_id']);
        $Qserver->bindValue(':date', $data['date']);
        $Qserver->bindValue(':time', $data['time']);
        $Qserver->bindValue(':code', $data['code']);
        $Qserver->bindValue(':type', $data['type']);
        $Qserver->bindValue(':message', $data['message']);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        return true;
    }

    function saveReplicationState($data)
    {
        global $osC_Database;
        $start_date = date("Y-m-d H:i:s");

        $query = "INSERT INTO delta_ogg_replication_state (config_id,state,comments,lag,date,fs_state,trail_seqno,trail_rba) VALUES (:config_id,:state,:comments,:lag,:when,:fs_state,:trail_seqno,:trail_rba)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':config_id', $data['config_id']);
        $Qserver->bindValue(':state', $data['state']);
        $Qserver->bindValue(':comments', $data['comments']);
        $Qserver->bindValue(':lag', $data['lag']);
        $Qserver->bindValue(':fs_state', $data['pct']);
        $Qserver->bindValue(':trail_seqno', $data['seqno']);
        $Qserver->bindValue(':trail_rba', $data['rba']);
        $Qserver->bindValue(':when',$start_date);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        $id = $osC_Database->nextID();

        $query = "delete from delta_ogg_replication_state where ogg_state_id < " . ($id - 200);

        $Qserver = $osC_Database->query($query);
        $Qserver->execute();

        return true;
    }

    function savePropagationState($data)
    {
        global $osC_Database;

        $start_date = date("Y-m-d H:i:s");

        $query = "INSERT INTO delta_ogg_propagation_state (config_id,state,comments,lag,net,date,trail_seqno,trail_rba) VALUES (:config_id,:state,:comments,:lag,:net,:when,:trail_seqno,:trail_rba)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':config_id', $data['config_id']);
        $Qserver->bindValue(':state', $data['state']);
        $Qserver->bindValue(':comments', $data['comments']);
        $Qserver->bindValue(':lag', $data['lag']);
        $Qserver->bindValue(':net', $data['net']);
        $Qserver->bindValue(':trail_seqno', $data['seqno']);
        $Qserver->bindValue(':trail_rba', $data['rba']);
        $Qserver->bindValue(':when',$start_date);
        $Qserver->execute();

        if ($osC_Database->isError()) {
            return false;
        }

        $id = $osC_Database->nextID();

        $query = "delete from delta_ogg_propagation_state where ogg_state_id < " . ($id - 200);

        $Qserver = $osC_Database->query($query);
        $Qserver->execute();

        return true;
    }

    function setStatus($id, $flag)
    {
        global $osC_Database;
        $Qstatus = $osC_Database->query('update :table_databases set databases_status= :databases_status, databases_last_modified = now() where databases_id = :databases_id');
        $Qstatus->bindInt(':databases_status', $flag);
        $Qstatus->bindInt(':databases_id', $id);
        $Qstatus->bindTable(':table_databases', TABLE_databaseS);
        $Qstatus->setLogging($_SESSION['module'], $id);
        $Qstatus->execute();
        return true;
    }

    function delete($id)
    {
        global $osC_Database;

        $osC_Database->startTransaction();


        $error = !content::deleteContent($id, 'databases');

        if ($error === false) {
            $QServers = $osC_Database->query('delete from :table_databases where databases_id = :databases_id');
            $QServers->bindTable(':table_databases', TABLE_DATABASES);
            $QServers->bindInt(':databases_id', $id);
            $QServers->setLogging($_SESSION['module'], $id);
            $QServers->execute();

            if ($osC_Database->isError()) {
                $_SESSION['LAST_ERROR'] = $osC_Database->error;
                $error = true;
            }
        }

        if ($error == true) {
            $osC_Database->rollbackTransaction();
            return false;
        }

        $osC_Database->commitTransaction();
        osC_Cache::clear('sefu-Databases');
        return true;
    }

    function save($id = null, $data)
    {
        global $osC_Database;

        $error = false;

        //we check the connection first
        $db_user = $data['user'];
        $db_pass = $data['pass'];
        $db_host = $data['host'];
        $db_sid = $data['sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $_SESSION['LAST_ERROR'] = 'Could not connect to database: ' . $e['message'];
            return false;
        } else {
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
    }
}

?>
