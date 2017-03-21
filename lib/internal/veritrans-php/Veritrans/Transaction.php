<?php
use Magento\Framework\App\Filesystem\DirectoryList;
$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$lib_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/ApiRequestor.php');
$conf_file = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/Config.php');
require_once($lib_file);
require_once($conf_file);

class Veritrans_Transaction {

    public static function status($id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $req = $om->create('Veritrans_ApiRequestor');
        $conf = $om->create('Veritrans\Veritrans_Config');
        return $req->get(
            $conf->getBaseUrl() . '/' . $id . '/status',
            $conf->getServerKey(),
            false);
    }

    public static function approve($id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $req = $om->create('Veritrans_ApiRequestor');
        $conf = $om->create('Veritrans_Config');
        return $req->post(
            $conf->getBaseUrl() . '/' . $id . '/approve',
            $conf->getServerKey(),
            false)->status_code;
    }

    public static function cancel($id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $req = $om->create('Veritrans_ApiRequestor');
        $conf = $om->create('Veritrans_Config');
        return $req->post(
            $conf->getBaseUrl() . '/' . $id . '/cancel',
            $conf->getServerKey(),
            false)->status_code;
    }
}