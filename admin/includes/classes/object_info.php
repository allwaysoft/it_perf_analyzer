<?php

  class osC_ObjectInfo {
    var $_keys = array();

    function osC_ObjectInfo($array) {
      foreach ($array as $key => $value) {
        $this->_keys[$key] = $value;
      }
    }

    function get($key) {
      return $this->_keys[$key];
    }

    function getAll() {
      return $this->_keys;
    }

    function set($key, $value) {
      $this->_keys[$key] = $value;
    }
  }
?>
