define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Icube_Snapio/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Ui/js/model/messageList'
    ],
    function (Component, $, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, setPaymentMethodAction, additionalValidators, url, messageList) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Icube_Snapio/payment/snapio'
            },
            redirectAfterPlaceOrder: false,
            /** Open VT-Snap */
            afterPlaceOrder: function () {
                var production = window.checkoutConfig.payment.snapio.production;
                console.log('production = '+production);
                var client_key = window.checkoutConfig.payment.snapio.clientkey;
                console.log('client_key = '+client_key);
                console.log(client_key);
                if(production == "1"){
                    var js = "https://app.midtrans.com/snap/snap.js";
                }else{
                    var js = "https://app.sandbox.midtrans.com/snap/snap.js";
                }

                var scriptTag = document.createElement('script');
                scriptTag.src = js;
                scriptTag.setAttribute('data-client-key', client_key);
                document.body.appendChild(scriptTag);

                //$.getScript(js, function(){
                    $.ajax({
                        type: 'post',
                        url: url.build('snapio/payment/redirect'),
                        cache: false,
                        success: function(data) {
                            //var token = data;
                            console.log('data = '+ data);
                            var token = data.substring(0,36)
                            console.log("token = " + token);
                            snap.pay(token, {
                                onSuccess: function(result){
                                    messageList.addSuccessMessage({
                                        message: result.status_message
                                    });
                                    window.location.replace(url.build('checkout/onepage/success'));
                                    console.log(result.status_message);
                                },
                                onPending: function(result){
                                    messageList.addSuccessMessage({
                                        message: result.status_message
                                    });
                                    window.location.replace(url.build('checkout/onepage/success'));
                                    console.log(result.status_message);
                                },
                                onError: function(result){
                                    messageList.addErrorMessage({
                                        message: result.status_message
                                    });
                                    window.location.replace(url.build('checkout/onepage/failure'));
                                    console.log(result.status_message);
                                },
                                onClose: function(){
                                    messageList.addErrorMessage({
                                        message: 'customer closed the popup without finishing the payment'
                                    });
                                    window.location.replace(url.build('checkout/onepage/failure'));
                                    console.log('customer closed the popup without finishing the payment');
                                }

                            });
                        }
                    });
                //});
            }
        });
    }
);
