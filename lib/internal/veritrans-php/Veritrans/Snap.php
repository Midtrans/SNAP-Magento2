<?php
namespace Veritrans;

use Magento\Framework\App\Filesystem\DirectoryList;
$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/SnapApiRequestor.php');
require_once($lib_file);

/**
 * Create Snap payment page and return snap token
 *
 */
class Veritrans_Snap {

  /**
   * Create Snap payment page
   *
   * Example:
   *
   * ```php
   *   $params = array(
   *     'transaction_details' => array(
   *       'order_id' => rand(),
   *       'gross_amount' => 10000,
   *     )
   *   );
   *   $paymentUrl = Veritrans_Snap::getSnapToken($params);
   * ```
   *
   * @param array $params Payment options
   * @return string Snap token.
   * @throws Exception curl error or veritrans error
   */
  public static function getSnapToken($params)
  {
      $om = \Magento\Framework\App\ObjectManager::getInstance();
      $req = $om->get('Veritrans_SnapApiRequestor');
      $conf = $om->get('Veritrans\Veritrans_Config');
      $san = $om->get('Veritrans_Sanitizer');
    $payloads = array(
      'credit_card' => array(
        // 'enabled_payments' => array('credit_card'),
        'secure' => $conf->getIs3ds()
      )
    );

    if (array_key_exists('item_details', $params)) {
      $gross_amount = 0;
      foreach ($params['item_details'] as $item) {
        $gross_amount += $item['quantity'] * $item['price'];
      }
      $params['transaction_details']['gross_amount'] = $gross_amount;
    }

    if ($conf->getIsSanitized()) {
      $san->jsonRequest($params);
    }

    $params = array_replace_recursive($payloads, $params);

    $result = $req->post(
        $conf->getSnapBaseUrl() . '/transactions',
        $conf->getServerKey(),
        $params);

    return $result->token;
  }
}
