define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
        ) {
        'use strict';
        rendererList.push(
            {
                type: 'snapio',
                component: 'Icube_Snapio/js/view/payment/method-renderer/snapio-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
