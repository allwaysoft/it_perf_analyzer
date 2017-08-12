<?php

define('OSC_ADMINISTRATORS_ACCESS_MODE_ADD', 'add');
define('OSC_ADMINISTRATORS_ACCESS_MODE_SET', 'set');
define('OSC_ADMINISTRATORS_ACCESS_MODE_REMOVE', 'remove');

if (!class_exists('osC_Users_Admin')) {
    include('includes/classes/users.php');
}

class osC_Roles_Admin
{
    function getData($id, $src)
    {
        global $osC_Database;

        $data = null;

        if ($src == 'local') {
            if ($id != -1) {
                $Qadmin = $osC_Database->query('select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) where r.roles_id = :id');
                $Qadmin->bindTable(':table_roles', TABLE_ROLES);
                $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
                $Qadmin->bindInt(':id', $id);
                $Qadmin->execute();
            }

            $modules = array('access_modules' => array());

            $Qaccess = $osC_Database->query('select module from :table_administrators_access where administrators_id = (select administrators_id from :table_roles where roles_id = :roles_id)');
            $Qaccess->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
            $Qaccess->bindTable(':table_roles', TABLE_ROLES);
            $Qaccess->bindInt(':roles_id', $id);
            $Qaccess->execute();

            while ($Qaccess->next()) {
                $modules['access_modules'][] = $Qaccess->value('module');
            }

            $admin = $id != -1 ? $Qadmin->toArray() : array(
                'administrators_id' => -1,
                'roles_id' => -1,
                'user_name' => 'everyone',
                'email_address' => 'everyone@innovics.org',
                'roles_name' => 'Tout le monde',
                'roles_description' => 'Tout le monde'
            );

            if ($id != -1) {
                $Qadmin->freeResult();
            }

            if (is_array($admin)) {
                $data = array_merge($admin, $modules);
            } else {
                $data = $modules;
            }

            unset($modules);
            $Qaccess->freeResult();
        }

        return $data;
    }

    function getRole($id)
    {
        global $osC_Database;

        $osC_Database->selectDatabase(DB_DATABASE);

        $data = null;

        $Qadmin = $osC_Database->query('select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) where r.roles_id = :id');
        $Qadmin->bindTable(':table_roles', TABLE_ROLES);
        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->bindValue(':id', $id);
        $Qadmin->execute();

        if (!$osC_Database->isError()) {
            return $Qadmin->toArray();
        }

        return $data;
    }

    function save($id = null, $data, $modules = null, $roles_id = null)
    {
        global $osC_Database;

        $error = false;
        if (osc_validate_email_address($data['email_address'])) {
            $QcheckEmail = $osC_Database->query('select id from :table_administrators where email_address = :email_address');
            $QcheckEmail->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $QcheckEmail->bindValue(':email_address', $data['email_address']);

            if (is_numeric($id)) {
                $QcheckEmail->appendQuery('and id != :id');
                $QcheckEmail->bindInt(':id', $id);
            }

            $QcheckEmail->execute();

            if ($osC_Database->isError()) {
                $_SESSION['error'] = $osC_Database->getError();
                //var_dump($QcheckEmail);
                return -5;
            }
            if ($QcheckEmail->numberOfRows() > 0) {
                return -4;
            }
        } else {
            return -3;
        }

        $Qcheck = $osC_Database->query('select id from :table_administrators where user_name = :user_name');

        if (is_numeric($id)) {
            $Qcheck->appendQuery('and id != :id');
            $Qcheck->bindInt(':id', $id);
        }

        $Qcheck->appendQuery('limit 1');
        $Qcheck->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qcheck->bindValue(':user_name', $data['username']);
        $Qcheck->execute();

        if ($osC_Database->isError()) {
            $_SESSION['error'] = $osC_Database->getError();
            //var_dump($Qcheck);
            return -5;
        }

        if ($Qcheck->numberOfRows() < 1) {
            $osC_Database->startTransaction();

            if (is_numeric($id)) {
                $Qadmin = $osC_Database->query('update :table_administrators set user_name = :user_name, email_address = :email_address');

                if (isset($data['password']) && !empty($data['password'])) {
                    $Qadmin->appendQuery(', user_password = :user_password');
                    $Qadmin->bindValue(':user_password', osc_encrypt_string(trim($data['password'])));
                }

                $Qadmin->appendQuery('where id = :id');
                $Qadmin->bindInt(':id', $id);
            } else {
                $Qadmin = $osC_Database->query('insert into :table_administrators (user_name, user_password, email_address) values (:user_name, :user_password, :email_address)');
                $Qadmin->bindValue(':user_password', osc_encrypt_string(trim($data['password'])));
            }

            $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qadmin->bindValue(':user_name', $data['username']);
            $Qadmin->bindValue(':email_address', $data['email_address']);
            $Qadmin->setLogging($_SESSION['module'], $id);
            $Qadmin->execute();

            if ($osC_Database->isError()) {
                $_SESSION['error'] = $osC_Database->getError();
                //var_dump($Qadmin);
                return -5;
            }

            if (!$osC_Database->isError()) {
                if (!is_numeric($id)) {
                    $id = $osC_Database->nextID();
                }
            } else {
                $error = true;
            }

            if ($error === false) {
                if (!empty($modules)) {
                    if (in_array('*', $modules)) {
                        $modules = array('*');
                    }

                    foreach ($modules as $module) {
                        $Qcheck = $osC_Database->query('select administrators_id from :table_administrators_access where administrators_id = :administrators_id and module = :module limit 1');
                        $Qcheck->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                        $Qcheck->bindInt(':administrators_id', $id);
                        $Qcheck->bindValue(':module', $module);
                        $Qcheck->execute();

                        if ($osC_Database->isError()) {
                            $_SESSION['error'] = $osC_Database->getError();
                            //var_dump($Qcheck);
                            return -5;
                        }

                        if ($Qcheck->numberOfRows() < 1) {
                            $Qinsert = $osC_Database->query('insert into :table_administrators_access (administrators_id, module) values (:administrators_id, :module)');
                            $Qinsert->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                            $Qinsert->bindInt(':administrators_id', $id);
                            $Qinsert->bindValue(':module', $module);
                            $Qinsert->setLogging($_SESSION['module'], $id);
                            $Qinsert->execute();

                            if ($osC_Database->isError()) {
                                $_SESSION['error'] = $osC_Database->getError();
                                //var_dump($Qinsert);
                                return -5;
                            }
                        }
                    }
                }
            }

            if ($error === false) {
                $Qdel = $osC_Database->query('delete from :table_administrators_access where administrators_id = :administrators_id');

                if (!empty($modules)) {
                    $Qdel->appendQuery('and module not in (":module")');
                    $Qdel->bindRaw(':module', implode('", "', $modules));
                }

                $Qdel->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                $Qdel->bindInt(':administrators_id', $id);
                $Qdel->setLogging($_SESSION['module'], $id);
                $Qdel->execute();

                if ($osC_Database->isError()) {
                    $_SESSION['error'] = $osC_Database->getError();
                    //var_dump($Qdel);
                    return -5;
                }
            }

            if ($error === false) {
                if (is_numeric($roles_id)) {
                    $Qrole = $osC_Database->query('update :table_roles set roles_description = :roles_description, roles_name = :roles_name,administrators_id = :administrators_id where roles_id = :roles_id');
                    $Qrole->bindInt(':roles_id', $roles_id);
                } else {
                    $Qrole = $osC_Database->query('insert into :table_roles (roles_description, roles_name, administrators_id) values (:roles_description, :roles_name, :administrators_id,:department_id)');
                }

                $Qrole->bindTable(':table_roles', TABLE_ROLES);
                $Qrole->bindInt(':administrators_id', $id);
                $Qrole->bindValue(':roles_description', $data['roles_description']);
                $Qrole->bindValue(':roles_name', $data['roles_name']);
                $Qrole->setLogging($_SESSION['module'], $id);
                $Qrole->execute();

                if ($osC_Database->isError()) {
                    $_SESSION['error'] = $osC_Database->getError();
                    //var_dump($Qrole);
                    return -5;
                }
            }

            if ($error === false) {
                if ($error === false && is_numeric($roles_id)) {
                    $Qadmin = $osC_Database->query('SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) INNER JOIN :table_users_roles ur ON (u.administrators_id = ur.administrators_id) ');
                    $Qadmin->appendQuery('where ur.roles_id = :roles_id');
                    $Qadmin->bindInt(':roles_id', $roles_id);
                    $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
                    $Qadmin->bindTable(':tables_users', TABLE_USERS);
                    $Qadmin->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                    $Qadmin->execute();

                    if ($osC_Database->isError()) {
                        $_SESSION['error'] = $osC_Database->getError();
                        //var_dump($Qadmin);
                        return -5;
                    }

                    $users = array();
                    while ($Qadmin->next()) {
                        $users[] = $Qadmin->toArray();
                    }

                    foreach ($users as $user) {
                        $user['password'] = null;
                        $user['delimage'] = '0';
                        $user['roles_id'] = osC_Access::getUserRoles($user['administrators_id']);
                        $user['access_modules'] = array();
                        $modules = array();
                        if (is_array($user['roles_id'])) {
                            foreach ($user['roles_id'] as $id) {
                                $_modules = osC_Users_Admin::getRolesModules($id);
                                if (is_array($_modules)) {
                                    $user['access_modules'] = array_merge($user['access_modules'], $_modules);
                                }
                            }
                        }

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
                        if (in_array('*', $modules)) {
                            $modules = array('*');
                        } else {
                            $modules = array_unique($modules);
                        }

                        if (osC_Users_Admin::save((isset($user['administrators_id']) && is_numeric($user['administrators_id'])
                                ? $user['administrators_id'] : null), $user, $modules) != 1
                        ) {
                            $error = true;
                        }
                    }
                }
            }

            if ($error === false) {
                $osC_Database->commitTransaction();
                return 1;
            } else {
                $osC_Database->rollbackTransaction();

                return -1;
            }
        } else {
            return -2;
        }
    }

    function saveExt($id = null,$modules = null,$roles_id = null)
    {
        global $osC_Database;

        $error = false;

        if (!empty($modules)) {
            if (in_array('*', $modules)) {
                $modules = array('*');
            }

            foreach ($modules as $module) {
                $Qcheck = $osC_Database->query('select administrators_id from :table_administrators_access where administrators_id = :administrators_id and module = :module limit 1');
                $Qcheck->bindTable(':table_administrators_access', TABLE_DELTA_ACCESS);
                $Qcheck->bindValue(':administrators_id', $roles_id);
                $Qcheck->bindValue(':module', $module);
                $Qcheck->execute();

                if ($Qcheck->numberOfRows() < 1) {
                    $Qinsert = $osC_Database->query('insert into :table_administrators_access (administrators_id, module) values (:administrators_id, :module)');
                    $Qinsert->bindTable(':table_administrators_access', TABLE_DELTA_ACCESS);
                    $Qinsert->bindValue(':administrators_id', $roles_id);
                    $Qinsert->bindValue(':module', $module);
                    $Qinsert->setLogging($_SESSION['module'], $id);
                    $Qinsert->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                        break;
                    }
                }
            }
        }

        if ($error === false) {
            $Qdel = $osC_Database->query('delete from :table_administrators_access where administrators_id = :administrators_id');

            if (!empty($modules)) {
                $Qdel->appendQuery('and module not in (":module")');
                $Qdel->bindRaw(':module', implode('", "', $modules));
            }

            $Qdel->bindTable(':table_administrators_access', TABLE_DELTA_ACCESS);
            $Qdel->bindValue(':administrators_id', $roles_id);
            $Qdel->setLogging($_SESSION['module'], $id);
            $Qdel->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }
        }


        if ($error === false) {
            $osC_Database->commitTransaction();
            return 1;
        } else {
            $osC_Database->rollbackTransaction();

            return -1;
        }
    }

    function delete($administrators_id,$roles_id)
    {
        global $osC_Database;

        $osC_Database->startTransaction();

        $Qdel = $osC_Database->query('delete from :table_administrators_access where administrators_id = :administrators_id');
        $Qdel->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
        $Qdel->bindInt(':administrators_id', $administrators_id);
        $Qdel->execute();

        if (!$osC_Database->isError()) {
            $Qdel = $osC_Database->query('delete from :table_administrators where id = :id');
            $Qdel->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qdel->bindInt(':id', $administrators_id);
            $Qdel->execute();
        }

        if (!$osC_Database->isError()) {
            $Qdel = $osC_Database->query('delete from :table_roles where roles_id = :id');
            $Qdel->bindTable(':table_roles', TABLE_ROLES);
            $Qdel->bindInt(':id', $roles_id);
            $Qdel->execute();
        }

        if (!$osC_Database->isError()) {
            $osC_Database->commitTransaction();

            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function setAccessLevels($id, $modules, $mode = OSC_ADMINISTRATORS_ACCESS_MODE_ADD)
    {
        global $osC_Database;

        $error = false;

        if (in_array('*', $modules)) {
            $modules = array('*');
        }

        $osC_Database->startTransaction();

        if (($mode == OSC_ADMINISTRATORS_ACCESS_MODE_ADD) || ($mode == OSC_ADMINISTRATORS_ACCESS_MODE_SET)) {
            foreach ($modules as $module) {
                $execute = true;

                if ($module != '*') {
                    $Qcheck = $osC_Database->query('select roles_id from :table_roles_access where roles_id = :roles_id and module = :module limit 1');
                    $Qcheck->bindTable(':table_roles_access', TABLE_ADMINISTRATORS_ACCESS);
                    $Qcheck->bindInt(':roles_id', $id);
                    $Qcheck->bindValue(':module', '*');
                    $Qcheck->execute();

                    if ($Qcheck->numberOfRows() === 1) {
                        $execute = false;
                    }
                }

                if ($execute === true) {
                    $Qcheck = $osC_Database->query('select roles_id from :table_roles_access where roles_id = :roles_id and module = :module limit 1');
                    $Qcheck->bindTable(':table_roles_access', TABLE_ADMINISTRATORS_ACCESS);
                    $Qcheck->bindInt(':roles_id', $id);
                    $Qcheck->bindValue(':module', $module);
                    $Qcheck->execute();

                    if ($Qcheck->numberOfRows() < 1) {
                        $Qinsert = $osC_Database->query('insert into :table_roles_access (roles_id, module) values (:roles_id, :module)');
                        $Qinsert->bindTable(':table_roles_access', TABLE_ADMINISTRATORS_ACCESS);
                        $Qinsert->bindInt(':roles_id', $id);
                        $Qinsert->bindValue(':module', $module);
                        $Qinsert->setLogging($_SESSION['module'], $id);
                        $Qinsert->execute();

                        if ($osC_Database->isError()) {
                            $error = true;
                            break;
                        }
                    }
                }
            }
        }

        if ($error === false) {
            if (($mode == OSC_ADMINISTRATORS_ACCESS_MODE_REMOVE) || ($mode == OSC_ADMINISTRATORS_ACCESS_MODE_SET) || in_array('*', $modules)) {
                if (!empty($modules)) {
                    $Qdel = $osC_Database->query('delete from :table_roles_access where roles_id = :roles_id');

                    if ($mode == OSC_ADMINISTRATORS_ACCESS_MODE_REMOVE) {
                        if (!in_array('*', $modules)) {
                            $Qdel->appendQuery('and module in (":module")');
                            $Qdel->bindRaw(':module', implode('", "', $modules));
                        }
                    } else {
                        $Qdel->appendQuery('and module not in (":module")');
                        $Qdel->bindRaw(':module', implode('", "', $modules));
                    }

                    $Qdel->bindTable(':table_roles_access', TABLE_ADMINISTRATORS_ACCESS);
                    $Qdel->bindInt(':roles_id', $id);
                    $Qdel->setLogging($_SESSION['module'], $id);
                    $Qdel->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                }
            }
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function checkEmail($email = null)
    {
        global $osC_Database;

        $QcheckEmail = $osC_Database->query('select id from :table_roles where email_address = :email_address');
        $QcheckEmail->bindTable(':table_roles', TABLE_ADMINISTRATORS);
        $QcheckEmail->bindValue(':email_address', $email);
        $QcheckEmail->execute();

        if ($QcheckEmail->numberOfRows() > 0) {
            return true;
        }

        return false;
    }

    function generatePassword($email)
    {
        global $osC_Database;

        $password = osc_create_random_string(8);

        $Qpassword = $osC_Database->query('update :table_roles set user_password = :user_password where email_address = :email_address');
        $Qpassword->bindTable(':table_roles', TABLE_ADMINISTRATORS);
        $Qpassword->bindValue(':user_password', osc_encrypt_string($password));
        $Qpassword->bindValue(':email_address', $email);
        $Qpassword->execute();

        if (!$osC_Database->isError()) {
            $Qadmin = $osC_Database->query('select id, user_name, email_address from :table_roles where email_address = :email_address');
            $Qadmin->bindTable(':table_roles', TABLE_ADMINISTRATORS);
            $Qadmin->bindValue(':email_address', $email);
            $Qadmin->execute();

            include('../includes/classes/email_template.php');
            $email_template = toC_Email_Template::getEmailTemplate('admin_password_forgotten');
            $email_template->setData($Qadmin->value('user_name'), osc_get_ip_address(), $password, $email);
            $email_template->buildMessage();
            $email_template->sendEmail();

            return true;
        }

        return false;
    }
}

?>
