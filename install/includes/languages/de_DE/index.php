# $Id: index.php $
# TomatoCart Open Source Shopping Cart Solutions
# http://www.tomatocart.com
#
# Copyright (c) 2009-2010 Wuxi Elootec Technology Co., Ltd
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License v2 (1991)
# as published by the Free Software Foundation.

page_title_welcome = Willkommen zu TomatoCart V1.1.3!
page_title_pre_installation_check = Installations Prüfüng
page_title_database_server_setup = Datenbank Server Einstellungen
page_title_web_server = Web Server
page_title_online_store_settings = Shop Einstellungen
page_title_finished = Fertig!

nav_menu_title = Schritte
nav_menu_step_1_text = 1. Lizens Vereinbarung
nav_menu_step_2_text = 2. Installations Prüfüng
nav_menu_step_3_text = 3. Datenbank Einstellungen
nav_menu_step_4_text = 4. Webserver Einstellungen
nav_menu_step_5_text = 5. Shop Einstellungen
nav_menu_step_6_text = 6. Fertig

title_language = Sprache:

box_title_license = Lizens
label_agree_to_the_license = Bin mit der Lizens einverstanden
warning_accept_license = Bitte überprüfen Sie das Kontrollkästchen von Vereinbarung und weiter!

text_welcome = <p style="background-color: #ff6633; padding: 5px; border: 1px #000 solid;">Bitte beachten Sie, dass dieses ein ungestützt Alpha release der Entwicklung ist, das nicht für Produktionsgebrauch verwendet werden soll.</p><p>TomatoCart is new generation open source shopping cart solution developed by Elootec; it is branched from osCommerce 3 alpha 4 "Lebkuchen" as a separate project. Its feature packed out-of-the-box installation allows store owners to setup, run, and maintain their online stores with minimum effort and with no costs involved.</p><p>TomatoCart combines open source solutions to provide a free and open development platform, which includes the <i>powerful</i> PHP web scripting language, the <i>stable</i> Apache web server, and the <i>fast</i> MySQL database server.</p><p>With no restrictions or special requirements, TomatoCart can be installed on any PHP4 or PHP5 enabled web server, on any environment that PHP and MySQL supports, which includes Linux, Solaris, BSD, and Microsoft Windows environments.</p>
text_pre_installation_check = Bevor Installation bitte stellen Sie sicher, dass Ihr System den Mindestanforderungen für die Installation erfüllt. Wenn eine dieser Einstellungen nicht unterstützt, bitte geeignete Maßnahmen ergreifen, um die Fehler zu korrigieren. Andernfalls könnte es dazu führen dass TomatoCart nicht richtig funktioniert.
text_database_server_setup = Der Datenbank-Server speichert den Inhalt des Online-Shops wie Produktinformationen, Kundendaten und die ertellte Bestellungen. Bitte informieren Sie sich bei Ihrem Server-Administrator, wenn Ihnen die Datenbank-Server-Parameter noch nicht bekannt sind.
text_web_server = <p>Der Web-Server kümmert dienen die Seiten der Online-Shop für die Besucher und Kunden. Der Web-Server-Parameter stellt es sicher, die Links zu den Seiten auf den richtigen Positionen.</p><p> Temporäre Dateien, wie die Session-Daten-und Cache-Dateien werden in das Arbeitserzeichnis gespeichert.Es ist wichtig, dass sich dieses Verzeichnis außerhalb des Web-Server Root-Verzeichnis befindet und aus dem öffentlichen Zugriff geschützt.</p>
text_online_store_settings = <p>Hier können Sie definieren den Namen Ihres Online-Shops und die Kontaktinformationen für den Shopbetreiber.</p><p> Der Administrator Benutzernamen und Passwort werden verwendet, um in den geschützten Administrations-Tool-Sektion einzuloggen.</p>
text_finished = <p>Gratulieren Sie zur Installation und Konfiguration des TomatoCarts als Ihr Online-Shop-Lösung.</p><p> Wir wünschen Ihnen alles Gute mit Ihrem Online-Shop und begrüßen Sie auf Mitgliedschaft und Mitwirkung in unserer Gemeinde.</p><p align=\\right\\>- Das TomatoCart Team</p>


param_database_server = Datenbank Server
param_database_server_description = Die Adresse zum Datenbankserver in Form von Hostname oder IP Adresse.
param_database_username = Benutzername
param_database_username_description = Benutzername für die Datenbank Verbindung
param_database_password = Kennwort
param_database_password_description = Das Passwort für die Datenbank Verbindung
param_database_name = Datenbank Name
param_database_name_description = Name der Datenbank in dem die Daten gespeichert werden.
param_database_type = Datenbank Typ
param_database_type_description = Datenbank Software, welches in Verwendung ist.
param_database_prefix = Datenbank Tabellen Prefix
param_database_prefix_description = Der Tabellenprefix, der für diese Installation verwendet werden soll.

param_database_import_sample_data = Beispieldaten import
param_database_import_sample_data_description = Importiert Beispieldaten, wie Produkte, Kategorien use. Wird für die Erst-Installation empfohlen.

param_web_address = WWW Adresse
param_web_address_description = Die Url zu Ihren Shop
param_web_root_directory = Webserver Hauptverzeichniss
param_web_root_directory_description = Verzeichnis, in dem Ihr Shop installiert werden soll.
param_web_work_directory = Arbeitsverzeichniss
param_web_work_directory_description = Das Arbeitsverzeichnis für temporär angelegten Dateien. Aus Sicherheitsgründen sollte dieses Verzeichnis sich außerhalb des öffentlichen Webserver Root-Verzeichnis befinden.(Shared-Hosting-Server sollte /tmp/ nicht verwendet werden.)

param_store_name = Shop-Name
param_store_name_description = Der Name des Online-Shops wird der Öffentlichkeit vorgestellt.
param_store_owner_name = Shop-Besitzer Name
param_store_owner_name_description = Der Name des Online-Shops wird der Öffentlichkeit vorgestellt.
param_store_owner_email_address = Die E-Mail-Adresse des Shop-Besitzers
param_store_owner_email_address_description = Die E-Mail-Adresse des Shop-Besitzer wird der Öffentlichkeit vorgestellt.
param_administrator_username = Benutzername des Administrators
param_administrator_username_description = Verwenden Sie den Benutzernamen des Administrators für die Administrations-Tool
param_administrator_password = Kennwort des Administrators
param_confirm_password = Kennwort bestätigen
param_administrator_password_description = Verwenden Sie das Kennwort für das Administrator-Konto.


rpc_database_connection_test = Überprüfung der Datenbankverbindung
rpc_database_connection_error = Es gab ein Problem beim Verbinden mit dem Datenbankserver. Der folgende Fehler aufgetreten: Bitte überprüfen Sie die Verbindungsparameter und versuchen Sie es erneut.\"\"
rpc_database_connected = Verbindung mit der Datenbank ist erfolgreich!
rpc_database_importing = Die Datenbank-Struktur wird nun eingeführt. Bitte um etwas Geduld bei diesem Prozess.
rpc_database_imported = Datenbank sind erfolgreich eingeführt.
rpc_database_import_error = Es gab ein Problem beim Import der Datenbank.Der folgende Fehler aufgetreten:</p><p><b>%s</b></p><p>Bitte überprüfen Sie die Verbindungsparameter und versuchen Sie es erneut.

rpc_store_setting_username_error = Es gab ein Problem auf dem Shop Einstellung. Der folgende Fehler aufgetreten:</p><p><b>Der Benutzername darf nicht null sein!</b></p><p>Bitte geben Sie den Benutzernamen ein
rpc_store_setting_password_error = Es gab ein Problem auf dem Shop Einstellung. Der folgende Fehler aufgetreten:</p><p><b>Der Kennwort darf nicht null sein!</b></p><p>Bitte geben Sie den Kennwort ein
rpc_store_setting_confirm_error = Es gab ein Problem auf dem Shop Einstellung. Der folgende Fehler aufgetreten:</p><p><b>Die Passwörter stimmen nicht überein!!</b></p><p>Bitte prüfen Sie den Kennwort
rpc_store_setting_email_error = Es gab ein Problem auf dem Shop Einstellung. Der folgende Fehler aufgetreten:</p><p><b>Ungültige E-Mail-Adresse!</b></p><p>Bitte geben Sie den Benutzernamen ein

rpc_work_directory_test = Überprüfung des Arbeitsverzeichnisses
rpc_work_directory_error_non_existent = Es gab ein Problem beim Zugriff auf das Arbeitsverzeichnis. Der folgende Fehler aufgetreten:<br /><br /><b>Das Verzeichnis existiert nicht:<br /><br />%s</b><br /><br />Bitte überprüfen Sie das Verzeichnis und versuche es erneut.
rpc_work_directory_error_not_writeable = Es gab ein Problem beim Zugriff auf das Arbeitsverzeichnis. Der folgende Fehler aufgetreten:<br /><br /><b>Der Webserver hat keine Schreibrechte auf das Verzeichnis:<br /><br />%s</b><br /><br />Bitte überprüfen Sie das Verzeichnis und versuche es erneut.
rpc_work_directory_configured = Arbeitsverzeichnis ist erfolgreich konfiguriert.

rpc_database_sample_data_importing = Die Probedaten werden nun in die Datenbank eingeführt. Bitte um etwas Geduld bei diesem Prozess.
rpc_database_sample_data_imported = Die Probedaten sind erfolgreich eingeführt.
rpc_database_sample_data_import_error = Es gab ein Problem bei der Einführung der Datenbank Probedaten. Der folgende Fehler aufgetreten:</p><p><b>%s</b></p><p>Bitte überprüfen Sie die Datenbank-Server und versuchen Sie es erneut.


box_pre_install_title = Pre-Installation überprüfen.
box_server_title = Server Fähigkeiten
box_server_php_version = PHP Version
box_server_php_settings = PHP Einstellungen
box_server_register_globals = register_globals
box_server_magic_quotes = magic_quotes
box_server_file_uploads = file_uploads
box_server_session_auto_start = session.auto_start
box_server_session_use_trans_sid = session.use_trans_sid
box_server_php_extensions = PHP Extensions
box_server_mysql = MySQL
box_server_gd = GD
box_server_curl = cURL
box_server_openssl = OpenSSL
box_server_on = On
box_server_off = Off
box_file_permissions = Datei Erlaubnis
box_directory_permissions = Verzeichnis Erlaubnis

error_configuration_file_not_writeable = <p>Before proceeding to installation please make sure you have the appropriate permissions on the following files and directories:</p><p>%s</p>
error_configuration_file_alternate_method = <p>Alternatively the possibility to copy the configuration parameters to the configuration file by hand is also provided at the end of the installation procedure.</p>
error_agree_to_license = Bitte akzeptieren Sie die Lizenz bevor Sie TomatoCart installieren!

text_go_to_shop_after_cfg_file_is_saved = Bitte besuchen Sie Ihren Shop nach der Konfigurationsdatei gespeichert ist:
