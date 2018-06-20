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
        'Icube_Snapinstmigs/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Ui/js/model/messageList'
    ],
    function (Component, $, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, setPaymentMethodAction, additionalValidators, url, messageList) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Icube_Snapinstmigs/payment/snapinstmigs'
            },
            redirectAfterPlaceOrder: false,
            /** Open VT-Snap */
            afterPlaceOrder: function () {
                var production = window.checkoutConfig.payment.snapinstmigs.production;
                var client_key = window.checkoutConfig.payment.snapinstmigs.clientkey;
                var merchant_id = window.checkoutConfig.payment.snapinstmigs.merchantid;
                var mixpanel_key = window.checkoutConfig.payment.snapinstmigs.mixpanelkey;

                console.log('production = '+production);
                console.log('client_key = '+client_key);
                console.log('merchant_id = '+merchant_id);
                console.log('mixpanel_key = '+mixpanel_key);
                
                
                if(production == "1"){
                    var js = "https://app.midtrans.com/snap/snap.js";
                }else{
                    var js = "https://app.sandbox.midtrans.com/snap/snap.js";
                }

                (function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,
                0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
                for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);
                mixpanel.init(mixpanel_key);

                var scriptTag = document.createElement('script');
                scriptTag.src = js;
                scriptTag.setAttribute('data-client-key', client_key);
                document.body.appendChild(scriptTag);

                
                $.ajax({
                    type: 'post',
                    url: url.build('snapinstmigs/payment/redirect'),
                    cache: false,
                    success: function(data) {

                        function trackResult(token, merchant_id, plugin_name, status, result) {
                            var eventNames = {
                              success: 'pg-success',
                              pending: 'pg-pending',
                              error: 'pg-error',
                              close: 'pg-close'
                            };
                              mixpanel.track(
                                eventNames[status], {
                                  merchant_id: merchant_id,
                                  cms_name: 'Magento',
                                  cms_version : '2',
                                  plugin_name: plugin_name,
                                  snap_token: data,
                                  payment_type: result ? result.payment_type: null,
                                  order_id: result ? result.order_id: null,
                                  status_code: result ? result.status_code: null,
                                  gross_amount: result && result.gross_amount ? Number(result.gross_amount) : null,
                                }
                              );
                        }

                            //var token = data;
                        console.log('data = '+ data);
                        var token = data.substring(0,36)
                        console.log("token = " + token);

                        mixpanel.track(
                            'pg-pay', {
                              merchant_id: merchant_id,
                              plugin_name: "installment_migs",
                              snap_token: data
                            }
                        );

                        var retryCount = 0;
                        var snapExecuted = false;
                        var intervalFunction = 0;
                        function execSnapCont(){
                            intervalFunction = setInterval(function() {
                                try{
                                    snap.pay(token, 
                                    {
                                        skipOrderSummary : true,
                                        onSuccess: function(result){
                                            trackResult(data, merchant_id, 'installment_migs', 'success', result);
                                            messageList.addSuccessMessage({
                                                message: result.status_message
                                            });
                                            window.location.replace(url.build('checkout/onepage/success'));
                                            console.log(result.status_message);
                                        },
                                        onPending: function(result){
                                            trackResult(data, merchant_id, 'installment_migs', 'pending', result);
                                            messageList.addSuccessMessage({
                                                message: result.status_message
                                            });
                                            window.location.replace(url.build('checkout/onepage/success'));
                                            console.log(result.status_message);
                                        },
                                        onError: function(result){
                                            trackResult(data, merchant_id , 'installment_migs', 'error', result);
                                            messageList.addErrorMessage({
                                                message: result.status_message
                                            });
                                            window.location.replace(url.build('checkout/onepage/failure'));
                                            console.log(result.status_message);    
                                        },
                                        onClose: function(){
                                            trackResult(data, merchant_id, 'installment_migs', 'close');
                                            $.ajax({
                                                url: url.build('snap/payment/cancel'),
                                                cache: false,
                                                success: function(){
                                                    messageList.addErrorMessage({
                                                        message: 'customer closed the popup without finishing the payment'
                                                    });
                                                    console.log('customer closed the popup without finishing the payment');
                                                    window.location.replace(url.build('checkout/onepage/failure'));
                                                
                                                }

                                            });
                                        }
                                    });
                                var snapExecuted = true;
                                } catch (e){
                                    retryCount++;
                                    if(retryCount >= 10){
                                        messageList.addErrorMessage({
                                        message: 'Trying to load snap, this might take longer'
                                        });
                                    }
                                    
                                    console.log(e);
                                    console.log("Snap not ready yet... Retrying in 1000ms!");
                                } finally {
                                    if (snapExecuted) {
                                        clearInterval(intervalFunction);
                                        // record 'pay' event to Mixpanel
                                        trackResult(data, merchant_id, 'installment_migs', 'pay', null);
                                    } 

                                }
                            }, 1000);         
                        }; //end of execsnapcont
                        execSnapCont();
                    }//end of ajax success
                });
            }
        });
    }
);
