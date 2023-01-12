<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

return [
    // This product is available in ce|pe|ee demodata
    'product' => [
        'id' => 'dc5ffdf380e15674b56dd562a7cb6aec',
        'title' => 'Kuyichi leather belt JEVER',
        'bruttoprice_single' => '29.90',
        'nettoprice_single' => '25.13',
        'shipping_cost' => '3.90',
        'currency' => '€'
    ],
    'amazonExclude' => [
        'excludedProduct' => [
            'artId' => '1402',
            'id' => '05848170643ab0deb9914566391c0c63'
        ],
        'notExcludedProduct' => [
            'artId' => '3102',
            'id' => 'adc5ee42bd3c37a27a488769d22ad9ed'
        ],
        'categoryExcluded' => [
            'sortNum' => '101',
            'id' => '0f4fb00809cec9aa0910aa9c8fe36751',
        ],
        'productInExcludedCategory' => [
            'artId' => '1209',
            'id' => 'b5666b6d4bcb67c61dee4887bfba8351'
        ],
        'productInNotExcludedCategory' => [
            'artId' => '2102',
            'id' => 'd86e244c8114c8214fbf83da8d6336b3'
        ]
    ],
];
