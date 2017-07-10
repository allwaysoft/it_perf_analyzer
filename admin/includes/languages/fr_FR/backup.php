# $Id: backup.php $
# Mefobe Cart Solutions
# http://www.mefobemarket.com
#
# Copyright (c) 2009-2010 Wuxi Elootec Technology Co., Ltd
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License v2 (1991)
# as published by the Free Software Foundation.

heading_title = Sauvegarde de la base de données

action_heading_new_backup = Nouvelle sauvegarde de base de données
action_heading_restore_local_file = Récupérer une copie de sauvegarde locale
action_heading_batch_delete_backup_files = Effacement de plusieurs fichiers de sauvegarde

table_heading_backups = Sauvegardes
table_heading_date = Date
table_heading_file_size = Taille du fichier
table_heading_action = Action

field_compression_none = Sans compression
field_compression_gzip = Compression GZIP
field_compression_zip = Compression ZIP
field_download_only = Téléchargement sans sauvegarde

backup_location = Répertoire de sauvegarde:
last_restoration_date = Date de dernière récupération:
forget_restoration_date = Date de récupération oublié

introduction_new_backup = Veuillez remplir les information suivantes pour la nouvelle sauvegarde de base de données.

introduction_restore_file = Veuillez valider la récupération du fichier de sauvegarde de base de données.

introduction_restore_local_file = Veuillez sélectionner le fichier de sauvegarde de base de données à récupérer.

introduction_delete_backup_file = Veuillez valider l\'effacement de ce fichier de sauvegarde de base de donneées.

introduction_batch_delete_backup_files = Veuillez valider l\'effacement de fichiers de sauvegarde de base de données suivants:.

ms_error_backup_directory_not_writable = Erreur: Le répertoire de sauvegarde ne permet pas l\'écriture: %s
ms_error_backup_directory_non_existant = Erreur: Le répertoire de sauvegarde n\'existe pas: %s
ms_error_download_link_not_acceptable = Erreur: Le lien de téléchargement n\'est pas accepté.

ms_success_database_restore = Succès : La base de données a été restaurée. Veuillez vous connecter à nouveau pour accéder au système.
