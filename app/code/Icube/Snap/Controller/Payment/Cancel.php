<?php
namespace Icube\Snap\Controller\Payment;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans.php');
require_once($lib_file);

class Cancel extends \Magento\Framework\App\Action\Action
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
        error_log('you did it from cancel.php');
        $om = $this->_objectManager;

        $orderId = $this->getValue();
        error_log($orderId);

        $order = $om->get('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
        //error_log(print_r($order,TRUE));
        $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->save();
        $this->unSetValue();
    }

    public function getValue(){
        $this->_coreSession->start();
        return $this->_coreSession->getMessage();
    }

    public function unSetValue(){
        $this->_coreSession->start();
        return $this->_coreSession->unsMessage();
    }    


}
