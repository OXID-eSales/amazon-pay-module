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
    'version' => '3.1.6-rc.6',
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
        '@osc_amazonpay/admin/amazonconfig.tpl' => 'views/smarty/admin/amazonconfig.tpl',
        '@osc_amazonpay/frontend/amazonexpressbutton.tpl' => 'views/smarty/frontend/amazonexpressbutton.tpl',
        '@osc_amazonpay/frontend/amazonbutton.tpl' => 'views/smarty/frontend/amazonbutton.tpl',
        '@osc_amazonpay/frontend/amazonloginbutton.tpl' => 'views/smarty/frontend/amazonloginbutton.tpl',
        '@osc_amazonpay/frontend/filtered_billing_address.tpl' => 'views/smarty/frontend/filtered_billing_address.tpl',
        '@osc_amazonpay/frontend/filtered_delivery_address.tpl' => 'views/smarty/frontend/filtered_delivery_address.tpl',
        '@osc_amazonpay/frontend/user_checkout_shipping_head_flow.tpl' =>
            'views/smarty/frontend/user_checkout_shipping_head_flow.tpl',
        '@osc_amazonpay/frontend/user_checkout_shipping_head_wave.tpl' =>
            'views/smarty/frontend/user_checkout_shipping_head_wave.tpl',
        '@osc_amazonpay/frontend/basket_btn_next_bottom_flow.tpl' => 'views/smarty/frontend/basket_btn_next_bottom_flow.tpl',
        '@osc_amazonpay/frontend/basket_btn_next_bottom_wave.tpl' => 'views/smarty/frontend/basket_btn_next_bottom_wave.tpl',
        '@osc_amazonpay/frontend/change_payment_block_flow.tpl' => 'views/smarty/frontend/change_payment_block_flow.tpl',
        '@osc_amazonpay/frontend/change_payment_block_wave.tpl' => 'views/smarty/frontend/change_payment_block_wave.tpl',
        '@osc_amazonpay/frontend/change_payment_form_flow.tpl' => 'views/smarty/frontend/change_payment_form_flow.tpl',
        '@osc_amazonpay/frontend/change_payment_form_wave.tpl' => 'views/smarty/frontend/change_payment_form_wave.tpl',
        '@osc_amazonpay/frontend/checkout_order_address_flow.tpl' => 'views/smarty/frontend/checkout_order_address_flow.tpl',
        '@osc_amazonpay/frontend/checkout_order_address_wave.tpl' => 'views/smarty/frontend/checkout_order_address_wave.tpl',
        '@osc_amazonpay/frontend/checkout_order_btn_submit_bottom_flow.tpl' =>
            'views/smarty/frontend/checkout_order_btn_submit_bottom_flow.tpl',
        '@osc_amazonpay/frontend/checkout_order_btn_submit_bottom_wave.tpl' =>
            'views/smarty/frontend/checkout_order_btn_submit_bottom_wave.tpl',
        '@osc_amazonpay/frontend/checkout_user_main_flow.tpl' => 'views/smarty/frontend/checkout_user_main_flow.tpl',
        '@osc_amazonpay/frontend/checkout_user_main_wave.tpl' => 'views/smarty/frontend/checkout_user_main_wave.tpl',
        '@osc_amazonpay/frontend/shippingandpayment_flow.tpl' => 'views/smarty/frontend/shippingandpayment_flow.tpl',
        '@osc_amazonpay/frontend/shippingandpayment_wave.tpl' => 'views/smarty/frontend/shippingandpayment_wave.tpl',
        '@osc_amazonpay/frontend/shippingandpayment_error_flow.tpl' => 'views/smarty/frontend/shippingandpayment_error_flow.tpl',
        '@osc_amazonpay/frontend/shippingandpayment_error_wave.tpl' => 'views/smarty/frontend/shippingandpayment_error_wave.tpl',
        '@osc_amazonpay/frontend/details_productmain_tobasket.tpl' =>
            'views/smarty/frontend/details_productmain_tobasket.tpl',
        '@osc_amazonpay/frontend/dd_layout_page_header_icon_menu_minibasket_functions_flow.tpl' =>
            'views/smarty/frontend/dd_layout_page_header_icon_menu_minibasket_functions_flow.tpl',
        '@osc_amazonpay/frontend/dd_layout_page_header_icon_menu_minibasket_functions_wave.tpl' =>
            'views/smarty/frontend/dd_layout_page_header_icon_menu_minibasket_functions_wave.tpl',
        '@osc_amazonpay/frontend/json.tpl' => 'osc/amazonpay/views/json.tpl',
        '@osc_amazonpay/frontend/base_js.tpl' => 'views/smarty/frontend/base_js.tpl',
        '@osc_amazonpay/frontend/base_style.tpl' => 'views/smarty/frontend/base_style.tpl'
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\AmazonPay\Core\Events::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\AmazonPay\Core\Events::onDeactivate'
    ],
    'blocks' => [
        [
            'template' => 'headitem.tpl',
            'block' => 'admin_headitem_inccss',
            'file' => 'views/smarty/extensions/themes/admin/admin_headitem_inccss.tpl'
        ],
        [
            'template' => 'deliveryset_main.tpl',
            'block'    => 'admin_deliveryset_main_form',
            'file'     => 'views/smarty/extensions/themes/admin/deliveryset_main.tpl',
            'position' => '5'
        ],
        [
            'template' => 'order_overview.tpl',
            'block' => 'admin_order_overview_checkout',
            'file' => 'views/smarty/extensions/themes/admin/admin_order_overview_reset_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'order_overview.tpl',
            'block' => 'admin_order_overview_send_form',
            'file' => 'views/smarty/extensions/themes/admin/admin_order_overview_send_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'order_overview.tpl',
            'block' => 'admin_order_overview_checkout',
            'file' => 'views/smarty/extensions/themes/admin/admin_order_overview_checkout.tpl',
            'position' => '5'
        ],
        [
            'template' => 'article_main.tpl',
            'block' => 'admin_article_main_extended',
            'file' => 'views/smarty/extensions/themes/admin/admin_article_main_extended.tpl',
            'position' => '5'
        ],
        [
            'template' => 'include/category_main_form.tpl',
            'block' => 'admin_category_main_form',
            'file' => 'views/smarty/extensions/themes/admin/category_main_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'layout/base.tpl',
            'block' => 'base_js',
            'file' => 'views/smarty/frontend/blocks/layout/base_js.tpl'
        ],
        [
            'template' => 'layout/base.tpl',
            'block' => 'base_style',
            'file' => 'views/smarty/frontend/blocks/layout/base_style.tpl'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_shipping_form',
            'file' => 'views/smarty/extensions/themes/default/form/checkout_shipping_form.tpl',
            'position' => '5'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_shipping_change',
            'file' => 'views/smarty/extensions/themes/default/form/checkout_shipping_change.tpl',
            'position' => '5'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_shipping_head',
            'file' => 'views/smarty/extensions/themes/default/form/user_checkout_shipping_head.tpl',
            'position' => '5'
        ],
        [
            'template' => 'form/user_checkout_change.tpl',
            'block' => 'user_checkout_billing_feedback',
            'file' => 'views/smarty/extensions/themes/default/form/checkout_billing_feedback.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/details/inc/productmain.tpl',
            'block' => 'details_productmain_tobasket',
            'file' => 'views/smarty/extensions/themes/default/page/details/inc/details_productmain_tobasket.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/basket.tpl',
            'block' => 'basket_btn_next_bottom',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/basket_btn_next_bottom.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_address',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/checkout_order_address.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_submit_bottom',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/checkout_order_btn_submit_bottom.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_confirm_bottom',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/checkout_order_btn_confirm_bottom.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/shippingandpayment.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/user.tpl',
            'block' => 'checkout_user_main',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/checkout_user_main.tpl',
            'position' => '5'
        ],
        [
            'template' => 'widget/minibasket/minibasket.tpl',
            'block' => 'dd_layout_page_header_icon_menu_minibasket_functions',
            'file' =>
                'views/smarty/extensions/themes/default/widget/minibasket/dd_layout_page_header_icon_menu_minibasket_functions.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'select_payment',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/select_payment.tpl',
            'position' => '5'
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'change_payment',
            'file' => 'views/smarty/extensions/themes/default/page/checkout/change_payment.tpl',
            'position' => '5'
        ],
        [
            'template' => 'widget/header/loginbox.tpl',
            'block' => 'thirdparty_login',
            'file' => 'views/smarty/extensions/themes/default/page/account/thirdparty_login.tpl',
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
