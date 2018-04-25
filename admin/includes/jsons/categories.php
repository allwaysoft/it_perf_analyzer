<?php

require('includes/classes/categories.php');
require('includes/classes/category_tree.php');
require('includes/classes/image.php');
require('includes/classes/products.php');

class toC_Json_Categories
{

    function listCategoriesAll()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.categories_status, c.date_added, c.last_modified from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id');
        //$Qcategories->appendQuery('and c.parent_id = :parent_id');

        if (isset($_REQUEST['date_added']) && !empty($_REQUEST['date_added'])) {
            $Qcategories->appendQuery('and c.date_added > :date_added');
            $Qcategories->bindValue(':date_added', $_REQUEST['date_added']);
        }

        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            $Qcategories->appendQuery('and cd.categories_name like :categories_name');
            $Qcategories->bindValue(':categories_name', $_REQUEST['search']);
        }

        $Qcategories->appendQuery('order by c.sort_order, cd.categories_name');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':language_id', $osC_Language->getID());
        $Qcategories->setExtBatchLimit($start, $limit);
        $Qcategories->execute();

        $records = array();
        $osC_CategoryTree = new osC_CategoryTree();
        while ($Qcategories->next()) {
            $records[] = array('categories_id' => $Qcategories->value('categories_id'),
                'categories_name' => $Qcategories->value('categories_name'),
                'status' => $Qcategories->valueInt('categories_status'),
                'path' => $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id')));
        }

        $user_categories_array = array();

        $roles = $_SESSION[admin][roles];
        $count = 0;

        foreach ($records as $category) {
            $permissions = osC_Categories_Admin::getCategoriesPermissions($category['categories_id']);
            $can_read_permissions = $permissions['can_read'];
            $can_write_permissions = $permissions['can_write'];
            $can_modify_permissions = $permissions['can_modify'];
            $can_publish_permissions = $permissions['can_publish'];

            $can_see = false;

            foreach ($roles as $role) {
                if (in_array($role, $can_read_permissions) || in_array($role, $can_write_permissions) || in_array($role, $can_modify_permissions) || in_array($role, $can_publish_permissions)) {
                    $can_see = true;
                }
            }

            if ($can_see) {
                $user_categories_array[] = $category;
                $count++;
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $user_categories_array);

        //echo "<pre>", print_r($_REQUEST, true), "</pre>";
        echo $toC_Json->encode($response);
    }

    function listCategories()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.categories_status, c.date_added, c.last_modified from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id');
        $Qcategories->appendQuery('and c.parent_id = :parent_id');

        if (isset($_REQUEST['categories_id']) && !empty($_REQUEST['categories_id'])) {
            $Qcategories->bindInt(':parent_id', $_REQUEST['categories_id']);
        } else {
            $Qcategories->bindInt(':parent_id', 0);
        }

        if (isset($_REQUEST['date_added']) && !empty($_REQUEST['date_added'])) {
            $Qcategories->appendQuery('and c.date_added > :date_added');
            $Qcategories->bindValue(':date_added', $_REQUEST['date_added']);
        }

        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            $Qcategories->appendQuery('and cd.categories_name like :categories_name');
            $Qcategories->bindValue(':categories_name', $_REQUEST['search']);
        }

        $count = 0;
        $Qcategories->appendQuery('order by c.sort_order, cd.categories_name');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':language_id', $osC_Language->getID());
        $Qcategories->setExtBatchLimit($start, $limit);
        $Qcategories->execute();

        $records = array();

        $records[] = array('categories_id' => -1,
            'categories_name' => 'Accueil',
            'status' => '1',
            'path' => '');

        $osC_CategoryTree = new osC_CategoryTree();
        while ($Qcategories->next()) {
            $records[] = array('categories_id' => $Qcategories->value('categories_id'),
                'categories_name' => $Qcategories->value('categories_name'),
                'status' => $Qcategories->valueInt('categories_status'),
                'path' => $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id')));
            $count++;
        }

        $user_categories_array = array();

        if ($_SESSION[admin][username] == 'admin') {
            $user_categories_array = $records;
        } else {
            $roles = $_SESSION[admin][roles];
            $count = 0;

            foreach ($records as $category) {
                $permissions = content::getContentPermissions($category['categories_id'], 'pages');
                $can_read_permissions = explode(';', $permissions['can_read']);
                $can_write_permissions = explode(';', $permissions['can_write']);
                $can_modify_permissions = explode(';', $permissions['can_modify']);
                $can_publish_permissions = explode(';', $permissions['can_publish']);

                $can_see = false;

                if (is_array($roles)) {
                    foreach ($roles as $role) {
                        if (in_array($role, $can_read_permissions) || in_array($role, $can_write_permissions) || in_array($role, $can_modify_permissions) || in_array($role, $can_publish_permissions)) {
                            $can_see = true;
                        }

                        if (in_array(-1, $can_read_permissions) || in_array(-1, $can_write_permissions) || in_array(-1, $can_modify_permissions) || in_array(-1, $can_publish_permissions)) {
                            $can_see = true;
                        }
                    }
                } else {
                    if (in_array($roles, $can_read_permissions) || in_array($roles, $can_write_permissions) || in_array($roles, $can_modify_permissions) || in_array($roles, $can_publish_permissions)) {
                        $can_see = true;
                    }

                    if (in_array(-1, $can_read_permissions) || in_array(-1, $can_write_permissions) || in_array(-1, $can_modify_permissions) || in_array(-1, $can_publish_permissions)) {
                        $can_see = true;
                    }
                }


                if ($can_see) {
                    $user_categories_array[] = $category;
                    $count++;
                }
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $user_categories_array);

        //echo "<pre>", print_r($_REQUEST, true), "</pre>";
        echo $toC_Json->encode($response);
    }

    function deleteCategory()
    {
        global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;

        $osC_Image = new osC_Image_Admin();
        $osC_CategoryTree = new osC_CategoryTree_Admin();

        if (isset($_REQUEST['categories_id']) && is_numeric($_REQUEST['categories_id']) && osC_Categories_Admin::delete($_REQUEST['categories_id'])) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function deleteCategories()
    {
        global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;

        $osC_Image = new osC_Image_Admin();
        $osC_CategoryTree = new osC_CategoryTree_Admin();

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $id) {
            if (!osC_Categories_Admin::delete($id)) {
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

    function moveCategories()
    {
        global $toC_Json, $osC_Language;

        $error = false;
        $batch = explode(',', $_REQUEST['categories_ids']);

        foreach ($batch as $id) {
            if (!osC_Categories_Admin::move($id, $_REQUEST['parent_category_id'])) {
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

    function loadCategory()
    {
        global $toC_Json, $osC_Language, $osC_Database, $osC_CategoryTree;

        $osC_CategoryTree = new osC_CategoryTree();

        $data = osC_Categories_Admin::getData($_REQUEST['categories_id']);

        $Qcategories = $osC_Database->query('select c.*, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where c.categories_id = :categories_id  ');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $_REQUEST['categories_id']);
        $Qcategories->execute();

        while ($Qcategories->next()) {
            $data['categories_name[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_name');
            $data['content_url[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_url');
            $data['page_title[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_page_title');
            $data['meta_keywords[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_meta_keywords');
            $data['meta_descriptions[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_meta_description');
        }
        $Qcategories->freeResult();

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function saveCategory()
    {
        global $toC_Json, $osC_Database, $osC_Language;

        $parent_id = isset($_REQUEST['parent_category_id']) ? end(explode('_', $_REQUEST['parent_category_id']))
            : 0;

        //search engine friendly urls
        $formatted_urls = array();
        $urls = $_REQUEST['content_url'];
        if (is_array($urls) && !empty($urls)) {
            foreach ($urls as $languages_id => $url) {
                $url = toc_format_friendly_url($url);
                if (empty($url)) {
                    $url = toc_format_friendly_url($_REQUEST['categories_name'][$languages_id]);
                }

                $formatted_urls[$languages_id] = $url;
            }
        }

        $data = array('parent_id' => $parent_id,
            'sort_order' => $_REQUEST['sort_order'],
            'image' => $_FILES['image'],
            'categories_status' => $_REQUEST['categories_status'],
            'name' => $_REQUEST['categories_name'],
            'url' => $formatted_urls,
            'page_title' => $_REQUEST['page_title'],
            'meta_keywords' => $_REQUEST['meta_keywords'],
            'meta_description' => $_REQUEST['meta_descriptions'],
            'flag' => (isset($_REQUEST['product_flag'])) ? $_REQUEST['product_flag'] : 0,
            'ratings' => $_REQUEST['ratings']);

        $category_id = osC_Categories_Admin::save((isset($_REQUEST['categories_id']) && is_numeric($_REQUEST['categories_id'])
            ? $_REQUEST['categories_id'] : null), $data);
        if ($category_id > 0) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function listParentCategory()
    {
        global $toC_Json, $osC_Language;

        $osC_CategoryTree = new osC_CategoryTree_Admin();

        $records = array(array('id' => '0',
            'text' => $osC_Language->get('top_category')));

        foreach ($osC_CategoryTree->getTree() as $value) {
            $records[] = array('id' => $value['id'],
                'text' => $value['title']);
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listParentArticleCategory()
    {
        global $toC_Json, $osC_Language;

        $osC_CategoryTree = new osC_CategoryTree_Admin();

        foreach ($osC_CategoryTree->getTree() as $value) {
            $records[] = array('id' => $value['id'],
                'text' => $value['title']);
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadCategoriesTree()
    {
        global $toC_Json;
        $roles = $_SESSION['admin']['roles'];

        if((is_null($roles) || empty($roles)) && $_SESSION['admin']['username'] != 'admin' && $_SESSION['admin']['username'] != 'makaki')
        {
            unset($_SESSION['admin']);
            $categories_array = array('id' => -5, 'text' => 'Session expirée');
            $categories_array['leaf'] = true;
            $categories_array['roles_id'] = null;
            $categories_array['cls'] = 'x-tree-node-collapsed';
            $categories_array['icon'] = 'templates/default/images/icons/16x16/home.png';
            $categories_array['permissions'] = null;
        }
        else
        {
            $include_custom_pages = isset($_REQUEST['filter']) && $_REQUEST['filter'] != '-1' ? true : false;
            $show_home = isset($_REQUEST['sh']) && $_REQUEST['sh'] == '1' ? true : false;
            $check_permissions = isset($_REQUEST['cp']) && $_REQUEST['cp'] == '0' ? false : true;
            $osC_CategoryTree = new osC_CategoryTree();
            $osC_CategoryTree->setShowCategoryProductCount(isset($_REQUEST['scc']) && $_REQUEST['scc'] == '1' ? true : false);

            $categories_array = $osC_CategoryTree->buildExtJsonTreeArrayForUser(0, '', $include_custom_pages, $show_home, $check_permissions);
        }

        echo $toC_Json->encode($categories_array);
    }

    function loadDashboardTree()
    {
        global $toC_Json;

        $include_custom_pages = isset($_REQUEST['filter']) && $_REQUEST['filter'] != '-1' ? true : false;
        $show_home = isset($_REQUEST['sh']) && $_REQUEST['sh'] == '1' ? true : false;
        $check_permissions = true;
        $osC_DashboardsTree = new osC_DashboardsTree();
        $osC_DashboardsTree->setShowCategoryProductCount(false);

        $categories_array = $osC_DashboardsTree->buildExtJsonTreeArrayForUser(0, '', $include_custom_pages, $show_home, $check_permissions);

        echo $toC_Json->encode($categories_array);
    }

    function listRolePermissions()
    {
        $categories_array = array();

        if (isset($_REQUEST['content_id']) && !empty($_REQUEST['content_id'])) {
            global $toC_Json;

            $osC_CategoryTree = new osC_CategoryTree();

            $categories_array = $osC_CategoryTree->buildExtJsonTreeArrayWithPermissions(0, 0, $_REQUEST['content_id']);
        }

        $response = array(EXT_JSON_READER_TOTAL => count($categories_array),
            EXT_JSON_READER_ROOT => $categories_array);

        echo $toC_Json->encode($response);
    }

    function setStatus()
    {
        global $toC_Json, $osC_Language;

        if (isset($_REQUEST['categories_id']) && osC_Categories_Admin::setStatus($_REQUEST['categories_id'], (isset($_REQUEST['flag'])
            ? $_REQUEST['flag'] : 1), (isset($_REQUEST['product_flag']) ? $_REQUEST['product_flag'] : 0))
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function setPermission()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['categories_id'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez specifier une Categorie');
            echo $toC_Json->encode($response);
            return;
        }

        if (!isset($_REQUEST['roles_id'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez specifier un Role');
            echo $toC_Json->encode($response);
            return;
        }

        if (!isset($_REQUEST['permission'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez specifier une permission pour ce Role');
            echo $toC_Json->encode($response);
            return;
        }

        $data = array('categories_id' => $_REQUEST['categories_id'], 'roles_id' => $_REQUEST['roles_id'], 'permission' => $_REQUEST['permission'], 'flag' => $_REQUEST['flag']);

        if (content::setPermission($data['categories_id'], 'pages', $data['permission'], $data['roles_id'], $data['flag'])) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }
}

?>
