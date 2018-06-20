<?php
namespace Icube\Snapinstmigs\Controller\Payment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans.php');
require_once($lib_file);

class Redirect extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $_checkoutSession;
    protected $_logger;
    protected $_coreSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ){
        parent::__construct($context);
        $this->_coreSession = $coreSession;
    }

    public function execute()
    {
        $om = $this->_objectManager;
        $session = $om->get('Magento\Checkout\Model\Session');
//        $quote = $session->getQuote();
        $quote2 = $session->getLastRealOrder();
//       echo 'QUOTE ID : '.$quote->getId();


        $vtConfig = $om->get('Veritrans\Veritrans_Config');
        $config = $om->get('Magento\Framework\App\Config\ScopeConfigInterface');

//        $orderIncrementId = $quote->getReservedOrderId();
        $orderIncrementId = $quote2->getIncrementId();
        $orderId = $quote2->getId();
        $quote = $om->create('Magento\Sales\Model\Order')->load($orderId);
//        echo $quote->getId();exit();
//        $order = Mage::getModel('sales/order')
//            ->loadByIncrementId($orderIncrementId);
//        $sessionId = Mage::getSingleton('core/session');

        /* send an order email when redirecting to payment page although payment
           has not been completed. */
//        $order->setState(Mage::getStoreConfig('payment/snap/'),true,
//            'New order, waiting for payment.');
//        $order->sendNewOrderEmail();
//        $order->setEmailSent(true);

//        $api_version = Mage::getStoreConfig('payment/snap/api_version');
//        $payment_type = Mage::getStoreConfig('payment/snap/payment_types');
//        $enable_installment = Mage::getStoreConfig('payment/snap/enable_installment');
//        $is_enabled_bni = Mage::getStoreConfig('payment/snap/enable_installment_bni');
//        $is_enabled_mandiri = Mage::getStoreConfig('payment/snap/enable_installment_mandiri');
        $isProduction = $config->getValue('payment/snapinstmigs/is_production', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)=='1'?true:false;
        $vtConfig->setIsProduction($isProduction);

        $is3ds = $config->getValue('payment/snapinstmigs/is_3ds', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)=='1'?true:false;
        $vtConfig->setIs3ds($is3ds); // selalu true

//        Veritrans_Config::$isSanitized =
//            Mage::getStoreConfig('payment/snap/enable_sanitized') == '1'
//                ? true : false;

//$config = new Veritrans_Config();
        $title = $config->getValue('payment/snapinstmigs/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $serverKey = $config->getValue('payment/snapinstmigs/server_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        echo $title;exit();
        $oneClick = $config->getValue('payment/snapinstmigs/one_click', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $bank = $config->getValue('payment/snapinstmigs/bank', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $vtConfig->setServerKey($serverKey);
//        $vtConfig->setIs3Ds(false);
        $vtConfig->setIsSanitized(false);

        $transaction_details = array();
        $transaction_details['order_id'] = $orderIncrementId;
        $this->setValue($orderIncrementId);

        $order_billing_address = $quote->getBillingAddress();
        $billing_address = array();
        $billing_address['first_name']   = $order_billing_address->getFirstname();
        $billing_address['last_name']    = $order_billing_address->getLastname();
        $billing_address['address']      = $order_billing_address->getStreet()[0];
        $billing_address['city']         = $order_billing_address->getCity();
        $billing_address['postal_code']  = $order_billing_address->getPostcode();
        $billing_address['country_code'] = $this->convert_country_code($order_billing_address->getCountryId());
        $billing_address['phone']        = $order_billing_address->getTelephone();

        $order_shipping_address = $quote->getShippingAddress();
        $shipping_address = array();
        $shipping_address['first_name']   = $order_shipping_address->getFirstname();
        $shipping_address['last_name']    = $order_shipping_address->getLastname();
        $shipping_address['address']      = $order_shipping_address->getStreet()[0];
        $shipping_address['city']         = $order_shipping_address->getCity();
        $shipping_address['postal_code']  = $order_shipping_address->getPostcode();
        $shipping_address['phone']        = $order_shipping_address->getTelephone();
        $shipping_address['country_code'] =
            $this->convert_country_code($order_shipping_address->getCountryId());

        $customer_details = array();
        $customer_details['billing_address']  = $billing_address;
        $customer_details['shipping_address'] = $shipping_address;
        $customer_details['first_name']       = $order_billing_address
            ->getFirstname();
        $customer_details['last_name']        = $order_billing_address
            ->getLastname();
        $customer_details['email']            = $order_billing_address->getEmail();
        $customer_details['phone']            = $order_billing_address
            ->getTelephone();

        /*dummy data*/
//        $billing_address['first_name']   = 'testfirst';
//        $billing_address['last_name']    = 'testlast';
//        $billing_address['address']      = 'tess address';
//        $billing_address['city']         = 'test city';
//        $billing_address['postal_code']  = '12345';
//        $billing_address['country_code'] = $this->convert_country_code('IN');
//        $billing_address['phone']        = '08123123123';

//        $shipping_address['first_name']   = 'testfirst';
//        $shipping_address['last_name']    = 'testlast';
//        $shipping_address['address']      = 'tess address';
//        $shipping_address['city']         = 'test city';
//        $shipping_address['postal_code']  = '12345';
//        $shipping_address['country_code'] = $this->convert_country_code('IN');
//        $shipping_address['phone']        = '08123123123';

        $customer_details['billing_address']  = $billing_address;
        $customer_details['shipping_address'] = $shipping_address;
//        $customer_details['first_name']       = 'testfirst';
//        $customer_details['last_name']        = 'testlast';
//        $customer_details['email']            = 'test@test.com';
//        $customer_details['phone']            = '08123123123';
        /*dummy data*/

        $items               = $quote->getAllItems();
//        var_dump($items);exit();
        $shipping_amount     = $quote->getShippingAmount();
        $shipping_tax_amount = $quote->getShippingTaxAmount();
        $tax_amount = $quote->getTaxAmount();

        $item_details = array();

        /*dummy data*/
//        $item = array(
//            'id'       => '123',
//            'price'    => 100000,
//            'quantity' => 1,
//            'name'     => 'dummy product'
//        );
//        $item_details[] = $item;
        /*dummy data*/

        foreach ($items as $each) {
//            echo print_r($each,true);
            $item = array(
                'id'       => $each->getProductId(),
                'price'    => (string)round($each->getPrice()),
                'quantity' => (string)round($each->getQtyOrdered()),
                'name'     => $this->repString($this->getName($each->getName()))
            );

//            if ($item['quantity'] == 0) continue;
            // error_log(print_r($each->getProductOptions(), true));
            $item_details[] = $item;
        }
//        exit();

        $num_products = count($item_details);

        unset($each);

        if ($quote->getDiscountAmount() != 0) {
            $couponItem = array(
                'id' => 'DISCOUNT',
                'price' => round($quote->getDiscountAmount()),
                'quantity' => 1,
                'name' => 'DISCOUNT'
            );
            $item_details[] = $couponItem;
        }

        if ($shipping_amount > 0) {
            $shipping_item = array(
                'id' => 'SHIPPING',
                'price' => round($shipping_amount),
                'quantity' => 1,
                'name' => 'Shipping Cost'
            );
            $item_details[] =$shipping_item;
        }

        if ($shipping_tax_amount > 0) {
            $shipping_tax_item = array(
                'id' => 'SHIPPING_TAX',
                'price' => round($shipping_tax_amount),
                'quantity' => 1,
                'name' => 'Shipping Tax'
            );
            $item_details[] = $shipping_tax_item;
        }

        if ($tax_amount > 0) {
            $tax_item = array(
                'id' => 'TAX',
                'price' => round($tax_amount),
                'quantity' => 1,
                'name' => 'Tax'
            );
            $item_details[] = $tax_item;
        }

        if ($quote->getBaseGiftCardsAmount() != 0) {
            $giftcardAmount = array(
                'id' => 'GIFTCARD',
                'price' => round($quote->getBaseGiftCardsAmount()*-1),
                'quantity' => 1,
                'name' => 'GIFTCARD'
            );
            $item_details[] = $giftcardAmount;
        }

        if ($quote->getBaseCustomerBalanceAmount() != 0) {
            $balancAmount = array(
                'id' => 'STORE CREDIT',
                'price' => round($quote->getBaseCustomerBalanceAmount()*-1),
                'quantity' => 1,
                'name' => 'STORE CREDIT'
            );
            $item_details[] = $balancAmount;
        }

        $totalPrice = 0;
        foreach ($item_details as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        $transaction_details['gross_amount'] = $totalPrice;

        $terms      = array(3,6,9,12,15,18,21,24,27,30,33,36);
        $installment = array();
        $installment['required'] = true;
        $installment['terms'] = array(

            'bca' => $terms,
            'bri' => $terms,
            'maybank' => $terms 
            );

        $credit_card['channel'] = "migs";
        $credit_card['bank'] = $bank;
        $credit_card['installment'] = $installment;

        
        $payloads = array();
        $payloads['transaction_details'] = $transaction_details;
        $payloads['enabled_payments']    = array('credit_card');
        $payloads['item_details']        = $item_details;
        $payloads['customer_details']    = $customer_details;


         if($oneClick == 1){
            $credit_card['secure'] = true;
            $credit_card['save_card'] = true;
            $payloads['user_id'] = crypt($order_billing_address->getEmail(), $serverKey);
        }
        $payloads['credit_card'] = $credit_card;

        try {
//            $this->_logger->addDebug('some text or variable');
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            error_log(print_r($payloads,true));
            $logger->info('$payloads:'.print_r($payloads,true));
//            var_dump($payloads);
//            Mage::log('$payloads:'.print_r($payloads,true),null,'snap_payloads.log',true);
            $snap = $om->get('Veritrans\Veritrans_Snap');
            $token = $snap->getSnapToken($payloads);
            $logger->info('snap token:'.print_r($token,true));
//            var_dump($redirUrl);exit();
            // error_log('snap_token:'.$token);
            // echo $token;

            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setData($token);
            return $result;



        }
        catch (Exception $e) {
            error_log($e->getMessage());
//            Mage::log('error:'.print_r($e->getMessage(),true),null,'snap.log',true);
        }

//        $page_object = $this->resultFactory->create();;
//        return $page_object;
    }

    public function setValue($order_id){
        $this->_coreSession->start();
        $this->_coreSession->setMessage($order_id);
    }

    public function setSessionData($key, $value)
    {
        return $this->_checkoutSession->setData($key, $value);
    }

    public function getSessionData($key, $remove = false)
    {
        return $this->_checkoutSession->getData($key, $remove);
    }


    public function convert_country_code( $country_code ) {

        // 3 digits country codes
        $cc_three = array(
            'AF' => 'AFG',
            'AX' => 'ALA',
            'AL' => 'ALB',
            'DZ' => 'DZA',
            'AD' => 'AND',
            'AO' => 'AGO',
            'AI' => 'AIA',
            'AQ' => 'ATA',
            'AG' => 'ATG',
            'AR' => 'ARG',
            'AM' => 'ARM',
            'AW' => 'ABW',
            'AU' => 'AUS',
            'AT' => 'AUT',
            'AZ' => 'AZE',
            'BS' => 'BHS',
            'BH' => 'BHR',
            'BD' => 'BGD',
            'BB' => 'BRB',
            'BY' => 'BLR',
            'BE' => 'BEL',
            'PW' => 'PLW',
            'BZ' => 'BLZ',
            'BJ' => 'BEN',
            'BM' => 'BMU',
            'BT' => 'BTN',
            'BO' => 'BOL',
            'BQ' => 'BES',
            'BA' => 'BIH',
            'BW' => 'BWA',
            'BV' => 'BVT',
            'BR' => 'BRA',
            'IO' => 'IOT',
            'VG' => 'VGB',
            'BN' => 'BRN',
            'BG' => 'BGR',
            'BF' => 'BFA',
            'BI' => 'BDI',
            'KH' => 'KHM',
            'CM' => 'CMR',
            'CA' => 'CAN',
            'CV' => 'CPV',
            'KY' => 'CYM',
            'CF' => 'CAF',
            'TD' => 'TCD',
            'CL' => 'CHL',
            'CN' => 'CHN',
            'CX' => 'CXR',
            'CC' => 'CCK',
            'CO' => 'COL',
            'KM' => 'COM',
            'CG' => 'COG',
            'CD' => 'COD',
            'CK' => 'COK',
            'CR' => 'CRI',
            'HR' => 'HRV',
            'CU' => 'CUB',
            'CW' => 'CUW',
            'CY' => 'CYP',
            'CZ' => 'CZE',
            'DK' => 'DNK',
            'DJ' => 'DJI',
            'DM' => 'DMA',
            'DO' => 'DOM',
            'EC' => 'ECU',
            'EG' => 'EGY',
            'SV' => 'SLV',
            'GQ' => 'GNQ',
            'ER' => 'ERI',
            'EE' => 'EST',
            'ET' => 'ETH',
            'FK' => 'FLK',
            'FO' => 'FRO',
            'FJ' => 'FJI',
            'FI' => 'FIN',
            'FR' => 'FRA',
            'GF' => 'GUF',
            'PF' => 'PYF',
            'TF' => 'ATF',
            'GA' => 'GAB',
            'GM' => 'GMB',
            'GE' => 'GEO',
            'DE' => 'DEU',
            'GH' => 'GHA',
            'GI' => 'GIB',
            'GR' => 'GRC',
            'GL' => 'GRL',
            'GD' => 'GRD',
            'GP' => 'GLP',
            'GT' => 'GTM',
            'GG' => 'GGY',
            'GN' => 'GIN',
            'GW' => 'GNB',
            'GY' => 'GUY',
            'HT' => 'HTI',
            'HM' => 'HMD',
            'HN' => 'HND',
            'HK' => 'HKG',
            'HU' => 'HUN',
            'IS' => 'ISL',
            'IN' => 'IND',
            'ID' => 'IDN',
            'IR' => 'RIN',
            'IQ' => 'IRQ',
            'IE' => 'IRL',
            'IM' => 'IMN',
            'IL' => 'ISR',
            'IT' => 'ITA',
            'CI' => 'CIV',
            'JM' => 'JAM',
            'JP' => 'JPN',
            'JE' => 'JEY',
            'JO' => 'JOR',
            'KZ' => 'KAZ',
            'KE' => 'KEN',
            'KI' => 'KIR',
            'KW' => 'KWT',
            'KG' => 'KGZ',
            'LA' => 'LAO',
            'LV' => 'LVA',
            'LB' => 'LBN',
            'LS' => 'LSO',
            'LR' => 'LBR',
            'LY' => 'LBY',
            'LI' => 'LIE',
            'LT' => 'LTU',
            'LU' => 'LUX',
            'MO' => 'MAC',
            'MK' => 'MKD',
            'MG' => 'MDG',
            'MW' => 'MWI',
            'MY' => 'MYS',
            'MV' => 'MDV',
            'ML' => 'MLI',
            'MT' => 'MLT',
            'MH' => 'MHL',
            'MQ' => 'MTQ',
            'MR' => 'MRT',
            'MU' => 'MUS',
            'YT' => 'MYT',
            'MX' => 'MEX',
            'FM' => 'FSM',
            'MD' => 'MDA',
            'MC' => 'MCO',
            'MN' => 'MNG',
            'ME' => 'MNE',
            'MS' => 'MSR',
            'MA' => 'MAR',
            'MZ' => 'MOZ',
            'MM' => 'MMR',
            'NA' => 'NAM',
            'NR' => 'NRU',
            'NP' => 'NPL',
            'NL' => 'NLD',
            'AN' => 'ANT',
            'NC' => 'NCL',
            'NZ' => 'NZL',
            'NI' => 'NIC',
            'NE' => 'NER',
            'NG' => 'NGA',
            'NU' => 'NIU',
            'NF' => 'NFK',
            'KP' => 'MNP',
            'NO' => 'NOR',
            'OM' => 'OMN',
            'PK' => 'PAK',
            'PS' => 'PSE',
            'PA' => 'PAN',
            'PG' => 'PNG',
            'PY' => 'PRY',
            'PE' => 'PER',
            'PH' => 'PHL',
            'PN' => 'PCN',
            'PL' => 'POL',
            'PT' => 'PRT',
            'QA' => 'QAT',
            'RE' => 'REU',
            'RO' => 'SHN',
            'RU' => 'RUS',
            'RW' => 'EWA',
            'BL' => 'BLM',
            'SH' => 'SHN',
            'KN' => 'KNA',
            'LC' => 'LCA',
            'MF' => 'MAF',
            'SX' => 'SXM',
            'PM' => 'SPM',
            'VC' => 'VCT',
            'SM' => 'SMR',
            'ST' => 'STP',
            'SA' => 'SAU',
            'SN' => 'SEN',
            'RS' => 'SRB',
            'SC' => 'SYC',
            'SL' => 'SLE',
            'SG' => 'SGP',
            'SK' => 'SVK',
            'SI' => 'SVN',
            'SB' => 'SLB',
            'SO' => 'SOM',
            'ZA' => 'ZAF',
            'GS' => 'SGS',
            'KR' => 'KOR',
            'SS' => 'SSD',
            'ES' => 'ESP',
            'LK' => 'LKA',
            'SD' => 'SDN',
            'SR' => 'SUR',
            'SJ' => 'SJM',
            'SZ' => 'SWZ',
            'SE' => 'SWE',
            'CH' => 'CHE',
            'SY' => 'SYR',
            'TW' => 'TWN',
            'TJ' => 'TJK',
            'TZ' => 'TZA',
            'TH' => 'THA',
            'TL' => 'TLS',
            'TG' => 'TGO',
            'TK' => 'TKL',
            'TO' => 'TON',
            'TT' => 'TTO',
            'TN' => 'TUN',
            'TR' => 'TUR',
            'TM' => 'TKM',
            'TC' => 'TCA',
            'TV' => 'TUV',
            'UG' => 'UGA',
            'UA' => 'UKR',
            'AE' => 'ARE',
            'GB' => 'GBR',
            'US' => 'USA',
            'UY' => 'URY',
            'UZ' => 'UZB',
            'VU' => 'VUT',
            'VA' => 'VAT',
            'VE' => 'VEN',
            'VN' => 'VNM',
            'WF' => 'WLF',
            'EH' => 'ESH',
            'WS' => 'WSM',
            'YE' => 'YEM',
            'ZM' => 'ZMB',
            'ZW' => 'ZWE'
        );

        // Check if country code exists
        if( isset( $cc_three[ $country_code ] ) && $cc_three[ $country_code ] != '' ) {
            $country_code = $cc_three[ $country_code ];
        }

        return $country_code;
    }

    public function setValue($order_id){
        $this->_coreSession->start();
        $this->_coreSession->setMessage($order_id);
    }

    private function repString($str){
        return preg_replace("/[^a-zA-Z0-9]+/", " ", $str);
    }

    private function getName($s)
    {
        $max_length = 20;
        if (strlen($s) > $max_length) {
            $offset = ($max_length - 3) - strlen($s);
            $s      = substr($s, 0, strrpos($s, ' ', $offset));
        }
        return $s;
    }
}
