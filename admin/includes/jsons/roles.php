<?php

if (!class_exists('osC_Roles_Admin')) {
    include('includes/classes/roles.php');
}

class toC_Json_Roles
{
    function listRoles()
    {
        global $toC_Json;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

//        $Qadmin = $osC_Database->query('select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) order by r.roles_name');
//        $Qadmin->bindTable(':table_roles', TABLE_ROLES);
//        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
//        $Qadmin->setExtBatchLimit($start, $limit);
//        $Qadmin->execute();
//
//        $records = array();
//        $records[] = array(
//            'administrators_id' => -1,
//            'roles_id' => -1,
//            'user_name' => 'everyone',
//            'email_address' => 'everyone@everyone.com',
//            'roles_name' => 'Tout le monde',
//            'roles_description' => 'Tout le monde',
//            'src' => 'local'
//        );
//
//        while ($Qadmin->next()) {
//            $records[] = array(
//                'administrators_id' => $Qadmin->valueInt('id'),
//                'roles_id' => $Qadmin->valueInt('roles_id'),
//                'user_name' => $Qadmin->value('user_name'),
//                'email_address' => $Qadmin->value('email_address'),
//                'roles_name' => $Qadmin->value('roles_name'),
//                'roles_description' => $Qadmin->value('roles_description'),
//                'src' => 'local'
//            );
//        }
//        $Qadmin->freeResult();

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $s = oci_parse($c, "SELECT trim(CACC) CACC,ltrim(LIB1) LIB1 FROM BKNOM WHERE CTAB = '994'");
        if (!$s) {
            $e = oci_error($c);
            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
        }

        $r = oci_execute($s);
        if (!$r) {
            $e = oci_error($s);
            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
        }

        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records[] = array(
                'administrators_id' => '-1',
                'roles_id' => $row['CACC'],
                'user_name' => $row['CACC'],
                'email_address' => 'everyone@everyone.com',
                'roles_name' => $row['CACC'],
                'roles_description' => $row['LIB1'],
                'src' => 'extern'
            );

            $count++;
        }

        oci_free_statement($r);
        oci_close($c);

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listUsers()
    {
        global $toC_Json,$osC_Database;

        $roles = array();

        $roles[] = array(
            'roles_id' => '-1',
            'user_name' => 'everyone',
            'email_address' => ALL_EMAIL,
            'roles_name' => 'Tout le monde',
            'roles_description' => 'Tout le monde',
            'icon' => osc_icon('folder_account.png')
        );

        $total = 1;
        $tot = 0;

        $Qadmin = $osC_Database->query('select id, user_name, email_address from :table_administrators where id != 1 order by user_name');
        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->execute();

        if ($osC_Database->isError()) {
            $total = $total + 1;
            var_dump($osC_Database);
            $roles[] = array(
                'administrators_id' => 0,
                'roles_id' => 'error',
                'user_name' => 'error',
                'email_address' => '',
                'roles_name' => 'error',
                'roles_description' => 'error',
                'icon' => osc_icon('xxx.error')
            );
        }

        while ($Qadmin->next()) {
            $total = $total + 1;
            $roles[] = array(
                'administrators_id' => $Qadmin->value('id'),
                'roles_id' => $Qadmin->value('user_name'),
                'user_name' => $Qadmin->value('user_name'),
                'email_address' => $Qadmin->value('email_address'),
                'roles_name' => $Qadmin->value('user_name'),
                'roles_description' => 'Utilisateur local',
                'icon' => osc_icon('folder_account.png')
            );
        }
        $Qadmin->freeResult();


        if(AUTH == 'amplitude' && isset($db_user) && !empty($db_user) && isset($db_pass) && !empty($db_pass) && isset($db_host) && !empty($db_host) && isset($db_sid) && !empty($db_sid))
        {
            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
            $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
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

            if(!empty($search))
            {
                $start = 0;
                $limit = 10000;

                $query="SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM(RTRIM (LIB)) LIB,0 TOTAL,EMAIL,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) WHERE EVUTI.SUS = 'N' AND (LOWER (evuti.cuti) LIKE :cuti OR LOWER (unix) LIKE :unix OR LOWER (lib) LIKE :lib) ORDER BY LTRIM (LIB)";
            }
            else
            {
                $query="SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (SELECT TRIM (EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,(SELECT COUNT (*) FROM evuti) TOTAL,EMAIL,UNIX FROM EVUTI LEFT OUTER JOIN EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) WHERE EVUTI.SUS = 'N' ORDER BY LTRIM (LIB)) a WHERE ROWNUM <= :MAX_ROW_TO_FETCH) WHERE rnum >= :MIN_ROW_TO_FETCH";
            }

            $fin = $start == 0 ? $start + $limit - 1 : $start + $limit;
            $s = oci_parse($c, $query);
            if (!$s) {
                $e = oci_error($c);
                $total = $total + 1;
                $roles[] = array(
                    'administrators_id' => 0,
                    'roles_id' => 'error',
                    'user_name' => 'error',
                    'email_address' => '',
                    'roles_name' => 'error',
                    'roles_description' => $e['message'],
                    'icon' => osc_icon('xxx.error')
                );
            }
            else
            {
                $search = '%' . strtolower($search) . '%';
                oci_bind_by_name($s, ":MAX_ROW_TO_FETCH", $fin);
                oci_bind_by_name($s, ":MIN_ROW_TO_FETCH", $start);
                oci_bind_by_name($s, ":cuti",$search);
                oci_bind_by_name($s, ":unix",$search);
                oci_bind_by_name($s, ":lib",$search);

                $r = oci_execute($s);
                if (!$r) {
                    $e = oci_error($s);
                    $total = $total + 1;
                    $roles[] = array(
                        'administrators_id' => 0,
                        'roles_id' => 'error',
                        'user_name' => 'error',
                        'email_address' => '',
                        'roles_name' => 'error',
                        'roles_description' => $e['message'],
                        'icon' => osc_icon('xxx.error')
                    );
                }
                else
                {
                    while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                        if(isset($row['CUTI']) && !empty($row['CUTI']))
                        {
                            $roles[] = array(
                                $tot = $row['TOTAL'],
                                'administrators_id' => $row['CUTI'],
                                'roles_id' => $row['CUTI'],
                                'user_name' => $row['UNIX'],
                                'email_address' => $row['EMAIL'],
                                'roles_name' => $row['LIB'] . " ( " . $row['CUTI'] . " )",
                                'roles_description' => 'Utilisateur AMPLITUDE',
                                'icon' => osc_icon('folder_account.png')
                            );
                        }
                    }
                }

                oci_free_statement($r);
            }

            oci_close($c);
        }

        $response = array(EXT_JSON_READER_TOTAL => $total + $tot,
            EXT_JSON_READER_ROOT => $roles);

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

            if (osC_Access::hasAccess($module)) {
                $module = 'osC_Access_' . ucfirst($module);
                $module = new $module();
                $title = osC_Access::getGroupTitle($module->getGroup());

                $access_modules_array[$title][] = array('id' => $module->getModule(),
                    'text' => $module->getTitle(),
                    'leaf' => true);
            }
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

    function loadRole()
    {
        global $toC_Json;

        $data = osC_Roles_Admin::getData($_REQUEST['roles_id'], $_REQUEST['src']);

        if (is_array($data['access_modules']) && !empty($data['access_modules'])) {
            if ($data['access_modules'][0] == '*')
                $data['access_globaladmin'] = '1';
        }

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function loadUser()
    {
        global $toC_Json;

        $data = osC_Roles_Admin::getData($_REQUEST['roles_id'], $_REQUEST['src']);

        if (is_array($data['access_modules']) && !empty($data['access_modules'])) {
            if ($data['access_modules'][0] == '*')
                $data['access_globaladmin'] = '1';
        }

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function saveRole()
    {
        global $toC_Json, $osC_Language;

        $username = $_REQUEST['roles_id'];
        $src = 'extern';
        $roles_id = $_REQUEST['roles_id'];
        $username = strtolower($username);
        $username = str_replace(' ', '_', $username);

        $data = array('username' => $username,
            'password' => '12345',
            'roles_name' => $_REQUEST['roles_name'],
            'roles_description' => $_REQUEST['roles_description'],
            'email_address' => $username . '@gmail.com');

        $mod = $_REQUEST['modules'] . ',documents';
        $modules = null;
        if (isset($_REQUEST['modules']) && !empty($_REQUEST['modules'])) {
            $modules = explode(",", $mod);
        }

        if (isset($_REQUEST['access_globaladmin']) && ($_REQUEST['access_globaladmin'] == 'on')) {
            $modules = array('*');
        }

        if (AUTH == 'local') {
            switch (osC_Roles_Admin::save((isset($_REQUEST['roles_id']) && is_numeric($_REQUEST['administrators_id'])
                ? $_REQUEST['administrators_id']
                : null), $data, $modules, (isset($_REQUEST['roles_id'])
                ? $_REQUEST['roles_id'] : null))) {
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
        } else {
            switch (osC_Roles_Admin::saveExt((isset($_REQUEST['administrators_id'])
                ? $_REQUEST['administrators_id']
                : null), $modules, $roles_id)) {
                case 1:
                    if (isset($_REQUEST['administrators_id']) && is_numeric($_REQUEST['administrators_id']) && ($_REQUEST['administrators_id'] == $_SESSION['admin']['id'])) {
                        $_SESSION['admin']['access'] = osC_Access::getUserLevels($_REQUEST['administrators_id']);
                    }

                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                    break;

                case -1:
                    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                    break;
            }
        }

        echo $toC_Json->encode($response);
    }

    function deleteRole()
    {
        global $toC_Json, $osC_Language;

        if (osC_Roles_Admin::delete($_REQUEST['adminId'])) {
            $response['success'] = true;
            $response['feedback'] = $osC_Language->get('ms_success_action_performed');
        } else {
            $response['success'] = false;
            $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
        }

        echo $toC_Json->encode($response);
    }

    function loadRolesTree()
    {
        global $osC_Database, $toC_Json;

        $Qcategories = $osC_Database->query('SELECT COUNT(ur.administrators_id) AS count, r.administrators_id,r.roles_id, r.roles_name FROM :table_roles r LEFT OUTER JOIN :table_users_roles ur ON (r.roles_id = ur.roles_id) GROUP BY r.roles_name, r.roles_id ORDER BY r.roles_name,r.roles_id ASC');
        $Qcategories->bindTable(':table_roles', TABLE_ROLES);
        $Qcategories->bindTable(':table_users_roles', TABLE_USERS_ROLES);
        $Qcategories->execute();

        $records = array();

        $records [] = array('roles_id' => -1, 'id' => -1, 'text' => 'Tout le monde', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);

        while ($Qcategories->next()) {
            $records [] = array('roles_id' => $Qcategories->value('roles_id'), 'id' => $Qcategories->value('roles_id'), 'text' => $Qcategories->value('roles_name') . ' (' . $Qcategories->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);
        }

        $Qcategories->freeResult();

        echo $toC_Json->encode($records);
    }

    function loadAgencesTreeCompte()
    {
        global $toC_Json;

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $query = "SELECT ag.age, ag.LIB, COUNT (*) AS COUNT FROM bank.bkcom cx INNER JOIN bank.bkage ag ON cx.age = ag.age GROUP BY ag.age, ag.lib ORDER BY lib";

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

        $records [] = array('agences_id' => -1, 'id' => -1, 'text' => 'Toutes les Agences', 'icon' => 'templates/default/images/icons/16x16/home.png', 'leaf' => true);

        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('agences_id' => $row['AGE'], 'id' => $row['AGE'], 'count' => $row['COUNT'], 'text' => $row['LIB'] . ' ( ' . $row['COUNT'] . ' )', 'icon' => 'templates/default/images/icons/16x16/home.png', 'leaf' => true);
            $count = $count + $row['COUNT'];
        }

        $records[0]['text'] = "Toutes les Agences " . ' ( ' . $count . ' )';
        $records[0]['count'] = $count;

        oci_free_statement($r);
        oci_close($c);

        echo $toC_Json->encode($records);
    }

    function loadAgencesTreeCarte()
    {
        global $toC_Json;

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];
        $src = $_REQUEST['src'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $query = "SELECT ag.age, ag.LIB, COUNT (*) AS COUNT FROM bank.bkcadab ca INNER JOIN bank.moctr mo ON ca.nctr = mo.nctr INNER JOIN bank.bkage ag ON ca.age = ag.age GROUP BY ag.age, ag.lib ORDER BY lib";

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

        $records [] = array('agences_id' => -1, 'id' => -1, 'text' => 'Toutes les Agences', 'icon' => 'templates/default/images/icons/16x16/home.png', 'leaf' => true);

        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('agences_id' => $row['AGE'], 'id' => $row['AGE'], 'count' => $row['COUNT'], 'text' => $row['LIB'] . ' ( ' . $row['COUNT'] . ' )', 'icon' => 'templates/default/images/icons/16x16/home.png', 'leaf' => true);
            $count = $count + $row['COUNT'];
        }

        $records[0]['text'] = "Toutes les Agences " . ' ( ' . $count . ' )';
        $records[0]['count'] = $count;

        oci_free_statement($r);
        oci_close($c);

        echo $toC_Json->encode($records);
    }

    function loadAgencesTreeDelta()
    {
        global $toC_Json;

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];
        $src = $_REQUEST['src'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        if($src == 'ctx')
        {
            $query = "SELECT ag.code_agence age, ag.libelle_agence LIB, COUNT (*) AS COUNT FROM content.cxclients c INNER JOIN content.cxagence ag ON c.code_agence = ag.code_agence GROUP BY ag.code_agence, ag.libelle_agence ORDER BY libelle_agence";
        }
        else
        {
            $query = "select ag.age,ag.LIB,COUNT (*) as count from bank.bkctxcli cx inner join bank.bkcli c on cx.cli = c.cli inner join bank.bkage ag on c.age = ag.age group by ag.age,ag.lib order by lib";
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

        $records = array();

        $records [] = array('agences_id' => -1, 'id' => -1, 'text' => 'Toutes les Agences', 'icon' => 'templates/default/images/icons/16x16/home.png', 'leaf' => true);

        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('agences_id' => $row['AGE'], 'id' => $row['AGE'], 'count' => $row['COUNT'], 'text' => $row['LIB'] . ' ( ' . $row['COUNT'] . ' )', 'icon' => 'templates/default/images/icons/16x16/home.png', 'leaf' => true);
            $count = $count + $row['COUNT'];
        }

        $records[0]['text'] = "Toutes les Agences " . ' ( ' . $count . ' )';
        $records[0]['count'] = $count;

        oci_free_statement($r);
        oci_close($c);

        echo $toC_Json->encode($records);
    }

    function loadRolesTreeDelta()
    {
        global $toC_Json;

        $db_user = empty($_REQUEST['db_user']) ? DB_USER : $_REQUEST['db_user'];
        $db_pass = empty($_REQUEST['db_pass']) ? DB_PASS : $_REQUEST['db_pass'];
        $db_host = empty($_REQUEST['db_host']) ? DB_HOST : $_REQUEST['db_host'];
        $db_sid = empty($_REQUEST['db_sid']) ? DB_SID : $_REQUEST['db_sid'];

        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
        if (!$c) {
            $e = oci_error();
            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
        }

        $s = oci_parse($c, "SELECT CACC, LIB1, COUNT (*) as count FROM BKNOM INNER JOIN EVUTI ON (CACC = PUTI) WHERE CTAB = '994' GROUP BY cacc, lib1 ORDER BY lib1");
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

        $records [] = array('roles_id' => -1, 'id' => -1, 'text' => 'Tout le monde', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);

        $count = 0;

        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
            $records [] = array('roles_id' => $row['CACC'], 'id' => $row['CACC'], 'count' => $row['COUNT'], 'text' => $row['LIB1'] . ' ( ' . $row['COUNT'] . ' )', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);
            $count = $count + $row['COUNT'];
        }

        $records[0]['text'] = "Tous " . ' ( ' . $count . ' )';
        $records[0]['count'] = $count;

        oci_free_statement($r);
        oci_close($c);

        echo $toC_Json->encode($records);
    }

    function deleteRoles()
    {
        global $toC_Json, $osC_Language;

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $id) {
            if (!osC_Roles_Admin::delete($id)) {
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
}

?>
