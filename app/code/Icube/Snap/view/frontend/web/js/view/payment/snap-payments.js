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
                type: 'snap',
                component: 'Icube_Snap/js/view/payment/method-renderer/snap-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
