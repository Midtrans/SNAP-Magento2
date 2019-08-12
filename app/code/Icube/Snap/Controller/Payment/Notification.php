<?php
namespace Icube\Snap\Controller\Payment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans.php');
require_once($lib_file);

class Notification extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     */
    protected $orderCommentSender;

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->orderCommentSender = $orderCommentSender;
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        echo 'you did it';
        error_log('notif url');
        $om = $this->_objectManager;
        //        $session = $om->get('Magento\Checkout\Model\Session');
        $vtConfig = $om->get('Veritrans\Veritrans_Config');
        $config = $om->get('Magento\Framework\App\Config\ScopeConfigInterface');

        $isProduction = $config->getValue('payment/snap/is_production', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)=='1'?true:false;
        error_log($isProduction);
        $vtConfig->setIsProduction($isProduction);
        $serverKey = $config->getValue('payment/snap/server_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        error_log($serverKey);
        $vtConfig->setServerKey($serverKey);
        $notif = $om->get('Veritrans_Notification');
        error_log(print_r($notif,TRUE));
        /*$prefix = $config->getValue('payment/snap/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $orderId = str_replace($prefix,'',$notif->order_id);
        if (strpos($orderId,'-') !== false) {
            $arrOrderId = explode("-",$orderId);
            $orderId = $arrOrderId[0];
        }*/
        $orderId = $notif->order_id;
        $order = $om->get('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
        
        $transaction = $notif->transaction_status;
        $fraud = $notif->fraud_status;
        $payment_type = $notif->payment_type;

        ##log notif snap
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/responsenotificationsnap.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $_info = "status : ".$transaction." , order : ".$orderId.", payment type : ".$payment_type;
        $logger->info( $_info );
        ##log notif snap
        if ($transaction == 'capture') {
          $order->setInstallmentTenor($notif->installment_term);
          if ($fraud == 'challenge') {
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
          }
          else if ($fraud == 'accept') {
            if($order->canInvoice() && !$order->hasInvoices()) {
              $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
              $invoice->register();
              $invoice->save();
              $invoice->pay();
              $transactionSave = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                  ->addObject($invoice)
                  ->addObject($invoice->getOrder());
              $transactionSave->save();
            }
            $order->setData('state', 'processing');
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            
            $emailSender = $this->_objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $emailSender->send($order);
            
            // Xtento_AdvancedOrderStatus compatibility
            if ($this->registry->registry('advancedorderstatus_notifications')) {
               $this->orderCommentSender->send($order);
            }
          }
        }
        else if ($transaction == 'settlement') {
          if($payment_type != 'credit_card'){
            if($order->canInvoice() && !$order->hasInvoices()) {
              $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
              $invoice->register();
              $invoice->save();
              $invoice->pay();
              $transactionSave = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                  ->addObject($invoice)
                  ->addObject($invoice->getOrder());
              $transactionSave->save();
            }
            $order->setData('state', 'processing');
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            if ($this->registry->registry('advancedorderstatus_notifications')) {
                $this->orderCommentSender->send($order);
            }

            $emailSender = $this->_objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $emailSender->send($order);
          }
        }
        else if ($transaction == 'pending') {
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        }
        else if ($transaction == 'cancel' || $transaction == 'deny' || $transaction == 'expire') {
          if ($order->canCancel()) {
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_CANCELED);
          }
        }
        error_log('before order save');
        $order->save();
        error_log('order save sukses');
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}