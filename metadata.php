<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\Eshop\Application\Component\UserComponent as CoreUserComponent;
use OxidEsales\Eshop\Application\Controller\Admin\DeliverySetMain as DeliverySetMainController;
use OxidEsales\Eshop\Application\Controller\Admin\OrderList as OrderListController;
use OxidEsales\Eshop\Application\Controller\Admin\OrderMain as OrderMainController;
use OxidEsales\Eshop\Application\Controller\Admin\OrderOverview as CoreOrderOverviewmodel;
use OxidEsales\Eshop\Application\Controller\Admin\OrderArticle as CoreOrderArticleModel;
use OxidEsales\Eshop\Application\Controller\ArticleDetailsController as CoreArticleDetailsController;
use OxidEsales\Eshop\Application\Controller\OrderController as CoreOrderController;
use OxidEsales\Eshop\Application\Controller\UserController as CoreUserController;
use OxidEsales\Eshop\Application\Model\Article as CoreArticleModel;
use OxidEsales\Eshop\Application\Model\Basket as CoreBasketModel;
use OxidEsales\Eshop\Application\Model\Category as CoreCategoryModel;
use OxidEsales\Eshop\Application\Model\Order as CoreOrderModel;
use OxidEsales\Eshop\Application\Model\User as CoreUserModel;
use OxidEsales\Eshop\Core\ViewConfig as CoreViewConfig;
use OxidEsales\Eshop\Core\InputValidator as CoreInputValidator;
use OxidSolutionCatalysts\AmazonPay\Component\UserComponent;
use OxidSolutionCatalysts\AmazonPay\Controller\Admin\ConfigController;
use OxidSolutionCatalysts\AmazonPay\Controller\Admin\DeliverySetMain as AmazonDeliverySetMain;
use OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderList as AmazonOrderList;
use OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderMain as AmazonOrderMain;
use OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderOverview as ModuleOrderOverview;
use OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderArticle as ModuleOrderArticle;
use OxidSolutionCatalysts\AmazonPay\Controller\AmazonCheckoutController;
use OxidSolutionCatalysts\AmazonPay\Controller\ArticleDetailsController;
use OxidSolutionCatalysts\AmazonPay\Controller\DispatchController;
use OxidSolutionCatalysts\AmazonPay\Controller\OrderController;
use OxidSolutionCatalysts\AmazonPay\Controller\UserController;
use OxidSolutionCatalysts\AmazonPay\Core\ViewConfig;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonInputValidator;
use OxidSolutionCatalysts\AmazonPay\Model\Article as ModuleArticle;
use OxidSolutionCatalysts\AmazonPay\Model\Basket as ModuleBasket;
use OxidSolutionCatalysts\AmazonPay\Model\Category as ModuleCategory;
use OxidSolutionCatalysts\AmazonPay\Model\Order as ModuleOrder;
use OxidSolutionCatalysts\AmazonPay\Model\User as ModuleUser;
use OxidSolutionCatalysts\AmazonPay\Controller\PaymentController;
use OxidEsales\Eshop\Application\Controller\PaymentController as CorePaymentController;
use OxidSolutionCatalysts\AmazonPay\Controller\AmazonCheckoutAjaxController;

$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id' => 'osc_amazonpay',
    'title' => [
        'de' => 'Amazon Pay - Online-Bezahldienst',
        'en' => 'Amazon Pay - Online-Payment'
    ],
    'description' => [
        'de' => 'Nutzung des Online-Bezahldienstes von amazon.de',
        'en' => 'Use of the online payment service from amazon.com'
    ],
    'thumbnail' => 'img/amazon-pay-logo.png',
    'version' => '3.1.3-rc.1',
    'author' => 'OXID eSales AG',
    'url' => 'https://www.oxid-esales.com',
    'email' => 'info@oxid-esales.com',
    'extend' => [
        CoreViewConfig::class => ViewConfig::class,
        CoreUserController::class => UserController::class,
        CoreOrderController::class => OrderController::class,
        CorePaymentController::class => PaymentController::class,
        CoreArticleDetailsController::class => ArticleDetailsController::class,
        CoreOrderOverviewmodel::class => ModuleOrderOverview::class,
        CoreOrderArticleModel::class => ModuleOrderArticle::class,
        CoreUserComponent::class => UserComponent::class,
        CoreOrderModel::class => ModuleOrder::class,
        CoreUserModel::class => ModuleUser::class,
        CoreArticleModel::class => ModuleArticle::class,
        CoreBasketModel::class => ModuleBasket::class,
        CoreCategoryModel::class => ModuleCategory::class,
        DeliverySetMainController::class => AmazonDeliverySetMain::class,
        OrderMainController::class => AmazonOrderMain::class,
        OrderListController::class => AmazonOrderList::class,
        CoreInputValidator::class => AmazonInputValidator::class,
    ],
    'controllers' => [
        'amazonconfig' => ConfigController::class,
        'amazoncheckout' => AmazonCheckoutController::class,
        'amazondispatch' => DispatchController::class,
        'amazoncheckoutajax' => AmazonCheckoutAjaxController::class,
    ],
    'templates' => [
        'amazonpay/amazonconfig.tpl' => 'osc/amazonpay/views/admin/tpl/amazonconfig.tpl',
        'amazonpay/amazonexpressbutton.tpl' => 'osc/amazonpay/views/elements/amazonexpressbutton.tpl',
        'amazonpay/amazonbutton.tpl' => 'osc/amazonpay/views/elements/amazonbutton.tpl',
        'amazonpay/amazonloginbutton.tpl' => 'osc/amazonpay/views/elements/amazonloginbutton.tpl',
        'amazonpay/filtered_billing_address.tpl' => 'osc/amazonpay/views/elements/filtered_billing_address.tpl',
        'amazonpay/filtered_delivery_address.tpl' => 'osc/amazonpay/views/elements/filtered_delivery_address.tpl',
        'amazonpay/user_checkout_shipping_head_flow.tpl' =>
            'osc/amazonpay/views/elements/user_checkout_shipping_head_flow.tpl',
        'amazonpay/user_checkout_shipping_head_wave.tpl' =>
            'osc/amazonpay/views/elements/user_checkout_shipping_head_wave.tpl',
        'amazonpay/basket_btn_next_bottom_flow.tpl' => 'osc/amazonpay/views/elements/basket_btn_next_bottom_flow.tpl',
        'amazonpay/basket_btn_next_bottom_wave.tpl' => 'osc/amazonpay/views/elements/basket_btn_next_bottom_wave.tpl',
        'amazonpay/change_payment_block_flow.tpl' => 'osc/amazonpay/views/elements/change_payment_block_flow.tpl',
        'amazonpay/change_payment_block_wave.tpl' => 'osc/amazonpay/views/elements/change_payment_block_wave.tpl',
        'amazonpay/change_payment_form_flow.tpl' => 'osc/amazonpay/views/elements/change_payment_form_flow.tpl',
        'amazonpay/change_payment_form_wave.tpl' => 'osc/amazonpay/views/elements/change_payment_form_wave.tpl',
        'amazonpay/checkout_order_address_flow.tpl' => 'osc/amazonpay/views/elements/checkout_order_address_flow.tpl',
        'amazonpay/checkout_order_address_wave.tpl' => 'osc/amazonpay/views/elements/checkout_order_address_wave.tpl',
        'amazonpay/checkout_order_btn_submit_bottom_flow.tpl' =>
            'osc/amazonpay/views/elements/checkout_order_btn_submit_bottom_flow.tpl',
        'amazonpay/checkout_order_btn_submit_bottom_wave.tpl' =>
            'osc/amazonpay/views/elements/checkout_order_btn_submit_bottom_wave.tpl',
        'amazonpay/checkout_user_main_flow.tpl' => 'osc/amazonpay/views/elements/checkout_user_main_flow.tpl',
        'amazonpay/checkout_user_main_wave.tpl' => 'osc/amazonpay/views/elements/checkout_user_main_wave.tpl',
        'amazonpay/shippingandpayment_flow.tpl' => 'osc/amazonpay/views/elements/shippingandpayment_flow.tpl',
        'amazonpay/shippingandpayment_wave.tpl' => 'osc/amazonpay/views/elements/shippingandpayment_wave.tpl',
        'amazonpay/shippingandpayment_error_flow.tpl' => 'osc/amazonpay/views/elements/shippingandpayment_error_flow.tpl',
        'amazonpay/shippingandpayment_error_wave.tpl' => 'osc/amazonpay/views/elements/shippingandpayment_error_wave.tpl',
        'amazonpay/details_productmain_tobasket.tpl' =>
            'osc/amazonpay/views/elements/details_productmain_tobasket.tpl',
        'amazonpay/dd_layout_page_header_icon_menu_minibasket_functions_flow.tpl' =>
            'osc/amazonpay/views/elements/dd_layout_page_header_icon_menu_minibasket_functions_flow.tpl',
        'amazonpay/dd_layout_page_header_icon_menu_minibasket_functions_wave.tpl' =>
            'osc/amazonpay/views/elements/dd_layout_page_header_icon_menu_minibasket_functions_wave.tpl',
        'amazonpay/json.tpl' => 'osc/amazonpay/views/json.tpl',
        'amazonpay/base_js.tpl' => 'osc/amazonpay/views/elements/base_js.tpl',
        'amazonpay/base_style.tpl' => 'osc/amazonpay/views/elements/base_style.tpl'
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\AmazonPay\Core\Events::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\AmazonPay\Core\Events::onDeactivate'
    ],
    'blocks' => [
        [
            'template' => 'headitem.tpl',
            'block' => 'admin_headitem_inccss',
            'file' => 'views/blocks/admin/admin_headitem_inccss.tpl'
        ],
        [
            'template' => 'deliveryset_main.tpl',
            'block'    => 'admin_deliveryset_main_form',
            'file'     => 'views/blocks/admin/deliveryset_main.tpl',
            'position' => '5'
        ],
        [
            'template' => 'order_overview.tpl',
            'block' => 'admin_order_overview_checkout',
            'file' => 'views/blocks/admin/admin_order_overview_reset_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'order_overview.tpl',
            'block' => 'admin_order_overview_send_form',
            'file' => 'views/blocks/admin/admin_order_overview_send_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'order_overview.tpl',
            'block' => 'admin_order_overview_checkout',
            'file' => 'views/blocks/admin/admin_order_overview_checkout.tpl',
            'position' => '5'
        ],
        [
            'template' => 'article_main.tpl',
            'block' => 'admin_article_main_extended',
            'file' => 'views/blocks/admin/admin_article_main_extended.tpl',
            'position' => '5'
        ],
        [
            'template' => 'include/category_main_form.tpl',
            'block' => 'admin_category_main_form',
            'file' => 'views/blocks/admin/category_main_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'layout/base.tpl',
            'block' => 'base_js',
            'file' => 'views/blocks/layout/base_js.tpl'
        ],
        [
            'template' => 'layout/base.tpl',
            'block' => 'base_style',
            'file' => 'views/blocks/layout/base_style.tpl'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_shipping_form',
            'file' => '/views/blocks/form/checkout_shipping_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_shipping_change',
            'file' => '/views/blocks/form/checkout_shipping_change.tpl',
            'position' => '5'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_shipping_head',
            'file' => '/views/blocks/form/user_checkout_shipping_head.tpl',
            'position' => '5'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_billing_feedback',
            'file' => '/views/blocks/form/checkout_billing_feedback.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/details/inc/productmain.tpl',
            'block' => 'details_productmain_tobasket',
            'file' => '/views/blocks/page/details/inc/details_productmain_tobasket.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/basket.tpl',
            'block' => 'basket_btn_next_bottom',
            'file' => '/views/blocks/page/checkout/basket_btn_next_bottom.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_address',
            'file' => '/views/blocks/page/checkout/checkout_order_address.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_submit_bottom',
            'file' => '/views/blocks/page/checkout/checkout_order_btn_submit_bottom.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_confirm_bottom',
            'file' => '/views/blocks/page/checkout/checkout_order_btn_confirm_bottom.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => '/views/blocks/page/checkout/shippingandpayment.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/user.tpl',
            'block' => 'checkout_user_main',
            'file' => '/views/blocks/page/checkout/checkout_user_main.tpl',
            'position' => '5'
        ],
        [
            'template' => 'widget/minibasket/minibasket.tpl',
            'block' => 'dd_layout_page_header_icon_menu_minibasket_functions',
            'file' =>
                '/views/blocks/widget/minibasket/dd_layout_page_header_icon_menu_minibasket_functions.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'select_payment',
            'file' => '/views/blocks/page/checkout/select_payment.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'change_payment',
            'file' => '/views/blocks/page/checkout/change_payment.tpl',
            'position' => '5'
        ],
        [
            'template' => 'widget/header/loginbox.tpl',
            'block' => 'thirdparty_login',
            'file' => '/views/blocks/page/account/thirdparty_login.tpl',
            'position' => '1'
        ],
    ],
    'settings' => [
        ['name' => 'blAmazonPaySandboxMode', 'type' => 'bool', 'value' => false, 'group' => null],
        ['name' => 'sAmazonPayPrivKey', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'sAmazonPayPubKeyId', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'sAmazonPayMerchantId', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'sAmazonPayStoreId', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'blAmazonPayExpressPDP', 'type' => 'bool', 'value' => true, 'group' => null],
        ['name' => 'blAmazonPayExpressMinicartAndModal', 'type' => 'bool', 'value' => true, 'group' => null],
        ['name' => 'blAmazonPayUseExclusion', 'type' => 'bool', 'value' => false, 'group' => null],
        ['name' => 'blAmazonSocialLoginDeactivated', 'type' => 'bool', 'value' => false, 'group' => null],
        ['name' => 'blAmazonAutomatedRefundActivated', 'type' => 'bool', 'value' => true, 'group' => null],
        ['name' => 'blAmazonAutomatedCancelActivated', 'type' => 'bool', 'value' => true, 'group' => null],
        ['name' => 'amazonPayCapType', 'type' => 'str', 'value' => '', 'group' => null],
    ]
];
