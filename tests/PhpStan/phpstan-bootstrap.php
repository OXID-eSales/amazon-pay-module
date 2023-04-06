<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

class_alias(
    \OxidEsales\Eshop\Application\Model\User::class,
    \OxidSolutionCatalysts\AmazonPay\Model\User_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Component\UserComponent::class,
    \OxidSolutionCatalysts\AmazonPay\Component\UserComponent_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\DeliverySetMain::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\Admin\DeliverySetMain_parent::class
);

class_alias(
    \OxidEsales\EshopCommunity\Application\Controller\Admin\OrderArticle::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderArticle_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderList_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\OrderMain::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderMain_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\Admin\OrderOverview_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\ArticleDetailsController::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\ArticleDetailsController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\OrderController::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\OrderController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\UserController::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\UserController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Core\ViewConfig::class,
    \OxidSolutionCatalysts\AmazonPay\Core\ViewConfig_parent::class
);

class_alias(
    \OxidEsales\Eshop\Core\InputValidator::class,
    \OxidSolutionCatalysts\AmazonPay\Core\AmazonInputValidator_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Article::class,
    \OxidSolutionCatalysts\AmazonPay\Model\Article_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Basket::class,
    \OxidSolutionCatalysts\AmazonPay\Model\Basket_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Category::class,
    \OxidSolutionCatalysts\AmazonPay\Model\Category_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Order::class,
    \OxidSolutionCatalysts\AmazonPay\Model\Order_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\PaymentController::class,
    \OxidSolutionCatalysts\AmazonPay\Controller\PaymentController_parent::class
);