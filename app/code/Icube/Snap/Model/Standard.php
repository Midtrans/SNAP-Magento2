<?php
/**
 * Veritrans VT Web Model Standard
 *
 * @category   Mage
 * @package    Mage_Veritrans_Snap_Model_Standard
 * @author     Kisman Hong, plihplih.com
 * this class is used after placing order, if the payment is Veritrans, this class will be called and link to redirectAction at Veritrans_Snap_PaymentController class
 */
namespace Icube\Snap\Model;
class Standard extends  \Magento\Payment\Model\Method\AbstractMethod {
    const TRX_STATUS_SETTLEMENT   = 'settlement';
    const ORDER_STATUS_EXPIRE   = 'expire';
	protected $_code = 'snap';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	
	protected $_formBlockType = 'snap/form';
  	protected $_infoBlockType = 'snap/info';
	
	// call to redirectAction function at Veritrans_Snap_PaymentController
	public function getOrderPlaceRedirectUrl() {
		 return 'http://www.google.com/';
	}
}
?>