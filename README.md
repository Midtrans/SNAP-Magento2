
Midtrans&nbsp; Magento Payment Gateway Module
=====================================

Midtrans&nbsp; :heart:Magento!

### Copyright

Midtrans Payment Extension for Magento 2" is owned and developed by ICUBE (PT Inovasi Informasi Indonesia -http://www.icubeonline.com), a private company established in Jakarta, Indonesia. Unless expressly stated to the contrary, the copyright and other intellectual property rights (such as, design rights, trademarks, patents, etc.) in any material provided on "Midtrans Payment Extension for Magento 2" remains the property of ICUBE. ICUBE owned material on "Midtrans Payment Extension for Magento 2" including text, images and source code, may not be printed, copied, reproduced, republished, downloaded, posted, displayed, modified, reused, broadcast or transmitted in any way, unless prior permission has been given. Please contact E-mail : sales@icube.us for further details on obtaining such permission. Unauthorized use by the third party entitles ICUBE to take certain legal steps.

Let your Magento store integrated with Midtrans payment gateway.

### Description

Midtrans payment gateway is an online payment gateway that is highly concerned with customer experience (UX). They strive to make payments simple for both the merchant and customers. With this plugin you can make your Magento store using Midtrans payment.

Payment Method Feature:

* Credit card fullpayment and other payment methods.
* Bank transfer, internet banking for various banks
* Credit card Online & offline installment payment.
* Credit card BIN, bank transfer, and other channel payment.
* Custom expiry.
* Two-click & One-click feature.
* Midtrans Snap all payment method fullpayment.

### Installation

#### Minimum Requirements

* This plugin is tested with Magento version 2.3.x
> ###### Note : if you are using Magento 2 version less than 2.3.x (2.0.x, 2.1.x, 2.2.x) and notification doesn't seem to work, please try this version: [magento2-pre-version-2.3](https://github.com/Midtrans/SNAP-Magento2/tree/magento2-pre-version-2.3)

#### Simple Installation
1. Download and extract the zip.
2. Locate the root Magento directory of your shop via FTP connection.
3. Copy the 'app' & 'lib' folders into magento root folder.
4. Login to your Magento Admin Panel.
5. Go to `System` - `Web Setup Wizard` - `Module Manager`.
6. Scroll or go to the next page untill you find **Midtrans_Snap**.
7. Click `Select` - `Enable` to enable the module.
8. Proceed to step **5** below.

#### Manual Instalation

1. Download and extract the zip.
2. Locate the root Magento directory of your shop via FTP connection
3. Copy the 'app' & 'lib' folders into magento root folder
4. Run below command:
```
php bin/magento module:enable --clear-static-content Midtrans_Snap
php bin/magento setup:upgrade
php bin/magento cache:clean
```
5. Login to your Magento Admin Panel.
6. In your Magento admin area, enable the Midtrans plug-in and insert your merchant details (Server key and client key) in the Menu "Stores" > "Configuration" > "Sales" > "Payment Method" > Tab "Midtrans Snap".
7. Login into your Midtrans account and change the Payment Notification URL in Settings to `http://[your shop's homepage]/snap/payment/notification`.

#### Get help

* [General Documentation Midtrans](http://docs.midtrans.com)
* Technical Support Team Midtrans [support@midtrans.com](mailto:support@midtrans.com)
* [SNAP Documentation Product Midtrans](https://snap-docs.midtrans.com/)
* [CoreAPI Documentation Product Midtrans](https://api-docs.midtrans.com/)
* [Mobile Documentation Product Midtrans](http://mobile-docs.midtrans.com/)