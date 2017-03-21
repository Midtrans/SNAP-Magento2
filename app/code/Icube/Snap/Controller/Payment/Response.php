<?php
namespace Icube\Snap\Controller\Payment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans.php');
require_once($lib_file);

class Response extends \Magento\Framework\App\Action\Action
{
    protected $product;
    protected $cart;
    protected $_responseFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product $product,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\ResponseFactory $responseFactory
    )
    {
        $this->product = $product;
        $this->cart = $cart;
        $this->_responseFactory = $responseFactory;
       parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $om = $this->_objectManager;
        $session = $om->get('Magento\Checkout\Model\Session');
        $quote = $session->getQuote();


        if(isset($_GET['order_id']) ) {
            $config = $om->get('Magento\Framework\App\Config\ScopeConfigInterface');
            $prefix = $config->getValue('payment/snap/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//            Mage::log('GET:'.print_r($_GET,true),null,'responseAction.log',true);
            $orderId = $_GET['order_id']; // Generally sent by gateway
            $orderId = str_replace($prefix,'',$orderId);
            $status = isset($_GET['status_code'])?$_GET['status_code']:0 ;
            $transStatus = isset($_GET['transaction_status'])?$_GET['transaction_status']:"Unknown" ;

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/responseafterpayment.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $_info = "status : ".$status." , orderid : ".$orderId.".";
            $logger->info( $_info );

            if( ($status == '200' or $status =='201' ) && !is_null($orderId) && $orderId != '') {
				return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
            } 
            else if($status == '202' && $transStatus == 'deny' && !is_null($orderId) && $orderId != '') {
                 $this->fromOrderId($orderId);
                 // Back to merchant - reorder
            }
            else {
                 $this->fromOrderId($orderId);
                // Back to merchant - reorder
            }
        } else if ( isset($_GET['id']) ) { // BCA klikpay
            $config = $om->get('Magento\Framework\App\Config\ScopeConfigInterface');

            $veritransconf = $om->create('Veritrans\Veritrans_Config');
            $veritransconf->setServerKey($config->getValue('payment/vtklikbca/server_key_v2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ;
            $data = \Veritrans_Transaction::status($_GET['id']);
            if ($data->transaction_status == 'settlement' ) {
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
            }else{
                $orderId = $_GET['order_id'];
                $this->fromOrderId($orderId);
            }

        }
        else{
               return $this->resultRedirectFactory->create()->setPath('/');
        }

    }

    public function fromOrderId($orderId){
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
        $orderItems = $order->getAllItems();
        $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_CANCELED, "Gateway has declined the payment");
        $order->save();

        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::DEFAULT_URL_TYPE);
        $redirUrl = $baseUrl.'checkout/onepage/success';

        foreach($orderItems as $item){
            $productId = $item->getProductId();
            $params = array();
            $params['qty'] = $item->getQtyOrdered();
            $_product = $this->product->load($productId);

            $stockState = $this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
            $stock = $stockState->getStockQty($_product->getId(), $_product->getStore()->getWebsiteId());
            if ($_product && $stock > 0) {
                $redirUrl = $baseUrl.'checkout/cart';
                $this->cart->addProduct($_product, $params);
                $this->cart->save();
            }
        }

        $this->_messageManager->addError( __('Can not submit payment confirmation. Please try again later.') );
        $a = $this->_responseFactory->create()->setRedirect($redirUrl)->sendResponse();

        return true;
    }
}
