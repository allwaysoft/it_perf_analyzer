<?php

    if (!class_exists('osC_Roles_Admin')) {
        include('includes/classes/roles.php');
    }
    if (!class_exists('content')) {
        include('includes/classes/content.php');
    }
class osC_Categories_Admin
{
    function getData($id, $language_id = null)
    {
        global $osC_Database, $osC_Language, $osC_CategoryTree;

        if (empty($language_id)) {
            $language_id = $osC_Language->getID();
        }

        $Qcategories = $osC_Database->query('select c.*, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where c.categories_id = :categories_id and cd.language_id = :language_id ');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $id);
        $Qcategories->bindInt(':language_id', $language_id);
        $Qcategories->execute();

        $data = $Qcategories->toArray();

        $data['childs_count'] = sizeof($osC_CategoryTree->getChildren($Qcategories->valueInt('categories_id'), $dummy = array()));
        $data['products_count'] = $osC_CategoryTree->getNumberOfProducts($Qcategories->valueInt('categories_id'));

        $cPath = explode('_', $osC_CategoryTree->getFullcPath($Qcategories->valueInt('categories_id')));
        array_pop($cPath);
        $data['parent_category_id'] = implode('_', $cPath);

        $Qcategories->freeResult();

        $Qcategories_ratings = $osC_Database->query('select ratings_id from toc_categories_ratings where categories_id = :categories_id');
        $Qcategories_ratings->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
        $Qcategories_ratings->bindInt(':categories_id', $id);
        $Qcategories_ratings->execute();

        $ratings = array();
        while ($Qcategories_ratings->next()) {
            $ratings[] = $Qcategories_ratings->ValueInt('ratings_id');
        }
        $data['ratings'] = $ratings;

        $Qcategories_ratings->freeResult();

        return $data;
    }

    function save($id = null, $data)
    {
        global $osC_Database, $osC_Language;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_categories set categories_status = :categories_status, sort_order = :sort_order, last_modified = now(),parent_id = :parent_id where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_categories (parent_id, categories_status, sort_order, date_added) values (:parent_id, :categories_status, :sort_order, now())');
        }

        $Qcat->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcat->bindInt(':parent_id', $data['parent_id']);
        $Qcat->bindInt(':sort_order', $data['sort_order']);
        $Qcat->bindInt(':categories_status', $data['categories_status']);
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            foreach ($osC_Language->getAll() as $l) {
                if (is_numeric($id)) {
                    $Qcd = $osC_Database->query('update :table_categories_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id and language_id = :language_id');
                } else {
                    $Qcd = $osC_Database->query('insert into :table_categories_description (categories_id, language_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :language_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
                }

                //var_dump($data);

                $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $category_id);
                $Qcd->bindInt(':language_id', $l['id']);
                $Qcd->bindValue(':categories_name', $data['name'][$l['id']]);
                $Qcd->bindValue(':categories_url', ($data['url'][$l['id']] == '') ? $data['name'][$l['id']]
                                                         : $data['url'][$l['id']]);
                $Qcd->bindValue(':categories_page_title', $data['page_title'][$l['id']]);
                $Qcd->bindValue(':categories_meta_keywords', $data['meta_keywords'][$l['id']]);
                $Qcd->bindValue(':categories_meta_description', $data['meta_description'][$l['id']]);
                $Qcd->setLogging($_SESSION['module'], $category_id);
                $Qcd->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                    break;
                }
            }

            $Qdelete = $osC_Database->query('delete from :toc_categories_ratings where categories_id = :categories_id');
            $Qdelete->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
            $Qdelete->bindInt(':categories_id', $category_id);
            $Qdelete->execute();

            if (!empty($data['ratings'])) {
                $ratings = explode(',', $data['ratings']);

                foreach ($ratings as $ratings_id) {
                    $Qinsert = $osC_Database->query('insert into :toc_categories_ratings (categories_id, ratings_id) values (:categories_id, :ratings_id)');
                    $Qinsert->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
                    $Qinsert->bindInt(':categories_id', $category_id);
                    $Qinsert->bindInt(':ratings_id', $ratings_id);
                    $Qinsert->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                        break;
                    }
                }
            }

            if ($error === false) {
                $categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));

                if ($categories_image->exists() && $categories_image->parse() && $categories_image->save()) {

                    $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                    $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qimage->bindInt(':categories_id', $category_id);
                    $Qimage->execute();

                    $old_image = $Qimage->value('categories_image');

                    if (!empty($old_image)) {
                        $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                        $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qcheck->bindValue(':categories_image', $old_image);
                        $Qcheck->execute();

                        if ($Qcheck->valueInt('image_count') == 1) {
                            $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                            unlink($path);
                        }
                    }

                    $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
                    $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qcf->bindValue(':categories_image', $categories_image->filename);
                    $Qcf->bindInt(':categories_id', $category_id);
                    $Qcf->setLogging($_SESSION['module'], $category_id);
                    $Qcf->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                }
            }
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');

            if (!is_numeric($id))
            {
                content::setPermission($category_id,'pages', 'can_read','-1', 1);
            }

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function saveFromRobot($id = null, $data)
    {
        global $osC_Database, $osC_Language;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_categories set categories_status = :categories_status, sort_order = :sort_order, last_modified = now() where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_categories (parent_id, categories_status, sort_order, date_added) values (:parent_id, :categories_status, :sort_order, now())');
            $Qcat->bindInt(':parent_id', $data['parent_id']);
        }

        $Qcat->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcat->bindInt(':sort_order', $data['sort_order']);
        $Qcat->bindInt(':categories_status', $data['categories_status']);
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            foreach ($osC_Language->getAll() as $l) {
                if (is_numeric($id)) {
                    $Qcd = $osC_Database->query('update :table_categories_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id and language_id = :language_id');
                } else {
                    $Qcd = $osC_Database->query('insert into :table_categories_description (categories_id, language_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :language_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
                }

                $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $category_id);
                $Qcd->bindInt(':language_id', $l['id']);
                $Qcd->bindValue(':categories_name', $data['name'][$l['id']]);
                $Qcd->bindValue(':categories_url', ($data['url'][$l['id']] == '') ? $data['name'][$l['id']]
                                                         : $data['url'][$l['id']]);
                $Qcd->bindValue(':categories_page_title', $data['page_title'][$l['id']]);
                $Qcd->bindValue(':categories_meta_keywords', $data['meta_keywords'][$l['id']]);
                $Qcd->bindValue(':categories_meta_description', $data['meta_description'][$l['id']]);
                $Qcd->setLogging($_SESSION['module'], $category_id);
                $Qcd->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                    break;
                }
            }

            $Qdelete = $osC_Database->query('delete from :toc_categories_ratings where categories_id = :categories_id');
            $Qdelete->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
            $Qdelete->bindInt(':categories_id', $category_id);
            $Qdelete->execute();

            if (!empty($data['ratings'])) {
                $ratings = explode(',', $data['ratings']);

                foreach ($ratings as $ratings_id) {
                    $Qinsert = $osC_Database->query('insert into :toc_categories_ratings (categories_id, ratings_id) values (:categories_id, :ratings_id)');
                    $Qinsert->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
                    $Qinsert->bindInt(':categories_id', $category_id);
                    $Qinsert->bindInt(':ratings_id', $ratings_id);
                    $Qinsert->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                        break;
                    }
                }
            }

            if ($error === false) {
                //$categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));

                $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qimage->bindInt(':categories_id', $category_id);
                $Qimage->execute();

                $old_image = $Qimage->value('categories_image');

                if (!empty($old_image)) {
                    $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                    $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qcheck->bindValue(':categories_image', $old_image);
                    $Qcheck->execute();

                    if ($Qcheck->valueInt('image_count') == 1) {
                        $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                        unlink($path);
                    }
                }

                $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
                $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qcf->bindValue(':categories_image', '../' . DIR_WS_IMAGES . 'categories' . '/' . $data['image']);
                $Qcf->bindInt(':categories_id', $category_id);
                $Qcf->setLogging($_SESSION['module'], $category_id);
                $Qcf->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }
            }
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function delete($id)
    {
        global $osC_Database, $osC_CategoryTree;

        $error = false;

        if (is_numeric($id)) {
            $osC_CategoryTree->setBreadcrumbUsage(false);

            $categories = array_merge(array(array('id' => $id, 'text' => '')), $osC_CategoryTree->getTree($id));
            $products = array();
            $products_delete = array();

            foreach ($categories as $c_entry) {
                $Qproducts = $osC_Database->query('select products_id from :table_products_to_categories where categories_id = :categories_id');
                $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                $Qproducts->bindInt(':categories_id', $c_entry['id']);
                $Qproducts->execute();

                while ($Qproducts->next()) {
                    $products[$Qproducts->valueInt('products_id')]['categories'][] = $c_entry['id'];
                }
            }

            foreach ($products as $key => $value) {
                $Qcheck = $osC_Database->query('select count(*) as total from :table_products_to_categories where products_id = :products_id and categories_id not in :categories_id');
                $Qcheck->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                $Qcheck->bindInt(':products_id', $key);
                $Qcheck->bindRaw(':categories_id', '("' . implode('", "', $value['categories']) . '")');
                $Qcheck->execute();

                if ($Qcheck->valueInt('total') < 1) {
                    $products_delete[$key] = $key;
                }
            }

            osc_set_time_limit(0);

            foreach ($categories as $c_entry) {
                $osC_Database->startTransaction();

                $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qimage->bindInt(':categories_id', $c_entry['id']);
                $Qimage->execute();

                $image = $Qimage->value('categories_image');

                if (!empty($image)) {
                    $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                    $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qcheck->bindValue(':categories_image', $image);
                    $Qcheck->execute();

                    if ($Qcheck->valueInt('image_count') == 1) {
                        $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }

                $Qc = $osC_Database->query('delete from :table_categories where categories_id = :categories_id');
                $Qc->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qc->bindInt(':categories_id', $c_entry['id']);
                $Qc->setLogging($_SESSION['module'], $id);
                $Qc->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }

                if ($error === false) {
                    $Qratings = $osC_Database->query('delete from :table_categories_ratings where categories_id = :categories_id');
                    $Qratings->bindTable(':table_categories_ratings', TABLE_CATEGORIES_RATINGS);
                    $Qratings->bindInt(':categories_id', $id);
                    $Qratings->setLogging($_SESSION['module'], $id);
                    $Qratings->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                }

                if ($error === false) {
                    $Qcd = $osC_Database->query('delete from :table_categories_description where categories_id = :categories_id');
                    $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                    $Qcd->bindInt(':categories_id', $c_entry['id']);
                    $Qcd->setLogging($_SESSION['module'], $id);
                    $Qcd->execute();

                    if (!$osC_Database->isError()) {
                        $Qp2c = $osC_Database->query('delete from :table_products_to_categories where categories_id = :categories_id');
                        $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qp2c->bindInt(':categories_id', $c_entry['id']);
                        $Qp2c->setLogging($_SESSION['module'], $id);
                        $Qp2c->execute();

                        if (!$osC_Database->isError()) {
                            $osC_Database->commitTransaction();

                            osC_Cache::clear('categories');
                            osC_Cache::clear('category_tree');
                            osC_Cache::clear('also_purchased');
                            osC_Cache::clear('sefu-products');
                            osC_Cache::clear('new_products');

                            if (!osc_empty($Qimage->value('categories_image'))) {
                                $Qcheck = $osC_Database->query('select count(*) as total from :table_categories where categories_image = :categories_image');
                                $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                                $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                                $Qcheck->execute();

                                if ($Qcheck->numberOfRows() === 0) {
                                    if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                                        @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                                    }
                                }
                            }
                        } else {
                            $osC_Database->rollbackTransaction();
                        }
                    } else {
                        $osC_Database->rollbackTransaction();
                    }
                } else {
                    $osC_Database->rollbackTransaction();
                }
            }

            foreach ($products_delete as $id) {
                osC_Products_Admin::delete($id);
            }

            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');
            osC_Cache::clear('sefu-products');
            osC_Cache::clear('new_products');

            return true;
        }

        return false;
    }

    function move($id, $new_id)
    {
        global $osC_Database;

        $category_array = explode('_', $new_id);

        if (in_array($id, $category_array)) {
            return false;
        }

        $Qupdate = $osC_Database->query('update :table_categories set parent_id = :parent_id, last_modified = now() where categories_id = :categories_id');
        $Qupdate->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qupdate->bindInt(':parent_id', end($category_array));
        $Qupdate->bindInt(':categories_id', $id);
        $Qupdate->setLogging($_SESSION['module'], $id);
        $Qupdate->execute();

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');

        return true;
    }

    function getPermissions($categories_id, $roles_id = null)
    {
        global $osC_Database;
        $Qpermissions = $osC_Database->query('select p.* from :table_permissions p where content_id = :categories_id and content_type = "pages"');
        $Qpermissions->bindTable(':table_permissions', TABLE_CONTENT_PERMISSIONS);
        $Qpermissions->bindInt(':categories_id', $categories_id);
        $Qpermissions->execute();

        $records = array();
        while ($Qpermissions->next()) {
            $records[] = array(
                'can_read' => $Qpermissions->value('can_read'),
                'can_write' => $Qpermissions->value('can_write'),
                'can_modify' => $Qpermissions->value('can_modify'),
                'can_publish' => $Qpermissions->value('can_publish')
            );
        }
        $Qpermissions->freeResult();

        $recs = array();
        $roles = array();

        if ($roles_id != null) {
            $roles[] = osC_Roles_Admin::getRoleDelta($roles_id);
        }
        else
        {
            $Qroles = $osC_Database->query('select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) order by r.roles_name');
            $Qroles->bindTable(':table_roles', TABLE_ROLES);
            $Qroles->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qroles->execute();

            $roles[] = array(
                'roles_id' => '-1',
                'user_name' => 'everyone',
                'email_address' => 'everyone',
                'roles_name' => 'Tout le monde',
                'roles_description' => 'Tout le monde',
                'icon' => osc_icon('folder_account.png')
            );

            while ($Qroles->next()) {
                $roles[] = array(
                    'roles_id' => $Qroles->valueInt('roles_id'),
                    'user_name' => $Qroles->value('user_name'),
                    'email_address' => $Qroles->value('email_address'),
                    'roles_name' => $Qroles->value('roles_name'),
                    'roles_description' => $Qroles->value('roles_description'),
                    'icon' => osc_icon('folder_account.png')
                );
            }
            $Qroles->freeResult();
        }

        if (count($records) > 0) {
            $permissions = $records[0];
        }
        else
        {
            $permissions = array(
                'can_read' => '',
                'can_write' => '',
                'can_modify' => '',
                'can_publish' => ''
            );
        }
        if (is_array($permissions)) {
            $read_permissions = explode(';', $permissions['can_read']);
            $write_permissions = explode(';', $permissions['can_write']);
            $modify_permissions = explode(';', $permissions['can_modify']);
            $publish_permissions = explode(';', $permissions['can_publish']);

            foreach ($roles as $role) {
                $current_role = $role[0];
                if (is_array($read_permissions) && in_array($current_role['roles_id'], $read_permissions)) {
                    $current_role['can_read'] = '1';
                }
                else
                {
                    $current_role['can_read'] = '0';
                }

                if (is_array($write_permissions) && in_array($current_role['roles_id'], $write_permissions)) {
                    $current_role['can_write'] = '1';
                }
                else
                {
                    $current_role['can_write'] = '0';
                }

                if (is_array($modify_permissions) && in_array($current_role['roles_id'], $modify_permissions)) {
                    $current_role['can_modify'] = '1';
                }
                else
                {
                    $current_role['can_modify'] = '0';
                }

                if (is_array($publish_permissions) && in_array($current_role['roles_id'], $publish_permissions)) {
                    $current_role['can_publish'] = '1';
                }
                else
                {
                    $current_role['can_publish'] = '0';
                }

                $current_role['categories_id'] = $_REQUEST['categories_id'];
                $recs[] = $current_role;
            }
        }

        return $recs;
    }

    function getCategoriesPermissions($categories_id)
    {
        global $osC_Database;

        $Qpermissions = $osC_Database->query('select p.* from :table_permissions p where categories_id = :categories_id');
        $Qpermissions->bindTable(':table_permissions', TABLE_CATEGORIES_PERMISSIONS);
        $Qpermissions->bindInt(':categories_id', $categories_id);
        $Qpermissions->execute();

        $records = array();
        while ($Qpermissions->next()) {
            $records[] = array(
                'can_read' => $Qpermissions->value('can_read'),
                'can_write' => $Qpermissions->value('can_write'),
                'can_modify' => $Qpermissions->value('can_modify'),
                'can_publish' => $Qpermissions->value('can_publish'),
                'is_set' => true
            );
        }
        $Qpermissions->freeResult();

        if (count($records) > 0) {
            $permissions = $records[0];
        }
        else
        {
            $permissions = array(
                'can_read' => '',
                'can_write' => '',
                'can_modify' => '',
                'can_publish' => '',
                'is_set' => false
            );
        }

        return $permissions;
    }

    function setPermission($categories_id, $permission, $roles_id, $flag)
    {
        global $osC_Database;

        $permissions = content::getContentPermissions($categories_id,'pages');

        if (array_key_exists($permission, $permissions)) {
            $roles = explode(';', $permissions[$permission]);
            $new_roles = $permissions[$permission];
            if (in_array($roles_id, $roles) && $flag == '1') {
                //nothing to do....
            }

            if (in_array($roles_id, $roles) && $flag == '0') {
                $new_roles = '';
                foreach ($roles as $role) {
                    if ($role != $roles_id) {
                        $new_roles = $new_roles . ';' . $role;
                    }
                }
            }

            if (!in_array($roles_id, $roles) && $flag == '1') {
                $new_roles = $new_roles . $roles_id . ';';
            }

            if (!in_array($roles_id, $roles) && $flag == '0') {
                //nothing to do....
            }

            if ($permissions['is_set'] == true) {
                $Qpermission = $osC_Database->query('update :table_categories_permissions set :permission = :roles where categories_id = :categories_id');
            }
            else
            {
                $Qpermission = $osC_Database->query('insert into :table_categories_permissions (categories_id,:permission) values (:categories_id,:roles)');
            }

            $roles = explode(';', $new_roles);
            $new_roles = '';
            $set_roles = array();

            foreach ($roles as $id) {
                if ($id != '' || $id == '-1') {
                    if (!in_array($id, $set_roles)) {
                        $new_roles = $new_roles . $id . ';';
                        $set_roles[] = $id;
                    }
                }
            }

            $Qpermission->bindTable(':table_categories_permissions', TABLE_CATEGORIES_PERMISSIONS);
            $Qpermission->bindInt(":categories_id", $categories_id);
            $Qpermission->bindTable(":permission", $permission);
            $Qpermission->bindValue(":roles", $new_roles);
            $Qpermission->execute();

            if (!$osC_Database->isError()) {
                osC_Cache::clear('categories');
                osC_Cache::clear('category_tree');

                return true;
            }
        }

        return false;
    }

    function setStatus($id, $flag, $product_flag)
    {
        global $osC_Database;

        $error = false;

        $Qstatus = $osC_Database->query('update :table_categories set categories_status = :categories_status where categories_id = :categories_id');
        $Qstatus->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qstatus->bindInt(":categories_id", $id);
        $Qstatus->bindValue(":categories_status", $flag);
        $Qstatus->execute();

        if (!$osC_Database->isError()) {
            if (($flag == 0) && ($product_flag == 1)) {
                $Qupdate = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
                $Qupdate->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                $Qupdate->bindInt(":categories_id", $id);
                $Qupdate->execute();
            }
        }

        if (!$osC_Database->isError()) {
            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');
            osC_Cache::clear('sefu-products');
            osC_Cache::clear('new_products');

            return true;
        }

        return false;
    }
}

?>