<?php

require('includes/classes/servers.php');
require('includes/classes/email_account.php');
require('includes/classes/email_accounts.php');
require('includes/classes/reports.php');

class toC_Json_Servers
{
    function listServers()
    {
        global $toC_Json, $osC_Database;

        $group_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        //$QServers = $osC_Database->query('select a.*, cd.*,c.*, atoc.*  from :table_servers a left outer join :table_content c on a.servers_id = c.content_id left outer join  :table_content_description cd on a.servers_id = cd.content_id left outer join :table_content_to_categories atoc on atoc.content_id = a.servers_id  where cd.language_id = :language_id and atoc.content_type = "servers" and c.content_type = "servers" AND cd.content_type = "servers"');
        $QServers = $osC_Database->query('select a.* from delta_servers a where 1 = 1 ');

        if (!empty($_REQUEST['search'])) {
            $QServers->appendQuery(' and (a.label like :content_name or a.host like :content_name or a.port like :content_name or a.typ like :content_name)');
            $QServers->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        if ($group_id != 0 && $group_id != -1) {
            $QServers->appendQuery(' and a.servers_id IN (SELECT servers_id FROM delta_server_to_groups WHERE group_id = :group_id)');
            $QServers->bindInt(':group_id', $group_id);
        }

        $QServers->appendQuery('order by a.label ');
        $QServers->execute();

        //var_dump($QServers);

        $records = array();
        while ($QServers->next()) {
            if (isset($_REQUEST['permissions'])) {
                $permissions = explode(',', $_REQUEST['permissions']);
                $records[] = array('servers_id' => $QServers->ValueInt('servers_id'),
                    'content_name' => $QServers->Value('label'),
                    'host' => $QServers->Value('host'),
                    'user' => $QServers->Value('user'),
                    'pass' => $QServers->Value('pass'),
                    'typ' => $QServers->Value('typ'),
                    'can_write' => $_SESSION['admin']['username'] == 'admin' ? 1 : $permissions[1],
                    'can_modify' => $_SESSION['admin']['username'] == 'admin' ? '' : $permissions[2],
                    'can_publish' => $_SESSION['admin']['username'] == 'admin' ? 1 : $permissions[3]
                );
            } else {
                $records[] = array('servers_id' => $QServers->ValueInt('servers_id'),
                    'content_name' => $QServers->Value('label'),
                    'host' => $QServers->Value('host'),
                    'port' => $QServers->Value('port'),
                    'user' => $QServers->Value('user'),
                    'pass' => $QServers->Value('pass'),
                    'typ' => $QServers->Value('typ'),
                    'can_read' => $_SESSION['admin']['username'] == 'admin' ? 1 : false,
                    'can_write' => $_SESSION['admin']['username'] == 'admin' ? 1 : false,
                    'can_modify' => $_SESSION['admin']['username'] == 'admin' ? '' : false,
                    'can_publish' => $_SESSION['admin']['username'] == 'admin' ? 1 : false
                );                
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listServerperf()
    {
        global $toC_Json, $osC_Database;

        $group_id = empty($_REQUEST['category']) ? 0 : $_REQUEST['category'];

        $query = "SELECT s.label,s.HOST,s.servers_id,s.typ,s.USER AS server_user,s.pass AS server_pass,s.PORT AS server_port FROM delta_servers s WHERE 1 = 1 ";
        $QServers = $osC_Database->query($query);

        if (!empty($_REQUEST['search'])) {
            $QServers->appendQuery('and s.label like :content_name');
            $QServers->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
        }

        if (!empty($_REQUEST['where'])) {
            $QServers->appendQuery('and ' . $_REQUEST['where']);
        }

        if ($group_id != 0 && $group_id != -1) {
            $QServers->appendQuery('and s.servers_id IN (SELECT servers_id FROM delta_server_to_groups WHERE group_id = :group_id)');
            $QServers->bindInt(':group_id', $group_id);
        }

        $QServers->appendQuery('order by s.label ');
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
                    'typ' => $QServers->Value('typ')
                );
            } else {
                $records[] = array('databases_id' => $QServers->ValueInt('databases_id'),
                    'host' => $QServers->Value('HOST'),
                    'server_user' => $QServers->Value('server_user'),
                    'servers_id' => $QServers->Value('servers_id'),
                    'server_pass' => $QServers->Value('server_pass'),
                    'server_port' => $QServers->Value('server_port'),
                    'label' => $QServers->Value('label'),
                    'typ' => $QServers->Value('typ')
                );
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadLayoutTree()
    {
        global $toC_Json,$osC_Database;

        $QServers = $osC_Database->query('select a.* from delta_servers a where 1 = 1 ');
        $QServers->appendQuery('order by a.label ');
        $QServers->execute();

        $records = array();
        while ($QServers->next()) {
            $records [] = array(
                'servers_id' => $QServers->ValueInt('servers_id'),
                'content_name' => $QServers->Value('label'),
                'host' => $QServers->Value('host'),
                'server_port' => $QServers->Value('port'),
                'server_user' => $QServers->Value('user'),
                'server_pass' => $QServers->Value('pass'),
                'typ' => $QServers->Value('typ'),
                'id' => $QServers->ValueInt('servers_id'),
                'text' => $QServers->value('label'),
                'icon' => 'templates/default/images/icons/16x16/server_info.png',
                'leaf' => true
            );
        }

        $QServers->freeResult();

        echo $toC_Json->encode($records);
    }

    function listSpaceusage()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT " .
            "  delta_servers.*," .
            "  delta_space_usage.snaps_id," .
            "  delta_space_usage.servers_id," .
            "  delta_space_usage.space_total_gb as size," .
            "  delta_space_usage.space_used_gb as used," .
            "  delta_space_usage.space_dispo_gb as dispo," .
            "  ROUND(space_used_gb/space_total_gb * 100) AS pct_used," .
            "  delta_space_usage.start_date," .
            "  delta_space_usage.end_date " .
            "FROM" .
            "  delta_space_usage " .
            "  RIGHT JOIN" .
            "  delta_servers " .
            "  ON (" .
            "    delta_space_usage.servers_id = delta_servers.servers_id" .
            "  ) " .
            "WHERE delta_space_usage.snaps_id = " .
            "  (SELECT " .
            "    MAX(snaps_id) " .
            "  FROM" .
            "    delta_snaps where job_id = 'space_usage') order by label";

        $QServers = $osC_Database->query($query);

        $QServers->execute();

        $records = array();
        while ($QServers->next()) {
            $records[] = array('servers_id' => $QServers->ValueInt('servers_id'),
                'snaps_id' => $QServers->ValueInt('snaps_id'),
                'label' => $QServers->Value('label'),
                'content_name' => $QServers->Value('content_name'),
                'size' => $QServers->ValueInt('size'),
                'used' => $QServers->ValueInt('used'),
                'dispo' => $QServers->ValueInt('dispo'),
                'pct_used' => $QServers->ValueInt('pct_used'),
                'start_date' => $QServers->Value('start_date'),
                'end_date' => $QServers->Value('end_date'),
                'host' => $QServers->Value('host'),
                'typ' => $QServers->Value('typ'),
                'port' => $QServers->ValueInt('port'),
                'user' => $QServers->Value('user'),
                'pass' => $QServers->Value('pass')
            );
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listSignatures()
    {
        global $toC_Json;

        if (empty($_REQUEST['ncp'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez renseigner un No de Compte');
        } else {
            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $limit = 1;
            $total = empty($_REQUEST['total']) ? 0 : $_REQUEST['total'];

            $db_user = 'bank';
            $db_pass = 'oracle';
            $db_host = '10.100.203.5';
            $db_sid = 'STOCKV10';

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                if (empty($_REQUEST['total'])) {
                    $query = "SELECT count(*) NBRE FROM DELTASIG.BKSIG_COMPTE WHERE ncp = '" . $_REQUEST['ncp'] . "'";
                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s);
                        if (!$r) {
                            $e = oci_error($s);
                            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
                        } else {
                            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                                $total = $row['NBRE'];
                            }
                        }

                        oci_free_statement($s);
                    }
                }

                //$query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT ident_sig FROM DELTASIG.BKSIG_COMPTE WHERE ncp = '" . $_REQUEST['ncp'] . "') a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT chemin FROM DELTASIG.BKSIG_image WHERE ident_sig IN (SELECT ident_sig FROM deltasig.bksig_compte WHERE ncp = '" . $_REQUEST['ncp'] . "')) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";

                $start = $start + 1;
                $fin = $start;

                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                } else {

                    oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                    oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de lister les signatures de ce compte ' . htmlentities($e['message']));
                    } else {
                        $records = array();

                        while (($row = oci_fetch_array($s, OCI_COMMIT_ON_SUCCESS))) {
                            $chemin = $row['IDENT_SIG'];

                            $query = "DECLARE "
                                . "l_file            UTL_FILE.FILE_TYPE; "
                                . "l_buffer          RAW (32767); "
                                . "l_amount          BINARY_INTEGER := 32767; "
                                . "l_pos             NUMBER := 1; "
                                . "l_blob            BLOB; "
                                . "l_blob_len        NUMBER; "
                                . "taille            NUMBER; "
                                . "taille_pngquant   NUMBER; "
                                . "taille_optipng    NUMBER; "
                                . "taille_pngcrush   NUMBER; "
                                . "col               VARCHAR (30); "
                                . "BEGIN "
                                . "SELECT IMAGE INTO l_blob FROM DELTASIG.BKSIG_BLOB WHERE CHEMIN = " . $chemin . "; "
                                . "l_blob_len := DBMS_LOB.getlength (l_blob); "
                                . "l_file := UTL_FILE.fopen ('SIGNATURES','" . $chemin . ".png','wb',32767); "
                                . "WHILE l_pos < l_blob_len LOOP "
                                . "DBMS_LOB.read (l_blob,l_amount,l_pos,l_buffer); "
                                . "UTL_FILE.put_raw (l_file, l_buffer, TRUE); "
                                . "l_pos := l_pos + l_amount; "
                                . "END LOOP; "
                                . "UTL_FILE.fclose (l_file); "
                                . "SELECT taille,taille_pngquant,taille_optipng,taille_pngcrush INTO taille,taille_pngquant,taille_optipng,taille_pngcrush FROM DELTASIG.BKSIG_BLOB WHERE CHEMIN = " . $chemin . "; "
                                . "col := 'IMAGE'; "
                                . "IF taille_pngquant < taille "
                                . "THEN "
                                . "taille := taille_pngquant; "
                                . "col := 'IMAGE_PNGQUANT'; "
                                . "END IF; "
                                . "IF taille_optipng < taille "
                                . "THEN "
                                . "taille := taille_optipng; "
                                . "col := 'IMAGE_OPTIPNG'; "
                                . "END IF; "
                                . "IF taille_pngcrush < taille "
                                . "THEN "
                                . "taille := taille_pngcrush; "
                                . "col := 'IMAGE_PNGCRUSH'; "
                                . "END IF; "
                                . "EXECUTE IMMEDIATE 'SELECT ' || col || ' FROM DELTASIG.BKSIG_BLOB WHERE CHEMIN = " . $chemin . "' INTO l_blob; "
                                . "l_blob_len := DBMS_LOB.getlength (l_blob); "
                                . "l_file := UTL_FILE.fopen ('SIGNATURES','" . $chemin . "_COMP.png','wb',32767); "
                                . "l_pos := 1; "
                                . "WHILE l_pos < l_blob_len LOOP "
                                . "DBMS_LOB.read (l_blob,l_amount,l_pos,l_buffer); "
                                . "UTL_FILE.put_raw (l_file, l_buffer, TRUE); "
                                . "l_pos := l_pos + l_amount; "
                                . "END LOOP; "
                                . "UTL_FILE.fclose (l_file); "
                                . "END; ";

                            $s1 = oci_parse($c, $query);

                            if (!$s1) {
                                $e = oci_error($c);
                                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                            } else {
                                $r1 = oci_execute($s1, OCI_COMMIT_ON_SUCCESS);
                                if (!$r1) {
                                    $e = oci_error($s1);
                                    $response = array('success' => false, 'feedback' => "Impossible d'extraire l'image " . $chemin . " de la BD : " . htmlentities($e['message']));
                                } else {
                                    $app_user = 'oracle';
                                    $app_pass = 'Guy2p@cc';
                                    $app_host = '10.100.203.5';

                                    $ssh = new Net_SSH2($app_host);
                                    if (!$ssh->login($app_user, $app_pass)) {
                                        $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH');
                                    } else {
                                        $ssh->disableQuietMode();

                                        $cmd = "cat /index/signatures/" . $chemin . ".png";
                                        $png = $ssh->exec($cmd);

                                        $cmd = "cat /index/signatures/" . $chemin . "_COMP.png";
                                        $png1 = $ssh->exec($cmd);
                                        $ssh->disconnect();

                                        $dir = realpath(DIR_WS_REPORTS) . '/';
                                        if (!file_exists($dir)) {
                                            mkdir($dir, 0777, true);
                                        }

                                        $file_name = $dir . '/' . $chemin . ".png";
                                        file_put_contents($file_name, $png);
                                        $size = toC_Servers_Admin::formatSizeUnits(filesize($file_name));

                                        $file_name = $dir . '/' . $chemin . "_COMP.png";
                                        file_put_contents($file_name, $png1);
                                        $size1 = toC_Servers_Admin::formatSizeUnits(filesize($file_name));
                                        $gain = round(100 - 100 * $size1 / $size);

                                        $records[] = array('chemin' => $chemin, 'original' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . '.png;' . $size, 'compresse' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . '_COMP.png;' . $size1 . ';' . $gain);
                                    }
                                }

                                oci_free_statement($s1);
                            }

                            $response = array(EXT_JSON_READER_TOTAL => $total,
                                EXT_JSON_READER_ROOT => $records);
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function restoreSignature()
    {
        global $toC_Json;

        $db_user = 'system';
        $db_pass = 'oracle';
        $db_host = 'signature.intra.bicec';
        $db_sid = 'SIGBICEC';
        $chemin = $_REQUEST['chemin'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $query = "BEGIN update BKSIG_blob set IMAGE = IMAGE_ORIG,TAILLE=TAILLE_ORIG,COMPRESSED=1 where chemin = " . $chemin . "; commit;END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de restaurer cette image ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de restaurer cette image ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Image restauree avec succes");
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function loadFile()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $url = $_REQUEST['url'];
        $data['file'] = $_REQUEST['url'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
            return false;
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
            } else {
                $ssh->disableQuietMode();

                $cmd = "cat " . $url;

                $data['content'] = $ssh->exec($cmd);
                $ssh->disconnect();
            }
        }

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function listSignaturesprod()
    {
        global $toC_Json;

        $ncp = $_REQUEST['ncp'];
        if (empty($ncp)) {
            $response = array('success' => false, 'feedback' => 'Veuillez renseigner un No de Compte');
        } else {
            $response = array('success' => false, 'feedback' => 'Aucune image trouvee !!!');
            $records = array();
            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $total = empty($_REQUEST['total']) ? 0 : $_REQUEST['total'];

            $db_user = 'system';
            $db_pass = 'oracle';
            $db_host = 'signature.intra.bicec';
            $db_sid = 'SIGBICEC';

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Impossible de connecter : ' . htmlentities($e['message']));
            } else {
                if (empty($_REQUEST['total'])) {
                    $query = "SELECT count(*) NBRE FROM DELTASIG.BKSIG_COMPTE WHERE ncp = '" . $ncp . "'";
                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => "Impossible de lister les signatures " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => "Impossible de lister les signatures " . htmlentities($e['message']));
                        } else {
                            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                                $total = $row['NBRE'];
                            }
                        }
                    }

                    oci_free_statement($s);
                }

                //$query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT ident_sig FROM DELTASIG.BKSIG_COMPTE WHERE ncp = '" . $ncp . "') a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT chemin FROM DELTASIG.BKSIG_image WHERE ident_sig IN (SELECT ident_sig FROM deltasig.bksig_compte WHERE ncp = '" . $ncp . "')) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";

                $start = $start + 1;
                $fin = $start;

                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible de lister les signatures " . htmlentities($e['message']));
                } else {

                    oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                    oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de lister les signatures de ce compte ' . htmlentities($e['message']));
                    } else {
                        while (($row = oci_fetch_array($s, OCI_COMMIT_ON_SUCCESS))) {
                            $chemin = $row['CHEMIN'];

                            $query = "DECLARE "
                                . "l_file            UTL_FILE.FILE_TYPE; "
                                . "l_buffer          RAW (32767); "
                                . "l_amount          BINARY_INTEGER := 32767; "
                                . "l_pos             NUMBER := 1; "
                                . "l_blob            BLOB; "
                                . "l_blob_len        NUMBER; "
                                . "taille            NUMBER; "
                                . "taille_pngquant   NUMBER; "
                                . "taille_optipng    NUMBER; "
                                . "taille_pngcrush   NUMBER; "
                                . "col               VARCHAR (30); "
                                . "BEGIN "
                                . "SELECT IMAGE_ORIG INTO l_blob FROM DELTASIG.BKSIG_BLOB WHERE CHEMIN = " . $chemin . "; "
                                . "l_blob_len := DBMS_LOB.getlength (l_blob); "
                                . "l_file := UTL_FILE.fopen ('SIGNATURES','" . $chemin . ".png','wb',32767); "
                                . "WHILE l_pos < l_blob_len LOOP "
                                . "DBMS_LOB.read (l_blob,l_amount,l_pos,l_buffer); "
                                . "UTL_FILE.put_raw (l_file, l_buffer, TRUE); "
                                . "l_pos := l_pos + l_amount; "
                                . "END LOOP; "
                                . "UTL_FILE.fclose (l_file); "
                                . "SELECT IMAGE INTO l_blob FROM DELTASIG.BKSIG_BLOB WHERE CHEMIN = " . $chemin . "; "
                                . "l_blob_len := DBMS_LOB.getlength (l_blob); "
                                . "l_file := UTL_FILE.fopen ('SIGNATURES','" . $chemin . "_COMP.png','wb',32767); "
                                . "l_pos := 1; "
                                . "WHILE l_pos < l_blob_len LOOP "
                                . "DBMS_LOB.read (l_blob,l_amount,l_pos,l_buffer); "
                                . "UTL_FILE.put_raw (l_file, l_buffer, TRUE); "
                                . "l_pos := l_pos + l_amount; "
                                . "END LOOP; "
                                . "UTL_FILE.fclose (l_file); "
                                . "END; ";

                            $s1 = oci_parse($c, $query);

                            if (!$s1) {
                                $e = oci_error($c);
                                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                            } else {
                                $r1 = oci_execute($s1, OCI_COMMIT_ON_SUCCESS);
                                if (!$r1) {
                                    $e = oci_error($s1);
                                    $response = array('success' => false, 'feedback' => "Impossible d'extraire l'image " . $chemin . " de la BD : " . htmlentities($e['message']));
                                } else {
                                    $app_user = 'oracle';
                                    $app_pass = 'Guy2p@cc';
                                    $app_host = 'signature.intra.bicec';

                                    $ssh = new Net_SSH2($app_host);
                                    if (!$ssh->login($app_user, $app_pass)) {
                                        $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH');
                                    } else {
                                        $ssh->disableQuietMode();

                                        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
                                        $charactersLength = strlen($characters);
                                        $randomString = '';
                                        for ($i = 0; $i < 5; $i++) {
                                            $randomString .= $characters[rand(0, $charactersLength - 1)];
                                        }

                                        $cmd = "cat /dev/shm/" . $chemin . ".png";
                                        $png = $ssh->exec($cmd);

                                        $cmd = "cat /dev/shm/" . $chemin . "_COMP.png";
                                        $png1 = $ssh->exec($cmd);

                                        $dir = realpath(DIR_WS_REPORTS) . '/';
                                        if (!file_exists($dir)) {
                                            mkdir($dir, 0777, true);
                                        }

                                        $file_name = $dir . '/' . $chemin . "_" . $randomString . ".png";
                                        file_put_contents($file_name, $png);
                                        $size = toC_Servers_Admin::formatSizeUnits(filesize($file_name));

                                        $file_name = $dir . '/' . $chemin . "_" . $randomString . "_COMP.png";
                                        file_put_contents($file_name, $png1);
                                        $size1 = toC_Servers_Admin::formatSizeUnits(filesize($file_name));
                                        $gain = round(100 - 100 * $size1 / $size);

                                        $cmd = "nohup rm -f /dev/shm/" . $chemin . "_COMP.png &";
                                        $ssh->exec($cmd);
                                        $cmd = "nohup rm -f /dev/shm/" . $chemin . ".png &";
                                        $ssh->exec($cmd);

                                        $ssh->disconnect();

                                        $records[] = array('chemin' => $chemin, 'original' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . "_" . $randomString . '.png;' . $size, 'compresse' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . "_" . $randomString . '_COMP.png;' . $size1 . ';' . $gain);
                                    }
                                }

                                oci_free_statement($s1);
                            }

                            $response = array(EXT_JSON_READER_TOTAL => $total,
                                EXT_JSON_READER_ROOT => $records);
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);

            if (count($records) == 0) {
                $response = array('success' => false, 'feedback' => "Aucune image trouvee !!");
            }
        }

        echo $toC_Json->encode($response);
    }

    function listSign()
    {
        global $toC_Json;

        if (empty($_REQUEST['ncp'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez renseigner un No de Compte');
        } else {
            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $limit = 1;
            $total = empty($_REQUEST['total']) ? 0 : $_REQUEST['total'];
            $quality = empty($_REQUEST['quality']) ? 5 : $_REQUEST['quality'];
            $quality_min = ($quality - 20) >= 0 ? $quality - 20 : 0;

            $db_user = 'bank';
            $db_pass = 'oracle';
            $db_host = '10.100.203.5';
            $db_sid = 'STOCKV10';

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                if (empty($_REQUEST['total'])) {
                    $query = "SELECT count(*) NBRE FROM DELTASIG.BKSIG_COMPTE WHERE ncp = '" . $_REQUEST['ncp'] . "'";
                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s);
                        if (!$r) {
                            $e = oci_error($s);
                            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
                        } else {
                            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                                $total = $row['NBRE'];
                            }
                        }

                        oci_free_statement($s);
                    }
                }

                //$query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT ident_sig FROM DELTASIG.BKSIG_COMPTE WHERE ncp = '" . $_REQUEST['ncp'] . "') a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT chemin FROM DELTASIG.BKSIG_image WHERE ident_sig IN (SELECT ident_sig FROM deltasig.bksig_compte WHERE ncp = '" . $_REQUEST['ncp'] . "')) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";

                $start = $start + 1;
                $fin = $start;

                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                } else {

                    oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                    oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de lister les signatures de ce compte ' . htmlentities($e['message']));
                    } else {
                        $records = array();

                        while (($row = oci_fetch_array($s, OCI_COMMIT_ON_SUCCESS))) {
                            $chemin = $row['CHEMIN'];

                            $query = "DECLARE "
                                . "l_file            UTL_FILE.FILE_TYPE; "
                                . "l_buffer          RAW (32767); "
                                . "l_amount          BINARY_INTEGER := 32767; "
                                . "l_pos             NUMBER := 1; "
                                . "l_blob            BLOB; "
                                . "l_blob_len        NUMBER; "
                                . "BEGIN "
                                . "SELECT IMAGE INTO l_blob FROM DELTASIG.BKSIG_BLOB WHERE CHEMIN = " . $chemin . "; "
                                . "l_blob_len := DBMS_LOB.getlength (l_blob); "
                                . "l_file := UTL_FILE.fopen ('SIGNATURES','" . $chemin . ".png','wb',32767); "
                                . "WHILE l_pos < l_blob_len LOOP "
                                . "DBMS_LOB.read (l_blob,l_amount,l_pos,l_buffer); "
                                . "UTL_FILE.put_raw (l_file, l_buffer, TRUE); "
                                . "l_pos := l_pos + l_amount; "
                                . "END LOOP; "
                                . "UTL_FILE.fclose (l_file); "
                                . "END; ";

                            $s1 = oci_parse($c, $query);

                            if (!$s1) {
                                $e = oci_error($c);
                                $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                            } else {
                                $r1 = oci_execute($s1, OCI_COMMIT_ON_SUCCESS);
                                if (!$r1) {
                                    $e = oci_error($s1);
                                    $response = array('success' => false, 'feedback' => "Impossible d'extraire l'image " . $chemin . " de la BD : " . htmlentities($e['message']));
                                } else {
                                    $app_user = 'oracle';
                                    $app_pass = 'Guy2p@cc';
                                    $app_host = '10.100.203.5';

                                    $ssh = new Net_SSH2($app_host);
                                    if (!$ssh->login($app_user, $app_pass)) {
                                        $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH');
                                    } else {
                                        $ssh->disableQuietMode();

                                        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
                                        $charactersLength = strlen($characters);
                                        $randomString = '';
                                        for ($i = 0; $i < 5; $i++) {
                                            $randomString .= $characters[rand(0, $charactersLength - 1)];
                                        }

                                        $cmd = "rm -f /index/signatures/" . $chemin . "_pngquant.png";
                                        $response = $ssh->exec($cmd);

                                        $cmd = "pngquant --speed 1 --quality=" . $quality_min . "-" . $quality . " /index/signatures/" . $chemin . ".png -o /index/signatures/" . $chemin . "_pngquant_" . $randomString . ".png";
                                        $response = $ssh->exec($cmd);

                                        $cmd = "optipng -o7 -quiet /index/signatures/" . $chemin . "_pngquant_" . $randomString . ".png -out /index/signatures/" . $chemin . "_optipng_" . $randomString . ".png";
                                        //$cmd = "optipng -o7 -quiet /index/signatures/" . $chemin . ".png -out /index/signatures/" . $chemin . "_optipng.png";
                                        $response = $ssh->exec($cmd);

                                        $cmd = "pngcrush -q -rem alla -brute -reduce /index/signatures/" . $chemin . "_pngquant_" . $randomString . ".png /index/signatures/" . $chemin . "_pngcrush_" . $randomString . ".png";
                                        //$cmd = "pngcrush -q -rem alla -brute -reduce /index/signatures/" . $chemin . ".png /index/signatures/" . $chemin . "_pngcrush.png";
                                        $response = $ssh->exec($cmd);

                                        $cmd = "cat /index/signatures/" . $chemin . ".png";
                                        $png = $ssh->exec($cmd);

                                        $cmd = "cat /index/signatures/" . $chemin . "_pngquant_" . $randomString . ".png";
                                        $png_pngquant = $ssh->exec($cmd);

                                        $cmd = "cat /index/signatures/" . $chemin . "_optipng_" . $randomString . ".png";
                                        $png_optipng = $ssh->exec($cmd);

                                        $cmd = "cat /index/signatures/" . $chemin . "_pngcrush_" . $randomString . ".png";
                                        $png_pngcrush = $ssh->exec($cmd);
                                        $ssh->disconnect();

                                        $dir = realpath(DIR_WS_REPORTS);
                                        if (!file_exists($dir)) {
                                            mkdir($dir, 0777, true);
                                        }

                                        fb($dir, '$dir', FirePHP::INFO);

                                        $file_name = $dir . '/' . $chemin . ".png";
                                        file_put_contents($file_name, $png);
                                        $size = toC_Servers_Admin::formatSizeUnits(filesize($file_name));

                                        $file_name = $dir . '/' . $chemin . "_pngquant_" . $randomString . ".png";
                                        file_put_contents($file_name, $png_pngquant);
                                        $size_pngquant = toC_Servers_Admin::formatSizeUnits(filesize($file_name));
                                        $gain_pngquant = round(100 - 100 * $size_pngquant / $size);

                                        $file_name = $dir . '/' . $chemin . "_optipng_" . $randomString . ".png";
                                        file_put_contents($file_name, $png_optipng);
                                        $size_optipng = toC_Servers_Admin::formatSizeUnits(filesize($file_name));
                                        $gain_optipng = round(100 - 100 * $size_optipng / $size);

                                        $file_name = $dir . '/' . $chemin . "_pngcrush_" . $randomString . ".png";
                                        file_put_contents($file_name, $png_pngcrush);
                                        $size_pngcrush = toC_Servers_Admin::formatSizeUnits(filesize($file_name));
                                        $gain_pngcrush = round(100 - 100 * $size_pngcrush / $size);

                                        $records[] = array('chemin' => $chemin,
                                            'original' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . '.png;' . $size,
                                            'pngquant' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . '_pngquant_' . $randomString . '.png;' . $size_pngquant . ';' . $gain_pngquant,
                                            'optipng' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . '_optipng_' . $randomString . '.png;' . $size_optipng . ';' . $gain_optipng,
                                            'pngcrush' => HTTP_SERVER . '/' . HTTP_COOKIE_PATH . '/' . DIR_FS_ADMIN . '/' . DIR_WS_REPORTS . '/' . $chemin . '_pngcrush_' . $randomString . '.png;' . $size_pngcrush . ';' . $gain_pngcrush
                                        );
                                    }
                                }

                                oci_free_statement($s1);
                            }

                            $response = array(EXT_JSON_READER_TOTAL => $total,
                                EXT_JSON_READER_ROOT => $records);
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function listSignaturescount()
    {
        global $toC_Json;

        if (empty($_REQUEST['ncp'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez renseigner un No de Compte');
        } else {
            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $limit = 1;
            $total = empty($_REQUEST['total']) ? 0 : $_REQUEST['total'];

            $db_user = 'system';
            $db_pass = 'oracle';
            $db_host = 'signature.intra.bicec';
            $db_sid = 'SIGBICEC';

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                if (empty($_REQUEST['total'])) {
                    //$query = "SELECT count(*) NBRE FROM DELTASIG.BKSIG_COMPTE WHERE ncp = '" . $_REQUEST['ncp'] . "'";
                    $query = "SELECT count(chemin) NBRE FROM DELTASIG.BKSIG_image WHERE ident_sig IN (SELECT ident_sig FROM deltasig.bksig_compte WHERE ncp = '" . $_REQUEST['ncp'] . "')";
                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s);
                        if (!$r) {
                            $e = oci_error($s);
                            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
                        } else {
                            while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                                $total = $row['NBRE'];
                            }

                            $response = array('success' => true, 'total' => $total);
                        }

                        oci_free_statement($s);
                    }
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function listEvents()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('event' => 'enable_account', 'label' => 'Activation Compte');
        $records[] = array('event' => 'archive_destination_error', 'label' => 'Archive Destination Error');
        $records[] = array('event' => 'archive_log_gap', 'label' => 'Archive Log Gap');
        $records[] = array('event' => 'no_connection', 'label' => 'Connexion impossible');
        $records[] = array('event' => 'create_account', 'label' => 'Creation Compte');
        $records[] = array('event' => 'disable_account', 'label' => 'Desactivation Compte');
        $records[] = array('event' => 'reset_password', 'label' => 'Reinitialisation Mot de Passe');
        $records[] = array('event' => 'drop_account', 'label' => 'Suppression Compte');
        $records[] = array('event' => 'tablespace_free_space', 'label' => 'Tablespace Free Space');

        $response = array(EXT_JSON_READER_TOTAL => 5,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listEventsinfoc()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('event' => 'enable_account', 'label' => 'Activation Compte');
        $records[] = array('event' => 'create_account', 'label' => 'Creation Compte');
        $records[] = array('event' => 'disable_account', 'label' => 'Desactivation Compte');
        $records[] = array('event' => 'reset_password', 'label' => 'Reinitialisation Mot de Passe');

        $response = array(EXT_JSON_READER_TOTAL => 5,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listUsersinfocentre()
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
            $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (  SELECT DECODE (partition_name,NULL, segment_name,segment_name || ':' || partition_name) segment_name,owner,tablespace_name,segment_type," .
                "initial_extent,next_extent,extents,bytes / 1024 / 1024 taille,PCT_INCREASE FROM dba_segments where segment_type = 'TABLE' and lower(segment_name) like :seg_name ORDER BY bytes desc) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
        } else {
            $query = "select count(*) nbre from dba_segments where segment_type = 'TABLE'";

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

            $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (  SELECT DECODE (partition_name,NULL, segment_name,segment_name || ':' || partition_name) segment_name,owner,tablespace_name,segment_type," .
                "initial_extent,next_extent,extents,bytes / 1024 / 1024 taille,max_extents,PCT_INCREASE FROM dba_segments where segment_type = 'TABLE' ORDER BY bytes desc) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
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

    function listLogcontent()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $url = $_REQUEST['url'];
        $count = $_REQUEST['count'];
        $search = $_REQUEST['search'];
        $typ = $_REQUEST['typ'];

        $ssh = new Net_SSH2($host, $port);
        $records = array();

        if (empty($ssh->server_identifier)) {
            $records[] = array('lines_id' => 0,
                'row' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme"
            );
        } else {
            if (!$ssh->login($user, $pass)) {
                $records[] = array('lines_id' => 0,
                    'row' => "Compte ou mot de passe invalide"
                );
            } else {
                $ssh->disableQuietMode();
                $cmd = "ls " . $url;
                $resp = trim($ssh->exec($cmd));

                if ($resp != $url) {
                    $records[] = array('lines_id' => 0,
                        'row' => "Fichier inexistant sur ce serveur"
                    );
                }
                else
                {
                    $limit = empty($_REQUEST['limit']) ? 1000 : $_REQUEST['limit'];
                    //$start = empty($_REQUEST['start']) ? ($count - $limit) : $_REQUEST['start'];
                    $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];

                    $file = '/dev/shm/' . substr(md5(rand()), 0, 7) . '.log';
                    if (!empty($search)) {
                        $cmd = 'strings ' . $url . ' | grep -i ' . $search . ' > ' . $file;
                        $ssh->exec($cmd);
                        $url = $file;
                        $start = 0;
                    } else {
                        $cmd = 'strings ' . $url . ' > ' . $file;
                        $ssh->exec($cmd);
                        $url = $file;
                    }

                    $cmd = "wc -l " . $url . " |awk '{print $1'}";
                    $resp = trim($ssh->exec($cmd));

                    $count = (int)$resp;

                    if($start >= $count)
                    {
                        $start = $count - $limit;
                    }

                    $end = $start + $limit;

                    $out = '/dev/shm/' . substr(md5(rand()), 0, 7) . '.log';
                    $cmd = "awk 'NR >= " . $start . " && NR <= " . $end . "' " . $url . " > " . $out;
                    $ssh->exec($cmd);

                    $cmd = "cat " . $out;
                    $resp = $ssh->exec($cmd);

                    $rows = explode("\n", $resp);

                    //var_dump($rows);

                    $index = 0;
                    foreach ($rows as $row) {
                        if(strpos(strtolower($row), 'unable') !== false || strpos(strtolower($row), 'suspended') !== false || strpos(strtolower($row), 'aborted') !== false || strpos(strtolower($row), 'error') !== false || strpos(strtolower($row), 'ora-') !== false || strpos(strtolower($row), 'failure') !== false || strpos(strtolower($row), 'tns-') !== false || strpos(strtolower($row), 'failed') !== false || strpos(strtolower($row), 'cannot') !== false)
                        {
                            $records[] = array('lines_id' => $index,
                                'row' => "<div style='white-space : normal'><span style='color:#ff0000;'>" . $row . "</span></div>"
                            );
                        }
                        else
                        {
                            $records[] = array('lines_id' => $index,
                                'row' => '</span><div style = "white-space : normal">' . $row . '</div>'
                            );
                        }

                        $index++;
                    }

                    $cmd = "rm -f " . $out . " " . $file;
                    $ssh->exec($cmd);
                }

                $ssh->disconnect();
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function deleteFile()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $url = $_REQUEST['url'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
            return false;
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
            } else {
                $ssh->disableQuietMode();

                $cmd = "rm -f " . $url;
                $feedback = trim($ssh->exec($cmd));

                $cmd = "ls " . $url;
                $resp = trim($ssh->exec($cmd));
                $ssh->disconnect();

                if ($resp != $url) {
                    $response = array('success' => true, 'feedback' => "Fichier " . $url . " supprime");
                } else {
                    $response = array('success' => false, 'feedback' => "Impossible de supprimer ce fichier : " . $feedback);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function deleteFolder()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $url = $_REQUEST['url'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
            return false;
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
            } else {
                $ssh->disableQuietMode();

                $cmd = "rm -rf " . $url;
                $feedback = trim($ssh->exec($cmd));

                $cmd = "ls " . $url;
                $resp = trim($ssh->exec($cmd));
                $ssh->disconnect();

                if ($resp != $url) {
                    $response = array('success' => true, 'feedback' => 'Repertoire ' . $url . ' supprime');
                } else {
                    $response = array('success' => false, 'feedback' => 'Impossible de supprimer ce repertoire : ' . $feedback);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function checkPath()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $dir = $_REQUEST['path'];
        $src = $_REQUEST['file'];
        $src_path = $_REQUEST['src_path'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'feedback' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme");
        } else {
            if (!$ssh->login($user, $pass)) {
                $response = array('success' => false, 'feedback' => "Compte ou mot de passe invalide");
            } else {
                $ssh->disableQuietMode();

                $cmd = "ls -l " . $dir . " | awk '{if (NR == 1) {print $1}}'";
                $resp = trim($ssh->exec($cmd));

                if ($resp != "total") {
                    $response = array('success' => false, 'feedback' => 'Ce repertoire est inexistant ...');
                } else {
                    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < 5; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }

                    $test = $dir . "/" . $randomString;
                    $cmd = "touch " . $test;
                    $resp = trim($ssh->exec($cmd));

                    $cmd = "ls " . $test;
                    $resp = trim($ssh->exec($cmd));

                    if ($resp != $test) {
                        $response = array('success' => false, 'feedback' => 'Vous ne pouvez pas ecrire dans le repertoire ' . $dir);
                    } else {
                        $cmd = "ls " . $src;
                        $resp = trim($ssh->exec($cmd));

                        if ($resp != $src) {
                            $response = array('success' => false, 'feedback' => 'Le fichier source ' . $src . ' est inexistant');
                        } else {
                            $cmd = "du -k " . $src . " |awk '{print $1}'";
                            $resp = trim($ssh->exec($cmd));

                            $src_size = (int)$resp;

                            $cmd = "df -PT " . $dir . " |awk '{if (NR == 2) {print $5}}'";
                            $resp = trim($ssh->exec($cmd));

                            $dest_size = (int)$resp;

                            if ($src_size > $dest_size) {
                                $response = array('success' => false, 'feedback' => "Ce FS n'a pas assez d'espace pour contenir ce fichier");
                            } else {
                                $response = array('success' => true, 'feedback' => 'OK');
                                $cmd = "nohup rm -f " . $test . " &";
                                $ssh->exec($cmd);
                            }
                        }
                    }
                }

                $ssh->disconnect();
            }
        }

        echo $toC_Json->encode($response);
    }

    function createFolder()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['server_port'];
        $user = $_REQUEST['server_user'];
        $pass = $_REQUEST['server_pass'];
        $path = $_REQUEST['path'];
        $folder = $_REQUEST['folder'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'feedback' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme");
        } else {
            if (!$ssh->login($user, $pass)) {
                $response = array('success' => false, 'feedback' => "Compte ou mot de passe invalide");
            } else {
                $ssh->disableQuietMode();

                $cmd = "ls -l " . $path . " | awk '{if (NR == 1) {print $1}}'";
                $resp = trim($ssh->exec($cmd));

                if ($resp != "total") {
                    $response = array('success' => false, 'feedback' => "Le repertoire " . $path . " est inexistant sur ce serveur ...");
                } else {
                    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < 5; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }

                    $test = $path . "/" . $randomString;
                    $cmd = "touch " . $test;
                    $resp = trim($ssh->exec($cmd));

                    $cmd = "ls " . $test;
                    $resp = trim($ssh->exec($cmd));

                    if ($resp != $test) {
                        $response = array('success' => false, 'feedback' => 'Vous ne pouvez pas ecrire dans le repertoire ' . $path);
                    } else {
                        $test = $path . "/" . $folder;

                        $cmd = "ls -l " . $test . " | awk '{if (NR == 1) {print $1}}'";
                        $resp = trim($ssh->exec($cmd));

                        if ($resp == "total") {
                            $response = array('success' => false, 'feedback' => 'Le repertoire ' . $test . ' existe deja');
                        } else {
                            $cmd = "mkdir " . $test;
                            $ssh->exec($cmd);

                            $response = array('success' => true, 'feedback' => 'OK');
                        }
                    }
                }

                $ssh->disconnect();
            }
        }

        echo $toC_Json->encode($response);
    }

    function loadPortlets()
    {
        global $toC_Json, $osC_Language;

        $portlets = 'overview:0,new_orders:1,new_customers:2,new_reviews:3,orders_statistics:4,last_visits:0';;

        if (!empty($portlets)) {
            $response = array('success' => true, 'portlets' => $portlets, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function batchDelete()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $files = $_REQUEST['files'];
        $folders = $_REQUEST['folders'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'feedback' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme");
        } else {
            if (!$ssh->login($user, $pass)) {
                $response = array('success' => false, 'feedback' => 'Compte ou mot de passe invalide');
            } else {
                $ssh->disableQuietMode();

                $rows = explode(";", $files);
                $feedback = '';

                foreach ($rows as $row) {
                    $cmd = "rm -f " . $row;
                    $resp = $ssh->exec($cmd);

                    $cmd = "ls " . $row;
                    $resp = trim($ssh->exec($cmd));

                    if ($resp == $row) {
                        $feedback = $feedback . " Le fichier " . $row . " n'a pas pu etre supprime ... ";
                    }
                }

                $rows = explode(";", $folders);

                foreach ($rows as $row) {
                    $cmd = "rm -rf \"" . $row . "\"";
                    $resp = $ssh->exec($cmd);

                    $cmd = "ls -l " . $row . " | awk '{if (NR == 1) {print $1}}'";
                    $resp = trim($ssh->exec($cmd));

                    if ($resp != "total") {
                        $feedback = $feedback . " Le dossier " . $row . " n'a pas pu etre supprime ... ";
                    }
                }

                $ssh->disconnect();

                $response = array('success' => true, 'feedback' => $feedback);
            }
        }

        echo $toC_Json->encode($response);
    }

    function fsUsage()
    {
        global $toC_Json, $osC_Database;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $typ = $_REQUEST['typ'];
        $servers_id = $_REQUEST['servers_id'];
        $snaps_id = $_REQUEST['snaps_id'];

        $total_dispo = 0;
        $total_used = 0;
        $total_size = 0;
        $start_date = date("Y-m-d H:i:s"); //"2015-03-30 15:20:50"

        $records = array();

        switch ($typ) {
            case "win":
                $command = "C:\\xampp\\htdocs\\dev\\tools\\psexec.exe \\\\" . $host . " -u " . $user . " -p " . $pass . " -c C:\\xampp\\htdocs\\dev\\tools\\psinfo.exe -d ";
                $resp = shell_exec($command);
                $end_date = date("Y-m-d H:i:s");
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

                            $total_dispo += (int)($free) / 1024 / 1024;
                            $total_used += (int)($used) / 1024 / 1024;
                            $total_size += (int)($size) / 1024 / 1024;

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

                $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur";
                return false;
            case "aix":
                $_SESSION['LAST_ERROR'] = "Ce systeme n'est pas encore supporte";
                return false;
            case "lin":
                $ssh = new Net_SSH2($host, $port);

                if (empty($ssh->server_identifier)) {
                    $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";

                    $data = $_REQUEST;
                    $data['state'] = 'down';
                    $data['comments'] = 'Impossible de se connecter au serveur';
                    $data['start_date'] = $start_date;

                    toC_Servers_Admin::saveServerState($data);

                    return false;
                } else {
                    if (!$ssh->login($user, $pass)) {
                        $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';

                        $data = $_REQUEST;
                        $data['state'] = 'down';
                        $data['comments'] = 'Compte ou mot de passe invalide';
                        $data['start_date'] = $start_date;

                        toC_Servers_Admin::saveServerState($data);
                    } else {
                        $ssh->disableQuietMode();

                        $cmd = "df -PT|awk '{if (NR > 1) {print \$1\";\"\$2\";\"\$3\";\"\$4\";\"\$5\";\"\$6\";\"\$7}}'";
                        $resp = $ssh->exec($cmd);
                        $ssh->disconnect();

                        $end_date = date("Y-m-d H:i:s");

                        $rows = explode("\n", $resp);

                        $index = 0;
                        foreach ($rows as $row) {
                            $record = explode(";", $row);

                            $records[] = array('fs' => $record[0],
                                'typ' => $record[1],
                                'size' => (int)($record[2]) / 1024,
                                'used' => (int)($record[3]) / 1024,
                                'dispo' => (int)($record[4]) / 1024,
                                'pct_used' => $record[5],
                                'mount' => $record[6]
                            );

                            $total_dispo += (int)($record[4]) / 1024 / 1024;
                            $total_used += (int)($record[3]) / 1024 / 1024;
                            $total_size += (int)($record[2]) / 1024 / 1024;

                            $data = $_REQUEST;
                            $data['fs_name'] = $record[0];
                            $data['space_total_mb'] = (int)($record[2]) / 1024;
                            $data['space_used_mb'] = (int)($record[3]) / 1024;
                            $data['space_dispo_mb'] = (int)($record[4]) / 1024;
                            $data['start_date'] = $start_date;
                            $data['end_date'] = $end_date;

                            toC_Servers_Admin::saveFsState($data);

                            $index++;
                        }

                        $data = $_REQUEST;
                        $data['state'] = 'up';
                        $data['comments'] = '';
                        $data['start_date'] = $start_date;

                        toC_Servers_Admin::saveServerState($data);

                        $data = $_REQUEST;
                        $data['space_total_gb'] = $total_size;
                        $data['space_used_gb'] = $total_used;
                        $data['space_dispo_gb'] = $total_dispo;
                        $data['start_date'] = $start_date;
                        $data['end_date'] = $end_date;

                        toC_Servers_Admin::saveServerSpaceUsage($data);
                    }
                }

                $ssh->disconnect();

                break;
        }

        session_write_close();

        return 0;
    }

    function collectFsUsage()
    {
        global $toC_Json, $osC_Database;

        $query = "INSERT INTO delta_snaps (job_id,status,running_host,created_date) VALUES (:job_id,:status,:running_host,:created_date)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':job_id', "space_usage");
        $Qserver->bindValue(':status', "ready");
        $Qserver->bindValue(':running_host', "10.100.18.19");
        $Qserver->bindValue(':created_date', date("Y-m-d H:i:s"));
        $Qserver->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'msg' => "Impossible de creer un job de collection de l'utilisation des espaces");
            //var_dump($osC_Database);
        } else {
            $snaps_id = $osC_Database->nextID();

            $query = "SELECT * FROM delta_servers where typ != 'win' order by label";

            $QServers = $osC_Database->query($query);

            $servers = array();
            $recs = array();

            while ($QServers->next()) {

                $servers[] = array('servers_id' => $QServers->ValueInt('servers_id'),
                    'host' => $QServers->Value('host'),
                    'user' => $QServers->Value('user'),
                    'pass' => $QServers->Value('pass'),
                    'typ' => $QServers->Value('typ'),
                    'label' => $QServers->Value('label'),
                    'port' => $QServers->Value('port')
                );

            }

            $ssh = new Net_SSH2(REPORT_RUNNER, '22');

            if (empty($ssh->server_identifier)) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
            } else {
                if (!$ssh->login("guyfomi", "12345")) {
                    $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
                } else {
                    $ssh->disableQuietMode();

                    foreach ($servers as $server) {
                        $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=servers&action=fs_usage&host='" . $server['host'] . "&port=" . $server['port'] . "&user=" . $server['user'] . "&pass=" . $server['pass'] . "&typ=" . $server['typ'] . "&servers_id=" . $server['servers_id'] . "&snaps_id=" . $snaps_id . " &";
                        $ssh->exec($cmd);
                    }

                    $ssh->disconnect();
                }

                $query = "SELECT
  delta_servers.label,
  delta_fs_usage.snaps_id,
  delta_fs_usage.servers_id,
  delta_fs_usage.fs_name,
  delta_fs_usage.space_total_mb,
  delta_fs_usage.space_used_mb,
  delta_fs_usage.space_dispo_mb,
  delta_fs_usage.start_date,
  delta_fs_usage.end_date
FROM
  delta_fs_usage
  INNER JOIN
  delta_servers
  ON (
    delta_fs_usage.servers_id = delta_servers.servers_id
  )
WHERE delta_fs_usage.start_date = (select max(start_date) from delta_fs_usage where snaps_id < " . $snaps_id . ") order by label,fs_name";

                $QServers = $osC_Database->query($query);

                while ($QServers->next()) {

                    $recs[] = array('servers_id' => $QServers->ValueInt('servers_id'),
                        'label' => $QServers->Value('label'),
                        'fs_name' => $QServers->Value('fs_name'),
                        'space_total' => $QServers->ValueInt('space_total_mb'),
                        'space_used' => $QServers->ValueInt('space_used_mb'),
                        'space_dispo' => $QServers->ValueInt('space_dispo_mb'),
                        'start_date' => $QServers->Value('start_date'),
                        'end_date' => $QServers->Value('end_date')
                    );

                }
            }

            $output = '';

            foreach ($servers as $server) {
                $output = $output . "<table border='1' cellpadding='1' cellspacing='1' style='width:100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(102, 102, 102);'><span style='font-size:20px;'><strong><span style='color:#FFF0F5;'>" . $server['label'] . " (" . $server['host'] . ")</span></strong></span></td></tr><tr><td><table border='0' cellpadding='1' cellspacing='1' style='width:100%;'><tbody><tr><td style='text-align: center;width:20%;background-color: rgb(0, 0, 102);'><strong><span style='color:#FFF0F5;'>Montage</span></strong></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:20%'><strong><span style='color:#FFF0F5;'>Total (MB)</span></strong></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:20%'><span style='color:#FFF0F5;'><strong>Utilise (MB)</strong></span></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:20%'><span style='color:#FFF0F5;'><strong>LIBRE (MB)</strong></span></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:%'><span style='color:#FFF0F5;'><strong>%</strong></span></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:15%'><span style='color:#FFF0F5;'><strong></strong></span></td></tr>";

                foreach ($recs as $usage) {
                    if ($usage['servers_id'] == $server['servers_id']) {
                        $pct = $usage['space_used'] * 100 / $usage['space_total'];
                        $pctfree = 100 - (int)$pct;
                        $pctcolor = "green";
                        if ($pct > 80 && $pct <= 90) {
                            $pctcolor = "yellow";
                        }

                        if ($pct > 90) {
                            $pctcolor = "red";
                        }

                        $output = $output . "<tr><td style='text-align: center;'><strong>" . $usage['fs_name'] . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_total'], 0, ' ', ' ') . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_used'], 0, ' ', ' ') . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_dispo'], 0, ' ', ' ') . "</strong></td><td style='text-align: center;width:100px'><strong>" . (int)$pct . "%</strong></td><td style='text-align: center;'><table align='left' border='0' cellpadding='0' cellspacing='0' style='width:100px;'><tbody><tr><td style='border-color: rgb(204, 204, 204); width: " . (int)$pct . "px; background-color:" . $pctcolor . ";'>&nbsp;</td><td style='border-color: rgb(204, 204, 204); width: " . (int)$pctfree . "; background-color: rgb(204, 204, 204);'>&nbsp;</td></tr></tbody></table></td></tr>";
                    }
                }

                $output = $output . "</tbody></table></td></tr></tbody></table><p></p>";

            }

            $data = $_REQUEST;
            $data['body'] = $output;

            $response = toC_Reports_Admin::sendEmail($data);
        }

        echo $toC_Json->encode($response);
    }

    function collectFsCritiques()
    {
        global $toC_Json, $osC_Database;

        $query = "INSERT INTO delta_snaps (job_id,status,running_host,created_date) VALUES (:job_id,:status,:running_host,:created_date)";

        $Qserver = $osC_Database->query($query);

        $Qserver->bindValue(':job_id', "space_usage");
        $Qserver->bindValue(':status', "ready");
        $Qserver->bindValue(':running_host', "10.100.18.19");
        $Qserver->bindValue(':created_date', date("Y-m-d H:i:s"));
        $Qserver->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'msg' => "Impossible de creer un job de collection de l'utilisation des espaces");
            //var_dump($osC_Database);
        } else {
            $snaps_id = $osC_Database->nextID();

            $query = "SELECT * FROM delta_servers where typ != 'win' order by label";

            $QServers = $osC_Database->query($query);

            $servers = array();
            $recs = array();

            while ($QServers->next()) {

                $servers[] = array('servers_id' => $QServers->ValueInt('servers_id'),
                    'host' => $QServers->Value('host'),
                    'user' => $QServers->Value('user'),
                    'pass' => $QServers->Value('pass'),
                    'typ' => $QServers->Value('typ'),
                    'label' => $QServers->Value('label'),
                    'port' => $QServers->Value('port')
                );

            }

            $ssh = new Net_SSH2(REPORT_RUNNER, '22');

            if (empty($ssh->server_identifier)) {
                $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, veuillez contacter votre administrateur systeme', 'subscriptions_id' => null, 'status' => null);
            } else {
                if (!$ssh->login("guyfomi", "12345")) {
                    $response = array('success' => false, 'msg' => 'Impossible de se connecter au serveur pour generer cet etat, Compte ou mot de passe invalide', 'subscriptions_id' => null, 'status' => null);
                } else {
                    $ssh->disableQuietMode();

                    foreach ($servers as $server) {
                        $cmd = "nohup curl '" . HTTP_SERVER . "/" . HTTP_COOKIE_PATH . "/admin/json.php' --data 'module=servers&action=fs_usage&host='" . $server['host'] . "&port=" . $server['port'] . "&user=" . $server['user'] . "&pass=" . $server['pass'] . "&typ=" . $server['typ'] . "&servers_id=" . $server['servers_id'] . "&snaps_id=" . $snaps_id . " &";
                        $ssh->exec($cmd);
                    }

                    $ssh->disconnect();
                }

                $query = "SELECT
  delta_servers.label,
  delta_fs_usage.snaps_id,
  delta_fs_usage.servers_id,
  delta_fs_usage.fs_name,
  delta_fs_usage.space_total_mb,
  delta_fs_usage.space_used_mb,
  delta_fs_usage.space_dispo_mb,
  delta_fs_usage.start_date,
  delta_fs_usage.end_date
FROM
  delta_fs_usage
  INNER JOIN
  delta_servers
  ON (
    delta_fs_usage.servers_id = delta_servers.servers_id
  )
WHERE delta_fs_usage.start_date = (select max(start_date) from delta_fs_usage where snaps_id < " . $snaps_id . ") order by label,fs_name";

                $QServers = $osC_Database->query($query);

                while ($QServers->next()) {

                    $recs[] = array('servers_id' => $QServers->ValueInt('servers_id'),
                        'label' => $QServers->Value('label'),
                        'fs_name' => $QServers->Value('fs_name'),
                        'space_total' => $QServers->ValueInt('space_total_mb'),
                        'space_used' => $QServers->ValueInt('space_used_mb'),
                        'space_dispo' => $QServers->ValueInt('space_dispo_mb'),
                        'start_date' => $QServers->Value('start_date'),
                        'end_date' => $QServers->Value('end_date')
                    );

                }
            }

            $output = '';
            $count = 0;

            foreach ($servers as $server) {
                $cnt = 0;
                $out = '';

                foreach ($recs as $usage) {
                    if ($usage['servers_id'] == $server['servers_id']) {
                        $pct = $usage['space_used'] * 100 / $usage['space_total'];
                        $pctfree = 100 - (int)$pct;
                        $pctcolor = "green";
                        if ($pct > 80 && $pct <= 90) {
                            $pctcolor = "yellow";
                        }

                        if (((int)$usage['space_dispo'] < 500) && ($pct > 90 && (int)$usage['space_dispo'] < 5000)) {
                            $count++;
                            $cnt++;
                            $pctcolor = "red";
                            $out = $out . "<tr><td style='text-align: center;'><strong>" . $usage['fs_name'] . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_total'], 0, ' ', ' ') . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_used'], 0, ' ', ' ') . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_dispo'], 0, ' ', ' ') . "</strong></td><td style='text-align: center;width:100px'><strong>" . (int)$pct . "%</strong></td><td style='text-align: center;'><table align='left' border='0' cellpadding='0' cellspacing='0' style='width:100px;'><tbody><tr><td style='border-color: rgb(204, 204, 204); width: " . (int)$pct . "px; background-color:" . $pctcolor . ";'>&nbsp;</td><td style='border-color: rgb(204, 204, 204); width: " . (int)$pctfree . "; background-color: rgb(204, 204, 204);'>&nbsp;</td></tr></tbody></table></td></tr>";
                            //$output = $output . "<tr><td style='text-align: center;'><strong>" . $usage['fs_name'] . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_total'],0, ' ', ' ') . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_used'],0, ' ', ' ') . "</strong></td><td style='text-align: center;'><strong>" . number_format($usage['space_dispo'],0, ' ', ' ') . "</strong></td><td style='text-align: center;width:100px'><strong>" . (int)$pct . "%</strong></td><td style='text-align: center;'><table align='left' border='0' cellpadding='0' cellspacing='0' style='width:100px;'><tbody><tr><td style='border-color: rgb(204, 204, 204); width: " . (int)$pct . "px; background-color:" . $pctcolor . ";'>&nbsp;</td><td style='border-color: rgb(204, 204, 204); width: " . (int)$pctfree . "; background-color: rgb(204, 204, 204);'>&nbsp;</td></tr></tbody></table></td></tr>";
                        }
                    }
                }

                if ($cnt > 0) {
                    $output = $output . "<table border='1' cellpadding='1' cellspacing='1' style='width:100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(102, 102, 102);'><span style='font-size:20px;'><strong><span style='color:#FFF0F5;'>" . $server['label'] . " (" . $server['host'] . ")</span></strong></span></td></tr><tr><td><table border='0' cellpadding='1' cellspacing='1' style='width:100%;'><tbody><tr><td style='text-align: center;width:20%;background-color: rgb(0, 0, 102);'><strong><span style='color:#FFF0F5;'>Montage</span></strong></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:20%'><strong><span style='color:#FFF0F5;'>Total (MB)</span></strong></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:20%'><span style='color:#FFF0F5;'><strong>Utilise (MB)</strong></span></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:20%'><span style='color:#FFF0F5;'><strong>LIBRE (MB)</strong></span></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:%'><span style='color:#FFF0F5;'><strong>%</strong></span></td><td style='text-align: center; background-color: rgb(0, 0, 102);width:15%'><span style='color:#FFF0F5;'><strong></strong></span></td></tr>";
                    $output = $output . $out;
                    $output = $output . "</tbody></table></td></tr></tbody></table><p></p>";
                }
            }

            if ($count > 0) {
                $data = $_REQUEST;
                $data['body'] = $output;
                $response = toC_Reports_Admin::sendEmail($data);
            } else {
                $response = array('success' => true, 'msg' => 'OK');
            }
        }

        echo $toC_Json->encode($response);
    }

    function listPs()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $typ = $_REQUEST['typ'];

        $records = array();

        switch ($typ) {
            case "win":
                $_SESSION['LAST_ERROR'] = "Ce systeme n'est pas encore supporte";
                return false;
            case "aix":
                $_SESSION['LAST_ERROR'] = "Ce systeme n'est pas encore supporte";
                return false;
            case "lin":
                $ssh = new Net_SSH2($host, $port);

                if (empty($ssh->server_identifier)) {
                    $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
                    return false;
                } else {
                    if (!$ssh->login($user, $pass)) {
                        $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
                    } else {
                        $ssh->disableQuietMode();

                        $cmd = "ps aux | sort -nrk 3,3 | head -n 40 | grep -v ^USER";
                        $resp = $ssh->exec($cmd);
                        $ssh->disconnect();

                        $rows = explode("\n", $resp);

                        $index = 0;
                        foreach ($rows as $row) {
                            $record = explode(" ", $row);

                            $records[] = array('fs' => $record[0],
                                'typ' => $record[1],
                                'size' => (int)($record[2]) / 1024,
                                'used' => (int)($record[3]) / 1024,
                                'dispo' => (int)($record[4]) / 1024,
                                'pct_used' => $record[5],
                                'mount' => $record[6]
                            );

                            $index++;
                        }
                    }
                }
                break;
        }

        $response = array(EXT_JSON_READER_TOTAL => $index,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listFs()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $typ = $_REQUEST['typ'];

        $records = array();

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

                $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur";
                return false;
            case "aix":
                $_SESSION['LAST_ERROR'] = "Ce systeme n'est pas encore supporte";
                return false;
            case "lin":
                $ssh = new Net_SSH2($host, $port);

                if (empty($ssh->server_identifier)) {
                    $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
                    return false;
                } else {
                    if (!$ssh->login($user, $pass)) {
                        $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
                    } else {
                        $ssh->disableQuietMode();

                        $cmd = "df -PT|awk '{if (NR > 1) {print \$1\";\"\$2\";\"\$3\";\"\$4\";\"\$5\";\"\$6\";\"\$7}}'";
                        $resp = $ssh->exec($cmd);
                        $ssh->disconnect();

                        $rows = explode("\n", $resp);

                        $index = 0;
                        foreach ($rows as $row) {
                            $record = explode(";", $row);

                            $records[] = array('fs' => $record[0],
                                'typ' => $record[1],
                                'size' => (int)($record[2]) / 1024,
                                'used' => (int)($record[3]) / 1024,
                                'dispo' => (int)($record[4]) / 1024,
                                'pct_used' => $record[5],
                                'mount' => $record[6]
                            );

                            $index++;
                        }
                    }
                }
                break;
        }

        $response = array(EXT_JSON_READER_TOTAL => $index,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function memUsage()
    {
        global $toC_Json;

        $db_host = $_REQUEST['db_host'];
        $os_user = $_REQUEST['server_user'];
        $os_pass = $_REQUEST['server_pass'];

        $usage = array();
        $pct = 0;

        $ssh = new Net_SSH2($db_host,22,5);
        if (!$ssh->login($os_user, $os_pass)) {
            $comment = 'Impossible de se connecter au serveur ' . $db_host;
            $pct = null;
        } else {
            $ssh->disableQuietMode();

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

        $ssh->disconnect();

        $response = array(EXT_JSON_READER_TOTAL => count($usage),
            EXT_JSON_READER_ROOT => $usage, 'comment' => $comment, 'pct' => $pct);

        session_write_close();

        echo $toC_Json->encode($response);
    }

    function diskActivity()
    {
        global $toC_Json;

        $db_host = $_REQUEST['db_host'];
        $os_user = $_REQUEST['server_user'];
        $os_pass = $_REQUEST['server_pass'];

        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($db_host);
        if (!$ssh->login($os_user, $os_pass)) {
            $comment = 'Impossible de se connecter au serveur ' . $db_host;
            $disks = null;
        } else {
            $ssh->disableQuietMode();
            $file = "/dev/shm/" . $randomString;

            $cmd = "cat /proc/diskstats | awk '{print \$3\";\"\$7\";\"\$11}'";
            $data = $ssh->exec($cmd);
            file_put_contents($file, $data);

            $content = file($file);

            $stat1 = toC_Servers_Admin::GetDisksInformation($content);

            sleep(1);

            $data = $ssh->exec($cmd);
            file_put_contents($file, $data);

            $content = file($file);

            $stat2 = toC_Servers_Admin::GetDisksInformation($content);

            $disks = toC_Servers_Admin::GetDiskActivity($stat1, $stat2);
            $comment = '';

            unlink($file);
        }

        $ssh->disconnect();

        $response = array(EXT_JSON_READER_TOTAL => count($disks),
            EXT_JSON_READER_ROOT => $disks, 'comment' => $comment);

        session_write_close();

        echo $toC_Json->encode($response);
    }

    function cpuUsage()
    {
        global $toC_Json;

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

        $ssh = new Net_SSH2($db_host,22,5);
        if (!$ssh->login($os_user, $os_pass)) {
            $comment = 'Impossible de se connecter au serveur ' . $db_host;
            $pct = null;
        } else {
            $ssh->disableQuietMode();
            $file = "/dev/shm/" . $randomString;

            $cmd = "cat /proc/stat";
            $data = $ssh->exec($cmd);
            file_put_contents($file, $data);

            $content = file($file);

            $stat1 = toC_Servers_Admin::GetCoreInformation($content);

            //var_dump($stat1);

            sleep(1);

            $data = $ssh->exec($cmd);
            file_put_contents($file, $data);

            $content = file($file);

            //var_dump($data);

            $stat2 = toC_Servers_Admin::GetCoreInformation($content);

            //var_dump($stat2);

            $pct = toC_Servers_Admin::GetCpuPercentages($stat1, $stat2);
            $comment = '';

            unlink($file);
        }

        $ssh->disconnect();

        $response = array(EXT_JSON_READER_TOTAL => count($pct),
            EXT_JSON_READER_ROOT => $pct, 'comment' => $comment);

        session_write_close();

        echo $toC_Json->encode($response);
    }

    function netUsage()
    {
        $db_host = $_REQUEST['db_host'];
        $os_user = $_REQUEST['server_user'];
        $os_pass = $_REQUEST['server_pass'];

        global $toC_Json;

        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ssh = new Net_SSH2($db_host,22,5);
        if (!$ssh->login($os_user, $os_pass)) {
            $comment = 'Impossible de se connecter au serveur ' . $db_host;
            $net = null;
        } else {
            $ssh->disableQuietMode();
            $file = "/dev/shm/" . $randomString;

            $cmd = "cat /proc/net/dev | awk '{if (NR > 2) print \$1\";\"\$9}'|awk -F \":\" '{if($1 != \"bond0\") print \$2}'";
            $data = $ssh->exec($cmd);
            file_put_contents($file, $data);

            $content = file($file);

            $stat1 = toC_Servers_Admin::GetNetInformation($content);

            //var_dump($stat1);

            sleep(1);

            $data = $ssh->exec($cmd);
            file_put_contents($file, $data);

            $content = file($file);

            //var_dump($data);

            $stat2 = toC_Servers_Admin::GetNetInformation($content);

            //var_dump($stat2);

            $net = toC_Servers_Admin::GetNetUsage($stat1, $stat2);
            $comment = '';

            //var_dump($pct);
            unlink($file);
        }

        $ssh->disconnect();

        $response = array(EXT_JSON_READER_TOTAL => count($net),
            EXT_JSON_READER_ROOT => $net, 'comment' => $comment);

        echo $toC_Json->encode($response);
    }

    function listTopfs()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
        $typ = $_REQUEST['typ'];

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

                $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur";
                return false;
            case "aix":
                $_SESSION['LAST_ERROR'] = "Ce systeme n'est pas encore supporte";
                return false;
            case "lin":
                $ssh = new Net_SSH2($host, $port,5);

                if (empty($ssh->server_identifier)) {
                    $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";

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
                        $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
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
                                'qtip' => toC_Servers_Admin::formatSizeUnits(($free)) . " libre sur " . toC_Servers_Admin::formatSizeUnits(((int)($record[2]) * 1024))
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

//                        $top = 0;
//                        foreach ($records as $rec) {
//                            $pcts = explode("%", $rec['pct_used']);
//                            if ($pcts[0] > $top && $pcts[0] < $recs[2]['pct_used']) {
//                                $top = $pcts[0];
//
//                                $recs[3] = array('fs' => $rec['fs'],
//                                    'typ' => $rec['typ'],
//                                    'size' => $rec['size'],
//                                    'used' => $rec['used'],
//                                    'dispo' => $rec['dispo'],
//                                    'pct_used' => $pcts[0],
//                                    'mount' => $rec['mount'],
//                                    'qtip' => $rec['qtip'],
//                                    'rest' => $rec['pct_used'] . ';' . $rec['size'] . ';' . $rec['dispo']
//                                );
//                            }
//                        }

                        $response = array(EXT_JSON_READER_TOTAL => 3,
                            EXT_JSON_READER_ROOT => $recs);
                    }
                }

                $ssh->disconnect();

                break;
        }

        session_write_close();

        echo $toC_Json->encode($response);
    }

    function watchFilemove()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['server_port'];
        $user = $_REQUEST['server_user'];
        $pass = $_REQUEST['server_pass'];
        $src_size = $_REQUEST['src_size'];
        $dir = $_REQUEST['dir'];
        $src = $_REQUEST['url'];
        $file_name = $_REQUEST['file_name'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'feedback' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme");
        } else {
            if (!$ssh->login($user, $pass)) {
                $response = array('success' => false, 'feedback' => 'Compte ou mot de passe invalide');
            } else {
                $ssh->disableQuietMode();

                $cmd = "ls " . $src;
                $resp = trim($ssh->exec($cmd));

                if ($resp != $src) {
                    $dest_size = $src_size;
                    $response = array('success' => true, 'feedback' => 'Deplacement termine ', 'src_size' => $src_size, 'dest_size' => $dest_size);
                } else {
                    $cmd = "du -m " . $dir . "/" . $file_name . " |awk '{print $1}'";
                    $resp = trim($ssh->exec($cmd));

                    $dest_size = (int)$resp;

                    $feedback = ($src_size == $dest_size) ? 'Deplacement termine ' : 'Deplacement en cours ...';

                    $response = array('success' => true, 'feedback' => $feedback, 'src_size' => $src_size, 'dest_size' => $dest_size);
                }

                $ssh->disconnect();
            }
        }

        echo $toC_Json->encode($response);
    }

    function watchFoldermove()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['server_port'];
        $user = $_REQUEST['server_user'];
        $pass = $_REQUEST['server_pass'];
        $src_size = $_REQUEST['src_size'];
        $dir = $_REQUEST['dir'];
        $src = $_REQUEST['url'];
        $file_name = $_REQUEST['file_name'];

        $dest = $dir . "/" . $file_name;
        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $response = array('success' => false, 'feedback' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme");
        } else {
            if (!$ssh->login($user, $pass)) {
                $response = array('success' => false, 'feedback' => 'Compte ou mot de passe invalide');
            } else {
                $ssh->disableQuietMode();

                $cmd = "ls -l " . $src . " | awk '{if (NR == 1) {print $1}}'";
                $resp = trim($ssh->exec($cmd));

                if ($resp != "total") {
                    $dest_size = $src_size;
                    $response = array('success' => true, 'feedback' => 'Deplacement termine ', 'src_size' => $src_size, 'dest_size' => $dest_size);
                } else {
                    $cmd = " du --max-depth=0 " . $dest . " | awk '{print $1}'";
                    $resp = trim($ssh->exec($cmd));

                    $dest_size = (int)$resp;

                    $feedback = ($src_size == $dest_size) ? 'Deplacement termine ' : 'Deplacement en cours ...';

                    $response = array('success' => true, 'feedback' => $feedback, 'src_size' => $src_size, 'dest_size' => $dest_size);
                }

                $ssh->disconnect();
            }
        }

        echo $toC_Json->encode($response);
    }

    function moveFile()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['server_port'];
        $user = $_REQUEST['server_user'];
        $pass = $_REQUEST['server_pass'];
        $src = $_REQUEST['url'];
        $dir = $_REQUEST['dir'];
        $file_name = $_REQUEST['file_name'];

        $dest = $dir . "/" . $file_name;

        $ssh = new Net_SSH2($host, $port);

        if (empty($src)) {
            $response = array('success' => false, 'feedback' => "Veuillez renseigner le fichier source !!!");
        } else {
            if (empty($file_name)) {
                $response = array('success' => false, 'feedback' => "Veuillez renseigner le fichier destination !!!");
            } else {
                if (empty($dir)) {
                    $response = array('success' => false, 'feedback' => "Veuillez renseigner le repertoire destination !!!");
                } else {
                    if (empty($ssh->server_identifier)) {
                        $response = array('success' => false, 'feedback' => "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme");
                    } else {
                        if (!$ssh->login($user, $pass)) {
                            $response = array('success' => false, 'feedback' => "Compte ou mot de passe invalide");
                        } else {
                            $ssh->disableQuietMode();

                            $cmd = "ls -l " . $dest . " | awk '{if (NR == 1) {print $1}}'";
                            $resp = trim($ssh->exec($cmd));

                            if ($resp == "total") {
                                $response = array('success' => false, 'feedback' => 'Le fichier de destination est un repertoire ...');
                            } else {
                                $cmd = "ls -l " . $dir . " | awk '{if (NR == 1) {print $1}}'";
                                $resp = trim($ssh->exec($cmd));

                                if ($resp != "total") {
                                    $response = array('success' => false, 'feedback' => 'Le repertoire de destination est inexistant ...');
                                } else {
                                    $test = $dir . "/aaa.txt";
                                    $cmd = "touch " . $test;
                                    $resp = trim($ssh->exec($cmd));

                                    $cmd = "ls " . $test;
                                    $resp = trim($ssh->exec($cmd));

                                    if ($resp != $test) {
                                        $response = array('success' => false, 'feedback' => 'Vous ne pouvez pas ecrire dans le repertoire ... ' . $dir);
                                    } else {
                                        $cmd = "ls " . $dest;
                                        $resp = trim($ssh->exec($cmd));

                                        if ($resp == $dest) {
                                            $response = array('success' => false, 'feedback' => 'Le fichier de destination existe deja ...');
                                        } else {
                                            $cmd = "ls " . $src;
                                            $resp = trim($ssh->exec($cmd));

                                            if ($resp != $src) {
                                                $response = array('success' => false, 'feedback' => 'Le fichier source est inexistant ...');
                                            } else {
                                                $cmd = "du -m " . $src . " |awk '{print $1}'";
                                                $resp = trim($ssh->exec($cmd));

                                                $src_size = (int)$resp;

                                                $cmd = "nohup mv " . $src . " " . $dest . " &";
                                                $resp = trim($ssh->exec($cmd));

                                                $cmd = "du -m " . $dest . " |awk '{print $1}'";
                                                $resp = trim($ssh->exec($cmd));

                                                $dest_size = (int)$resp;

                                                $pct = $src_size * 100 / $dest_size;

                                                $feedback = ($src_size == $dest_size) ? 'Deplacement termine ' : $pct . '% ==> Deplacement en cours ...';

                                                $response = array('success' => true, 'feedback' => $feedback, 'src_size' => $src_size, 'dest_size' => $dest_size);
                                            }
                                        }
                                    }
                                }
                            }

                            $ssh->disconnect();
                        }
                    }
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function run()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['server_port'];
        $user = $_REQUEST['server_user'];
        $pass = $_REQUEST['server_pass'];
        $cmd = $_REQUEST['cmd'];

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
            $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
                $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
            } else {
                $ssh->disableQuietMode();

                $resp = trim($ssh->exec($cmd));
                $ssh->disconnect();
                $response = array('success' => true, 'feedback' => $resp);
            }
        }

        echo $toC_Json->encode($response);
    }

    function moveFolder()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['server_port'];
        $user = $_REQUEST['server_user'];
        $pass = $_REQUEST['server_pass'];
        $src = $_REQUEST['url'];
        $dir = $_REQUEST['dir'];
        $file_name = $_REQUEST['file_name'];

        $dest = $dir . "/" . $file_name . "/";
        $src = $src . "/";

        $ssh = new Net_SSH2($host, $port);

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
            return false;
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
            } else {
                $ssh->disableQuietMode();

                $cmd = "ls -l " . $dir . " | awk '{if (NR == 1) {print $1}}'";
                $resp = trim($ssh->exec($cmd));

                if ($resp != "total") {
                    $response = array('success' => false, 'feedback' => 'Le repertoire de destination est inexistant ...');
                } else {
                    $cmd = "ls -l " . $dest . " | awk '{if (NR == 1) {print $1}}'";
                    $resp = trim($ssh->exec($cmd));

                    if ($resp == "total") {
                        $response = array('success' => false, 'feedback' => 'Le repertoire de destination existe deja ...');
                    } else {
                        $test = $dir . "/aaa.txt";
                        $cmd = "touch " . $test;
                        $resp = trim($ssh->exec($cmd));

                        $cmd = "ls " . $test;
                        $resp = trim($ssh->exec($cmd));

                        if ($resp != $test) {
                            $response = array('success' => false, 'feedback' => 'Vous ne pouvez pas ecrire dans le repertoire ... ' . $dir);
                        } else {
                            $cmd = "ls -l " . $src . " | awk '{if (NR == 1) {print $1}}'";
                            $resp = trim($ssh->exec($cmd));

                            if ($resp != "total") {
                                $response = array('success' => false, 'feedback' => 'Le repertoire source est inexistant ... ' . $resp);
                            } else {
                                $cmd = " du --max-depth=0 " . $src . " | awk '{print \$1}'";
                                $resp = trim($ssh->exec($cmd));

                                $src_size = (int)$resp;

                                $cmd = "nohup mv " . $src . " " . $dir . "/ &";
                                $resp = trim($ssh->exec($cmd));

                                $cmd = " du --max-depth=0 " . $dest . " | awk '{print \$1}'";
                                $resp = trim($ssh->exec($cmd));

                                $dest_size = (int)$resp;

                                $feedback = ($src_size == $dest_size) ? 'Deplacement termine ' : 'Deplacement en cours ...';

                                $response = array('success' => true, 'feedback' => $feedback, 'src_size' => $src_size, 'dest_size' => $dest_size);
                            }
                        }
                    }
                }

                $ssh->disconnect();
            }
        }

        echo $toC_Json->encode($response);
    }

    function listDir()
    {
        global $toC_Json;

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['server_user'];
        $pass = $_REQUEST['server_pass'];
        $path = $_REQUEST['path'];

        $ssh = new Net_SSH2($host, $port);
        $records = array();
        $index = 0;

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
            return false;
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
            } else {
                $ssh->disableQuietMode();

                $cmd = "ls -lash " . $path . " |awk '{if (NR > 1) {print \$1\";\"\$2\";\"\$3\";\"\$4\";\"\$5\";\"\$6\";\"\$7\";\"\$8\";\"\$9\";\"\$10}}'";

                $resp = $ssh->exec($cmd);

                $rows = explode("\n", $resp);

                foreach ($rows as $row) {
                    $record = explode(";", $row);
                    $size = $record[5];
                    $icon = osc_icon('file.png');
                    $typ = 'file';

                    $type = $record[1];
                    $type = $type[0];
                    if ($type == 'd') {
                        $size = 0;
                        $icon = osc_icon('folder_red.png');
                        $typ = 'folder';

                        if ($record[9] != '.' && $record[9] != '..') {
                            $cmd = " du --max-depth=0 -m " . $path . "/" . $record[9] . " | awk '{print \$1}'";
                            $size = (int)($ssh->exec($cmd)) . "M";
                        }
                    }

                    $records[] = array('permission' => $record[1],
                        'owner' => $record[3],
                        'group' => $record[4],
                        'icon' => $icon,
                        'size' => $size,
                        'date_mod' => $record[6] . " " . $record[7] . " " . $record[8],
                        'file_name' => $record[9],
                        'type' => $typ
                    );

                    $index++;
                }

                $ssh->disconnect();
            }
        }

        if (isset($_REQUEST['show_files']) && !empty($_REQUEST['show_files']) && $_REQUEST['show_files'] == 'false') {
            $index = 0;
            $recs = array();

            foreach ($records as $rec) {
                if ($rec['type'] == 'folder') {
                    $recs[] = $rec;
                }
            }

            $records = $recs;
        }

        $response = array(EXT_JSON_READER_TOTAL => $index,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listLogs()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $host = $_REQUEST['host'];
        $port = $_REQUEST['port'];
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];

        $ssh = new Net_SSH2($host, $port);
        $records = array();

        if (empty($ssh->server_identifier)) {
            $_SESSION['LAST_ERROR'] = "Impossible de se connecter au serveur, veuillez contacter votre administrateur systeme";
        } else {
            if (!$ssh->login($user, $pass)) {
                $_SESSION['LAST_ERROR'] = 'Compte ou mot de passe invalide';
                return false;
            } else {
                $ssh->disableQuietMode();

                $QList = $osC_Database->query("select a.*, cd.*,c.*, atoc.*,s.host,s.user,s.pass,s.port from :table_logs a left join :table_content c on a.logs_id = c.content_id left join :table_servers s on a.servers_id = s.servers_id  left join  :table_content_description cd on a.logs_id = cd.content_id left join :table_content_to_categories atoc on atoc.content_id = a.logs_id  where cd.language_id = :language_id and atoc.content_type = 'logs' and c.content_type = 'logs' and a.content_id = :content_id and a.content_type = :content_type and cd.content_type = 'logs'");

                if ($current_category_id != 0) {
                    $QList->appendQuery('and atoc.categories_id = :categories_id ');
                    $QList->bindInt(':categories_id', $current_category_id);
                }

                if (!empty($_REQUEST['search'])) {
                    $QList->appendQuery('and cd.content_name like :content_name');
                    $QList->bindValue(':content_name', '%' . $_REQUEST['search'] . '%');
                }

                $QList->appendQuery('order by cd.content_name ');
                $QList->bindValue(':content_type', $_REQUEST['content_type']);
                $QList->bindInt(':content_id', $_REQUEST['content_id']);
                $QList->bindTable(':table_logs', 'delta_log');
                $QList->bindTable(':table_servers', TABLE_SERVERS);
                $QList->bindTable(':table_content', TABLE_CONTENT);
                $QList->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
                $QList->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
                $QList->bindInt(':language_id', $osC_Language->getID());
                $QList->setExtBatchLimit($start, $limit);
                $QList->execute();

                while ($QList->next()) {
                    $url = $QList->Value('url');
                    $cmd = "du -m " . $url . " |awk '{print $1'}";
                    $resp = trim($ssh->exec($cmd));

                    $size = (int)$resp;

                    $cmd = "wc -l " . $url . " |awk '{print $1'}";
                    $resp = trim($ssh->exec($cmd));

                    $lc = (int)$resp;

                    if (isset($_REQUEST['permissions'])) {
                        $permissions = explode(',', $_REQUEST['permissions']);

                        $records[] = array('servers_id' => $QList->ValueInt('servers_id'),
                            'content_status' => $QList->ValueInt('content_status'),
                            'content_order' => $QList->Value('content_order'),
                            'content_name' => $QList->Value('content_name'),
                            'host' => $QList->Value('host'),
                            'url' => $QList->Value('url'),
                            'lines' => $lc,
                            'size' => $size,
                            'user' => $user,
                            'pass' => $pass,
                            'port' => $port,
                            'logs_id' => $QList->Value('logs_id'),
                            'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[1],
                            'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : $permissions[2],
                            'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : $permissions[3]
                        );
                    } else {

                        $records[] = array('servers_id' => $QList->ValueInt('servers_id'),
                            'content_status' => $QList->ValueInt('content_status'),
                            'content_order' => $QList->Value('content_order'),
                            'content_name' => $QList->Value('content_name'),
                            'host' => $QList->Value('host'),
                            'url' => $QList->Value('url'),
                            'lines' => $lc,
                            'size' => $size,
                            'user' => $user,
                            'pass' => $pass,
                            'port' => $port,
                            'logs_id' => $QList->Value('logs_id'),
                            'can_read' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                            'can_write' => $_SESSION[admin][username] == 'admin' ? 1 : false,
                            'can_modify' => $_SESSION[admin][username] == 'admin' ? '' : false,
                            'can_publish' => $_SESSION[admin][username] == 'admin' ? 1 : false
                        );
                    }
                }

                $ssh->disconnect();
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listSubscribers()
    {
        global $toC_Json, $osC_Database;

        $event = $_REQUEST['event'];
        $databases_id = $_REQUEST['databases_id'];

        $query = "select * from delta_databases_subscribers where event = '" . $event . "' and databases_id = " . $databases_id;

        $QServers = $osC_Database->query($query);
        $QServers->execute();

        $records = array();
        while ($QServers->next()) {
            $records[] = array('email' => $QServers->Value('email'), 'name' => $QServers->Value('nom'), 'event' => $QServers->Value('event'));
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function saveGroup()
    {
        global $toC_Json, $osC_Language;

        if (toC_Servers_Admin::saveGroup((isset($_REQUEST['group_id']) && is_numeric($_REQUEST['group_id'])
            ? $_REQUEST['group_id']
            : null), $_REQUEST)) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        }
        else
        {
            $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
        }

        echo $toC_Json->encode($response);
    }

    function listServerGroups()
    {
        global $toC_Json, $osC_Database;

        $query = "select * from delta_server_groups order by group_name";
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

    function listServersconnexions()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT  CONCAT('host=',`delta_servers`.`host`,'#server_port=',`delta_servers`.`PORT`,'#server_user=',`delta_servers`.`user`,'#server_pass=',`delta_servers`.`pass`,'#servers_id=',`delta_servers`.`servers_id`) AS `server_connexion`,`delta_servers`.`label` AS label_server FROM `delta_servers` order by `delta_servers`.`label`";

        $QServers = $osC_Database->query($query);
        $QServers->execute();

        $i = 0;
        $records = array();
        while ($QServers->next()) {
            $records[] = array('server_connexion' => $QServers->Value('server_connexion'), 'label_server' => $QServers->Value('label_server'),
                'id' => $i
            );

            $i++;
        }


        $response = array(EXT_JSON_READER_TOTAL => $i,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function getServersCategories()
    {
        global $toC_Json, $osC_Language;

        $article_categories = toC_Servers_Categories_Admin::getServersCategories();

        $records = array();
        if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
            $records = array(array('id' => '', 'text' => $osC_Language->get('top_Servers_category')));
        }

        foreach ($article_categories as $category) {
            if ($category['Servers_categories_id'] != '1') {
                $records[] = array('id' => $category['Servers_categories_id'],
                    'text' => $category['Servers_categories_name']);
            }
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadServer()
    {
        global $toC_Json;

        $data = toC_Servers_Admin::getData($_REQUEST['servers_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function loadGroup()
    {
        global $toC_Json;

        $data = toC_Servers_Admin::getGroup($_REQUEST['group_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function loadGroupsTree()
    {
        global $osC_Database, $toC_Json;

        $Qtree = $osC_Database->query('SELECT COUNT(ur.servers_id) AS count, ur.servers_id,r.group_id, r.group_name FROM delta_server_groups r LEFT OUTER JOIN delta_server_to_groups ur ON (r.group_id = ur.group_id) GROUP BY r.group_name, r.group_id ORDER BY r.group_name,r.group_id ASC');
        $Qtree->execute();

        $records = array();

        while ($Qtree->next()) {
            $records [] = array('group_id' => $Qtree->value('group_id'), 'id' => $Qtree->value('group_id'), 'text' => $Qtree->value('group_name') . ' (' . $Qtree->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/server_info.png', 'leaf' => true);
        }

        $Qtree->freeResult();

        echo $toC_Json->encode($records);
    }

    function showLog()
    {
        global $toC_Json;

        $data = toC_Servers_Admin::getLog($_REQUEST['host'], $_REQUEST['user'], $_REQUEST['pass'], $_REQUEST['port'], $_REQUEST['url'], $_REQUEST['lines']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function saveServer()
    {
        global $toC_Json;

        $_SESSION['LAST_ERROR'] = '';

        $data = array('content_name' => $_REQUEST['label'],
            'content_url' => '',
            'created_by' => $_SESSION[admin][username],
            'modified_by' => $_SESSION[admin][username],
            'content_description' => $_REQUEST['label'],
            'content_order' => 0,
            'content_status' => $_REQUEST['content_status'],
            'page_title' => $_REQUEST['label'],
            'meta_keywords' => $_REQUEST['label'],
            'host' => $_REQUEST['host'],
            'label' => $_REQUEST['label'],
            'typ' => $_REQUEST['typ'],
            'port' => $_REQUEST['port'],
            'user' => $_REQUEST['user'],
            'pass' => $_REQUEST['pass'],
            'meta_descriptions' => $_REQUEST['label']);

        if (isset($_REQUEST['group_id'])) {
            $data['group_id'] = explode(',', $_REQUEST['group_id']);

            if (is_array($data['group_id'])) {

                if (toC_Servers_Admin::save((isset($_REQUEST['servers_id']) && ($_REQUEST['servers_id'] != -1)
                    ? $_REQUEST['servers_id'] : null), $data)
                ) {
                    $response = array('success' => true, 'feedback' => 'Configuration enregistree ...');
                } else {
                    $response = array('success' => false, 'feedback' => "Erreur survenue lors de l'enregistrement de la configuration :\n" . $_SESSION['LAST_ERROR']);
                }
            }
        } else {
            $response = array('success' => false, 'feedback' => 'Vous devez selectionner au moins un groupe pour ce Serveur');
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function saveLog()
    {
        global $toC_Json;

        $data = array('content_name' => $_REQUEST['label'],
            'content_url' => '',
            'created_by' => $_SESSION[admin][username],
            'modified_by' => $_SESSION[admin][username],
            'content_description' => $_REQUEST['content_description'],
            'content_order' => 0,
            'content_status' => $_REQUEST['content_status'],
            'page_title' => $_REQUEST['label'],
            'meta_keywords' => $_REQUEST['label'],
            'host' => $_REQUEST['host'],
            'url' => $_REQUEST['url'],
            'servers_id' => $_REQUEST['servers_id'],
            'port' => $_REQUEST['port'],
            'user' => $_REQUEST['user'],
            'pass' => $_REQUEST['pass'],
            'content_type' => $_REQUEST['content_type'],
            'content_id' => $_REQUEST['content_id'],
            'meta_descriptions' => $_REQUEST['label']);

        if (isset($_REQUEST['content_categories_id'])) {
            $data['categories'] = explode(',', $_REQUEST['content_categories_id']);
        } else {
            $data['categories'] = $_REQUEST['current_category_id'];
        }

        if (toC_Servers_Admin::saveLog((isset($_REQUEST['logs_id']) && ($_REQUEST['logs_id'] != -1)
            ? $_REQUEST['logs_id'] : null), $data)
        ) {
            $response = array('success' => true, 'feedback' => 'Configuration enregistree ...');
        } else {
            $response = array('success' => false, 'feedback' => "Erreur survenue lors de l'enregistrement de la configuration :\n" . $_SESSION['LAST_ERROR']);
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function deleteServer()
    {
        if (isset($_REQUEST['servers_id']) && !empty($_REQUEST['servers_id'])) {
            global $toC_Json, $osC_Language;

            if (toC_Servers_Admin::delete($_REQUEST['servers_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }
        } else {
            $response = array('success' => false, 'feedback' => 'Veuillez selectionner l\'article que vous voulez supprimer');
        }

        echo $toC_Json->encode($response);
    }

    function deleteGroup()
    {
        if (isset($_REQUEST['group_id']) && !empty($_REQUEST['group_id'])) {
            global $toC_Json, $osC_Language;

            if (toC_Servers_Admin::deleteGroup($_REQUEST['group_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['last_error']);
            }
        } else {
            $response = array('success' => false, 'feedback' => 'Veuillez selectionner !!');
        }

        echo $toC_Json->encode($response);
    }

    function purgeRepo()
    {
        global $toC_Json, $osC_Language;

        if (toC_Servers_Admin::purge_repo()) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function deleteServers()
    {
        global $toC_Json, $osC_Language, $osC_Image;

        $osC_Image = new osC_Image_Admin();

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $servers_id) {
            if (!toC_Servers_Admin::delete($servers_id)) {
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

    function deleteDatabase()
    {
        global $toC_Json;

        if (isset($_REQUEST['databases_id']) && !empty($_REQUEST['databases_id'])) {
            if (toC_Databases_Admin::delete($_REQUEST['databases_id'])) {
                $response = array('success' => true, 'feedback' => 'Configuration supprimee');
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['LAST_ERROR']);
            }
        } else {
            $response = array('success' => false, 'feedback' => 'Veuillez selectionner la configuration que vous souhaitez supprimer');
        }

        echo $toC_Json->encode($response);
    }

    function setStatus()
    {
        global $toC_Json, $osC_Language;

        if (isset($_REQUEST['servers_id']) && content::setStatus($_REQUEST['servers_id'], (isset($_REQUEST['flag'])
            ? $_REQUEST['flag'] : null), 'Servers')
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }
}

?>