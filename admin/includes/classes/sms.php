<?php

class osC_Sms_Admin
{
    function getData($id)
    {
        global $osC_Database;

        $QSms = $osC_Database->query('select * from :table_sms where ID = :sms_id');
        $QSms->bindTable(':table_sms', 'messages.messages');
        $QSms->bindInt(':sms_id', $id);
        $QSms->execute();

        $data = $QSms->toArray();

        $QSms->freeResult();

        return $data;
    }

    function getPhone($customerid)
    {
        $db_user = DB_USER;
        $db_pass = DB_PASS;
        $db_host = DB_HOST;
        $db_sid = DB_SID;

        $phone = '';

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            osC_Sms_Admin::addDetail("Erreur Recuperation du No Phone dans AMPLITUDE ===> trxlogid = " . $_REQUEST['trxlogid'] . " : " . $e);
        } else {
            $query = "select num from bank.BKTELCLI where cli = '" . $customerid . "' and rownum = 1";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                osC_Sms_Admin::addDetail("Erreur Recuperation du No Phone dans AMPLITUDE ===> trxlogid = " . $_REQUEST['trxlogid'] . " : " . $e);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    osC_Sms_Admin::addDetail("Erreur Recuperation du No Phone dans AMPLITUDE ===> trxlogid = " . $_REQUEST['trxlogid'] . " : " . $e);
                } else {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        $phone = $row['NUM'];
                    }
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);

        return $phone;
    }

    function deleteTrans($trxlogid)
    {
        $db_user = IRISDB_USER;
        $db_pass = IRISDB_PASS;
        $db_host = IRISDB_HOST;
        $db_sid = IRISDB_SID;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
        } else {
            $query = "BEGIN DELETE FROM trans WHERE trxlogid = '" . $trxlogid . "'; COMMIT; END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                } else {
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);
    }

    function updateDrapeau($eventid)
    {
        $db_user = IRISDB_USER;
        $db_pass = IRISDB_PASS;
        $db_host = IRISDB_HOST;
        $db_sid = IRISDB_SID;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
        } else {
            $query = "BEGIN update atm_event set drapeau = drapeau +1 WHERE eventid = '" . $eventid . "'; COMMIT; END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                } else {
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);
    }

    function addError($msg)
    {
        $db_user = IRISDB_USER;
        $db_pass = IRISDB_PASS;
        $db_host = IRISDB_HOST;
        $db_sid = IRISDB_SID;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
        } else {
            $query = "BEGIN insert into errors(msg) values('" . $msg . "'); COMMIT; END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                } else {
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);
    }

    function addDetail($what)
    {
        $db_user = IRISDB_USER;
        $db_pass = IRISDB_PASS;
        $db_host = IRISDB_HOST;
        $db_sid = IRISDB_SID;

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
        } else {
            $query = "BEGIN INSERT INTO EXECUTION_DETAILS (WHAT) VALUES ('" . $what . "'); COMMIT; END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                } else {
                }
            }

            oci_free_statement($s);

        }

        oci_close($c);
    }

    function save($id = null, $data)
    {
        global $osC_Database;

        if (is_numeric($id)) {
            $Qemail = $osC_Database->query('update :table_sms set title = :title, content = :content, nbre_days = :nbre_days where relances_id = :relances_id');
            $Qemail->bindInt(':relances_id', $id);
        } else {
            $Qemail = $osC_Database->query('insert into :table_sms (title, content, nbre_days, date_added, status) values (:title, :content, :nbre_days, now(), 0)');
        }

        $Qemail->bindTable(':table_sms', TABLE_RELANCES);
        $Qemail->bindValue(':title', $data['title']);
        $Qemail->bindValue(':content', $data['content']);
        $Qemail->bindValue(':nbre_days', $data['nbre_days']);
        $Qemail->setLogging($_SESSION['title'], $id);
        $Qemail->execute();

        if (!$osC_Database->isError()) {
            return true;
        }

        return false;
    }

    function delete($id)
    {
        global $osC_Database;

        $error = false;

        $ids = explode(',', $id);

        $osC_Database->startTransaction();

        foreach ($ids as $v) {
            $Qcheck = $osC_Database->query('select s.status,s.body from :table_sms s where ID = :ID');
            $Qcheck->bindTable(':table_sms', 'messages.messages');
            $Qcheck->bindInt(':ID', $v);
            $Qcheck->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($Qcheck->value('status') != '2') {
                $Qdelete = $osC_Database->query('delete from :table_sms where ID = :ID');
                $Qdelete->bindTable(':table_sms', 'messages.messages');
                $Qdelete->bindInt(':ID', $v);
                $Qdelete->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }
            }
            else
            {
                return "Impossible de supprimer le message " . $Qcheck->value('body') . '......il a deja ete envoye';
            }
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            return "true";
        }

        $osC_Database->rollbackTransaction();

        return $osC_Database->getError();
    }
}

?>
