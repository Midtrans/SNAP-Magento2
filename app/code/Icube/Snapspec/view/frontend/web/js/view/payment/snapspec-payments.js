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
                type: 'snapspec',
                component: 'Icube_Snapspec/js/view/payment/method-renderer/snapspec-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
