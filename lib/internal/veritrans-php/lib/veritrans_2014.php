<?php 

namespace Veritrans;

require_once(dirname(__FILE__) . '/../Veritrans.php');
require_once(dirname(__FILE__) . '/veritrans_utility.php');

class Veritrans2014 {

  const DEV_ENDPOINT_URL = 'https://api.sandbox.veritrans.co.id/v2';
  const PRODUCTION_ENDPOINT_URL = 'https://api.veritrans.co.id/v2';

  private $veritrans;
  private $sanitizers;

  public function __construct($veritrans)
  {
    $this->veritrans = $veritrans;
  }

  public function confirm($transaction_id)
  {
    $uri = "/$transaction_id/status";
    return Utility::get($this->_getBaseUrl() . $uri, $this->veritrans->server_key, NULL);
  }

  public function charge($options)
  {
    return $this->getTokens($options);
  }

  public function getTokens($options)
  {
    $ch = curl_init();
    
    $body = json_encode($this->_getPostData());
    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/klikbca.log');
    $logger = new \Zend\Log\Logger();
    $logger->addWriter($writer);

    $logger->info("url ". $this->_getBaseUrl() . '/charge');
    $logger->info('header : Content-Type: application/json
      Accept: application/json Authorization: Basic ' . base64_encode($this->veritrans->server_key . ':'));
    $logger->info("klikbca json ". $body); //die();

    curl_setopt($ch, CURLOPT_URL, $this->_getBaseUrl() . '/charge');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Accept: application/json',
      'Authorization: Basic ' . base64_encode($this->veritrans->server_key . ':')
      ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    try {
      $result = curl_exec($ch);
      if (FALSE === $result)
        throw new \Exception(curl_error($ch), curl_errno($ch));

      curl_close($ch);

      // convert the result into an associative array
      return json_decode($result, true);  
    } catch (Exception $e) {
      trigger_error(sprintf(
        'Curl failed with error #%d: %s',
        $e->getCode(), $e->getMessage()),
        E_USER_ERROR);
      exit;
    }
    
  }

  protected function _getPaymentType()
  {
    return ($this->veritrans->payment_type == \Veritrans::VT_DIRECT ? 'credit_card' :( ($this->veritrans->payment_type)=='bca_klikpay' ? 'bca_klikpay' : 'vtweb' ) );
  }

  protected function _getAddress()
  {
    if ($this->veritrans->address != NULL)
      return $this->veritrans->address;
    else
      return $this->veritrans->address1 . ' ' . $this->veritrans->address2;
  }

  protected function _getItems()
  {
    $items = array();
    foreach ($this->veritrans->items as $item)
    {
      $new_item = array();
      $new_item['id'] = $item['item_id'];
      $new_item['price'] = $item['price'];
      $new_item['quantity'] = $item['quantity'];
      $new_item['name']= $item['item_name1'];
      $items[] = $new_item;
    }
    return $items;
  }

  protected function _getGrossAmount()
  {
    $total = 0;
    foreach ($this->veritrans->items as $item) {
      $total += $item['price'] * intval($item['quantity']);
    }
    return $total;
  }

  protected function _getPostData()
  {
    $data = array();

    $data['payment_type'] = $this->_getPaymentType();
    $payment_type_str = strtolower($this->_getPaymentType());
    $data[$payment_type_str] = array();
    // $data[$payment_type_str]['enabled_payments'] = $this->veritrans->payment_methods;

    $data[$payment_type_str]['type'] = $this->veritrans->bca_klikpay["type"];
    $data[$payment_type_str]['description'] = $this->veritrans->bca_klikpay["description"];

      if($this->veritrans->bank != '')
      $data[$payment_type_str]['bank'] = $this->veritrans->bank;

    // if ($payment_type_str == 'credit_card')
    // {
    //   $data[$payment_type_str]['token_id'] = $this->veritrans->token_id;
    //   $data[$payment_type_str]['bank'] = $this->veritrans->bank;
    //   $data[$payment_type_str]['save_token_id'] = $this->veritrans->save_token_id;
    // }
    
    $data['transaction_details'] = array();
    $data['transaction_details']['order_id'] = $this->veritrans->order_id;
    $data['transaction_details']['gross_amount'] = $this->_getGrossAmount();

    $data['item_details'] = $this->_getItems();

    $data['customer_details'] = array();
    $data['customer_details']['first_name'] = $this->veritrans->first_name;
    if ($this->veritrans->last_name != NULL)
      $data['customer_details']['last_name'] = $this->veritrans->last_name;
    $data['customer_details']['email'] = $this->veritrans->email;
    $data['customer_details']['phone'] = $this->veritrans->phone;
    $data['customer_details']['country'] = $this->veritrans->country_code;

    $data['customer_details']['billing_address'] = array();
    $data['customer_details']['billing_address']['first_name'] = $this->veritrans->first_name;
    $data['customer_details']['billing_address']['last_name'] = $this->veritrans->last_name;
    $data['customer_details']['billing_address']['address'] = $this->_getAddress();
    $data['customer_details']['billing_address']['city'] = $this->veritrans->city;
    $data['customer_details']['billing_address']['postal_code'] = $this->veritrans->postal_code;
    $data['customer_details']['billing_address']['phone'] = $this->veritrans->phone;
    $data['customer_details']['billing_address']['country_code'] = $this->veritrans->country_code;

    if ($this->veritrans->enable_3d_secure)
    {
      if ($this->veritrans->payment_type == \Veritrans::VT_WEB)
      {
        if (is_array($this->veritrans->enable_3d_secure))
        {
          foreach ($this->veritrans->enable_3d_secure as $method) {
            $data[$payment_type_str][$method . '_3d_secure'] = true ;
          }
        } else
        {
          foreach (array("credit_card", "mandiri_clickpay", "cimb_clicks", "permata") as $method) {
            $data[$payment_type_str][$method . '_3d_secure'] = true ;
          }
        }
      } else
      {
        $data['secure'] = TRUE;
      }
    }
      

    return $data;        
  }

  protected function _getBaseUrl() {
    return ($this->veritrans->environment == \Veritrans::ENVIRONMENT_PRODUCTION ? self::PRODUCTION_ENDPOINT_URL : self::DEV_ENDPOINT_URL);
  }

}