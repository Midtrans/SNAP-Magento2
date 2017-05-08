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
                type: 'snapinst',
                component: 'Icube_Snapinst/js/view/payment/method-renderer/snapinst-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
