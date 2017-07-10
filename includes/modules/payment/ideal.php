<?php

	class osC_Payment_ideal extends osC_Payment
	{
        var $_code = 'ideal';

    		var $_title = 'iDEAL';
    		var $_method_title = 'iDEAL';
    		var $_description = 'Met iDEAL betaald u direct online betalen via uw eigen bank.';
        var $_status = true;
        var $_sort_order = 0;

		function osC_Payment_Ideal()
		{
			// $this->_status = (MODULE_PAYMENT_IDEAL_STATUS == '1') ? true : false;
			$this->_sort_order = MODULE_PAYMENT_IDEAL_SORT_ORDER;
		}

		function getJavascriptBlock() 
		{
			return '';
		}

		function selection()
		{
			// Shows in payment selection screen
			return array('id' => $this->_code, 'module' => $this->_title);
		}

		function pre_confirmation_check()
		{
			return false;
		}

		function confirmation()
		{
			// Shows in confirm screen
			return array('title' => $this->_description);
		}

		function process_button()
		{
			return '';
		}

		function process()
		{
			global $osC_Database, $osC_Currencies, $osC_ShoppingCart;

			$this->_order_id = osC_Order::insert();


			// Load gateway setings
			require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/ext/payments/ideal/config.php');
			$aGatewaySettings = gateway_getSettings();

			// Load gateway file 
			require_once($aGatewaySettings['GATEWAY_FILE']);


			// Add transaction record to database
			$sOrderId = $this->_order_id;
			$sOrderCode = GatewayCore::randomCode(32);
			$sTransactionId = GatewayCore::randomCode(32);
			$sTransactionCode = GatewayCore::randomCode(32);
			$sTransactionMethod = $aGatewaySettings['GATEWAY_METHOD'];
			$fTransactionAmount = $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), 'EUR');
			$sTransactionDescription = 'Webshop bestelling #' . $sOrderId;
			$sTransactionPaymentUrl = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . FILENAME_CHECKOUT . '?callback&module=ideal&order_id=' . $sOrderId . '&order_code=' . $sOrderCode;
			$sTransactionSuccessUrl = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . FILENAME_CHECKOUT . '?callback&module=ideal&order_id=' . $sOrderId . '&order_code=' . $sOrderCode;
			$sTransactionPendingUrl = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . FILENAME_CHECKOUT . '?callback&module=ideal&order_id=' . $sOrderId . '&order_code=' . $sOrderCode;
			$sTransactionFailureUrl = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . FILENAME_CHECKOUT . '?callback&module=ideal&order_id=' . $sOrderId . '&order_code=' . $sOrderCode;


			// Insert into #_transactions
			$sql = "INSERT INTO `" . DB_TABLE_PREFIX . "transactions` SET 
              `id` = NULL, 
              `order_id` = '" . mysql_real_escape_string($sOrderId) . "', 
              `order_code` = '" . mysql_real_escape_string($sOrderCode) . "', 
              `transaction_id` = '" . mysql_real_escape_string($sTransactionId) . "', 
              `transaction_code` = '" . mysql_real_escape_string($sTransactionCode) . "', 
              `transaction_method` = '" . mysql_real_escape_string($sTransactionMethod) . "', 
              `transaction_date` = '" . mysql_real_escape_string(time()) . "', 
              `transaction_amount` = '" . mysql_real_escape_string($fTransactionAmount) . "', 
              `transaction_description` = '" . mysql_real_escape_string($sTransactionDescription) . "', 
              `transaction_status` = NULL, 
              `transaction_url` = NULL, 
              `transaction_payment_url` = '" . mysql_real_escape_string($sTransactionPaymentUrl) . "', 
              `transaction_success_url` = '" . mysql_real_escape_string($sTransactionSuccessUrl) . "', 
              `transaction_pending_url` = '" . mysql_real_escape_string($sTransactionPendingUrl) . "', 
              `transaction_failure_url` = '" . mysql_real_escape_string($sTransactionFailureUrl) . "', 
              `transaction_params` = NULL, 
              `transaction_log` = NULL;";

			$oQuery = $osC_Database->query($sql);
			$oQuery->execute();

			// Redirect to iDEAL Setup
			osc_redirect(HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . '/ext/payments/ideal/setup.php?order_id=' . $sOrderId . '&order_code=' . $sOrderCode);
			// osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'callback&module=ideal&order_id=' . $sOrderId . '&order_code=' . $sOrderCode, 'SSL'));
		}
		
		function callback()
		{
			global $osC_Database, $osC_ShoppingCart;

			if(empty($_GET['order_id']) || empty($_GET['order_code']))
			{
				// Invalid request
			}
			else
			{
				$sOrderId = $_GET['order_id'];
				$sOrderCode = $_GET['order_code'];
				
				$sql = "SELECT `transaction_status`, `transaction_url` FROM `" . DB_TABLE_PREFIX . "transactions` WHERE (`order_id` = '" . addslashes($sOrderId) . "') AND (`order_code` = '" . addslashes($sOrderCode) . "') ORDER BY `id` DESC LIMIT 1;";
				$oQuery = $osC_Database->query($sql);
				$oRecordset = $oQuery->execute();
				
				if(mysql_num_rows($oRecordset))
				{
					$oRecord = mysql_fetch_assoc($oRecordset);
					
					$iOrderId = (int) $sOrderId;
					$sTransactionStatus = $oRecord['transaction_status'];
					$sTransactionUrl = $oRecord['transaction_url'];

					if(osC_Order::exists($iOrderId))
					{
						if(strcmp($sTransactionStatus, 'SUCCESS') === 0)
						{
							// Update order status
							osC_Order::process($iOrderId, MODULE_PAYMENT_IDEAL_SUCCESS_ORDER_STATUS_ID);
							$osC_ShoppingCart->reset(true);
							
							// Redirect
							osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'success', 'SSL'));
						}
						elseif(strcmp($sTransactionStatus, 'PENDING') === 0)
						{
							// Update order status
							osC_Order::process($iOrderId, MODULE_PAYMENT_IDEAL_PENDING_ORDER_STATUS_ID);
							$osC_ShoppingCart->reset(true);

							// Redirect
							osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'success', 'SSL'));
						}
						elseif(strcmp($sTransactionStatus, 'OPEN') === 0)
						{
							// Update order status
							osC_Order::process($iOrderId, MODULE_PAYMENT_IDEAL_OPEN_ORDER_STATUS_ID);

							if($sTransactionUrl)
							{
								// Redirect
								osc_redirect($sTransactionUrl);
							}
						}
						elseif(strcmp($sTransactionStatus, 'CANCELLED') === 0) // Explicit cancel
						{
							if(MODULE_PAYMENT_IDEAL_REMOVE_ORDER_ON_CANCELLED)
							{
								// Remove Order
								osC_Order::remove($iOrderId);
							}
							else
							{
								// Update order status
								osC_Order::process($iOrderId, MODULE_PAYMENT_IDEAL_CANCELLED_ORDER_STATUS_ID);
							}
							
							// Redirect
							osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL'));
						}
						elseif(strcmp($sTransactionStatus, 'EXPIRED') === 0)
						{
							// Update order status
							osC_Order::process($iOrderId, MODULE_PAYMENT_IDEAL_EXPIRED_ORDER_STATUS_ID);
						}
						elseif(strcmp($sTransactionStatus, 'FAILURE') === 0)
						{
							// Update order status
							osC_Order::process($iOrderId, MODULE_PAYMENT_IDEAL_FAILURE_ORDER_STATUS_ID);
						}

						// Redirect
						osc_redirect(HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . '/ext/payments/ideal/setup.php?order_id=' . $sOrderId . '&order_code=' . $sOrderCode);
					}
				}
			}
			
			echo 'Cannot verify your order and/or payment. Please contact the webmaster.';
			exit;
		}
	}

?>