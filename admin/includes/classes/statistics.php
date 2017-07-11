<?php

  class osC_Statistics {

// Private variables

    var $_icon, $_title, $_header, $_data, $_resultset;

// Public methods

    function &getIcon() {
      return $this->_icon;
    }

    function &getTitle() {
      return $this->_title;
    }

    function &getHeader() {
      return $this->_header;
    }

    function &getData() {
      return $this->_data;
    }

    function activate() {
      $this->_setHeader();
      $this->_setData();
    }

    function getBatchTotalPages($text) {
      return $this->_resultset->getBatchTotalPages($text);
    }

    function getBatchPageLinks($batch_keyword = 'page', $parameters = '', $with_pull_down_menu = true) {
      return $this->_resultset->getBatchPageLinks($batch_keyword, $parameters, $with_pull_down_menu);
    }

    function getBatchPagesPullDownMenu($batch_keyword = 'page', $parameters = '') {
      return $this->_resultset->getBatchPagesPullDownMenu($batch_keyword, $parameters);
    }

    function isBatchQuery() {
      return $this->_resultset->isBatchQuery();
    }
  }
?>
