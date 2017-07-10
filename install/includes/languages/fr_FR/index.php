# $Id: index.php $
# Mefobe Cart Solutions
# http://www.mefobemarket.com
#
# Copyright (c) 2009-2010 Wuxi Elootec Technology Co., Ltd
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License v2 (1991)
# as published by the Free Software Foundation.

page_title_welcome = Bienvenue à TomatoCart V1.0 !
page_title_pre_installation_check = Vérification de la pré-installation
page_title_database_server_setup = Installation de la base de données serveur
page_title_web_server = Serveur Web
page_title_online_store_settings = Paramètres de la boutique en ligne
page_title_finished = Terminé !

nav_menu_title = Etapes
nav_menu_step_1_text = 1: Accords de licence
nav_menu_step_2_text = 2: Vérification de la pré-installation
nav_menu_step_3_text = 3: Installation de la base de données
nav_menu_step_4_text = 4: Installation du serveur web
nav_menu_step_5_text = 5: Paramètres de la boutique en ligne
nav_menu_step_6_text = 6: Terminé

title_language = Langage:

box_title_license = Licence
label_agree_to_the_license = J\'accepte les termes de la licence
warning_accept_license = Veuillez cocher la case d\'accord pour continuer !

text_welcome = <p style="background-color: #ff6633; padding: 5px; border: 1px #000 solid;">Veuillez prendre note que ceci est une version alpha de Tomatocart non supportée par l\'équipe développement, et donc n\'est pas utilisable en mode production.</p><p>TomatoCart est une solution de boutique en ligne de nouvelle génération développée par Elootec; Elle provient de osCommerce 3 alpha 4 "Lebkuchen" en tant que projet séparé. Son installation en ASP indépendante permet à des propriétaires de boutique d\'installer, faire marcher et maintenir leur boutique en ligne avec le minimum d\'efforts et sans coûts.</p><p>TomatoCart combine des solutions open source afin de fournir une plateforme de développement ouverte et gratuite, incluant la <i>puissance</i> du langage PHP web scripting, la <i>stabilité</i> du serveur Apache, et la <i>rapidité</i> de la base de données MySQL pour serveur.</p><p>Sans restriction ou contrainte spéciale, TomatoCart peut être installé sur n\'importe quel serveur web ayant activé PHP4 ou PHP5, sur n\'importe quel environnement supportant PHP et MySQL, incluant Linux, Solaris, BSD, et les environnements Microsoft Windows.</p>
text_pre_installation_check = Avant de procéder à l\'installation, veuillez vérifier que votre système respecte les conditions minimum pour l\'installation. If une seule de ces conditions n\'est pas respectée, veuillez prendre les actions nécessaires pour corriger les erreurs. TomatoCart pourrait ne pas fonctionner correctement si ce n\'était pas le cas.
text_database_server_setup = La base de données du serveur enregistre le contenu de la boutique en ligne comme les informations produits, clients, et les commandes qui ont été passées. Veuillez consulter votre administrateur de base de données si vous ne connaissez pas tous les paramètres de la base de données.
text_web_server = <p>Le serveur web propose les pages de la boutique aux visiteurs et clients. Les paramètres du serveur permettent de générer des liens actifs vers les bonnes pages.</p><p>Des fichiers temporaires commes les données de session et les fichiers cache sont stockées dans un dossier de travail. Il est important que ce dossier soit localisé en dehors du répertoire de la racine du serveur et protégé de l\'accès au public.</p>
text_online_store_settings = <p>Ici vous pouvez définir le nom de votre boutique en ligne, ainsi que les informations de contact concernant le propriétaire.</p><p>Les pseudo et mot-de-passe de l\'administrateur sont utilisés pour se connecter à la section protégée d\'administration</p>
text_finished = <p>Félicitations ! Vous avez installé et configuré TomatoCart comme votre solution de boutique en ligne !</p>
<p>Nous espérons le meilleur pour votre boutique et vous proposons de vous joindre et participer à la communauté.</p> <p align="right">- L\'équipe TomatoCart</p>


param_database_server = Base de données serveur
param_database_server_description = L\'adresse de la base de données sous la forme d\'un host ou adresse IP.
param_database_username = Pseudo
param_database_username_description = Le pseudo utilisé pour se connecter à la base de données.
param_database_password = Mot-de-passe
param_database_password_description = Le mot-de-passe utilisé avec le pseudo pour se connecter à la base de données.
param_database_name = Nom de la base de données
param_database_name_description = Le nom de la base de données qui hébergera les données à conserver.
param_database_type = Type de base de données
param_database_type_description = La solution de base de données qui est utilisée.
param_database_prefix = Préfixe de la table dans la base de données
param_database_prefix_description = Le préfixe qui est utilisé pour les tables dans la base de données.

param_database_import_sample_data = Importer des données en exemple
param_database_import_sample_data_description = Insérer des données en exemple est recommandé pour les premières installations.

param_web_address = Adresse WWW
param_web_address_description = L\'adresse web pour la boutique en ligne.
param_web_root_directory = Répertoire de la racine du serveur
param_web_root_directory_description = Le répertoire où la boutique est installé sur le serveur.
param_web_work_directory = Répertoire de travail
param_web_work_directory_description = Le répertoire de travail pour les fichiers temporaires. Ce répertoire devrait être situé en dehors de la racine pour des raisons de sécurité. (Les serveurs mutualisés ne devraient pas utiliser /tmp/)

param_store_name = Nom de la boutique
param_store_name_description = Le nom de la boutique qui sera présenté au public.
param_store_owner_name = Nom du propriétaire de la boutique
param_store_owner_name_description = Le nom du propriétaire de la boutique qui sera présenté au public.
param_store_owner_email_address = Adresse email du propriétaire
param_store_owner_email_address_description = L\'adresse email du propriétaire qui sera présentée au public.
param_administrator_username = Pseudo de l\'administrateur
param_administrator_username_description = Le pseudo de l\'administrateur à utiliser dans le menu administration.
param_administrator_password = mot-de-passe administrateur
param_confirm_password = Confirmez le mot-de-passe
param_administrator_password_description = Le mot-de-passe à utiliser dans le menu administration.


rpc_database_connection_test = Test de connexion à la base de données
rpc_database_connection_error = <p>La tentative de connexion à la base de données a échouée. L\'erreur suivante est apparue :</p><p style="width: 15opx;"><b>%s</b></p><p>Veuillez vérifier les paramètres de connexion et ré-essayer.</p>
rpc_database_connected = Vous êtes maintenant connecté à la base de données.
rpc_database_importing = La structure de la base de données est en cours d\'importation. Veuillez patienter pendant cette procédure.
rpc_database_imported = Import de la base de données réussi.
rpc_database_import_error = <p>L\'import de la base de données a échoué. L\'erreur suivante est apparue :</p><p><b>%s</b></p><p>Veuillez vérifier les paramètres de connexion et ré-essayer.</p>

rpc_store_setting_username_error = <p>Problème dans le paramétrage de la boutique. L\'erreur suivante est apparue :</p><p><b>Le pseudo ne peut pas être vide.</b></p><p>Veuillez entrer un pseudo.</p>
rpc_store_setting_password_error = <p>Problème dans le paramétrage de la boutique. L\'erreur suivante est apparue : </p><p><b>Le mot-de-passe ne peut pas être vide.</b></p><p>Veuillez entrer un mot-de-passe.</p>
rpc_store_setting_confirm_error = <p>Problème dans le paramétrage de la boutique. L\'erreur suivante est apparue :</p><p><b>Mauvais mot-de-passe ou pseudo!</b></p><p>Veuillez corriger et ré-essayer</p>
rpc_store_setting_email_error = <p>Problème dans le paramétrage de la boutique. L\'erreur suivante est apparue :</p><p><b>Adresse email invalide !</b></p><p>Veuillez corriger et ré-essayer.</p>

rpc_work_directory_test = Test du répertoire de travail...
rpc_work_directory_error_non_existent = Problème lors de l\'accès au répertoire de travail. L\'erreur suivante est apparue :<br /><br /><b>Le répertoire n\'existe pas :<br /><br />%s</b><br /><br />Veuillez vérifier le répertoire et ré-essayer.
rpc_work_directory_error_not_writeable = Problème lors de l\'accès au répertoire de travail. L\'erreur suivante est apparue :<br /><br /><b>Le serveur web n\'a pas les permissions en écriture sur le répertoire :<br /><br />%s</b><br /><br />Veuillez vérifier les permissions du répertoire et ré-essayer.
rpc_work_directory_configured = Répertoire de travail correctement configuré.

rpc_database_sample_data_importing = Les données en exemple dont en cours d\'importation dans la base de données. Veuillez patienter pendant cette procédure.
rpc_database_sample_data_imported = Succès de l\'importation des données en exemple.
rpc_database_sample_data_import_error = Erreur lors de l\'importation des données en exemple. L\'erreur suivante est apparue : </p><p><b>%s</b></p><p>Veuillez vérifier la base de données et ré-essayer.


box_pre_install_title = Vérification Pré-installation
box_server_title = Capacités du serveur
box_server_php_version = Version du PHP
box_server_php_settings = Paramètres PHP
box_server_register_globals = register_globals
box_server_magic_quotes = magic_quotes
box_server_file_uploads = file_uploads
box_server_session_auto_start = session.auto_start
box_server_session_use_trans_sid = session.use_trans_id
box_server_php_extensions = Extensions du PHP
box_server_mysql = MySQL
box_server_gd = GD
box_server_curl = cURL
box_server_openssl = OpenSSL
box_server_on = Activé
box_server_off = Déactivé
box_file_permissions = Permissions des fichiers
box_directory_permissions = Permissions des répertoires

error_configuration_file_not_writeable = <p>  Avant de procéder à l\'installation s\'il vous plaît assurez-vous que vous disposez des autorisations appropriées sur les fichiers et répertoires suivants: </p>% s </p>
error_configuration_file_alternate_method = <p> Alternativement il existe la possibilité de copier à la main les paramètres de configuration dans le fichier de configuration prévue à la fin de la procédure d\'installation. </p>
error_agree_to_license = Merci d\'accepter les conditions de la licence d\'utilisation avant d\'installer TomatoCart !

text_go_to_shop_after_cfg_file_is_saved = Veuillez visiter votre boutique en ligne après la sauvegarde du fichier de configuration :
