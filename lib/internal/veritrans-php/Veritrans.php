<?php

// This snippet due to the braintree_php.
if (version_compare(PHP_VERSION, '5.2.1', '<')) {
    throw new Exception('PHP version >= 5.2.1 required');
}

// This snippet (and some of the curl code) due to the Facebook SDK.
if (!function_exists('curl_init')) {
  throw new Exception('Veritrans needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Veritrans needs the JSON PHP extension.');
}
use Magento\Framework\App\Filesystem\DirectoryList;
$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
$filesystem = $object_manager->get('Magento\Framework\Filesystem');
$root = $filesystem->getDirectoryRead(DirectoryList::ROOT);
$conf = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/Config.php');
$trans = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/Transaction.php');
$req = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/ApiRequestor.php');
$notif = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/Notification.php');
$vtd = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/VtDirect.php');
$vtw = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/VtWeb.php');
$vtw = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/Snap.php');
$san = $root->getAbsolutePath('lib/internal/veritrans-php/Veritrans/Sanitizer.php');

// Configurations
require_once($conf);

// Veritrans API Resources
require_once($trans);

// Plumbing
require_once($req);
require_once($notif);
require_once($vtd);
require_once($vtw);

// Sanitization
require_once($san);