<?php
use Magento\Framework\App\Filesystem\DirectoryList;
$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/Transaction.php');
require_once($lib_file);

class Veritrans_Notification {

    private $response;

    public function __construct($input_source = "php://input")
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $om->get('Psr\Log\LoggerInterface');
        $trans = $om->create('Veritrans_Transaction');
        $raw_notification = json_decode(file_get_contents("php://input"), true);
        $type = gettype($raw_notification);
        $logger->debug('VT-Web $type : '.$type);
        if($type=='string'){
            $raw_notification = json_decode($raw_notification, true);
        }
        $logger->debug('VT-Web $raw_notification : '.print_r($raw_notification,true));
        $status_response = $trans->status($raw_notification['transaction_id']);
        $this->response = $status_response;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->response)) {
            return $this->response->$name;
        }
    }
}

?>