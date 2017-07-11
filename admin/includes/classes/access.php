<?php

    if (!class_exists('content')) {
        include('includes/classes/content.php');
    }
  class osC_Access {
    var $_group = 'misc',
        $_icon = 'configure.png',
        $_title,
        $_sort_order = 0,
        $_subgroups;

    function getUserLevels($id) {
      global $osC_Database;

      $modules = array();

      $Qaccess = $osC_Database->query('select module from :table_administrators_access where administrators_id = :administrators_id');
      $Qaccess->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
      $Qaccess->bindInt(':administrators_id', $id);
      $Qaccess->execute();

      while ( $Qaccess->next() ) {
        $modules[] = $Qaccess->value('module');
      }

      if ( in_array('*', $modules) ) {
        $modules = array();

        $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/access');
        $osC_DirectoryListing->setIncludeDirectories(false);

        foreach ($osC_DirectoryListing->getFiles() as $file) {
          $modules[] = substr($file['name'], 0, strrpos($file['name'], '.'));
        }
      }

      return $modules;
    }

      function getUserLevelsExt($id) {
          global $osC_Database;

          $modules = array();

          $Qaccess = $osC_Database->query('select module from :table_administrators_access where administrators_id = :administrators_id');
          $Qaccess->bindTable(':table_administrators_access', TABLE_DELTA_ACCESS);
          $Qaccess->bindValue(':administrators_id', $id);
          $Qaccess->execute();

          while ( $Qaccess->next() ) {
              $modules[] = $Qaccess->value('module');
          }

          if ( in_array('*', $modules) ) {
              $modules = array();

              $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/access');
              $osC_DirectoryListing->setIncludeDirectories(false);

              foreach ($osC_DirectoryListing->getFiles() as $file) {
                  $modules[] = substr($file['name'], 0, strrpos($file['name'], '.'));
              }
          }

          return $modules;
      }

    function getUserRoles($id) {
      global $osC_Database;

      $roles = array();

      $Qaccess = $osC_Database->query('select roles_id from :table_users_roles where administrators_id = :administrators_id');
      $Qaccess->bindTable(':table_users_roles', TABLE_USERS_ROLES);
      $Qaccess->bindInt(':administrators_id', $id);
      $Qaccess->execute();

      while ( $Qaccess->next() ) {
        $roles[] = $Qaccess->value('roles_id');
      }

      return $roles;
    }

      function getUserRolesExt($id) {
          global $osC_Database;

          $roles = array();

          $Qaccess = $osC_Database->query('select roles_id from :table_users_roles where roles_id = :administrators_id');
          $Qaccess->bindTable(':table_users_roles', TABLE_USERS_ROLES);
          $Qaccess->bindValue(':administrators_id', $id);
          $Qaccess->execute();

          while ( $Qaccess->next() ) {
              $roles[] = $Qaccess->value('roles_id');
          }

          return $roles;
      }

    function getLevels() {
      global $osC_Language;

      $access = array();

      foreach ( $_SESSION['admin']['access'] as $module ) {
        if ( file_exists('includes/modules/access/' . $module . '.php') ) {
          $module_class = 'osC_Access_' . ucfirst($module);

          if ( !class_exists( $module_class ) ) {
            $osC_Language->loadIniFile('modules/access/' . $module . '.php');
            include('includes/modules/access/' . $module . '.php');
          }

          $module_class = new $module_class();

          $data = array('module' => $module,
                        'icon' => $module_class->getIcon(),
                        'title' => $module_class->getTitle(),
                        'subgroups' => $module_class->getSubGroups());

          if ( !isset( $access[$module_class->getGroup()][$module_class->getSortOrder()] ) ) {
            $access[$module_class->getGroup()][$module_class->getSortOrder()] = $data;
          } else {
            $access[$module_class->getGroup()][] = $data;
          }
        }
      }

      ksort($access);
      foreach ( $access as $group => $links )
        ksort($access[$group]);

      return $access;
    }

    function getModule() {
      return $this->_module;
    }

    function getGroup() {
      return $this->_group;
    }

    /**
     * Get the Group which the Module belongs to.
     *
     * @param $module_name is the module name
     * @return String of the group.
     */
    function getModuleGroup($module_name){
        $group = '';
        foreach ( $_SESSION['admin']['access'] as $module ) {
            if( $module_name == $module ){
                $module_class = 'osC_Access_' . ucfirst($module);
                if ( !class_exists( $module_class ) ) {
                    $osC_Language->loadIniFile('modules/access/' . $module . '.php');
                    include('includes/modules/access/' . $module . '.php');
                }
                $module_class = new $module_class();
                $group = $module_class->getGroup();
            }
        }
        return $group;
    }

    function getGroupTitle($group) {
      global $osC_Language;

      if ( !$osC_Language->isDefined('access_group_' . $group . '_title') ) {
        $osC_Language->loadIniFile( 'modules/access/groups/' . $group . '.php' );
      }

      return $osC_Language->get('access_group_' . $group . '_title');
    }

    function getIcon() {
      return $this->_icon;
    }

    function getTitle() {
      return $this->_title;
    }

    function getSortOrder() {
      return $this->_sort_order;
    }

    function getSubGroups() {
      return $this->_subgroups;
    }

    function getUserPermissions($roles,$categories_id)
    {
        $permission = array();
        $permission['can_see'] = 0;
        
        if ($_SESSION[admin][username] == 'admin') {
            $permission['can_see'] = 1;
            $permission['can_write'] = 1;
            $permission['can_modify'] = '';
            $permission['can_publish'] = 1;
            $permission['can_read'] = 1;
            
            return $permission;
        }

        $permission['can_write'] = 0;
        $permission['can_modify'] = 0;
        $permission['can_publish'] = 0;
        $permission['can_read'] = 0;

        $permissions = content::getContentPermissions($categories_id,'pages');
        $can_read_permissions = explode(';', $permissions['can_read']);
        $can_write_permissions = explode(';', $permissions['can_write']);
        $can_modify_permissions = explode(';', $permissions['can_modify']);
        $can_publish_permissions = explode(';', $permissions['can_publish']);

        if(is_array($roles))
        {
            foreach ($roles as $role)
            {
                if (in_array('-1', $can_read_permissions))
                {
                    $permission['can_see'] = 1;
                    $permission['can_read'] = 1;
                }

                if (in_array('-1', $can_write_permissions)) {
                    $permission['can_see'] = 1;
                    $permission['can_write'] = 1;
                }

                if (in_array('-1', $can_modify_permissions)) {
                    $permission['can_see'] = 1;
                    $permission['can_modify'] = '';
                }

                if (in_array('-1', $can_publish_permissions)) {
                    $permission['can_see'] = 1;
                    $permission['can_publish'] = 1;
                }

                if (in_array($role, $can_read_permissions)) {
                    $permission['can_see'] = 1;
                    $permission['can_read'] = 1;
                }

                if (in_array($role, $can_write_permissions)) {
                    $permission['can_see'] = 1;
                    $permission['can_write'] = 1;
                }

                if (in_array($role, $can_modify_permissions)) {
                    $permission['can_see'] = 1;
                    $permission['can_modify'] = '';
                }

                if (in_array($role, $can_publish_permissions)) {
                    $permission['can_see'] = 1;
                    $permission['can_publish'] = 1;
                }
            }
        }
        else
        {
            if (in_array('-1', $can_read_permissions))
            {
                $permission['can_see'] = 1;
                $permission['can_read'] = 1;
            }

            if (in_array('-1', $can_write_permissions)) {
                $permission['can_see'] = 1;
                $permission['can_write'] = 1;
            }

            if (in_array('-1', $can_modify_permissions)) {
                $permission['can_see'] = 1;
                $permission['can_modify'] = '';
            }

            if (in_array('-1', $can_publish_permissions)) {
                $permission['can_see'] = 1;
                $permission['can_publish'] = 1;
            }

            if (in_array($roles, $can_read_permissions)) {
                $permission['can_see'] = 1;
                $permission['can_read'] = 1;
            }

            if (in_array($roles, $can_write_permissions)) {
                $permission['can_see'] = 1;
                $permission['can_write'] = 1;
            }

            if (in_array($roles, $can_modify_permissions)) {
                $permission['can_see'] = 1;
                $permission['can_modify'] = '';
            }

            if (in_array($roles, $can_publish_permissions)) {
                $permission['can_see'] = 1;
                $permission['can_publish'] = 1;
            }
        }

        return $permission;
    }

    function hasAccess($module = null) {
      if ( empty($module) ) {
        $module = $this->_module;
      }

      return in_array($module,$_SESSION['admin']['access']);
    }
  }
?>
