<?php

  include('../includes/classes/session.php');

  class osC_Session_Admin extends osC_Session {
    function delete($id) {
      global $osC_Database;

      if (STORE_SESSIONS == '') {
        if (file_exists($this->_save_path . $id)) {
          @unlink($this->_save_path . $id);
        }
      } elseif (STORE_SESSIONS == 'mysql') {
        $Qsession = $osC_Database->query('delete from :table_sessions where sesskey = :sesskey');
        $Qsession->bindRaw(':table_sessions', TABLE_SESSIONS);
        $Qsession->bindValue(':sesskey', $id);
        $Qsession->execute();

        $Qsession->freeResult();
      }
    }
  }
?>
