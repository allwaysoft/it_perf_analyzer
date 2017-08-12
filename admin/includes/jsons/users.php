<?php

if (!class_exists('osC_Users_Admin')) {
    require('includes/classes/users.php');
}

if (!class_exists('content')) {
    require('includes/classes/content.php');
}
//    require('includes/classes/roles.php');
require('includes/classes/image.php');
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

        $username = $_SESSION['admin']['username'];
        $id = $_SESSION['admin']['id'];

        if($username == 'admin')
        {
            $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" ';
        }
        else
        {
            $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" and a.id in (select administrators_id from :table_users_roles where roles_id in (select roles_id from :table_customers where administrators_id = :administrators_id))';
        }

        $Qadmin = $osC_Database->query($query);
        if ($roles_id != 0 && $roles_id != -1) {
            $Qadmin->appendQuery('and u.administrators_id IN (SELECT administrators_id FROM :table_users_roles WHERE roles_id = :roles_id)');
            $Qadmin->bindTable(':table_users_roles', TABLE_USERS_ROLES);
            $Qadmin->bindInt(':roles_id', $roles_id);
        }

        if($username != 'admin')
        {
            $Qadmin->bindTable(':table_users_roles', TABLE_USERS_ROLES);
            $Qadmin->bindTable(':table_customers', TABLE_CUSTOMERS);
            $Qadmin->bindInt(':administrators_id', $id);
        }

        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->bindTable(':tables_users', TABLE_USERS);
        $Qadmin->setExtBatchLimit($start, $limit);
        $Qadmin->execute();

        $records = array();
        while ($Qadmin->next()) {
            $data = array(
                'users_id' => $Qadmin->valueInt('users_id'),
                'staff_id' => $Qadmin->valueInt('staff_id'),
                'administrators_id' => $Qadmin->valueInt('administrators_id'),
                'user_name' => $Qadmin->value('user_name'),
                'status' => $Qadmin->value('status'),
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

    function getUsers()
    {
        global $toC_Json, $osC_Database;

        $roles_id = empty($_REQUEST['roles_id']) ? 0 : $_REQUEST['roles_id'];

        $Qadmin = $osC_Database->query('SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" ');
        if ($roles_id != 0 && $roles_id != -1) {
            $Qadmin->appendQuery('and u.administrators_id IN (SELECT administrators_id FROM :table_users_roles WHERE roles_id = :roles_id)');
            $Qadmin->bindTable(':table_users_roles', TABLE_USERS_ROLES);
            $Qadmin->bindInt(':roles_id', $roles_id);
        }

        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->bindTable(':tables_users', TABLE_USERS);
        $Qadmin->execute();

        $records = array();
        while ($Qadmin->next()) {
            $data = array(
                'administrators_id' => $Qadmin->valueInt('administrators_id'),
                'user_name' => $Qadmin->value('user_name')
            );
            $records[] = $data;

        }
        $Qadmin->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $Qadmin->getBatchSize(),
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
        global $toC_Json, $osC_Language;

        $data = array('user_name' => $_REQUEST['user_name'],
            'content_name' => $_REQUEST['content_name'],
            'content_description' => $_REQUEST['content_description'],
            'page_title' => '',
            'meta_keywords' => '',
            'meta_descriptions' => '',
            'password' => $_REQUEST['user_password'],
            'description' => $_REQUEST['description'],
            'status' => $_REQUEST['status'],
            'email_address' => $_REQUEST['email_address']);

        $modules = array();
        if (isset($_REQUEST['roles_id'])) {
            $data['roles_id'] = explode(',', $_REQUEST['roles_id']);

            if (is_array($data['roles_id'])) {
                $mod = $_REQUEST['modules'];
                if (isset($_REQUEST['modules']) && !empty($_REQUEST['modules'])) {
                    $modules = explode(",", $mod);
                }

                if (isset($_REQUEST['access_globaladmin']) && ($_REQUEST['access_globaladmin'] == 'on')) {
                    $modules = array('*');
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
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . $_SESSION['error']);
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
            $response = array('success' => false, 'feedback' => 'Vous devez selectionner au moins un role pour cet utilisateur '  . $_SESSION['error']);
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

            $response = array('success' => true, 'feedback' => 'Souscription supprim�e avec succes');
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
