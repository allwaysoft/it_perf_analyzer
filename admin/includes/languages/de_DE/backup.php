# $Id: backup.php $
# TomatoCart Open Source Shopping Cart Solutions
# http://www.tomatocart.com
#
# Copyright (c) 2009-2010 Wuxi Elootec Technology Co., Ltd
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License v2 (1991)
# as published by the Free Software Foundation.

heading_title = Datenbank-Backup Manager

action_heading_new_backup = Neue Datenbank Sicherungskopie
action_heading_restore_local_file = Von lokalen Sicherungskopie wiederherstellen
action_heading_batch_delete_backup_files = Sicherungskopie-Dateien löschen

table_heading_backups = Sicherungskopien
table_heading_date = Datum
table_heading_file_size = Dateigröße
table_heading_action = Aktion

field_compression_none = Ohne Kompression
field_compression_gzip = GZIP Kompression
field_compression_zip = ZIP Kompression
field_download_only = Runterladen ohne Speichern

backup_location = Sicherungskopie-Verzeichnis:
last_restoration_date = Letzte Wiederherstellungsdatum:
forget_restoration_date = Wiederherstellungsdatum vergessen

introduction_new_backup = Bitte füllen Sie folgenden Informationen für die neue Sicherungskopie des Datenbanks aus.

introduction_restore_file = Bitte bestätigen Sie die Wiederstellung von der folgenden Sicherungskopie der Datenbank .

introduction_restore_local_file = Bitte wählen Sie die Sicherungskopie zum Wiederstellen des Datenbanks aus.

introduction_delete_backup_file = Bitte bestätigen Sie das Entfernen dieser Datenbank-Sicherungskopie .

introduction_batch_delete_backup_files = Bitte bestätigen Sie das Entfernen den folgenden Datenbank-Sicherungskopien .

ms_error_backup_directory_not_writable = FEHLER: Das Verzeichnis der Datenbank-Sicherungskopie ist nicht beschreibbar: %s
ms_error_backup_directory_non_existant = FEHLER: Das Verzeichnis der Datenbank-Sicherungskopie existiert nicht: %s
ms_error_download_link_not_acceptable = FEHLER: Der Download-Link ist nicht akzeptabel.

ms_success_database_restore = Success: The database is successfully restored. Please Login again to access the system.
