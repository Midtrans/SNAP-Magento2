<?php
namespace Veritrans;

class Veritrans_Config {

  public static $serverKey;
  public static $clientKey;
  public static $apiVersion = 2;
  public static $isProduction = false;
  public static $is3ds = false;
  public static $isSanitized = false;
    public static $curlOptions = array();

  const SANDBOX_BASE_URL = 'https://api.sandbox.veritrans.co.id/v2';
  const PRODUCTION_BASE_URL = 'https://api.veritrans.co.id/v2';
  const SNAP_SANDBOX_BASE_URL = 'https://app.sandbox.midtrans.com/snap/v1';
  const SNAP_PRODUCTION_BASE_URL = 'https://app.midtrans.com/snap/v1';

    public static function getBaseUrl()
    {
        return self::$isProduction ?
            self::PRODUCTION_BASE_URL : self::SANDBOX_BASE_URL;
    }

    public static function getServerKey()
    {
        return self::$serverKey;
    }

    public static function setServerKey($serverKey)
    {
        self::$serverKey = $serverKey;
    }

    public static function getClientKey()
    {
        return self::$clientKey;
    }

    public static function setClientKey($param)
    {
        self::$clientKey = $param;
    }

    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    public static function setApiVersion($param)
    {
        self::$apiVersion = $param;
    }

    public static function getIsProduction()
    {
        return self::$isProduction;
    }

    public static function setIsProduction($param)
    {
        self::$isProduction = $param;
    }

    public static function getIs3ds()
    {
        return self::$is3ds;
    }

    public static function setIs3ds($param)
    {
        self::$is3ds = $param;
    }

    public static function getIsSanitized()
    {
        return self::$isSanitized;
    }

    public static function setIsSanitized($param)
    {
        self::$isSanitized = $param;
    }
	  public static function getSnapBaseUrl()
	  {
	    return self::$isProduction ?
	        self::SNAP_PRODUCTION_BASE_URL : self::SNAP_SANDBOX_BASE_URL;
	  }
}
