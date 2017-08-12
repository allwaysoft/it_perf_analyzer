<?php

require('includes/classes/administrators.php');
include('includes/modules/Net/SSH2.php');

class toC_Json_Login
{
    function login()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $response = array();
        if (!empty($_REQUEST['user_name']) && !empty($_REQUEST['user_password'])) {
            if ($_REQUEST['user_name'] == "admin") {
                $Qadmin = $osC_Database->query('select id, user_name, user_password from :table_administrators where user_name = :user_name');
                $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
                $Qadmin->bindValue(':user_name', $_REQUEST['user_name']);
                $Qadmin->execute();

                if ($Qadmin->numberOfRows() > 0) {
                    if (osc_validate_password($_REQUEST['user_password'], $Qadmin->value('user_password'))) {
                        $_SESSION['admin'] = array('id' => $Qadmin->valueInt('id'),
                            'username' => $Qadmin->value('user_name'),
                            'name' => 'Guy FOMI',
                            'access' => osC_Access::getUserLevels($Qadmin->valueInt('id')),
                            'roles' => osC_Access::getUserRoles($Qadmin->valueInt('id'))
                        );

                        $response = array('success' => true, 'feedback' => 'OK');
                    }
                } else {
                    $response = array('success' => false, 'feedback' => 'Compte ou mot de passe invalide');
                }
            } else {
                $user = $_REQUEST['user_name'];
                $pass = $_REQUEST['user_password'];

                $db_user = DB_USER;
                $db_pass = DB_PASS;
                $db_host = DB_HOST;
                $db_sid = DB_SID;
                $app_host = APP_HOST;

                $ssh = new Net_SSH2($app_host);

                if (empty($ssh->server_identifier)) {
                    $response = array('success' => false, 'feedback' => "Impossible de se connecter au serveur d'authentification, veuillez contacter votre administrateur systeme");
                } else {
                    if (!$ssh->login($user, $pass)) {
                        $response = array('success' => false, 'feedback' => 'Compte ou mot de passe invalide');
                    } else {
                        $ssh->disconnect();
                        $c = oci_pconnect($db_user, $db_pass, $db_host . "/" . $db_sid);
                        if (!$c) {
                            $e = oci_error();
                            trigger_error('Could not connect to database: ' . $e['message'], E_USER_ERROR);
                        }

                        $query = "SELECT TRIM(EVUTI.CUTI) CUTI,LTRIM (RTRIM (LIB)) LIB,SUS,ECRAN,UNIX,trim(PUTI) PUTI FROM BANK.EVUTI INNER JOIN BANK.EVUTAUT ON (EVUTI.CUTI = EVUTAUT.CUTI) where lower(trim(evutaut.unix)) = :unix";

                        $s = oci_parse($c, $query);
                        if (!$s) {
                            $e = oci_error($c);
                            trigger_error('Could not parse statement: ' . $e['message'], E_USER_ERROR);
                        }

                        oci_bind_by_name($s, ":unix", strtolower($user));

                        $r = oci_execute($s);
                        if (!$r) {
                            $e = oci_error($s);
                            trigger_error('Could not execute statement: ' . $e['message'], E_USER_ERROR);
                        }

                        $records = array();
                        $ok = false;

                        while (($row = oci_fetch_array($s, OCI_ASSOC))) {
                            $status = trim($row['ECRAN']);
                            $records [] = array('cuti' => $row['CUTI'], 'unix' => $row['UNIX'], 'lib' => $row['LIB'], 'status' => !empty($status) ? '1' : '0');

                            $_SESSION['admin'] = array('id' => $row['CUTI'],
                                'username' => $row['UNIX'],
                                'name' => $row['LIB'],
                                'access' => osC_Access::getUserLevelsExt($row['CUTI']),
                                'roles' => $row['UNIX']
                                //'roles' => osC_Access::getUserRolesExt($row['PUTI']
                            );

                            $ok = true;
                        }

                        oci_free_statement($r);
                        oci_close($c);

                        if (!$ok) {
                            $response = array('success' => false, 'feedback' => "Aucune correspondance AMPLITUDE existe pour ce compte");
                        } else {
                            $response = array('success' => true, 'feedback' => 'OK');
                        }
                    }
                }
            }

            echo $toC_Json->encode($response);

            exit;
        }

        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_login_invalid'));
        echo $toC_Json->encode($response);
    }

    function loginwin()
    {

        global $toC_Json, $osC_Language, $osC_Database;

        $response = array();
        if (!empty($_REQUEST['user_name']) && !empty($_REQUEST['user_password'])) {
            $Qadmin = $osC_Database->query('select id, user_name, user_password from :table_administrators where user_name = :user_name');
            $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qadmin->bindValue(':user_name', $_REQUEST['user_name']);
            $Qadmin->execute();

            if ($Qadmin->numberOfRows() > 0) {
                if (osc_validate_password($_REQUEST['user_password'], $Qadmin->value('user_password'))) {
                    $_SESSION['admin'] = array('id' => $Qadmin->valueInt('id'),
                        'username' => $Qadmin->value('user_name'),
                        'access' => osC_Access::getUserLevels($Qadmin->valueInt('id')));

                    $token = toc_generate_token();
                    $response = array('success' => true, 'token' => $token);
                    echo $toC_Json->encode($response);
                    exit;
                }
            }
        }

        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_login_invalid'));
        echo $toC_Json->encode($response);
    }

    function logoff()
    {
        global $toC_Json, $osC_Language;

        unset($_SESSION['admin']);

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_logged_out'));

        echo $toC_Json->encode($response);
    }

    function getPassword()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $error = false;
        $feedback = '';

        $email = $_REQUEST['email_address'];

        if (!osc_validate_email_address($email)) {
            $error = true;
            $feedback = $osC_Language->get('ms_error_wrong_email_address');
        } else if (!osC_Administrators_Admin::checkEmail($email)) {
            $error = true;
            $feedback = $osC_Language->get('ms_error_email_not_exist');
        }

        if ($error === false) {
            if (!osC_Administrators_Admin::generatePassword($email)) {
                $error = true;
                $feedback = $osC_Language->get('ms_error_email_send_failure');
            }
        }

        if ($error == false) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $feedback);
        }

        echo $toC_Json->encode($response);
    }
}

?>