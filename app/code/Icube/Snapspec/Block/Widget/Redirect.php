<?php

namespace Icube\Snapspec\Block\Widget;
use \Magento\Framework\View\Element\Template;


class Redirect extends Template
{
    protected $Config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Icube\Snapspec\Model\Snapspec $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->Config = $paymentConfig;
    }

    public function getGateUrl(){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::DEFAULT_URL_TYPE);
        $redirUrl = $baseUrl.'snapspec/payment/redirect';
        return $redirUrl;
    }

    public function getAmount()
    {   $orderId = $this->_checkoutSession->getLastOrderId(); 
        if ($orderId) {
            $incrementId = $this->_checkoutSession->getLastRealOrderId();
            return $this->Config->getAmount($incrementId);
        }
    }

    public function getPostData()
    {
        $orderId = $this->_checkoutSession->getLastOrderId(); 
        if ($orderId) {
            $incrementId = $this->_checkoutSession->getLastRealOrderId();
            return $this->Config->getPostData($incrementId);
        }
    }

    public function isVisible() {
        $orderId = $this->_checkoutSession->getLastRealOrderId();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
        $payment = $order->getPayment();
        $code = $payment->getMethodInstance()->getCode();
        return ($code==\Icube\Snapspec\Model\Snapspec::SNAPSPEC_PAYMENT_CODE);
    }

}
