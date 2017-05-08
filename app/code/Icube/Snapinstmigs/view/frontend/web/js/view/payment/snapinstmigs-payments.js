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
                type: 'snapinstmigs',
                component: 'Icube_Snapinstmigs/js/view/payment/method-renderer/snapinstmigs-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
