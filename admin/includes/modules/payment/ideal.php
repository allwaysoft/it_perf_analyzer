<?php

	class osC_Payment_ideal extends osC_Payment_Admin 
	{
		var $_code = 'ideal';
		
		var $_title = 'iDEAL';
		var $_method_title = 'ideal';
		var $_description = 'Online betalen via uw eigen bank.';
		var $_author_name = 'ideal-checkout.nl';
		var $_author_www = 'http://www.ideal-checkout.nl';
		var $_status = true;

		function osC_Payment_ideal()
		{
			$this->_status = (defined('MODULE_PAYMENT_IDEAL_STATUS') && (MODULE_PAYMENT_IDEAL_STATUS == '1') ? true : false);
			$this->_sort_order = (defined('MODULE_PAYMENT_IDEAL_SORT_ORDER') ? MODULE_PAYMENT_IDEAL_SORT_ORDER : null);
		}

		function isInstalled()
		{
			return (bool) defined('MODULE_PAYMENT_IDEAL_STATUS');
		}

		function install() 
		{
			global $osC_Database;

			parent::install();
			
			$osC_Database->simpleQuery("CREATE TABLE IF NOT EXISTS `" . DB_TABLE_PREFIX . "transactions` (`id` int(11) unsigned NOT NULL auto_increment, `order_id` varchar(100) default NULL, `order_code` varchar(100) default NULL, `transaction_id` varchar(100) default NULL, `transaction_code` varchar(100) default NULL, `transaction_method` varchar(100) default NULL, `transaction_date` int(11) unsigned default NULL, `transaction_amount` decimal(10,2) unsigned default NULL, `transaction_description` varchar(100) default NULL, `transaction_status` varchar(16) default NULL, `transaction_url` varchar(255) default NULL, `transaction_payment_url` varchar(255) default NULL, `transaction_success_url` varchar(255) default NULL, `transaction_pending_url` varchar(255) default NULL, `transaction_failure_url` varchar(255) default NULL, `transaction_params` text, `transaction_log` text, PRIMARY KEY (`id`));");

			// Load gateway setings
			require_once(realpath(dirname(__FILE__) . '/../../../') . '/ext/payments/ideal/config.php');
			$aGatewaySettings = gateway_getSettings();

			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('iDEAL Module', 'MODULE_PAYMENT_IDEAL_STATUS', '-1', '" . addSlashes('<iframe src="' . HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . '/ext/payments/ideal/gateway_info.php" height="400" width="343" scrolling="no" frameborder="0"></iframe>') . "<b>Enable iDEAL Module</b>', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_IDEAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Remove on PAYMENT CANCELLED', 'MODULE_PAYMENT_IDEAL_REMOVE_ORDER_ON_CANCELLED', '-1', 'Remove order when payment is cancelled.', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
			
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Status on PAYMENT SUCCESS', 'MODULE_PAYMENT_IDEAL_SUCCESS_ORDER_STATUS_ID', '0', '', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Status on PAYMENT PENDING', 'MODULE_PAYMENT_IDEAL_PENDING_ORDER_STATUS_ID', '0', '', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Status on PAYMENT OPEN', 'MODULE_PAYMENT_IDEAL_OPEN_ORDER_STATUS_ID', '0', '', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Status on PAYMENT CANCELLED', 'MODULE_PAYMENT_IDEAL_CANCELLED_ORDER_STATUS_ID', '0', '', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Status on PAYMENT EXPIRED', 'MODULE_PAYMENT_IDEAL_EXPIRED_ORDER_STATUS_ID', '0', '', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
			$osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Status on PAYMENT FAILURE', 'MODULE_PAYMENT_IDEAL_FAILURE_ORDER_STATUS_ID', '0', '', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
		}

		function getKeys()
		{
			if(!isset($this->_keys))
			{
				$this->_keys = array('MODULE_PAYMENT_IDEAL_STATUS', 'MODULE_PAYMENT_IDEAL_SORT_ORDER', 'MODULE_PAYMENT_IDEAL_REMOVE_ORDER_ON_CANCELLED', 'MODULE_PAYMENT_IDEAL_SUCCESS_ORDER_STATUS_ID', 'MODULE_PAYMENT_IDEAL_PENDING_ORDER_STATUS_ID', 'MODULE_PAYMENT_IDEAL_OPEN_ORDER_STATUS_ID', 'MODULE_PAYMENT_IDEAL_CANCELLED_ORDER_STATUS_ID', 'MODULE_PAYMENT_IDEAL_EXPIRED_ORDER_STATUS_ID', 'MODULE_PAYMENT_IDEAL_FAILURE_ORDER_STATUS_ID');
			}

			return $this->_keys;
		}
		
		function remove() {
		  global $osC_Database;
		  
		  parent::remove();
		  
		  $osC_Database->simpleQuery('drop table ' . DB_TABLE_PREFIX . 'transactions;');
		}

		function getPostTransactionActions($history) 
		{
			return array();
/*
			$actions = array(4 => 'inquiryTransaction');
			return $actions;
*/
		}
	}

?>