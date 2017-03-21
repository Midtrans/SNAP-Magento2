<?php
namespace Veritrans;

use Magento\Framework\App\Filesystem\DirectoryList;
$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/ApiRequestor.php');
require_once($lib_file);

class Veritrans_VtWeb {

  public static function getRedirectionUrl($params)
  {
      $om = \Magento\Framework\App\ObjectManager::getInstance();
      $req = $om->get('Veritrans_ApiRequestor');
      $conf = $om->get('Veritrans\Veritrans_Config');
      $san = $om->get('Veritrans_Sanitizer');
    $payloads = array(
        'payment_type' => 'vtweb',
        'vtweb' => array(
          // 'enabled_payments' => array('credit_card'),
          'credit_card_3d_secure' => $conf->getIs3ds()
        )
      );

    if (array_key_exists('item_details', $params)) {
      $gross_amount = 0;
      foreach ($params['item_details'] as $item) {
        $gross_amount += $item['quantity'] * $item['price'];
      }
      $payloads['transaction_details']['gross_amount'] = $gross_amount;
    }

    $payloads = array_replace_recursive($payloads, $params);

    if ($conf->getIsSanitized()) {
        $san->jsonRequest($payloads);
    }
    $result = $req->post(
        $conf->getBaseUrl() . '/charge',
        $conf->getServerKey(),
        $payloads);

      echo '$conf->getBaseUrl() :'.$conf->getBaseUrl();
      echo '$conf->getServerKey() :'.$conf->getServerKey();
      echo '$payloads :'.print_r($payloads,true);
      echo '$result :'.print_r($result,true);
    return $result->redirect_url;
  }
}
