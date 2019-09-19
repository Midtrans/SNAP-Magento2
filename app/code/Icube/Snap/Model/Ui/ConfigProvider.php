<?php
namespace Icube\Snap\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Icube\Snap\Model\Snap;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;
use Psr\Log\LoggerInterface;
use Magento\Payment\Model\Config as PaymentConfig; 
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session;

final class ConfigProvider  implements ConfigProviderInterface{
    const CODE = 'snap';
    protected $iv;
    protected $config;
    protected $request;
    protected $assetRepo;
    protected $logger;
    protected $urlBuilder;
    protected $paymentHelper;
    protected $methods = [];
    protected $_checkoutSession;


    public function __construct(
        PaymentConfig $paymentConfig, 
        Repository $assetRepo,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        \Icube\Snap\Model\Snap $iv,
        \Magento\Checkout\Model\Session $checkoutSession,
        LoggerInterface $logger){

        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->iv = $iv;
        $this->config = $paymentConfig;
        $this->assetRepo = $assetRepo; 
        $this->_checkoutSession = $checkoutSession;
    }

    public function getConfig()
    {
        $production = $this->iv->getConfigData("is_production");
        $clientkey = $this->iv->getConfigData("client_key");
        $merchantid = $this->iv->getConfigData("merchant_id");
        $mixpanelkey = $production == 1 ? "17253088ed3a39b1e2bd2cbcfeca939a" : "9dcba9b440c831d517e8ff1beff40bd9";
        $magentoversion = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();

        $composer = file_get_contents(dirname(__FILE__) . '/../../composer.json');
        $json = json_decode($composer, true); // decode the JSON into an associative array
        $pluginversion = $json['version'];

        return [
            'payment' => [
                self::CODE => [
                    'production'=> $production,
                    'clientkey'=> $clientkey,
                    'merchantid' => $merchantid,
                    'mixpanelkey' => $mixpanelkey,
                    'magentoversion' => $magentoversion,
                    'pluginversion' => $pluginversion
                ]
            ]
        ];
    }

}