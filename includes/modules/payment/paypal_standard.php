<?php
/*
  $Id: paypal_standard.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_paypal_standard extends osC_Payment {
    var $_title,
        $_code = 'paypal_standard',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_ignore_order_totals = array('sub_total', 'tax', 'total'),
        $_transaction_response;

    // class constructor
    function osC_Payment_paypal_standard() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_paypal_standard_title');
      $this->_method_title = $osC_Language->get('payment_paypal_standard_method_title');
      $this->_sort_order = MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER;
      $this->_status = ((MODULE_PAYMENT_PAYPAL_STANDARD_STATUS == '1') ? true : false);

      if (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') {
        $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';
      } else {
        $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }

      if ($this->_status === true) {
        $this->order_status = MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : (int)ORDERS_STATUS_PAID;

        if ((int)MODULE_PAYMENT_PAYPAL_STANDARD_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PAYPAL_IPN_ZONE);
          $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
          $Qcheck->execute();

          while ($Qcheck->next()) {
            if ($Qcheck->valueInt('zone_id') < 1) {
              $check_flag = true;
              break;
            } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
              $check_flag = true;
              break;
            }
          }

          if ($check_flag == false) {
            $this->_status = false;
          }
        }
      }
    }

    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }

    function confirmation() {
      $this->_order_id = osC_Order::insert(ORDERS_STATUS_PREPARING);
    }

    function process_button() {
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Tax, $osC_Language;

      $process_button_string = '';
      $params = array('business' => MODULE_PAYMENT_PAYPAL_STANDARD_ID,
                      'currency_code' => $osC_Currencies->getCode(),
                      'invoice' => $this->_order_id,
                      'custom' => $osC_Customer->getID(),
                      'no_note' => '1',
                      'lc' => 'EN', //AU, DE, FR, IT, GB, ES, US
                      'notify_url' =>  HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . FILENAME_CHECKOUT . '?callback&module=' . $this->_code,
                      //'notify_url' => osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code, 'SSL', false, false, true),
                      'return' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                      'rm' => '2',
                      'cancel_return' => osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL', null, null, true),
                      'bn' => 'Tomatocart_Default_ST',
                      'paymentaction' => ((MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD == 'Sale') ? 'sale' : 'authorization'));

      if ($osC_ShoppingCart->hasShippingAddress()) {
        $params['address_override'] = '1';
        $params['first_name'] = $osC_ShoppingCart->getShippingAddress('firstname');
        $params['last_name'] =  $osC_ShoppingCart->getShippingAddress('lastname');
        $params['address1'] = $osC_ShoppingCart->getShippingAddress('street_address');
        $params['city'] = $osC_ShoppingCart->getShippingAddress('city');
        $params['state'] = $osC_ShoppingCart->getShippingAddress('zone_code');
        $params['zip'] = $osC_ShoppingCart->getShippingAddress('postcode');
        $params['country'] = $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
      } else {
        $params['no_shipping'] = '1';
        $params['first_name'] = $osC_ShoppingCart->getBillingAddress('firstname');
        $params['last_name'] = $osC_ShoppingCart->getBillingAddress('lastname');
        $params['address1'] = $osC_ShoppingCart->getBillingAddress('street_address');
        $params['city'] = $osC_ShoppingCart->getBillingAddress('city');
        $params['state'] = $osC_ShoppingCart->getBillingAddress('zone_code');
        $params['zip'] = $osC_ShoppingCart->getBillingAddress('postcode');
        $params['country'] = $osC_ShoppingCart->getBillingAddress('country_iso_code_2');
      }

      if (MODULE_PAYMENT_PAYPAL_STANDARD_TRANSFER_CART == '-1') {
        $params['cmd'] = '_xclick';
        $params['item_name'] = STORE_NAME;

        $shipping_tax = ($osC_ShoppingCart->getShippingMethod('cost')) * ($osC_Tax->getTaxRate($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id')) / 100);

        if (DISPLAY_PRICE_WITH_TAX == '1') {
          $shipping = $osC_ShoppingCart->getShippingMethod('cost');
        } else {
          $shipping = $osC_ShoppingCart->getShippingMethod('cost') + $shipping_tax;
        }
        $params['shipping'] = $osC_Currencies->formatRaw($shipping);

        $total_tax = $osC_ShoppingCart->getTax() - $shipping_tax;
        $params['tax'] = $osC_Currencies->formatRaw($total_tax);
        $params['amount'] = $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $shipping - $total_tax);
      }else {
        $params['cmd'] = '_cart';
        $params['upload'] = '1';
        if (DISPLAY_PRICE_WITH_TAX == '-1') {
          $params['tax_cart'] = $osC_Currencies->formatRaw($osC_ShoppingCart->getTax());
        }

        //products
        $products = array();
        if ($osC_ShoppingCart->hasContents()) {
          $i = 1;

          $products = $osC_ShoppingCart->getProducts();
          foreach($products as $product) {
            $product_name = $product['name'];

            //gift certificate
            if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              $product_name .= "\n" . ' - ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];

              if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                $product_name .= "\n" . ' - ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
              }

              $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];

              if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
              }

              $product_name .= "\n" . ' - ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
            }

            if ($osC_ShoppingCart->hasVariants($product['id'])) {
              foreach ($osC_ShoppingCart->getVariants($product['id']) as $variant) {
                $product_name .= ' - ' . $variant['groups_name'] . ': ' . $variant['values_name'];
              }
            }

            $product_data = array('item_name_' . $i => $product_name, 'item_number_' . $i => $product['sku'], 'quantity_' . $i  => $product['quantity']);

            $tax = $osC_Tax->getTaxRate($product['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
            $price = $osC_Currencies->addTaxRateToPrice($product['final_price'], $tax);
            $product_data['amount_' . $i] = $osC_Currencies->formatRaw($price);

            $params = array_merge($params,$product_data);

            $i++;
          }
        }

        //order totals
        foreach ($osC_ShoppingCart->getOrderTotals() as $total) {
          if ( !in_array($total['code'], $this->_ignore_order_totals) ) {
            if ( ($total['code'] == 'coupon') || ($total['code'] == 'gift_certificate') ) {
              $params['discount_amount_cart'] += $osC_Currencies->formatRaw(abs($total['value']));
            } else {
              $order_total = array('item_name_' . $i => $total['title'], 'quantity_' . $i => 1, 'amount_' . $i => $total['value']);
              $params = array_merge($params, $order_total);

              $i++;
            }
          }
        }
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE') ) {
        $params['page_style'] = MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE;
      }

      if (MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS == '1') {
        $params['cert_id'] = MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID;

        $random_string = osc_create_random_string(5, 'digits') . '-' . $osC_Customer->getID() . '-';

        $data = '';
        reset($params);
        foreach ($params as $key => $value) {
          $data .= $key . '=' . $value . "\n";
        }

        $fp = fopen(DIR_FS_WORK . $random_string . 'data.txt', 'w');
        fwrite($fp, $data);
        fclose($fp);

        unset($data);

        if (function_exists('openssl_pkcs7_sign') && function_exists('openssl_pkcs7_encrypt')) {
          openssl_pkcs7_sign(DIR_FS_WORK . $random_string . 'data.txt', DIR_FS_WORK . $random_string . 'signed.txt', file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY), file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY), array('From' => MODULE_PAYMENT_PAYPAL_STANDARD_ID), PKCS7_BINARY);

          unlink(DIR_FS_WORK . $random_string . 'data.txt');

          // remove headers from the signature
          $signed = file_get_contents(DIR_FS_WORK . $random_string . 'signed.txt');
          $signed = explode("\n\n", $signed);
          $signed = base64_decode($signed[1]);

          $fp = fopen(DIR_FS_WORK . $random_string . 'signed.txt', 'w');
          fwrite($fp, $signed);
          fclose($fp);

          unset($signed);

          openssl_pkcs7_encrypt(DIR_FS_WORK . $random_string . 'signed.txt', DIR_FS_WORK . $random_string . 'encrypted.txt', file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY), array('From' => MODULE_PAYMENT_PAYPAL_STANDARD_ID), PKCS7_BINARY);

          unlink(DIR_FS_WORK . $random_string . 'signed.txt');

          // remove headers from the encrypted result
          $data = file_get_contents(DIR_FS_WORK . $random_string . 'encrypted.txt');
          $data = explode("\n\n", $data);
          $data = '-----BEGIN PKCS7-----' . "\n" . $data[1] . "\n" . '-----END PKCS7-----';

          unlink(DIR_FS_WORK . $random_string . 'encrypted.txt');
        } else {
          exec(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL . ' smime -sign -in ' . DIR_FS_WORK . $random_string . 'data.txt -signer ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY . ' -inkey ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY . ' -outform der -nodetach -binary > ' . DIR_FS_WORK . $random_string . 'signed.txt');
          unlink(DIR_FS_WORK . $random_string . 'data.txt');

          exec(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL . ' smime -encrypt -des3 -binary -outform pem ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY . ' < ' . DIR_FS_WORK . $random_string . 'signed.txt > ' . DIR_FS_WORK . $random_string . 'encrypted.txt');
          unlink(DIR_FS_WORK . $random_string . 'signed.txt');

          $fp = fopen(DIR_FS_WORK . $random_string . 'encrypted.txt', 'rb');
          $data = fread($fp, filesize(DIR_FS_WORK . $random_string . 'encrypted.txt'));
          fclose($fp);

          unset($fp);

          unlink(DIR_FS_WORK . $random_string . 'encrypted.txt');
        }

        $process_button_string = osc_draw_hidden_field('cmd', '_s-xclick') .
                                 osc_draw_hidden_field('encrypted', $data);

        unset($data);
      } else {
        $process_button_string = '';

        foreach ($params as $key => $value) {
          $process_button_string .= osc_draw_hidden_field($key, $value);
        }
      }

      return $process_button_string;
    }

    function process() {
      if (isset($_POST['invoice']) && is_numeric($_POST['invoice']) && isset($_POST['receiver_email']) && ($_POST['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_ID) && isset($_POST['verify_sign']) && (empty($_POST['verify_sign']) === false) && isset($_POST['txn_id']) && (empty($_POST['txn_id']) === false)) {
        unset($_SESSION['prepOrderID']);
      }
    }

    function callback() {
      global $osC_Database, $osC_Currencies;

      $post_string = 'cmd=_notify-validate&';

      foreach ($_POST as $key => $value) {
        $post_string .= $key . '=' . urlencode($value) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $this->_transaction_response = $this->sendTransactionToGateway($this->form_action_url, $post_string);
      
      if (strtoupper(trim($this->_transaction_response)) == 'VERIFIED') {
        if (isset($_POST['invoice']) && is_numeric($_POST['invoice']) && ($_POST['invoice'] > 0)) {
          $Qcheck = $osC_Database->query('select orders_status, currency, currency_value from :table_orders where orders_id = :orders_id and customers_id = :customers_id');
          $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
          $Qcheck->bindInt(':orders_id', $_POST['invoice']);
          $Qcheck->bindInt(':customers_id', $_POST['custom']);
          $Qcheck->execute();
          
          if ($Qcheck->numberOfRows() > 0) {
            $order = $Qcheck->toArray();
            
            $Qtotal = $osC_Database->query('select value from :table_orders_total where orders_id = :orders_id and class = "total" limit 1');
            $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
            $Qtotal->bindInt(':orders_id', $_POST['invoice']);
            $Qtotal->execute();
            
            $total = $Qtotal->toArray();
            
            $comment = $_POST['payment_status'] . ' (' . ucfirst($_POST['payer_status']) . '; ' . $osC_Currencies->format($_POST['mc_gross'], false, $_POST['mc_currency']) . ')';
            
            if ($_POST['payment_status'] == 'Pending') {
              $comment .= '; ' . $_POST['pending_reason'];
            } elseif ($_POST['payment_status'] == 'Reversed' || $_POST['payment_status'] == 'Refunded') {
              $comment .= '; ' . $_POST['reason_code'];
            }
            
            if ( $_POST['mc_gross'] != number_format($total['value'] * $order['currency_value'], $osC_Currencies->getDecimalPlaces($order['currency'])) ) {
              $comment .= '; PayPal transaction value (' . osc_output_string_protected($_POST['mc_gross']) . ') does not match order value (' . number_format($total['value'] * $order['currency_value'], $osC_Currencies->getDecimalPlaces($order['currency'])) . ')';
            }
            
            $comments = 'PayPal IPN Verified [' . $comment . ']';
            
            osC_Order::process($_POST['invoice'], $this->order_status, $comments);
          }
        }
      } else {
        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL')) {
          $email_body = 'PAYPAL_STANDARD_DEBUG_POST_DATA:' . "\n\n";

          reset($_POST);
          foreach($_POST as $key=>$value) {
            $email_body .= $key . '=' . $value . "\n";
          }

          $email_body .= "\n" . 'PAYPAL_STANDARD_DEBUG_GET_DATA:' . "\n\n";
          reset($_GET);
          foreach($_GET as $key=>$value) {
            $email_body .= $key . '=' . $value . "\n";
          }

          osc_email('', MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL, 'PayPal IPN Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }

        if (isset($_POST['invoice']) && is_numeric($_POST['invoice']) && $_POST['invoice'] > 0) {
          $Qcheck = $osC_Database->query('select orders_id from :table_orders where orders_id=:orders_id and customers_id=:customers_id');
          $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
          $Qcheck->bindInt('orders_id', $_POST['invoice']);
          $Qcheck->bindInt('customers_id', $_POST['custom']);
          $Qcheck->execute();

          if ($Qcheck->numberOfRows() > 0) {
            $comment = $_POST['payment_status'];

            if ($_POST['payment_status'] == 'Pending') {
              $comment .= '; ' . $_POST['pending_reason'];
            }elseif ( ($_POST['payment_status'] == 'Reversed') || ($_POST['payment_status'] == 'Refunded') ) {
              $comment .= '; ' . $_POST['reason_code'];
            }
            $comments = 'PayPal IPN Invalid [' . $comment . ']';

            osC_Order::insertOrderStatusHistory($_POST['invoice'], $this->order_status, $comments);
          }
        }
      }
    }
  }
?>
