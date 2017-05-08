<?php
namespace Icube\Snapinst\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Icube\Snapinst\Model\Snapinst;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;
use Psr\Log\LoggerInterface;
use Magento\Payment\Model\Config as PaymentConfig; 
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session;

final class ConfigProvider  implements ConfigProviderInterface{
    const CODE = 'snapinst';
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
        \Icube\Snapinst\Model\Snapinst $iv,
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
        return [
            'payment' => [
                self::CODE => [
                    'production'=> $production,
                    'clientkey'=> $clientkey
                ]
            ]
        ];
    }

}