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
        'Icube_Snap/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Ui/js/model/messageList'
    ],
    function (Component, $, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, setPaymentMethodAction, additionalValidators, url, messageList) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Icube_Snap/payment/snap'
            },
            redirectAfterPlaceOrder: false,
            /** Open VT-Snap */
            afterPlaceOrder: function () {

                var production = window.checkoutConfig.payment.snap.production;
                console.log('production = '+production);
                var client_key = window.checkoutConfig.payment.snap.clientkey;
                var merchant_id = window.checkoutConfig.payment.snap.merchantid;
                var mixpanel_key = window.checkoutConfig.payment.snap.mixpanelkey;
                var magento_version = window.checkoutConfig.payment.snap.magentoversion;
                var plugin_version = window.checkoutConfig.payment.snap.pluginversion;
                
                if(production == "1"){
                    var js = "https://app.midtrans.com/snap/snap.js";
                }else{
                    var js = "https://app.sandbox.midtrans.com/snap/snap.js";
                }

                // <!-- start Mixpanel -->
                (function(c,a){if(!a.__SV){var b=window;try{var d,m,j,k=b.location,f=k.hash;d=function(a,b){return(m=a.match(RegExp(b+"=([^&]*)")))?m[1]:null};f&&d(f,"state")&&(j=JSON.parse(decodeURIComponent(d(f,"state"))),"mpeditor"===j.action&&(b.sessionStorage.setItem("_mpcehash",f),history.replaceState(j.desiredHash||"",c.title,k.pathname+k.search)))}catch(n){}var l,h;window.mixpanel=a;a._i=[];a.init=function(b,d,g){function c(b,i){var a=i.split(".");2==a.length&&(b=b[a[0]],i=a[1]);b[i]=function(){b.push([i].concat(Array.prototype.slice.call(arguments,
                0)))}}var e=a;"undefined"!==typeof g?e=a[g]=[]:g="mixpanel";e.people=e.people||[];e.toString=function(b){var a="mixpanel";"mixpanel"!==g&&(a+="."+g);b||(a+=" (stub)");return a};e.people.toString=function(){return e.toString(1)+".people (stub)"};l="disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(" ");
                for(h=0;h<l.length;h++)c(e,l[h]);var f="set set_once union unset remove delete".split(" ");e.get_group=function(){function a(c){b[c]=function(){call2_args=arguments;call2=[c].concat(Array.prototype.slice.call(call2_args,0));e.push([d,call2])}}for(var b={},d=["get_group"].concat(Array.prototype.slice.call(arguments,0)),c=0;c<f.length;c++)a(f[c]);return b};a._i.push([b,d,g])};a.__SV=1.2;b=c.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?
                MIXPANEL_CUSTOM_LIB_URL:"file:"===c.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";d=c.getElementsByTagName("script")[0];d.parentNode.insertBefore(b,d)}})(document,window.mixpanel||[]);
                mixpanel.init(mixpanel_key);
                // <!-- end Mixpanel -->

                var scriptTag = document.createElement('script');
                scriptTag.src = js;
                scriptTag.setAttribute('data-client-key', client_key);
                document.body.appendChild(scriptTag);

                // $.getScript(js, function(){
                $.ajax({
                    type: 'post',
                    url: url.build('snap/payment/redirect'),
                    cache: false,
                    success: function(data) {
                            //var token = data;

                        function trackResult(token, merchant_id, plugin_name, status, result) {
                            var eventNames = {
                                pay: 'pg-pay',
                                success: 'pg-success',
                                pending: 'pg-pending',
                                error: 'pg-error',
                                close: 'pg-close'
                              };
                              mixpanel.track(
                                eventNames[status], {
                                  merchant_id: merchant_id,
                                  cms_name: 'Magento',
                                  cms_version : magento_version,
                                  plugin_name: plugin_name,
                                  plugin_version : plugin_version,
                                  snap_token: data,
                                  payment_type: result ? result.payment_type: null,
                                  order_id: result ? result.order_id: null,
                                  status_code: result ? result.status_code: null,
                                  gross_amount: result && result.gross_amount ? Number(result.gross_amount) : null,
                                }
                            );
                        }

                        console.log('data = '+ data);
                        var token = data.substring(0,36)
                        console.log("token = " + token);

                        trackResult(data, merchant_id, 'fullpayment', 'pay', null);
                            
                        var retryCount = 0;
                        var snapExecuted = false;
                        var intervalFunction = 0;
                        function execSnapCont(){
                            intervalFunction = setInterval(function() {
                                try{
                                    snap.pay(token, 
                                    {
                                        skipOrderSummary : true,
                                        showOrderId : true,
                                        onSuccess: function(result){
                                            trackResult(data, merchant_id, 'fullpayment', 'success', result);
                                            messageList.addSuccessMessage({
                                                message: result.status_message
                                            });
                                            window.location.replace(url.build('checkout/onepage/success'));
                                            console.log(result.status_message);
                                        },
                                        onPending: function(result){
                                            trackResult(data, merchant_id, 'fullpayment', 'pending', result);
                                            messageList.addSuccessMessage({
                                                message: result.status_message
                                            });
                                            window.location.replace(url.build('checkout/onepage/success'));
                                            console.log(result.status_message);
                                        },
                                        onError: function(result){
                                            trackResult(data, merchant_id , 'fullpayment', 'error', result);
                                            messageList.addErrorMessage({
                                                message: result.status_message
                                            });
                                            window.location.replace(url.build('checkout/onepage/failure'));
                                            console.log(result.status_message);    
                                        },
                                        onClose: function(){
                                            console.log("get to onclose")
                                            trackResult(data, merchant_id, 'fullpayment', 'close');
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
                                        trackResult(data, merchant_id, 'fullpayment', 'pay', null);
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
