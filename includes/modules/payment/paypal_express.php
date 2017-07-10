<?php
/*
  $Id: paypal_express.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_paypal_express extends osC_Payment {
    var $_title,
        $_code = 'paypal_express',
        $_status = false,
        $_sort_order,
        $_order_id;
        
    function osC_Payment_paypal_express() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
      
      $osC_Language->load('modules-payment');
      
      $this->_title = $osC_Language->get('payment_paypal_express_title');
      $this->_method_title = $osC_Language->get('payment_paypal_express_method_title');
      $this->_status = (MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER;

      switch (MODULE_PAYMENT_PAYPAL_EXPRESS_SERVER) {
        case 'Production':
          $this->api_url = 'https://api-3t.paypal.com/nvp';
          $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          break;

        default:
          $this->api_url = 'https://api-3t.sandbox.paypal.com/nvp';
          $this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          break;
      }

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_PAYPAL_EXPRESS_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_PAYPAL_EXPRESS_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_PAYPAL_EXPRESS_ZONE > 0) {
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
      
    function checkout_initialization_method() {
      global $osC_Language;
      
      if (file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'modules/payment/paypal/btn_express_' . basename($osC_Language->getCode()) . '.gif')) {
        $image = DIR_WS_IMAGES . 'modules/payment/paypal/btn_express_' . basename($osC_Language->getCode()) . '.gif';
      } else {
        $image = DIR_WS_IMAGES . 'modules/payment/paypal/btn_express.gif';
      }
      
      $string = osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'callback&module=paypal_express'), osc_image($image, $osC_Language->get('payment_paypal_express_button_title')));

      return $string;
    }
    
    function selection() {
      if (isset($_SESSION['ppe_token'])) {
        return array('id' => $this->_code,
                 'module' => $this->_method_title);
      }else {
        return false;
      }
    }
    
    function pre_confirmation_check() {
       return osc_href_link(FILENAME_CHECKOUT,'callback&module=paypal_express');
    } 
    
    function confirmation() {
      return false;
    }
    
    function process_button() {
      return false;
    }

    
    function process() {
      global $osC_ShoppingCart, $osC_Currencies, $messageStack;

      $orders_id = osC_Order::insert();
      
      $params = array('USER' => MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME,
                      'PWD' => MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD,
                      'VERSION' => '3.2',
                      'SIGNATURE' => MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE,
                      'METHOD' => 'DoExpressCheckoutPayment',
                      'TOKEN' => $_SESSION['ppe_token'],
                      'PAYMENTACTION' => ((MODULE_PAYMENT_PAYPAL_EXPRESS_METHOD == 'Sale') ? 'Sale' : 'Authorization'),
                      'PAYERID' => $_SESSION['ppe_payerid'],
                      'AMT' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $osC_ShoppingCart->getShippingMethod('cost'), $osC_Currencies->getCode()),
                      'CURRENCYCODE' => $osC_Currencies->getCode(),
                      'BUTTONSOURCE' => PROJECT_VERSION);
      
      if ($osC_ShoppingCart->hasShippingAddress()) {
        $params['SHIPTONAME'] = $osC_ShoppingCart->getShippingAddress('firstname') . ' ' . $osC_ShoppingCart->getShippingAddress('lastname');
        $params['SHIPTOSTREET'] = $osC_ShoppingCart->getShippingAddress('street_address');
        $params['SHIPTOCITY'] = $osC_ShoppingCart->getShippingAddress('city');
        $params['SHIPTOSTATE'] = $osC_ShoppingCart->getShippingAddress('zone_code');
        $params['SHIPTOCOUNTRYCODE'] = $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
        $params['SHIPTOZIP'] = $osC_ShoppingCart->getShippingAddress('postcode');
      }
      
      $post_string = '';
      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }
      $post_string = substr($post_string, 0, -1);
           
      $response = $this->sendTransactionToGateway($this->api_url, $post_string);
      $response_array = array();
      parse_str($response, $response_array);
      
      unset($_SESSION['ppe_token']);
      unset($_SESSION['ppe_payerid']);

      if (($response_array['ACK'] != 'Success') && ($response_array['ACK'] != 'SuccessWithWarning')) {
        $messageStack->add_session('shopping_cart', stripslashes($response_array['L_LONGMESSAGE0']), 'error');
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, '', 'SSL'));
        exit;
      }else {
        osC_Order::process($orders_id, $this->order_status);
      }
    }
    
    function callback() {
      global $osC_Database, $osC_ShoppingCart, $osC_Currencies;
      
      if (!$osC_ShoppingCart->hasContents()) {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
      }
      
      $params = array('USER' => MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME,
                      'PWD' => MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD,
                      'VERSION' => '3.2',
                      'SIGNATURE' => MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE);
      
      if (isset($_GET['express_action']) && ($_GET['express_action'] == 'retrieve')) {
        self::_get_express_checkout_details($params);
      } else {
        self::_set_express_checkout($params);
      }
      
      exit;
    }
    
    function _get_express_checkout_details($params) {
      global $osC_ShoppingCart, $osC_Database, $osC_Customer;
      
      $params['METHOD'] = 'GetExpressCheckoutDetails';
      $params['TOKEN'] = $_GET['token'];
      
      $post_string = '';
      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }
      $post_string = substr($post_string, 0, -1);
      
      $response = $this->sendTransactionToGateway($this->api_url, $post_string);
     
      $response_array = array();
      parse_str($response, $response_array);
      
      if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
        if ($osC_ShoppingCart->getContentType() != 'virtual') {
          $country_query = $osC_Database->query('select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format from :table_countries where countries_iso_code_2 = :country_iso_code_2');
          $country_query->bindTable(':table_countries', TABLE_COUNTRIES);
          $country_query->bindValue(':country_iso_code_2', $response_array['SHIPTOCOUNTRYCODE']);
          $country_query->execute();
          
          $country = $country_query->toArray();
          
          $zone_name = $response_array['SHIPTOSTATE'];
          $zone_id = 0;
          
          $zone_query = $osC_Database->query('select zone_id, zone_name from :table_zones where zone_country_id = :zone_country_id and zone_code = :zone_code');
          $zone_query->bindTable(':table_zones', TABLE_ZONES);
          $zone_query->bindInt(':zone_country_id', $country['countries_id']);
          $zone_query->bindValue(':zone_code', $response_array['SHIPTOSTATE']);
          $zone_query->execute();
          
          if ($zone_query->numberOfRows()) {
            $zone = $zone_query->toArray();
            $zone_name = $zone['zone_name'];
            $zone_id = $zone['zone_id'];
          }
          
          $sendto = array('firstname' => substr($response_array['SHIPTONAME'], 0, strpos($response_array['SHIPTONAME'], ' ')),
                          'lastname' => substr($response_array['SHIPTONAME'], strpos($response_array['SHIPTONAME'], ' ')+1),
                          'company' => '',
                          'street_address' => $response_array['SHIPTOSTREET'],
                          'suburb' => '',
                          'email_address' => $response_array['EMAIL'],
                          'postcode' => $response_array['SHIPTOZIP'],
                          'city' => $response_array['SHIPTOCITY'],
                          'zone_id' => $zone_id,
                          'zone_name' => $zone_name,
                          'country_id' => $country['countries_id'],
                          'country_name' => $country['countries_name'],
                          'country_iso_code_2' => $country['countries_iso_code_2'],
                          'country_iso_code_3' => $country['countries_iso_code_3'],
                          'address_format_id' => ($country['address_format_id'] > 0 ? $country['address_format_id'] : '1'));
          
          $osC_ShoppingCart->setRawShippingAddress($sendto);
          $osC_ShoppingCart->setRawBillingAddress($sendto);
          $osC_ShoppingCart->setBillingMethod(array('id' => $this->getCode(), 'title' => $this->getMethodTitle()));
          
          if (!isset($_SESSION['payment'])) {
            $_SESSION['payment'] = $this->getCode();
          }
          
          if (!isset($_SESSION['ppe_token'])) {
            $_SESSION['ppe_token'] = $response_array['TOKEN'];
          }
          
          if (!isset($_SESSION['ppe_payerid'])) {
            $_SESSION['ppe_payerid'] = $response_array['PAYERID'];
          }
          
          if ($osC_Customer->isLoggedOn() === true) {
            osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&express=active&view=shippingMethodForm', 'SSL'));
          } else if ($this->_findEmail($response_array['EMAIL'])) {
            osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'));
          } else {
            osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&express=active&view=billingInformationForm', 'SSL'));
          }
        }
      }
    }
    
    function _set_express_checkout($params) {
      global $osC_ShoppingCart, $osC_Currencies;
      
      $params['METHOD'] = 'SetExpressCheckout';
      $params['PAYMENTACTION'] = ((MODULE_PAYMENT_PAYPAL_EXPRESS_METHOD == 'Sale') ? 'Sale' : 'Authorization');
      $params['RETURNURL'] = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . FILENAME_CHECKOUT . '?callback&module=paypal_express&express_action=retrieve';
      //$params['RETURNURL'] = osc_href_link(FILENAME_CHECKOUT, 'callback&module=paypal_express&express_action=retrieve', 'NONSSL', true, true, true);
      $params['CANCELURL'] = osc_href_link(FILENAME_CHECKOUT, '', 'NONSSL', true, true, true);
      $params['AMT'] =  $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $osC_ShoppingCart->getShippingMethod('cost'), $osC_Currencies->getCode());
      $params['CURRENCYCODE'] = $osC_Currencies->getCode();
      
      if ($osC_ShoppingCart->getContentType() == 'virtual') {
        $params['NOSHIPPING'] = '1';
      }
      
      if ($osC_ShoppingCart->hasShippingAddress()) {
        $params['SHIPTOSTREET'] = $osC_ShoppingCart->getShippingAddress('street_address');
        $params['SHIPTOCITY'] = $osC_ShoppingCart->getShippingAddress('city');
        $params['SHIPTOSTATE'] = $osC_ShoppingCart->getShippingAddress('state');
        $params['SHIPTOCOUTRYCODE'] = $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
        $params['SHIPTOZIP'] = $osC_ShoppingCart->getShippingAddress('postcode');
      }

      $post_string = '';
      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }
      $post_string = substr($post_string, 0, -1);
      
      $response = $this->sendTransactionToGateway($this->api_url, $post_string);
      
      $response_array = array();
      parse_str($response, $response_array);
      
      if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
        osc_redirect($this->paypal_url . '&token=' . $response_array['TOKEN']);
      } else {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, '', 'SSL'));
      }
    }
    
    function _findEmail($email) {
      global $osC_Database;
      
      $Qcheck = $osC_Database->query('select customers_id from :table_customers where customers_email_address = :email');
      $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcheck->bindValue(':email', $email);
      $Qcheck->execute();
      
      if ($Qcheck->numberOfRows() > 0) {
        return true;
      }else {
        return false;
      }
    }
  }
?>