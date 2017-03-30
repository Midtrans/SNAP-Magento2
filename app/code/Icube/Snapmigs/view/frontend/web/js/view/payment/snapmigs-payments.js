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
                type: 'snapmigs',
                component: 'Icube_Snapmigs/js/view/payment/method-renderer/snapmigs-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
