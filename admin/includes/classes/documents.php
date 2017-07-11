<?php

    class toC_Documents_Admin
    {
        function getData($id)
        {
            global $osC_Database, $osC_Language;

            $Qdocuments = $osC_Database->query('select a.*, ad.* from :table_documents a, :table_documents_description ad where a.documents_id = :documents_id and a.documents_id =ad.documents_id and ad.languages_id = :language_id');

            $Qdocuments->bindTable(':table_documents', TABLE_DOCUMENTS);
            $Qdocuments->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
            $Qdocuments->bindInt(':documents_id', $id);
            $Qdocuments->bindInt(':language_id', $osC_Language->getID());
            $Qdocuments->execute();

            $data = $Qdocuments->toArray();
            $data['html'] = '<a href="../cache/documents/' . $data['cache_filename'] . '" target="_blank">' . $data['filename'] . '</a>';

            $Qdocuments->freeResult();

            return $data;
        }

        function getBreadcrumb($id)
        {
            global $osC_Database;

            $Qcategories = $osC_Database->query('select cd.categories_name,c.parent_id from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and c.categories_id = :id');
            $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
            $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
            $Qcategories->bindInt(':id',$id);
            $Qcategories->execute();

            $data = $Qcategories->toArray();
            $breadcrumb = $data['categories_name'];
            $parent = $data['parent_id'];

            $Qcategories->freeResult();

            while($parent > 0)
            {
                $Qcategories = $osC_Database->query('select cd.categories_name,c.parent_id from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and c.categories_id = :id');
                $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                $Qcategories->bindInt(':id',$parent);
                $Qcategories->execute();

                $data = $Qcategories->toArray();
                $breadcrumb = $data['categories_name'] . '/' . $breadcrumb;
                $parent = $data['parent_id'];

                $Qcategories->freeResult();
            }

            return $breadcrumb;
        }

        function setStatus($id, $flag)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('update :table_documents set documents_status= :documents_status, documents_last_modified = now() where documents_id = :documents_id');
            $Qstatus->bindInt(':documents_status', $flag);
            $Qstatus->bindInt(':documents_id', $id);
            $Qstatus->bindTable(':table_documents', TABLE_DOCUMENTS);
            $Qstatus->setLogging($_SESSION['module'], $id);
            $Qstatus->execute();
            return true;
        }

        function save($id = null, $data)
        {
            global $osC_Database, $osC_Language;

            $osC_Database->startTransaction();
            $error = false;

            if ($data['documents_file']) {
                $file = new upload($data['documents_file']);

                if ($file->exists()) {
                    //remove old attachment file
                    if (is_numeric($id)) {
                        $Qfile = $osC_Database->query('select cache_filename from :table_documents where documents_id = :id');
                        $Qfile->bindTable(':table_documents', TABLE_DOCUMENTS);
                        $Qfile->bindInt(':id', $id);
                        $Qfile->execute();

                        if ($Qfile->numberOfRows() == 1) {
                            $file = DIR_FS_CACHE . 'documents/' . $Qfile->value('cache_filename');
                            if(file_exists($file))
                            {
                                @unlink($file);
                            }
                        }
                    }

                    $file->set_destination(realpath(DIR_FS_CACHE . '/documents'));

                    if ($file->parse() && $file->save()) {
                        $filename = $file->filename;

                        //$url = toc_format_friendly_url($data['documents_name']);

                        //$cache_filename = md5($filename . time());
                        $cache_filename = $filename;
                        $cache_filename = str_replace(' ', "_",$cache_filename);
                        $cache_filename = str_replace("-", "_",$cache_filename);

                        @rename(DIR_FS_CACHE . 'documents/' . $file->filename, DIR_FS_CACHE . '/documents/' . $cache_filename);

                        if (is_numeric($id)) {
                            $Qdocument = $osC_Database->query('update :table_documents set documents_status = :documents_status,documents_categories_id = :documents_categories_id,documents_last_modified = now() where documents_id = :documents_id');
                            $Qdocument->bindInt(':documents_id', $id);
                        } else {
                            $Qdocument = $osC_Database->query('insert into :table_documents (documents_status,filename,cache_filename,documents_categories_id,documents_date_added) values (:documents_status,:filename,:cache_filename,:documents_categories_id ,:documents_date_added)');
                            $Qdocument->bindRaw(':documents_date_added', 'now()');
                        }

                        $Qdocument->bindTable(':table_documents', TABLE_DOCUMENTS);
                        $Qdocument->bindValue(':documents_status', $data['documents_status']);
                        $Qdocument->bindValue(':filename', $filename);
                        $Qdocument->bindValue(':cache_filename', $cache_filename);
                        $Qdocument->bindValue(':documents_categories_id', $data['documents_categories']);
                        $Qdocument->setLogging($_SESSION['module'], $id);
                        $Qdocument->execute();

                        if ($osC_Database->isError()) {
                            $_SESSION['LAST_ERROR'] = $osC_Database->getError();
                            $osC_Database->rollbackTransaction();

                            return false;
                        }
                    }
                    else
                    {
                        $_SESSION['LAST_ERROR'] = "Could not parse file ....";
                        return false;
                    }
                }
                else
                {
                    $_SESSION['LAST_ERROR'] = "File does not exists ....";
                    return false;
                }
            }
            else
            {
                $_SESSION['LAST_ERROR'] = "documents_file pb ....";
                return false;
            }

            if ($osC_Database->isError()) {
                $_SESSION['LAST_ERROR'] = $osC_Database->getError();
                $osC_Database->rollbackTransaction();

                return false;
            } else {
                if (is_numeric($id)) {
                    $documents_id = $id;
                } else {
                    $documents_id = $osC_Database->nextID();
                }
            }

            //Process Languages
            //
            if ($error === false) {
                foreach ($osC_Language->getAll() as $l) {
                    if (is_numeric($id)) {
                        $Qad = $osC_Database->query('update :table_documents_description set documents_name = :documents_name, documents_description = :documents_description where documents_id = :documents_id and languages_id = :language_id');
                    } else {
                        $Qad = $osC_Database->query('insert into :table_documents_description (documents_id, languages_id, documents_name, documents_description) values (:documents_id, :language_id, :documents_name, :documents_description)');
                    }

                    $Qad->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
                    $Qad->bindInt(':documents_id', $documents_id);
                    $Qad->bindInt(':language_id', $l['id']);
                    $Qad->bindValue(':documents_name', $data['documents_name'][$l['id']]);
                    $Qad->bindValue(':documents_description', $data['documents_description'][$l['id']]);
                    $Qad->setLogging($_SESSION['module'], $documents_id);
                    $Qad->execute();

                    if ($osC_Database->isError()) {
                        $_SESSION['LAST_ERROR'] = $osC_Database->getError();
                        $osC_Database->rollbackTransaction();

                        return false;
                    }
                }
            }

            //content
            if ($error === false) {
                $error = !content::saveContent($id, $documents_id, 'documents', $data);
            }

            //content_to_categories
            if ($error === false) {
                $error = !content::saveContentToCategories($id, $documents_id, 'documents', $data);
            }

            if ($error === false) {
                $osC_Database->commitTransaction();

                osC_Cache::clear('sefu-documents');

                $name = $_SESSION[admin][name];
                $breadcrumb = toC_Documents_Admin::getBreadcrumb($data['documents_categories']);
                $subscriber = content::getContentSubscriber($data['documents_categories'], 'pages');

                if(!empty($subscriber['on_publish']))
                {
                    $to = array();
                    $emails = explode(';',$subscriber['on_publish']);
                    foreach ($emails as $email) {
                        if (!empty($email)) {
                            $to[] = osC_Mail::parseEmail($email);
                        }
                    }

                    $flag = $data['documents_status'];

                    $body = $flag == 1 ? "<table border='0' cellpadding='1' cellspacing='1' style='width: 100%'><tbody><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'><span style='color:#000066;'><strong>Nom du Document</strong></span></td></tr><tr><td>" . $data['documents_name'] . "</td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'><span style='color:#000066;'><strong>Description</strong></span></td></tr><tr><td>" . $data['documents_description'] . "</td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'><span style='color:#000066;'><strong>Telecharger</strong></span></td></tr><tr><td><a href='http://plateformesvp.intra.bicec/bicec/cache/documents/" . $data['cache_filename'] .  "'>Telecharger</a></td></tr></tbody></table>" : "<p><strong>" . $name . "</strong> a depublie le document <strong>" . $data['documents_name'] . "</strong> dans le repertoire " . $breadcrumb . "</p>";
                    $toC_Email_Account = new toC_Email_Account(4);

                    $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                        'id' => 222,
                        'to' => $to,
                        'from' => $toC_Email_Account->getAccountName(),
                        'sender' => $toC_Email_Account->getAccountEmail(),
                        'subject' => $flag == 1 ? "Publication d'un document dans le repertoire " . $breadcrumb . " par " . $name : "Depublication d'un document dans le repertoire " . $breadcrumb . " par " . $name,
                        'reply_to' => $toC_Email_Account->getAccountEmail(),
                        'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                        'body' => $body,
                        'priority' => 1,
                        'content_type' => 'html',
                        'notification' => false,
                        'udate' => time(),
                        'date' => date('m/d/Y H:i:s'),
                        'fetch_timestamp' => time(),
                        'messages_flag' => EMAIL_MESSAGE_DRAFT,
                        'attachments' => null);

                    $msg = $toC_Email_Account->sendMailJob($mail);
                }

                return true;
            }

            $osC_Database->rollbackTransaction();

            return false;
        }

        public static function setDocumentStatus($id, $flag,$username)
        {
            global $osC_Database;

            $osC_Database->startTransaction();
            $error = false;

            $query = "update :table_content set content_status= :documents_status, date_modified = now(),modified_by = :modified_by ";
            if($flag == 1)
            {
                $query = $query . ",date_published = now(),published_by = '" . $username . "'";
            }

            $query = $query . " where content_id = :documents_id and content_type = 'documents'";

            $Qstatus = $osC_Database->query($query);
            $Qstatus->bindInt(':documents_status', $flag);
            $Qstatus->bindInt(':documents_id', $id);
            $Qstatus->bindValue(':modified_by', $username);
            $Qstatus->bindTable(':table_content', TABLE_CONTENT);
            $Qstatus->setLogging($_SESSION['module'], $id);
            $Qstatus->execute();

            if ($osC_Database->isError()) {
                $_SESSION['LAST_ERROR'] = $osC_Database->getError();
                $osC_Database->rollbackTransaction();

                return false;
            }

            $osC_Database->commitTransaction();

            osC_Cache::clear('sefu-documents');

            $name = $_SESSION[admin][name];
            $doc = toC_Documents_Admin::getData($id);

            $breadcrumb = toC_Documents_Admin::getBreadcrumb($doc['documents_categories_id']);
            $subscriber = content::getContentSubscriber($doc['documents_categories_id'], 'pages');

            if(!empty($subscriber['on_publish']))
            {
                $to = array();
                $emails = explode(';',$subscriber['on_publish']);
                foreach ($emails as $email) {
                    if (!empty($email)) {
                        $to[] = osC_Mail::parseEmail($email);
                    }
                }

                //$body = $flag == 1 ? "<table border='0' cellpadding='1' cellspacing='1' style='width: 100%'><tbody><tr><td>" . $doc['documents_name'] . "</td></tr><tr><td>" . $doc['documents_description'] . "</td></tr><tr><td><a href='http://plateformesvp.intra.bicec/bicec/cache/documents/" . $doc['cache_filename'] .  "'>Telecharger</a></td></tr></tbody></table>" : $name . " a depublié un document dans le repertoire " . $breadcrumb;

                $body = $flag == 1 ? "<table border='0' cellpadding='1' cellspacing='1' style='width: 100%'><tbody><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'><span style='color:#000066;'><strong>Nom du Document</strong></span></td></tr><tr><td>" . $doc['documents_name'] . "</td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'><span style='color:#000066;'><strong>Description</strong></span></td></tr><tr><td>" . $doc['documents_description'] . "</td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'><span style='color:#000066;'><strong>Telecharger</strong></span></td></tr><tr><td><a href='http://plateformesvp.intra.bicec/bicec/cache/documents/" . $doc['cache_filename'] .  "'>Telecharger</a></td></tr></tbody></table>" : "<p><strong>" . $name . "</strong> a depublie le document <strong>" . $doc['documents_name'] . "</strong> dans le repertoire " . $breadcrumb . "</p>";
                $toC_Email_Account = new toC_Email_Account(4);

                $mail = array('accounts_id' => $toC_Email_Account->getAccountId(),
                    'id' => 222,
                    'to' => $to,
                    'from' => $toC_Email_Account->getAccountName(),
                    'sender' => $toC_Email_Account->getAccountEmail(),
                    'subject' => $flag == 1 ? "Publication d'un document dans le repertoire " . $breadcrumb . " par " . $name : "Depublication d'un document dans le repertoire " . $breadcrumb . " par " . $name,
                    'reply_to' => $toC_Email_Account->getAccountEmail(),
                    'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                    'body' => $body,
                    'priority' => 1,
                    'content_type' => 'html',
                    'notification' => false,
                    'udate' => time(),
                    'date' => date('m/d/Y H:i:s'),
                    'fetch_timestamp' => time(),
                    'messages_flag' => EMAIL_MESSAGE_DRAFT,
                    'attachments' => null);

                $msg = $toC_Email_Account->sendMailJob($mail);
            }

            return true;
        }

        function delete($id, $filename)
        {
            global $osC_Database;
            $error = false;

            $error = !content::deleteContent($id,'documents');

            $osC_Database->startTransaction();

            $Qad = $osC_Database->query('delete from :table_documents_description where documents_id = :documents_id');
            $Qad->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
            $Qad->bindInt(':documents_id', $id);
            $Qad->setLogging($_SESSION['module'], $id);
            $Qad->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $Qdocuments = $osC_Database->query('delete from :table_documents where documents_id = :documents_id');
                $Qdocuments->bindTable(':table_documents', TABLE_DOCUMENTS);
                $Qdocuments->bindInt(':documents_id', $id);
                $Qdocuments->setLogging($_SESSION['module'], $id);
                $Qdocuments->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }

                if ($error === false) {
                    $osC_Database->commitTransaction();

                    @unlink(DIR_FS_CACHE . 'documents/' . $filename);
                    osC_Cache::clear('sefu-documents');
                    return true;
                }
            }
            $osC_Database->rollbackTransaction();
            return false;
        }
    }

?>
