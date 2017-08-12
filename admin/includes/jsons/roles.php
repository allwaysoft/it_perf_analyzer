<?php

if (!class_exists('osC_Roles_Admin')) {
    include('includes/classes/roles.php');
}

require ('includes/classes/osticket.php');

class toC_Json_Roles
{
    function listRoles()
    {
        global $toC_Json, $osC_Database;

        $username = $_SESSION['admin']['username'];
        $id = $_SESSION['admin']['id'];

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

//        if($username == 'admin')
//        {
//            $query = "select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) order by r.roles_name";
//            $Qadmin = $osC_Database->query($query);
//        }
//        else
//        {
//            $query = "select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) where r.roles_id in (select roles_id from :table_customers where administrators_id = :administrators_id) order by r.roles_name ";
//            $Qadmin = $osC_Database->query($query);
//            $Qadmin->bindTable(':table_customers', TABLE_CUSTOMERS);
//            $Qadmin->bindInt(':administrators_id', $id);
//        }

        $query = "select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) order by r.roles_name";
        $Qadmin = $osC_Database->query($query);

        $Qadmin->bindTable(':table_roles', TABLE_ROLES);
        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->setExtBatchLimit($start, $limit);
        $Qadmin->execute();

        $records = array();
        $records[] = array(
            'administrators_id' => -1,
            'roles_id' => -1,
            'user_name' => 'everyone',
            'email_address' => 'everyone@innovics.org',
            'roles_name' => 'Tout le monde',
            'roles_description' => 'Tout le monde',
            'src' => 'local',
            'hide' => true
        );

        $count = 0;

        while ($Qadmin->next()) {
            $records[] = array(
                'administrators_id' => $Qadmin->valueInt('id'),
                'roles_id' => $Qadmin->valueInt('roles_id'),
                'user_name' => $Qadmin->value('user_name'),
                'email_address' => $Qadmin->value('email_address'),
                'roles_name' => $Qadmin->value('roles_name'),
                'roles_description' => $Qadmin->value('roles_description'),
                'src' => 'local',
                'hide' => false
            );

            $count++;
        }
        $Qadmin->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function getRoles()
    {
        global $toC_Json, $osC_Database;

        $Qadmin = $osC_Database->query('select r.roles_id,r.roles_name from :table_roles r order by r.roles_name');
        $Qadmin->bindTable(':table_roles', TABLE_ROLES);
        $Qadmin->execute();

        $records = array();
        $count = 0;

        while ($Qadmin->next()) {
            $records[] = array(
                'roles_id' => $Qadmin->valueInt('roles_id'),
                'roles_name' => $Qadmin->value('roles_name'),
            );

            $count++;
        }
        $Qadmin->freeResult();

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

        $data = osC_Roles_Admin::getRole($_REQUEST['roles_id']);

        if(isset($data))
        {
            if (is_array($data['access_modules']) && !empty($data['access_modules'])) {
                if ($data['access_modules'][0] == '*')
                    $data['access_globaladmin'] = '1';
            }

            $response = array('success' => true, 'data' => $data);
        }
        else
        {
            $response = array('success' => false, 'data' => null);
        }

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

        $src = 'local';
        $roles_id = $_REQUEST['roles_id'];

        $characters = 'abcdefghijklmnopqrstuvxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $username = $randomString;

        $data = array('username' => $username,
            'password' => '12345',
            'roles_name' => $_REQUEST['roles_name'],
            'roles_description' => $_REQUEST['roles_description'],
            'email_address' => $randomString . '@innovics.org');

        $mod = $_REQUEST['modules'] . ',documents';
        $modules = null;
        if (isset($_REQUEST['modules']) && !empty($_REQUEST['modules'])) {
            $modules = explode(",", $mod);
        }

        if (isset($_REQUEST['access_globaladmin']) && ($_REQUEST['access_globaladmin'] == 'on')) {
            $modules = array('*');
        }

        if ($src == 'local') {
            $department_id = osC_Ticket_Admin::saveDepartment((isset ($_REQUEST ['department_id']) && is_numeric($_REQUEST ['department_id'] && $_REQUEST ['department_id'] != 0)
                ? $_REQUEST ['department_id'] : null),$data);

            //$department_id = $data['department_id'];

            if($department_id != -1 && $department_id != 0)
            {
                $data['department_id'] = $department_id;

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
                    case -5:
                        $response = array('success' => false, 'feedback' => $_SESSION['error']);
                        break;
                }
            }
            else
            {
                $response = array('success' => false, 'feedback' => "Impossible de creer le departement : " . $_SESSION['error']);
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

        if (osC_Roles_Admin::delete($_REQUEST['administrators_id'],$_REQUEST['roles_id'])) {
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

        $records = array();
        $id = $_SESSION['admin']['id'];

        if (empty($id)) {
            $records [] = array('roles_id' => null, 'id' => null, 'text' => 'Votre session est expirée ... vous devez vous reconnecter', 'icon' => 'templates/default/images/icons/16x16/trash.png', 'leaf' => true);
        }
        else
        {
            $username = $_SESSION['admin']['username'];

            if($username == 'admin')
            {
                $Qcategories = $osC_Database->query('SELECT COUNT(ur.administrators_id) AS count, r.administrators_id,r.roles_id, r.roles_name FROM :table_roles r LEFT OUTER JOIN :table_users_roles ur ON (r.roles_id = ur.roles_id) GROUP BY r.roles_name, r.roles_id ORDER BY r.roles_name,r.roles_id ASC');
                $Qcategories->bindTable(':table_roles', TABLE_ROLES);
                $Qcategories->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                $Qcategories->execute();

                $records [] = array('roles_id' => -1, 'id' => -1, 'text' => 'Tout le monde', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);

                while ($Qcategories->next()) {
                    $records [] = array('roles_id' => $Qcategories->value('roles_id'), 'id' => $Qcategories->value('roles_id'), 'text' => $Qcategories->value('roles_name') . ' (' . $Qcategories->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);
                }

                $Qcategories->freeResult();
            }
            else
            {
                $Qcategories = $osC_Database->query('SELECT COUNT(ur.administrators_id) AS count, r.administrators_id,r.roles_id, r.roles_name FROM :table_roles r LEFT OUTER JOIN :table_users_roles ur ON (r.roles_id = ur.roles_id) where r.roles_id in (select roles_id from :table_customers where administrators_id = :administrators_id) GROUP BY r.roles_name, r.roles_id ORDER BY r.roles_name,r.roles_id ASC');
                $Qcategories->bindTable(':table_roles', TABLE_ROLES);
                $Qcategories->bindTable(':table_customers', TABLE_CUSTOMERS);
                $Qcategories->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                $Qcategories->bindInt(':administrators_id', $id);
                $Qcategories->execute();

                while ($Qcategories->next()) {
                    $records [] = array('roles_id' => $Qcategories->value('roles_id'), 'id' => $Qcategories->value('roles_id'), 'text' => $Qcategories->value('roles_name') . ' (' . $Qcategories->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'leaf' => true);
                }

                $Qcategories->freeResult();
            }
        }

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
