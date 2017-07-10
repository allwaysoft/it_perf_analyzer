<?php
/*
  $Id: administrators.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
if (!class_exists('osC_Users_Admin')) {
    require('includes/classes/users.php');
}

if (!class_exists('content')) {
    require('includes/classes/content.php');
}
//    require('includes/classes/roles.php');
require('includes/classes/image.php');
include('includes/modules/Net/SSH2.php');
require('includes/classes/email_account.php');
require('includes/classes/email_accounts.php');

class toC_Json_Users
{
    function listUsers()
    {
        global $toC_Json, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $Qadmin = $osC_Database->query('SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" ');
        if ($roles_id != 0 && $roles_id != -1) {
            $Qadmin->appendQuery('and u.administrators_id IN (SELECT administrators_id FROM :table_users_roles WHERE roles_id = :roles_id)');
            $Qadmin->bindTable(':table_users_roles', TABLE_USERS_ROLES);
            $Qadmin->bindInt(':roles_id', $roles_id);
        }

        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->bindTable(':tables_users', TABLE_USERS);
        $Qadmin->setExtBatchLimit($start, $limit);
        $Qadmin->execute();

        $records = array();
        while ($Qadmin->next()) {
            $image = '<img src="../images/users/mini/' . $Qadmin->value('image_url') . '" width="100" height="80" />';
            $data = array(
                'users_id' => $Qadmin->valueInt('users_id'),
                'administrators_id' => $Qadmin->valueInt('administrators_id'),
                'image_url' => $image,
                'user_name' => $Qadmin->value('user_name'),
                'email_address' => $Qadmin->value('email_address'),
                'description' => $Qadmin->value('description'),
                'account' => array('user_name' => $Qadmin->value('user_name'), 'description' => $Qadmin->value('description'))
            );
            $records[] = $data;

        }
        $Qadmin->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $Qadmin->getBatchSize(),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listDeltausers()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
        $total = empty($_REQUEST['count']) ? 0 : $_REQUEST['count'];
        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search)) {
            $start = 0;
            $limit = 10000;
            $query = "select * from ( select a.*, ROWNUM rnum from (SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,SUS,ECRAN,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) where (lower(evuti.cuti) like :cuti or lower(unix) like :unix or lower(lib) like :lib) ORDER BY LTRIM (LIB)) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
        } else {
            if ($roles_id == "-1") {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,SUS,ECRAN,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) ORDER BY LTRIM (LIB)) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
            } else {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,SUS,ECRAN,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) where puti = :puti ORDER BY LTRIM (LIB)) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
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
        oci_bind_by_name($s, ":cuti", $search);
        oci_bind_by_name($s, ":unix", $search);
        oci_bind_by_name($s, ":lib", $search);

        if ($roles_id != '0' && $roles_id != '-1') {
            oci_bind_by_name($s, ":puti", $roles_id);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $status = trim($row['ECRAN']);
            $records [] = array('cuti' => $row['CUTI'], 'unix' => $row['UNIX'], 'lib' => $row['LIB'], 'status' => !empty($status) ? '1' : '0');
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listCartes()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
        $total = empty($_REQUEST['count']) ? 0 : $_REQUEST['count'];
        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search)) {
            $start = 0;
            $limit = 10000;
            $query = "SELECT *
  FROM (SELECT a.*, ROWNUM rnum
          FROM (  SELECT c.age,
                         c.ncart,
                         c.eta,
                         c.dfv,
                         c.nom,
                         c.ncpbc,
                         m.nctr,
                         m.cli,
                         m.ncp,
                         m.idansc,
                         m.sit
                    FROM bank.bkcadab c, bank.moctr m
                   WHERE c.nctr = m.nctr and (LOWER (m.cli) LIKE :cli
                          OR LOWER (c.nom) LIKE :nom
                          OR LOWER (c.ncart) LIKE :ncart)
                ORDER BY TRIM (nom)) a
         WHERE ROWNUM <= :MAX_ROW_TO_FETCH)
 WHERE rnum >= :MIN_ROW_TO_FETCH";
        } else {
            if ($roles_id == "-1") {
                $query = "SELECT *
  FROM (SELECT a.*, ROWNUM rnum
          FROM (  SELECT c.age,
                         c.ncart,
                         c.eta,
                         c.dfv,
                         c.nom,
                         c.ncpbc,
                         m.nctr,
                         m.cli,
                         m.ncp,
                         m.idansc,
                         m.sit
                    FROM bank.bkcadab c, bank.moctr m
                   WHERE c.nctr = m.nctr
                ORDER BY TRIM (nom)) a
         WHERE ROWNUM <= :MAX_ROW_TO_FETCH)
 WHERE rnum >= :MIN_ROW_TO_FETCH";
            } else {
                $query = "SELECT *
  FROM (SELECT a.*, ROWNUM rnum
          FROM (SELECT c.age,
                         c.ncart,
                         c.eta,
                         c.dfv,
                         c.nom,
                         c.ncpbc,
                         m.nctr,
                         m.cli,
                         m.ncp,
                         m.idansc,
                         m.sit
                    FROM bank.bkcadab c, bank.moctr m
                   WHERE c.nctr = m.nctr and c.age = :age
                ORDER BY TRIM (nom)) a
         WHERE ROWNUM <= :MAX_ROW_TO_FETCH)
 WHERE rnum >= :MIN_ROW_TO_FETCH";
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
        oci_bind_by_name($s, ":cli", $search);
        oci_bind_by_name($s, ":nom", $search);
        oci_bind_by_name($s, ":ncart", $search);

        if ($roles_id != '0' && $roles_id != '-1') {
            oci_bind_by_name($s, ":age", $roles_id);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $entry_icon = osc_icon_from_filename('.' . strtolower($row['SIT']));
            //$status = trim($row['ECRAN']);
            $records [] = array(
                'icon' => $entry_icon,
                'age' => $row['AGE'],
                'ncart' => $row['NCART'],
                'eta' => $row['ETA'],
                'dfv' => $row['DFV'],
                'nom' => $row['NOM'],
                'ncpbc' => $row['NCPBC'],
                'nctr' => $row['NCTR'],
                'cli' => $row['CLI'],
                'ncp' => $row['NCP'],
                'sit' => $row['SIT'],
                'idansc' => $row['IDANSC']);
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listAmplitudeCtx()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
        $total = empty($_REQUEST['count']) ? 0 : $_REQUEST['count'];
        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search) && isset($search)) {
            $start = 0;
            $limit = 10000;
            $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT trim(cx.cli) cli,cx.dctx,trim(cx.uti) uti,ltrim(rtrim(c.nomrest)) nomrest FROM bank.bkctxcli cx INNER JOIN bank.bkcli c ON cx.cli = c.cli INNER JOIN bank.bkage ag ON c.age = ag.age WHERE (   LOWER (cx.cli) LIKE :search OR LOWER (c.nomrest) LIKE :search )) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
        } else {
            if ($roles_id == "-1" || $roles_id == "0") {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT trim(cx.cli) cli,cx.dctx,trim(cx.uti) uti,ltrim(rtrim(c.nomrest)) nomrest FROM bank.bkctxcli cx INNER JOIN bank.bkcli c ON cx.cli = c.cli INNER JOIN bank.bkage ag ON c.age = ag.age) a)  where ROWNUM <= :MAX_ROW_TO_FETCH AND rnum  > :MIN_ROW_TO_FETCH";
            } else {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT trim(cx.cli) cli,cx.dctx,trim(cx.uti) uti,ltrim(rtrim(c.nomrest)) nomrest FROM bank.bkctxcli cx INNER JOIN bank.bkcli c ON cx.cli = c.cli INNER JOIN bank.bkage ag ON c.age = ag.age WHERE c.age = :age) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  > :MIN_ROW_TO_FETCH";
            }
        }

        $fin = $start + $limit;
        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
        oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

        if (!empty($search) && isset($search)) {
            $search = '%' . strtolower($search) . '%';
            oci_bind_by_name($s, ":search", $search);
        }

        if ($roles_id != '0' && $roles_id != '-1') {
            oci_bind_by_name($s, ":age", $roles_id);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();

        if (!empty($search) && isset($search)) {
            $total = 0;
        }

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            if (!empty($search) && isset($search)) {
                $total = $total + 1;
            }

            $records [] = array('cli' => $row['CLI'], 'dctx' => $row['DCTX'], 'uti' => $row['UTI'], 'nomrest' => $row['NOMREST']);
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listAmplitudeNcp()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
        $total = empty($_REQUEST['count']) ? 0 : $_REQUEST['count'];
        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search) && isset($search)) {
            $start = 0;
            $limit = 1000000;
            $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT c.age,TRIM (cx.cli) cli,LTRIM (RTRIM (cx.nomrest)) nomrest,c.ncp,c.clc FROM bank.bkcli cx INNER JOIN bank.bkcom c ON cx.cli = c.cli INNER JOIN bank.bkage ag ON c.age = ag.age WHERE (   LOWER (c.ncp) LIKE :search OR LOWER (cx.cli) LIKE :search OR LOWER (cx.nomrest) LIKE :search)) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
        } else {
            if ($roles_id == "-1" || $roles_id == "0") {
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT c.age,TRIM (cx.cli) cli,LTRIM (RTRIM (cx.nomrest)) nomrest,c.ncp,c.clc FROM bank.bkcli cx INNER JOIN bank.bkcom c ON cx.cli = c.cli INNER JOIN bank.bkage ag ON c.age = ag.age) a) WHERE ROWNUM <= :MAX_ROW_TO_FETCH AND rnum > :MIN_ROW_TO_FETCH";
            } else {
                $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT c.age,TRIM (cx.cli) cli,LTRIM (RTRIM (cx.nomrest)) nomrest,c.ncp,c.clc FROM bank.bkcli cx INNER JOIN bank.bkcom c ON cx.cli = c.cli INNER JOIN bank.bkage ag ON c.age = ag.age WHERE c.age = :age) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum > :MIN_ROW_TO_FETCH";
            }
        }

        $fin = $start + $limit;
        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
        oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

        if (!empty($search) && isset($search)) {
            $search = '%' . strtolower($search) . '%';
            oci_bind_by_name($s, ":search", $search);
        }

        if ($roles_id != '0' && $roles_id != '-1') {
            oci_bind_by_name($s, ":age", $roles_id);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();

        if (!empty($search) && isset($search)) {
            $total = 0;
        }

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            if (!empty($search) && isset($search)) {
                $total = $total + 1;
            }

            $records [] = array('age' => $row['AGE'], 'cli' => $row['CLI'], 'ncp' => $row['NCP'], 'nomrest' => $row['NOMREST'], 'clc' => $row['CLC']);
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listCtx()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
        $total = empty($_REQUEST['count']) ? 0 : $_REQUEST['count'];
        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search) && isset($search)) {
            $start = 0;
            $limit = 10000;
            $query = "SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT NUMERO_DOSSIER no_dossier,CXDOSSIER.NUMERO_CLIENT no_client,DATE_ENTREE_CONTENTI date_ctx,PRENOM_CLIENT || ' ' || NOM_CLIENT nom FROM CONTENT.CXCLIENTS INNER JOIN CONTENT.CXDOSSIER ON (CXCLIENTS.CDOS = CXDOSSIER.CDOS) AND (CXCLIENTS.NUMERO_CLIENT = CXDOSSIER.NUMERO_CLIENT) WHERE (LOWER (CXDOSSIER.NUMERO_CLIENT) LIKE :search OR LOWER (PRENOM_CLIENT) LIKE :search OR LOWER (NOM_CLIENT) LIKE :search) order by 4) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
        } else {
            if ($roles_id == "-1" || $roles_id == "0") {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT NUMERO_DOSSIER no_dossier,CXDOSSIER.NUMERO_CLIENT no_client,DATE_ENTREE_CONTENTI date_ctx,PRENOM_CLIENT || ' ' || NOM_CLIENT nom FROM CONTENT.CXCLIENTS INNER JOIN CONTENT.CXDOSSIER ON (CXCLIENTS.CDOS = CXDOSSIER.CDOS) AND (CXCLIENTS.NUMERO_CLIENT = CXDOSSIER.NUMERO_CLIENT) order by 4) a)  where ROWNUM <= :MAX_ROW_TO_FETCH AND rnum  > :MIN_ROW_TO_FETCH";
            } else {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT NUMERO_DOSSIER no_dossier,CXDOSSIER.NUMERO_CLIENT no_client,DATE_ENTREE_CONTENTI date_ctx,PRENOM_CLIENT || ' ' || NOM_CLIENT nom FROM CONTENT.CXCLIENTS INNER JOIN CONTENT.CXDOSSIER ON (CXCLIENTS.CDOS = CXDOSSIER.CDOS) AND (CXCLIENTS.NUMERO_CLIENT = CXDOSSIER.NUMERO_CLIENT) WHERE code_agence = :age order by 4) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  > :MIN_ROW_TO_FETCH";
            }
        }

        $fin = $start + $limit;
        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
        oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

        if (!empty($search) && isset($search)) {
            $search = '%' . strtolower($search) . '%';
            oci_bind_by_name($s, ":search", $search);
        }

        if ($roles_id != '0' && $roles_id != '-1') {
            oci_bind_by_name($s, ":age", $roles_id);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();

        if (!empty($search) && isset($search)) {
            $total = 0;
        }

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            if (!empty($search) && isset($search)) {
                $total = $total + 1;
            }

            $records [] = array('no_dossier' => $row['NO_DOSSIER'], 'no_client' => $row['NO_CLIENT'], 'date_ctx' => $row['DATE_CTX'], 'nom' => $row['NOM']);
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $total,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listOnlineusers()
    {
        global $toC_Json;

        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search)) {
            $query = "select * from ( select a.*, ROWNUM rnum from (SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,SUS,ECRAN,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) where LENGTH (TRIM (ecran)) = 15 and (lower(evuti.cuti) like :cuti or lower(unix) like :unix or lower(lib) like :lib) ORDER BY LTRIM (LIB)) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
        } else {
            if ($roles_id == "-1") {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,SUS,ECRAN,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) where LENGTH (TRIM (ecran)) = 15 ORDER BY LTRIM (LIB)) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
            } else {
                $query = "select * from ( select a.*, ROWNUM rnum from (SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,SUS,ECRAN,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) where LENGTH (TRIM (ecran)) = 15 and puti = :puti ORDER BY LTRIM (LIB)) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
            }
        }

        $start = 0;
        $limit = 10000;
        $fin = $start + $limit;
        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $search = '%' . strtolower($search) . '%';
        oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
        oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);

        if (!empty($search)) {
            oci_bind_by_name($s, ":cuti", $search);
            oci_bind_by_name($s, ":unix", $search);
            oci_bind_by_name($s, ":lib", $search);
        }

        if ($roles_id != '0' && $roles_id != '-1') {
            oci_bind_by_name($s, ":puti", $roles_id);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();
        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $count++;
            $status = trim($row['ECRAN']);
            $records [] = array('cuti' => $row['CUTI'], 'unix' => $row['UNIX'], 'lib' => $row['LIB'], 'status' => !empty($status) ? '1' : '0');
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listLettrage()
    {
        global $toC_Json;

        $roles_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $query = "SELECT BKINTRALETBLK.NCP," .
            "BKINTRALETBLK.UTI," .
            "EVUTI.LIB," .
            "EVUTAUT.UNIX " .
            "FROM BKINTRALETBLK, EVUTI, EVUTAUT " .
            "WHERE (BKINTRALETBLK.UTI = EVUTI.CUTI) " .
            "AND (BKINTRALETBLK.UTI = EVUTAUT.CUTI) " .
            "UNION ALL " .
            "SELECT BKLETBLK.NCP," .
            "BKLETBLK.UTI," .
            "EVUTI.LIB," .
            "EVUTAUT.UNIX " .
            "FROM BKLETBLK, EVUTI, EVUTAUT " .
            "WHERE (BKLETBLK.UTI = EVUTI.CUTI) " .
            "AND (BKLETBLK.UTI = EVUTAUT.CUTI)";

        $start = 0;
        $limit = 10000;
        $fin = $start + $limit;
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
        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $count++;
            $records [] = array('cuti' => $row['UTI'], 'unix' => $row['UNIX'], 'lib' => $row['LIB'], 'ncp' => $row['NCP']);
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listProgrammes()
    {
        global $toC_Json;

        $search = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if (!empty($search)) {
            $query = "select * from ( select a.*, ROWNUM rnum from (select * from EVPRG where mprg is not null and lang = '001' and (lower(mprg) like :mprg or lower(lprg) like :mprg)) a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
        } else {
            $query = "select * from ( select a.*, ROWNUM rnum from (select * from EVPRG where mprg is not null and lang = '001') a where ROWNUM <= :MAX_ROW_TO_FETCH ) where rnum  >= :MIN_ROW_TO_FETCH";
        }

        $start = 0;
        $limit = 10000;
        $fin = $start + $limit;
        $s = oci_parse($c, $query);
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $search = '%' . strtolower($search) . '%';
        oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
        oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);
        oci_bind_by_name($s, ":mprg", $search);

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $records = array();
        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $count++;
            $records [] = array('nprg' => $row['NPRG'], 'lprg' => $row['LPRG'], 'mprg' => $row['MPRG']);
        }

        oci_free_statement($s);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function getAccesses()
    {
        global $toC_Json, $osC_Language;

        $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/access');
        $osC_DirectoryListing->setIncludeDirectories(false);

        $access_modules_array = array();

        foreach ($osC_DirectoryListing->getFiles() as $file) {
            $module = substr($file['name'], 0, strrpos($file['name'], '.'));

            if (!class_exists('osC_Access_' . ucfirst($module))) {
                $osC_Language->loadIniFile('modules/access/' . $file['name']);
                include($osC_DirectoryListing->getDirectory() . '/' . $file['name']);
            }

            $module = 'osC_Access_' . ucfirst($module);
            $module = new $module();
            $title = osC_Access::getGroupTitle($module->getGroup());

            $access_modules_array[$title][] = array('id' => $module->getModule(),
                'text' => $module->getTitle(),
                'leaf' => true);
        }

        ksort($access_modules_array);

        $access_options = array();
        $count = 1;
        foreach ($access_modules_array as $group => $modules) {
            $access_option['id'] = $count;
            $access_option['text'] = $group;

            $mod_arrs = array();
            foreach ($modules as $module) {
                $mod_arrs[] = $module;
            }

            $access_option['children'] = $mod_arrs;

            $access_options[] = $access_option;
            $count++;
        }

        echo $toC_Json->encode($access_options);
    }

    function loadUser()
    {
        global $toC_Json;

        $with_modules = isset($_REQUEST['wm']) && $_REQUEST['wm'] == '1';

        $data = osC_Users_Admin::getData($_REQUEST['administrators_id'], $with_modules);

        if (is_array($data['access_modules']) && !empty($data['access_modules'])) {
            if ($data['access_modules'][0] == '*')
                $data['access_globaladmin'] = '1';
        }

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function getUser()
    {
        global $toC_Json;

        $data = osC_Users_Admin::getUser($_REQUEST['account']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function saveUser()
    {
        global $toC_Json, $osC_Language, $osC_Image;

        $osC_Image = new osC_Image_Admin();

        $data = array('user_name' => $_REQUEST['user_name'],
            'content_name' => $_REQUEST['content_name'],
            'content_description' => $_REQUEST['content_description'],
            'page_title' => '',
            'meta_keywords' => '',
            'meta_descriptions' => '',
            'password' => $_REQUEST['user_password'],
            'description' => $_REQUEST['description'],
            'status' => $_REQUEST['status'],
            'delimage' => (isset($_REQUEST['delimage']) && ($_REQUEST['delimage'] == 'on') ? '1' : '0'),
            'email_address' => $_REQUEST['email_address']);

        $modules = array();
        if (isset($_REQUEST['roles_id'])) {
            $data['roles_id'] = explode(',', $_REQUEST['roles_id']);

            if (is_array($data['roles_id'])) {
                foreach ($data['roles_id'] as $roles_id) {
                    if ($roles_id != -1) {
                        $user = osC_Roles_Admin::getData($roles_id, "");

                        if (is_array($user['access_modules']) && !empty($user['access_modules'])) {
                            if ($user['access_modules'][0] == '*')
                                $user['access_globaladmin'] = '1';
                        }

                        if (isset($user['access_modules']) && !empty($user['access_modules'])) {
                            $modules = array_merge($modules, $user['access_modules']);
                        }

                        if (isset($user['access_globaladmin']) && ($user['access_globaladmin'] == 'on')) {
                            $modules = array('*');
                            //goto save;
                            break;
                        }
                    }
                }

                if (in_array('*', $modules)) {
                    $modules = array('*');
                } else {
                    $modules = array_unique($modules);
                }

                switch (osC_Users_Admin::save((isset($_REQUEST['administrators_id']) && is_numeric($_REQUEST['administrators_id'])
                    ? $_REQUEST['administrators_id'] : null), $data, $modules)) {
                    case 1:
                        if (isset($_REQUEST['administrators_id']) && is_numeric($_REQUEST['administrators_id']) && ($_REQUEST['administrators_id'] == $_SESSION['admin']['id'])) {
                            $_SESSION['admin']['access'] = osC_Access::getUserLevels($_REQUEST['administrators_id']);
                        }

                        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                        break;

                    case -1:
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                        break;

                    case -2:
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_username_already_exists'));
                        break;

                    case -3:
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_email_format'));
                        break;

                    case -4:
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_email_already_exists'));
                        break;
                }
            }
        } else {
            $response = array('success' => false, 'feedback' => 'Vous devez selectionner au moins un role pour cet utilisateur');
        }

        echo $toC_Json->encode($response);
    }

    function deconnectUser()
    {
        global $toC_Json;

        $unix = empty($_REQUEST['unix']) ? '' : $_REQUEST['unix'];
        $cuti = empty($_REQUEST['cuti']) ? '' : $_REQUEST['cuti'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $cuti = strtolower($cuti);

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
        } else {
            $unix = strtolower(trim($unix));
            $query = "BEGIN update bank.EVUTI set ECRAN = '' where lower(CUTI) = '$cuti'; commit; FOR x IN (SELECT Sid,Serial#,machine,program FROM v\$session WHERE LOWER (osuser) in ('$unix','$cuti')) LOOP EXECUTE IMMEDIATE 'Alter System Kill Session ''' || x.Sid || ','|| x.Serial# || ''' IMMEDIATE'; END LOOP; END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de deconnecter cet utilisateur ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de deconnecter cet utilisateur ' . htmlentities($e['message']));
                } else {
                    $app_user = empty($_REQUEST['app_user']) ? APP_USER : $_REQUEST['app_user'];
                    $app_pass = empty($_REQUEST['app_pass']) ? APP_PASS : $_REQUEST['app_pass'];
                    $app_host = empty($_REQUEST['app_host']) ? APP_HOST : $_REQUEST['app_host'];

                    $ssh = new Net_SSH2($app_host);
                    if (!$ssh->login($app_user, $app_pass)) {
                        $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH');
                    } else {
                        $ssh->disableQuietMode();
//                            $cmd = "ps -ef|grep $unix|grep -v grep|awk '{print $2}'| sudo xargs kill -9";
                        $cmd = "ps -ef|grep -i $unix|grep -v grep|awk '{print $2}'";
                        $resp = $ssh->exec($cmd);
                        $pids = explode("\n", $resp);
                        $feedback = '';

                        foreach ($pids as &$pid) {
                            if (!empty($pid)) {
                                $cmd = "echo $app_pass|sudo kill -9 $pid";
                                $resp = $ssh->exec($cmd);
                                $feedback = $feedback . $resp;
                            }
                        }

                        $cmd = "ps -ef|grep -i $cuti|grep -v grep|awk '{print $2}'";
                        $resp = $ssh->exec($cmd);
                        $pids = explode("\n", $resp);

                        foreach ($pids as &$pid) {
                            if (!empty($pid)) {
                                $cmd = "echo $app_pass|sudo kill -9 $pid";
                                $resp = $ssh->exec($cmd);
                                $feedback = $feedback . $resp;
                            }
                        }

                        $response = array('success' => true, 'feedback' => $feedback);

                        $ssh->disconnect();
                    }
                }
            }

            oci_free_statement($s);
        }

        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function cancelCarte()
    {
        global $toC_Json;

        $ncart = $_REQUEST['ncart'];
        $nctr = $_REQUEST['nctr'];
        $age = $_REQUEST['age'];

        if(empty($ncart) || !isset($ncart))
        {
            $response = array('success' => false, 'feedback' => 'Veuillez renseigner le No de Carte');
        }
        else
            {
                $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
                $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
                $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
                $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

                $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
                if (!$c) {
                    $e = oci_error();
                    $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
                } else {
                    $ncart = trim($ncart);
                    $nctr = trim($nctr);
                    $age = trim($age);

                    $query = "BEGIN update bank.moctr set sit = 'A' where trim(nctr) = '" . $nctr . "';update bank.bkcadab set eta = '20' where trim(ncart) = '" . $ncart . "' and trim(age) = '" . $age . "' and trim(nctr) = '" . $nctr . "'; commit; END;";
                    //var_dump($query);

                    $s = oci_parse($c, $query);
                    if (!$s) {
                        $e = oci_error($c);
                        $response = array('success' => false, 'feedback' => "Impossible d'annuler cette carte " . htmlentities($e['message']));
                    } else {
                        $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                        if (!$r) {
                            $e = oci_error($s);
                            $response = array('success' => false, 'feedback' => "Impossible d'annuler cette carte " . htmlentities($e['message']));
                        } else {
                                $response = array('success' => true, 'feedback' => "Carte annulee avec succes !!!");
                            }
                        }

                       oci_free_statement($s);
                    }

                oci_close($c);
                }

        echo $toC_Json->encode($response);
    }

    function sortieCtx()
    {
        global $toC_Json;

        $response = array('success' => false, 'feedback' => 'Un probleme est survenu ... veuillez contacter votre administrateur !!!');

        if (empty($_REQUEST['cli'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez renseigner le code du client SVP !!!');
        } else {
            $cli = $_REQUEST['cli'];

            $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
            $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
            $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
            $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Impossible de se connecter à la base : ' . htmlentities($e['message']));
            } else {
                $cli = strtolower($cli);

                $query = "BEGIN DELETE FROM bank.bkctxcli WHERE cli = '" . $cli . "'; DELETE FROM bank.bkctxcpt WHERE cli = '" . $cli . "'; DELETE FROM bank.bkctxcre WHERE cli = '" . $cli . "'; DELETE FROM bank.bkctxcpth WHERE EXISTS (SELECT * FROM bank.bkcom WHERE cli = '" . $cli . "' AND bank.bkctxcpth.age = bank.bkcom.age AND bank.bkctxcpth.dev = bank.bkcom.dev AND bank.bkctxcpth.ncp = bank.bkcom.ncp AND bank.bkctxcpth.suf = bank.bkcom.suf); UPDATE bank.bkcli SET ges = '000' WHERE cli = '" . $cli . "'; UPDATE bank.bkcom SET ctx = ' ' WHERE cli = '" . $cli . "'; UPDATE bank.bkcli SET qua = '01' WHERE cli = '" . $cli . "'; DELETE FROM bank.bkadcli WHERE bank.bkadcli.cli = '" . $cli . "' AND bank.bkadcli.typ = 'C'; UPDATE bank.bkadcli SET typ = 'C' WHERE bank.bkadcli.cli = '" . $cli . "' AND bank.bkadcli.typ = 'X'; COMMIT; END;";

                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => 'Impossible de sortir ce client du Contentieux ' . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de sortir ce client du Contentieux ' . htmlentities($e['message']));
                    } else {
                        $response = array('success' => true, 'feedback' => 'Operation effectuée');
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function deblocageIgc()
    {
        global $toC_Json;

        $response = array('success' => false, 'feedback' => 'Un probleme est survenu ... veuillez contacter votre administrateur !!!');

        if (empty($_REQUEST['cli'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez renseigner le code du client SVP !!!');
        } else {
            $cli = $_REQUEST['cli'];

            //$db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
            //$db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
            //$db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
            //$db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

            $db_user = 'gm_fomi';
            $db_pass = 'Kouepe3073';
            $db_host = '10.100.1.51';
            $db_sid = 'DRSTOCKV10';

            $age = $_REQUEST['age'];
            $ncp = $_REQUEST['ncp'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Impossible de se connecter à la base : ' . htmlentities($e['message']));
            } else {
                //$query = "BEGIN DELETE FROM bank.bkctxcli WHERE cli = '" . $cli . "'; DELETE FROM bank.bkctxcpt WHERE cli = '" . $cli . "'; DELETE FROM bank.bkctxcre WHERE cli = '" . $cli . "'; DELETE FROM bank.bkctxcpth WHERE EXISTS (SELECT * FROM bank.bkcom WHERE cli = '" . $cli . "' AND bank.bkctxcpth.age = bank.bkcom.age AND bank.bkctxcpth.dev = bank.bkcom.dev AND bank.bkctxcpth.ncp = bank.bkcom.ncp AND bank.bkctxcpth.suf = bank.bkcom.suf); UPDATE bank.bkcli SET ges = '000' WHERE cli = '" . $cli . "'; UPDATE bank.bkcom SET ctx = ' ' WHERE cli = '" . $cli . "'; UPDATE bank.bkcli SET qua = '01' WHERE cli = '" . $cli . "'; DELETE FROM bank.bkadcli WHERE bank.bkadcli.cli = '" . $cli . "' AND bank.bkadcli.typ = 'C'; UPDATE bank.bkadcli SET typ = 'C' WHERE bank.bkadcli.cli = '" . $cli . "' AND bank.bkadcli.typ = 'X'; COMMIT; END;";

                $query = "BEGIN update bank.bkcptdos set bank.bkcptdos.ctr = '9' where bank.bkcptdos.age ='" . $age . "' AND bank.bkcptdos.ncp ='" . $ncp . "'; COMMIT; END;";

                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => 'Impossible de debloquer ce compte ' . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de debloquer ce compte ' . htmlentities($e['message']));
                    } else {
                        $response = array('success' => true, 'feedback' => 'Operation effectuée');
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function lockUser()
    {
        global $toC_Json;

        $username = $_SESSION[admin][username];

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter');
        }
        else
        {
            $db_user = $_REQUEST['db_user'];
            $label = $_REQUEST['label'];
            $databases_id = $_REQUEST['databases_id'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $account = $_REQUEST['account'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                $query = "BEGIN EXECUTE IMMEDIATE 'alter user " . $account . " account lock'; END;";
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de desactiver ce compte ' . htmlentities($e['message']));
                    } else {

                        $response = array('success' => true, 'feedback' => "Compte " . $account . " desactivé avec succes");

                        $subscribers = osC_Users_Admin::getSubscribers($databases_id,'disable_account');

                        $to = array();
                        $emails = explode(';',$subscribers);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $body = "<p>Bonjour,</p><p>Le compte " . $account . " a été desactivé par " . $username . " sur la base " . $label . "</p>";

                        $toC_Email_Account = new toC_Email_Account(4);

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => 222,
                            'to' => $to,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Le compte " . $account . " a été desactivé par " . $username . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if($toC_Email_Account->sendMailJob($mail))
                        {
                            $msg = "OK";
                        }
                        else
                        {
                            $msg = "NOK";
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function unlockUser()
    {
        global $toC_Json;

        $username = $_SESSION[admin][username];
        //$username = 'xxxx';

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter');
        }
        else
        {
            $db_user = $_REQUEST['db_user'];
            $label = $_REQUEST['label'];
            $databases_id = $_REQUEST['databases_id'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $account = $_REQUEST['account'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                $query = "BEGIN EXECUTE IMMEDIATE 'alter user " . $account . " account unlock'; END;";
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible d'executer cette requete " . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible d activer ce compte ' . htmlentities($e['message']));
                    } else {

                        $response = array('success' => true, 'feedback' => "Compte " . $account . " activé avec succes");

                        $subscribers = osC_Users_Admin::getSubscribers($databases_id,'enable_account');

                        $to = array();
                        $emails = explode(';',$subscribers);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $body = "<p>Bonjour,</p><p>Le compte " . $account . " a été activé par " . $username . " sur la base " . $label . "</p>";

                        $toC_Email_Account = new toC_Email_Account(4);

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => 222,
                            'to' => $to,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Le compte " . $account . " a été activé par " . $username . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if($toC_Email_Account->sendMailJob($mail))
                        {
                            $msg = "OK";
                        }
                        else
                        {
                            $msg = "NOK";
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function debloqueCompte()
    {
        global $toC_Json;

        $cuti = empty($_REQUEST['cuti']) ? '' : $_REQUEST['cuti'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $cuti = trim(strtolower($cuti));

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        } else {
            $query = "BEGIN delete from BKINTRALETBLK where trim(lower(UTI)) = '$cuti'; commit; delete from BKLETBLK where trim(lower(UTI)) = '$cuti'; commit; END;";

            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $response = array('success' => false, 'feedback' => 'Impossible de debloquer ce Compte ' . htmlentities($e['message']));
            } else {
                $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                if (!$r) {
                    $e = oci_error($s);
                    $response = array('success' => false, 'feedback' => 'Impossible de debloquer ce Compte ' . htmlentities($e['message']));
                } else {
                    $response = array('success' => true, 'feedback' => "Compte debloqué avec succes");
                }
            }
        }

        oci_free_statement($s);
        oci_close($c);

        echo $toC_Json->encode($response);
    }

    function changePwd()
    {
        global $toC_Json;

        $username = $_SESSION[admin][username];
        //$username = 'xxxx';

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter');
        }
        else
        {
            $db_user = $_REQUEST['db_user'];
            $label = $_REQUEST['label'];
            $databases_id = $_REQUEST['databases_id'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $account = $_REQUEST['account'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                $characters = '0123456789';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 5; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $pass = ucfirst(strtolower($account)) . $randomString;

                $query = "BEGIN EXECUTE IMMEDIATE 'alter user " . $account . " identified by " . $pass . "'; EXECUTE IMMEDIATE 'alter user " . $account . " account unlock'; END;";
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible de reinitialiser le mot de ce compte " . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de reinitialiser le mot de ce compte ' . htmlentities($e['message']));
                    } else {

                        osC_Users_Admin::saveUsermail($account, $_REQUEST['name'], $_REQUEST['email']);

                        $to = array();
                        $emails = explode(';', $_REQUEST['email']);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $cc = array();
                        if (isset($_REQUEST['cc']) && !empty($_REQUEST['cc'])) {
                            $emails = explode(';', $_REQUEST['cc']);

                            foreach ($emails as $email) {
                                if (!empty($email)) {
                                    $cc[] = osC_Mail::parseEmail($email);
                                }
                            }
                        }

                        $bcc = array();
                        if (isset($_REQUEST['bcc']) && !empty($_REQUEST['bcc'])) {
                            $emails = explode(';', $_REQUEST['bcc']);

                            foreach ($emails as $email) {
                                if (!empty($email)) {
                                    $bcc[] = osC_Mail::parseEmail($email);
                                }
                            }
                        }

                        $toC_Email_Account = new toC_Email_Account(4);

                        $body = "<p>Bonjour <strong>" . $_REQUEST['name'] . "</strong>,</p><p>Ci dessous vos parametres de connexion &agrave; la base <strong>" . $label . " </strong>:</p><table border='0' cellpadding='1' cellspacing='1' style='width: 300px;'><tbody><tr><td>Compte</td><td style='text-align: center;'>:</td><td><strong>" . $account . "</strong></td></tr><tr><td>Mot de passe</td><td style='text-align: center;'>:</td><td><strong>" . $pass . "</strong></td></tr></tbody></table><p>Cdt</p>";

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => 111,
                            'to' => $to,
                            'cc' => $cc,
                            'bcc' => $bcc,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Votre compte " . $account . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if ($toC_Email_Account->sendMailJob($mail)) {
                            $response = array('success' => true, 'feedback' => "Compte " . $account . " reinitialisé avec succes");
                        } else {
                            $response = array('success' => false, 'feedback' => "Compte " . $account . " a ete reinitialisé avec succes, mais le message n'a pu etre envoye, veuillez contacter votre administrateur");
                        }

                        $subscribers = osC_Users_Admin::getSubscribers($databases_id,'reset_password');

                        $to = array();
                        $emails = explode(';',$subscribers);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $body = "<p>Bonjour,</p><p>Le mot de passe du compte " . $account . " a été reinitialisé par " . $username . " sur la base " . $label . "</p>";

                        $toC_Email_Account = new toC_Email_Account(4);

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => 222,
                            'to' => $to,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Le mot de passe du compte " . $account . " a été reinitialisé par " . $username . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if($toC_Email_Account->sendMailJob($mail))
                        {
                            $msg = "OK";
                        }
                        else
                        {
                            $msg = "NOK";
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function createUser()
    {
        global $toC_Json;
        $username = $_SESSION[admin][username];

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter');
        }
        else
        {
            $db_user = $_REQUEST['db_user'];
            $label = $_REQUEST['label'];
            $databases_id = $_REQUEST['databases_id'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $account = $_REQUEST['account'];
            $tbs = $_REQUEST['tbs'];
            $temp = $_REQUEST['temp'];
            $profile = $_REQUEST['profile'];
            $roles = $_REQUEST['roles'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                $characters = '0123456789';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 5; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $pass = ucfirst(strtolower($account)) . $randomString;

                $query = "BEGIN EXECUTE IMMEDIATE 'create user " . $account . " identified by " . $pass . " DEFAULT TABLESPACE " . $tbs . " TEMPORARY TABLESPACE " . $temp . " PROFILE " . $profile . " QUOTA UNLIMITED ON " . $tbs . "';EXECUTE IMMEDIATE 'grant " . $roles . ",CONNECT,RESOURCE to " . $account . "'; END;";
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible de creer ce compte " . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce compte ' . $e['message']);
                    } else {

                        osC_Users_Admin::saveUsermail($account, $_REQUEST['name'], $_REQUEST['email']);

                        $to = array();
                        $emails = explode(';', $_REQUEST['email']);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $cc = array();
                        if (isset($_REQUEST['cc']) && !empty($_REQUEST['cc'])) {
                            $emails = explode(';', $_REQUEST['cc']);

                            foreach ($emails as $email) {
                                if (!empty($email)) {
                                    $cc[] = osC_Mail::parseEmail($email);
                                }
                            }
                        }

                        $bcc = array();
                        if (isset($_REQUEST['bcc']) && !empty($_REQUEST['bcc'])) {
                            $emails = explode(';', $_REQUEST['bcc']);

                            foreach ($emails as $email) {
                                if (!empty($email)) {
                                    $bcc[] = osC_Mail::parseEmail($email);
                                }
                            }
                        }

                        $toC_Email_Account = new toC_Email_Account(4);

                        $body = "<p>Bonjour " . $_REQUEST['name'] . ",</p><p>Ci dessous vos parametres de connexion à la base " . $label . " :</p><ul><li>Compte&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : " . $account . "</li><li>Mot de passe : " . $pass . "</li></ul><p>Cdt</p>";

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => $randomString . '111',
                            'to' => $to,
                            'cc' => $cc,
                            'bcc' => $bcc,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Votre compte " . $account . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if ($toC_Email_Account->sendMailJob($mail)) {
                            $response = array('success' => true, 'feedback' => "Compte " . $account . " cree avec succes");
                        } else {
                            $response = array('success' => false, 'feedback' => "Compte " . $account . " a ete cree avec succes, mais le message n'a pu etre envoye, veuillez contacter votre administrateur");
                        }

                        $subscribers = osC_Users_Admin::getSubscribers($databases_id,'create_account');

                        $to = array();
                        $emails = explode(';',$subscribers);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $body = "<p>Bonjour,</p><p>Le compte " . $account . " a été créé par " . $username . " sur la base " . $label . "</p>";

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => $randomString . '222',
                            'to' => $to,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Le compte " . $account . " a été créé par " . $username . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if($toC_Email_Account->sendMailJob($mail))
                        {
                            $msg = "OK";
                        }
                        else
                        {
                            $msg = "NOK";
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function createUserinfoc()
    {
        global $toC_Json;
        $username = $_SESSION[admin][username];

        if (empty($username)) {
            $response = array('success' => false, 'feedback' => 'Votre session est expirée ... vous devez vous reconnecter');
        }
        else
        {
            $db_user = $_REQUEST['db_user'];
            $label = $_REQUEST['label'];
            $databases_id = $_REQUEST['databases_id'];
            $db_pass = $_REQUEST['db_pass'];
            $db_host = $_REQUEST['db_host'];
            $db_sid = $_REQUEST['db_sid'];
            $account = $_REQUEST['account'];
            $tbs = 'CLIENTTBSN';
            $temp = 'TEMP';
            $profile = 'DEFAULT';
            $roles = $_REQUEST['roles'];

            $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
            if (!$c) {
                $e = oci_error();
                $response = array('success' => false, 'feedback' => 'Could not connect to database: ' . htmlentities($e['message']));
            } else {
                $characters = '0123456789';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 5; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $pass = ucfirst(strtolower($account)) . $randomString;

                $query = "BEGIN EXECUTE IMMEDIATE 'create user " . $account . " identified by " . $pass . " DEFAULT TABLESPACE " . $tbs . " TEMPORARY TABLESPACE " . $temp . " PROFILE " . $profile . " QUOTA UNLIMITED ON " . $tbs . "';EXECUTE IMMEDIATE 'grant " . $roles . ",CONNECT,RESOURCE to " . $account . "'; END;";
                $s = oci_parse($c, $query);
                if (!$s) {
                    $e = oci_error($c);
                    $response = array('success' => false, 'feedback' => "Impossible de creer ce compte " . htmlentities($e['message']));
                } else {
                    $r = oci_execute($s, OCI_COMMIT_ON_SUCCESS);
                    if (!$r) {
                        $e = oci_error($s);
                        $response = array('success' => false, 'feedback' => 'Impossible de creer ce compte ' . $e['message']);
                    } else {

                        osC_Users_Admin::saveUsermail($account, $_REQUEST['name'], $_REQUEST['email']);

                        $to = array();
                        $emails = explode(';', $_REQUEST['email']);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $cc = array();
                        if (isset($_REQUEST['cc']) && !empty($_REQUEST['cc'])) {
                            $emails = explode(';', $_REQUEST['cc']);

                            foreach ($emails as $email) {
                                if (!empty($email)) {
                                    $cc[] = osC_Mail::parseEmail($email);
                                }
                            }
                        }

                        $bcc = array();
                        if (isset($_REQUEST['bcc']) && !empty($_REQUEST['bcc'])) {
                            $emails = explode(';', $_REQUEST['bcc']);

                            foreach ($emails as $email) {
                                if (!empty($email)) {
                                    $bcc[] = osC_Mail::parseEmail($email);
                                }
                            }
                        }

                        $toC_Email_Account = new toC_Email_Account(4);

                        $body = "<p>Bonjour " . $_REQUEST['name'] . ",</p><p>Ci dessous vos parametres de connexion à la base " . $label . " :</p><ul><li>Compte&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : " . $account . "</li><li>Mot de passe : " . $pass . "</li></ul><p>Cdt</p>";

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => $randomString . '111',
                            'to' => $to,
                            'cc' => $cc,
                            'bcc' => $bcc,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Votre compte " . $account . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if ($toC_Email_Account->sendMailJob($mail)) {
                            $response = array('success' => true, 'feedback' => "Compte " . $account . " cree avec succes");
                        } else {
                            $response = array('success' => false, 'feedback' => "Compte " . $account . " a ete cree avec succes, mais le message n'a pu etre envoye, veuillez contacter votre administrateur");
                        }

                        $subscribers = osC_Users_Admin::getSubscribers($databases_id,'create_account');

                        $to = array();
                        $emails = explode(';',$subscribers);
                        foreach ($emails as $email) {
                            if (!empty($email)) {
                                $to[] = osC_Mail::parseEmail($email);
                            }
                        }

                        $body = "<p>Bonjour,</p><p>Le compte " . $account . " a été créé par " . $username . " sur la base " . $label . "</p>";

                        $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                            'id' => $randomString . '222',
                            'to' => $to,
                            'from' => $toC_Email_Account->getAccountName(),
                            'sender' => $toC_Email_Account->getAccountEmail(),
                            'subject' => "Le compte " . $account . " a été créé par " . $username . " sur la base " . $label,
                            'reply_to' => $toC_Email_Account->getAccountEmail(),
                            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                            'body' => $body,
                            'priority' => 1,
                            'content_type' => 'html',
                            'notification' => false,
                            'udate' => time(),
                            'date' => date('m/d/Y H:i:s'),
                            'fetch_timestamp' => time(),
                            'messages_flag' => EMAIL_MESSAGE_DRAFT,
                            'attachments' => null);

                        if($toC_Email_Account->sendMailJob($mail))
                        {
                            $msg = "OK";
                        }
                        else
                        {
                            $msg = "NOK";
                        }
                    }

                    oci_free_statement($s);
                }
            }

            oci_close($c);
        }

        echo $toC_Json->encode($response);
    }

    function debugProgram()
    {
        global $toC_Json;

        $nprg = empty($_REQUEST['nprg']) ? '' : $_REQUEST['nprg'];
        $mprg = empty($_REQUEST['mprg']) ? '' : $_REQUEST['mprg'];
        //$cuti = empty($_REQUEST['cuti']) ? $_SESSION['admin']['id'] : $_REQUEST['cuti'];
        $cuti = empty($_REQUEST['cuti']) ? '3820' : $_REQUEST['cuti'];

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $nprg = strtolower($nprg);
        $mprg = trim(strtolower($mprg)) . '.42r';

        $app_user = DEBUG_USER;
        $app_pass = DEBUG_PASS;
        $app_host = empty($_REQUEST['app_host']) ? APP_HOST : $_REQUEST['app_host'];

        $ssh = new Net_SSH2($app_host);
        if (!$ssh->login($app_user, $app_pass)) {
            $response = array('success' => false, 'feedback' => 'Impossible d etablir une connexion SSH');
        } else {
            $ssh->disableQuietMode();

            $cmd = 'echo cd ' . PROFILE_PATH . ' > /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo sh ' . PROFILE_SCRIPT . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export FGLSERVER=10.100.120.32:0 >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export FGLSQLDEBUG=3 >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

//                $cmd = 'echo export FGLGUIDEBUG=0 >> /tmp/run.sh';
//                $resp = $ssh->exec($cmd);

            $cmd = 'echo export FGLGUI=1 >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export FGLLDPATH=' . FGLLDPATH . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export DBPATH=' . DBPATH . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export LIBPATH=' . LIBPATH . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export ORACLE_HOME=' . ORACLE_HOME . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export LD_LIBRARY_PATH=' . LD_LIBRARY_PATH . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export DATABASE=' . GENERO_DATABASE . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export FGLDIR=' . FGLDIR . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export FGLPROFILE=' . FGLPROFILE . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = 'echo export BANK=' . BANK . ' >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = "find " . BANK . " -name " . $mprg;
            $fgl = $ssh->exec($cmd);
            $fgl = explode("\n", $fgl);
            $fgl = $fgl[0];

            $cmd = 'echo "' . PATH . '/fglrun ' . $fgl . ' ' . $cuti . ' ' . $nprg . '" >> /tmp/run.sh';
            $resp = $ssh->exec($cmd);

            $cmd = "sh /tmp/run.sh 2>/tmp/run.debug";
            $resp = $ssh->exec($cmd);

            $response = array('success' => true, 'feedback' => $resp);

            $ssh->disconnect();
        }

        echo $toC_Json->encode($response);
    }

    function addSubscriber()
    {
        global $osC_Database,$toC_Json;

        $name = $_REQUEST['name'];
        $email = $_REQUEST['email'];
        $event = $_REQUEST['event'];
        $databases_id = $_REQUEST['databases_id'];

        $osC_Database->startTransaction();

        $Qdel = $osC_Database->query('INSERT INTO delta_databases_subscribers (databases_id,event,nom,email) VALUES (:databases_id,:event,:nom,:email)');
        $Qdel->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
        $Qdel->bindInt(':databases_id', $databases_id);
        $Qdel->bindValue(':event', $event);
        $Qdel->bindValue(':nom', $name);
        $Qdel->bindValue(':email', $email);
        $Qdel->execute();

        if (!$osC_Database->isError()) {
            $osC_Database->commitTransaction();

            $response = array('success' => true, 'feedback' => 'Souscription enregistree avec succes');
        }
        else
        {
            $osC_Database->rollbackTransaction();
            $response = array('success' => false, 'feedback' => "Un probleme est survenu lors de l'enregistrement de cette souscription : " . $osC_Database->error);
        }

        echo $toC_Json->encode($response);
    }

    function deleteSubscriber()
    {
        global $osC_Database,$toC_Json;

        $email = $_REQUEST['email'];
        $event = $_REQUEST['event'];
        $databases_id = $_REQUEST['databases_id'];

        $osC_Database->startTransaction();

        $Qdel = $osC_Database->query('DELETE FROM delta_databases_subscribers where email = :email and event = :event and databases_id = :databases_id');
        $Qdel->bindInt(':databases_id', $databases_id);
        $Qdel->bindValue(':event', $event);
        $Qdel->bindValue(':email', $email);
        $Qdel->execute();

        if (!$osC_Database->isError()) {
            $osC_Database->commitTransaction();

            $response = array('success' => true, 'feedback' => 'Souscription supprimée avec succes');
        }
        else
        {
            $osC_Database->rollbackTransaction();
            $response = array('success' => false, 'feedback' => "Un probleme est survenu lors de la suppression de cette souscription : " . $osC_Database->error);
        }

        echo $toC_Json->encode($response);
    }

    function deleteUser()
    {
        global $toC_Json, $osC_Language;

        if (osC_Users_Admin::delete($_REQUEST['users_id'])) {
            $response['success'] = true;
            $response['feedback'] = $osC_Language->get('ms_success_action_performed');
        } else {
            $response['success'] = false;
            $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
        }

        echo $toC_Json->encode($response);
    }

    function deleteUsers()
    {
        global $toC_Json, $osC_Language;

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $id) {
            if (!osC_Users_Admin::delete($id)) {
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

        if (isset($_REQUEST['users_id']) && osC_Users_Admin::setStatus($_REQUEST['users_id'], (isset($_REQUEST['flag'])
            ? $_REQUEST['flag'] : null))
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }
}

?>
