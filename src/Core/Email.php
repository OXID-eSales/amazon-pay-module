<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Registry;

/**
 * Confirmation mails for refunds and order cancellations triggered in the
 * backend. Rendering and recipient handling only; whether a mail is sent at all
 * is decided by RefundMailService, which is the single caller.
 *
 * @mixin \OxidEsales\Eshop\Core\Email
 */
class Email extends Email_parent
{
    /**
     * @var string refund confirmation - HTML
     */
    protected $amazonRefundTplHtml = 'amazonpay/email/html/refund.tpl';

    /**
     * @var string refund confirmation - plain text
     */
    protected $amazonRefundTplPlain = 'amazonpay/email/plain/refund.tpl';

    /**
     * @var string cancellation confirmation - HTML
     */
    protected $amazonCancelTplHtml = 'amazonpay/email/html/cancel.tpl';

    /**
     * @var string cancellation confirmation - plain text
     */
    protected $amazonCancelTplPlain = 'amazonpay/email/plain/cancel.tpl';

    /**
     * @param Order $order
     * @param float $refundedAmount amount Amazon confirmed as refunded
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendAmazonRefundMailToCustomer(
        Order $order,
        float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendAmazonRefundMail($order, $refundedAmount, $currency, false);
    }

    /**
     * @param Order $order
     * @param float $refundedAmount amount Amazon confirmed as refunded
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendAmazonRefundMailToOwner(
        Order $order,
        float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendAmazonRefundMail($order, $refundedAmount, $currency, true);
    }

    /**
     * @param Order $order
     * @param float|null $refundedAmount amount Amazon confirmed as refunded along
     *                                   with the cancellation, null if no refund
     *                                   was made
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendAmazonCancelMailToCustomer(
        Order $order,
        ?float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendAmazonCancelMail($order, $refundedAmount, $currency, false);
    }

    /**
     * @param Order $order
     * @param float|null $refundedAmount amount Amazon confirmed as refunded along
     *                                   with the cancellation, null if no refund
     *                                   was made
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendAmazonCancelMailToOwner(
        Order $order,
        ?float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendAmazonCancelMail($order, $refundedAmount, $currency, true);
    }

    /**
     * @param Order $order
     * @param float $refundedAmount
     * @param string $currency
     * @param bool $toOwner send to the shop owner instead of the customer
     * @return bool
     */
    protected function sendAmazonRefundMail(
        Order $order,
        float $refundedAmount,
        string $currency,
        bool $toOwner
    ): bool {
        return $this->sendAmazonOrderMail(
            $order,
            $toOwner,
            $this->amazonRefundTplHtml,
            $this->amazonRefundTplPlain,
            $toOwner ? 'AMAZON_PAY_REFUND_MAIL_SUBJECT_OWNER' : 'AMAZON_PAY_REFUND_MAIL_SUBJECT',
            [
                'amazonRefundedAmount' => $refundedAmount,
                'amazonCurrencyCode' => $currency,
            ]
        );
    }

    /**
     * @param Order $order
     * @param float|null $refundedAmount
     * @param string $currency
     * @param bool $toOwner send to the shop owner instead of the customer
     * @return bool
     */
    protected function sendAmazonCancelMail(
        Order $order,
        ?float $refundedAmount,
        string $currency,
        bool $toOwner
    ): bool {
        return $this->sendAmazonOrderMail(
            $order,
            $toOwner,
            $this->amazonCancelTplHtml,
            $this->amazonCancelTplPlain,
            $toOwner ? 'AMAZON_PAY_CANCEL_MAIL_SUBJECT_OWNER' : 'AMAZON_PAY_CANCEL_MAIL_SUBJECT',
            [
                'amazonRefundedAmount' => $refundedAmount,
                'amazonCurrencyCode' => $currency,
            ]
        );
    }

    /**
     * @param Order $order
     * @param bool $toOwner
     * @param string $htmlTemplate
     * @param string $plainTemplate
     * @param string $subjectIdent language ident, receives the order number
     * @param array $viewData additional template variables
     * @return bool
     */
    protected function sendAmazonOrderMail(
        Order $order,
        bool $toOwner,
        string $htmlTemplate,
        string $plainTemplate,
        string $subjectIdent,
        array $viewData
    ): bool {
        /** @var Shop $shop */
        $shop = $this->_getShop();
        $this->_setMailParams($shop);

        $this->setViewData('order', $order);
        $this->setViewData('currency', $order->getOrderCurrency());
        $this->setViewData('isAmazonOwnerMail', $toOwner);
        foreach ($viewData as $name => $value) {
            $this->setViewData($name, $value);
        }

        $renderer = $this->getRenderer();

        // Process view data array through oxOutput processor
        $this->_processViewArray();

        $this->setBody($renderer->renderTemplate($htmlTemplate, $this->getViewData()));
        $this->setAltBody($renderer->renderTemplate($plainTemplate, $this->getViewData()));

        /** @var string $subject */
        $subject = Registry::getLang()->translateString($subjectIdent);
        $this->setSubject(sprintf($subject, (string)$order->getFieldData('oxordernr')));

        $this->setAmazonRecipient($order, $shop, $toOwner);

        return $this->send();
    }

    /**
     * @param Order $order
     * @param Shop $shop
     * @param bool $toOwner
     * @return void
     */
    protected function setAmazonRecipient(Order $order, Shop $shop, bool $toOwner): void
    {
        if ($toOwner) {
            $this->setRecipient(
                (string)$shop->getFieldData('oxowneremail'),
                $shop->oxshops__oxname->getRawValue()
            );

            return;
        }

        $fullName = $order->oxorder__oxbillfname->getRawValue()
            . ' ' . $order->oxorder__oxbilllname->getRawValue();

        $this->setRecipient((string)$order->getFieldData('oxbillemail'), $fullName);
        $this->setReplyTo(
            (string)$shop->getFieldData('oxorderemail'),
            $shop->oxshops__oxname->getRawValue()
        );
    }
}
