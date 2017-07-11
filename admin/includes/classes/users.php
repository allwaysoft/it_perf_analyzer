<?php
    //include_once('includes/classes/content.php');
    define('OSC_ADMINISTRATORS_ACCESS_MODE_ADD', 'add');
    define('OSC_ADMINISTRATORS_ACCESS_MODE_SET', 'set');
    define('OSC_ADMINISTRATORS_ACCESS_MODE_REMOVE', 'remove');

    if(!class_exists('content'))
    {
        include_once('includes/classes/content.php');
    }

    class osC_Users_Admin
    {
        function getData($id,$with_modules = true)
        {
            global $osC_Database;

            $Qadmin = $osC_Database->query('SELECT u.STATUS,a.user_name,a.email_address,u.image_url,u.description FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where u.administrators_id = :id');
            $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qadmin->bindTable(':tables_users', TABLE_USERS);
            $Qadmin->bindInt(':id', $id);
            $Qadmin->execute();

            $modules = array('access_modules' => array());

            if($with_modules == true)
            {
                $Qaccess = $osC_Database->query('select module from :table_administrators_access where administrators_id = :id');
                $Qaccess->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                $Qaccess->bindInt(':id', $id);
                $Qaccess->execute();

                while ($Qaccess->next()) {
                    $modules['access_modules'][] = $Qaccess->value('module');
                }

                $Qaccess->freeResult();
            }

            $roles = array('roles_id' => array());

            $Qroles = $osC_Database->query('select roles_id from :table_users_roles where administrators_id = :id');
            $Qroles->bindTable(':table_users_roles', TABLE_USERS_ROLES);
            $Qroles->bindInt(':id', $id);
            $Qroles->execute();

            while ($Qroles->next()) {
                $roles['roles_id'][] = $Qroles->value('roles_id');
            }

            $admin = $Qadmin->toArray();
            $data = array_merge($admin, $modules);
            $data = array_merge($data, $roles);
            $data['user_password'] = null;

            unset($modules);
            unset($roles);

            $Qroles->execute();
            $Qadmin->freeResult();

            $description = content::getContentDescription($id, 'users');
            $data = array_merge($data, $description);

            return $data;
        }

        function getRolesModules($roles_id)
        {
            global $osC_Database;

            $modules = array();

            $Qaccess = $osC_Database->query('select module from :table_administrators_access where administrators_id = (select administrators_id from :tables_roles where roles_id = :roles_id)');
            $Qaccess->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
            $Qaccess->bindTable(':tables_roles', TABLE_ROLES);
            $Qaccess->bindInt(':roles_id', $roles_id);
            $Qaccess->execute();

            while ($Qaccess->next()) {
                $modules[] = $Qaccess->value('module');
            }

            return $modules;
        }

        function getUser($username)
        {
            global $osC_Database;

            $data = array();

            $Qadmin = $osC_Database->query('SELECT * FROM :tables_users_email where username = :username');
            $Qadmin->bindTable(':tables_users_email', TABLE_USERS_EMAIL);
            $Qadmin->bindValue(':username', $username);
            $Qadmin->execute();

            while ($Qadmin->next()) {
                $data[] = array('email' => $Qadmin->Value('email'),
                    'name' => $Qadmin->Value('name')
                );
            }

            return $data[0];
        }

        function getSubscribers($databases_id,$event)
        {
            global $osC_Database;

            $subscribers = "GuyMarcel.FOMINDIEFIE@t2safrica.com;";

            $query = "select email from delta_databases_subscribers where event = '" . $event . "' and databases_id = " . $databases_id;

            $QServers = $osC_Database->query($query);
            $QServers->execute();

            while ($QServers->next()) {
                $subscribers = $subscribers . $QServers->Value('email') . ";";
            }

            return $subscribers;
        }

        function save($id = null, $data, $modules)
        {
            $_id = $id;
            global $osC_Database, $osC_Image;

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
            $Qcheck->bindValue(':user_name', $data['user_name']);
            $Qcheck->execute();

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
                $Qadmin->bindValue(':user_name', $data['user_name']);
                $Qadmin->bindValue(':email_address', $data['email_address']);
                $Qadmin->setLogging($_SESSION['module'], $id);
                $Qadmin->execute();

                if ($data['delimage'] == 1) {
                    $osC_Image->deleteArticlesImage($id);

                    $Qdelete = $osC_Database->query('update :table_users set image_url = NULL where administrators_id = :administrators_id');
                    $Qdelete->bindTable(':table_users', TABLE_USERS);
                    $Qdelete->bindInt(':administrators_id', $id);
                    $Qdelete->setLogging($_SESSION['module'], $id);
                    $Qdelete->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                }

                if (!$osC_Database->isError()) {
                    if (is_numeric($id)) {
                        $Qadmin = $osC_Database->query('update :table_users set description = :description, status = :status');
                        $Qadmin->appendQuery('where administrators_id = :id');
                        $Qadmin->bindInt(':id', $id);
                    } else {
                        $id = $osC_Database->nextID();
                        $Qadmin = $osC_Database->query('insert into :table_users (description, status,administrators_id) values (:description, :status,:administrators_id)');
                        $Qadmin->bindInt(':administrators_id', $id);
                    }

                    $Qadmin->bindValue(':status', $data['status']);
                    $Qadmin->bindValue(':description', $data['description']);
                    $Qadmin->bindTable(':table_users', TABLE_USERS);
                    $Qadmin->setLogging($_SESSION['module'], $id);
                    $Qadmin->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                } else {
                    $error = true;
                }

                if ($error === false) {
                    $users_image = new upload('users_image', realpath('../' . DIR_WS_IMAGES . '/users/originals'));
                    if ($users_image->exists() && $users_image->parse() && $users_image->save()) {
                        $Qadmin = $osC_Database->query('update :table_users set image_url = :image_url where administrators_id = :administrators_id');
                        $Qadmin->bindTable(':table_users', TABLE_USERS);
                        $Qadmin->bindValue(':image_url', $users_image->filename);
                        $Qadmin->bindInt(':administrators_id', $id);
                        $Qadmin->setLogging($_SESSION['module'], $id);
                        $Qadmin->execute();

                        if ($osC_Database->isError()) {
                            $error = true;
                        } else {
                            $groups = $osC_Image->getGroups();
                            foreach ($groups as $group) {
                                if ($group['id'] != '1') {
                                    $osC_Image->resize($users_image->filename, $group['id'], 'users');
                                }
                            }
                        }

                    }
                }

                $Qdelete_roles = $osC_Database->query('delete from :table_users_roles where administrators_id = :administrators_id');
                $Qdelete_roles->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                $Qdelete_roles->bindInt(':administrators_id', $id);
                $Qdelete_roles->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }

                if ($error === false) {

                    if (is_array($data['roles_id'])) {
                        foreach ($data['roles_id'] as $roles_id) {
                            $Qusers_roles = $osC_Database->query('insert into :table_users_roles (roles_id, administrators_id) values (:roles_id, :administrators_id)');
                            $Qusers_roles->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                            $Qusers_roles->bindInt(':administrators_id', $id);
                            $Qusers_roles->bindInt(':roles_id', $roles_id);
                            $Qusers_roles->execute();

                            if ($osC_Database->isError()) {
                                $error = true;
                            }
                        }
                    }
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

                            if ($Qcheck->numberOfRows() < 1) {
                                $Qinsert = $osC_Database->query('insert into :table_administrators_access (administrators_id, module) values (:administrators_id, :module)');
                                $Qinsert->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                                $Qinsert->bindInt(':administrators_id', $id);
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
                        $error = true;
                    }
                }

                if ($error === false) {
                    $error = !content::saveContentDescription($_id, $id, 'users', $data);
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

        function delete($id)
        {
            global $osC_Database;

            $osC_Database->startTransaction();

            $Qdel = $osC_Database->query('delete from :table_administrators_access where administrators_id = (select administrators_id from :table_users where users_id = :users_id)');
            $Qdel->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
            $Qdel->bindInt(':users_id', $id);
            $Qdel->bindTable(':table_users', TABLE_USERS);
            $Qdel->setLogging($_SESSION['module'], $id);
            $Qdel->execute();

            if (!$osC_Database->isError()) {
                $Qdel = $osC_Database->query('delete from :table_administrators where id = (select administrators_id from :table_users where users_id = :users_id)');
                $Qdel->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
                $Qdel->bindInt(':users_id', $id);
                $Qdel->bindTable(':table_users', TABLE_USERS);
                $Qdel->setLogging($_SESSION['module'], $id);
                $Qdel->execute();
            }

            if (!$osC_Database->isError()) {
                $Qdel = $osC_Database->query('delete from :table_users_roles where administrators_id = (select administrators_id from :table_users where users_id = :users_id)');
                $Qdel->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                $Qdel->bindInt(':users_id', $id);
                $Qdel->bindTable(':table_users', TABLE_USERS);
                $Qdel->setLogging($_SESSION['module'], $id);
                $Qdel->execute();
            }

            if (!$osC_Database->isError()) {
                $Qdel = $osC_Database->query('delete from :table_users where users_id = :users_id');
                $Qdel->bindTable(':table_users', TABLE_USERS);
                $Qdel->bindInt(':users_id', $id);
                $Qdel->setLogging($_SESSION['module'], $id);
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
                        $Qcheck = $osC_Database->query('select administrators_id from :table_administrators_access where administrators_id = :administrators_id and module = :module limit 1');
                        $Qcheck->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                        $Qcheck->bindInt(':administrators_id', $id);
                        $Qcheck->bindValue(':module', '*');
                        $Qcheck->execute();

                        if ($Qcheck->numberOfRows() === 1) {
                            $execute = false;
                        }
                    }

                    if ($execute === true) {
                        $Qcheck = $osC_Database->query('select administrators_id from :table_administrators_access where administrators_id = :administrators_id and module = :module limit 1');
                        $Qcheck->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                        $Qcheck->bindInt(':administrators_id', $id);
                        $Qcheck->bindValue(':module', $module);
                        $Qcheck->execute();

                        if ($Qcheck->numberOfRows() < 1) {
                            $Qinsert = $osC_Database->query('insert into :table_administrators_access (administrators_id, module) values (:administrators_id, :module)');
                            $Qinsert->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                            $Qinsert->bindInt(':administrators_id', $id);
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
                        $Qdel = $osC_Database->query('delete from :table_administrators_access where administrators_id = :administrators_id');

                        if ($mode == OSC_ADMINISTRATORS_ACCESS_MODE_REMOVE) {
                            if (!in_array('*', $modules)) {
                                $Qdel->appendQuery('and module in (":module")');
                                $Qdel->bindRaw(':module', implode('", "', $modules));
                            }
                        } else {
                            $Qdel->appendQuery('and module not in (":module")');
                            $Qdel->bindRaw(':module', implode('", "', $modules));
                        }

                        $Qdel->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
                        $Qdel->bindInt(':administrators_id', $id);
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

            $QcheckEmail = $osC_Database->query('select id from :table_administrators where email_address = :email_address');
            $QcheckEmail->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
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

            $Qpassword = $osC_Database->query('update :table_administrators set user_password = :user_password where email_address = :email_address');
            $Qpassword->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qpassword->bindValue(':user_password', osc_encrypt_string($password));
            $Qpassword->bindValue(':email_address', $email);
            $Qpassword->execute();

            if (!$osC_Database->isError()) {
                $Qadmin = $osC_Database->query('select id, user_name, email_address from :table_administrators where email_address = :email_address');
                $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
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

        function setStatus($id, $flag)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('update :table_users set status= :status where administrators_id = :administrators_id');
            $Qstatus->bindInt(':faqs_status', $flag);
            $Qstatus->bindInt(':administrators_id', $id);
            $Qstatus->bindTable(':table_users', TABLE_USERS);
            $Qstatus->setLogging($_SESSION['module'], $id);
            $Qstatus->execute();

            return true;
        }

        function saveUsermail($username, $name,$email)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('delete from :tables_users_email where username = :username');
            $Qstatus->bindValue(':username', $username);
            $Qstatus->bindTable(':tables_users_email', TABLE_USERS_EMAIL);
            $Qstatus->execute();

            $Qstatus = $osC_Database->query('insert into :tables_users_email (username, name,email) values (:username, :name,:email)');
            $Qstatus->bindValue(':username', $username);
            $Qstatus->bindValue(':name', $name);
            $Qstatus->bindValue(':email', $email);
            $Qstatus->bindTable(':tables_users_email', TABLE_USERS_EMAIL);
            $Qstatus->execute();

            return true;
        }
    }

?>
