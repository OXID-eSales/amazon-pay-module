<?php

/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Integration\Core;

use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\RefundMailService;
use PHPUnit\Framework\TestCase;

/**
 * Which confirmation mails the configured recipient modes produce. The mails
 * themselves are not sent here: the service is asked which recipients a mode
 * expands to, and the sending is exercised through the mailer in Core\Email.
 */
class RefundMailServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        $config = oxNew(Config::class);
        $config->setRefundMailRecipient(Constants::MAIL_RECIPIENT_NONE);
        $config->setCancelMailRecipient(Constants::MAIL_RECIPIENT_NONE);

        parent::tearDown();
    }

    public function recipientModeProvider(): array
    {
        return [
            'no mail' => [Constants::MAIL_RECIPIENT_NONE, []],
            'customer only' => [Constants::MAIL_RECIPIENT_CUSTOMER, [Constants::MAIL_RECIPIENT_CUSTOMER]],
            'owner only' => [Constants::MAIL_RECIPIENT_OWNER, [Constants::MAIL_RECIPIENT_OWNER]],
            'both, customer first' => [
                Constants::MAIL_RECIPIENT_BOTH,
                [Constants::MAIL_RECIPIENT_CUSTOMER, Constants::MAIL_RECIPIENT_OWNER],
            ],
            'unknown value is no mail' => ['7', []],
            'empty value is no mail' => ['', []],
        ];
    }

    /**
     * @dataProvider recipientModeProvider
     */
    public function testResolveRecipients(string $mode, array $expected): void
    {
        $service = oxNew(RefundMailService::class);
        $resolve = function (string $mode) {
            return $this->resolveRecipients($mode);
        };

        $this->assertSame($expected, $resolve->call($service, $mode));
    }

    /**
     * The cancellation flow sends one mail covering cancellation and refunded
     * amount, so the refund mail must stay silent for that context even when
     * recipients are configured.
     */
    public function testRefundMailIsSuppressedForTheCancelContext(): void
    {
        oxNew(Config::class)->setRefundMailRecipient(Constants::MAIL_RECIPIENT_BOTH);

        $mailer = $this->getMockBuilder(RefundMailService::class)
            ->onlyMethods(['deliver'])
            ->getMock();
        $mailer->expects($this->never())->method('deliver');

        $mailer->sendRefundMail($this->createOrder(), 19.90, 'EUR', Constants::REFUND_CONTEXT_CANCEL);
    }

    /**
     * @dataProvider refundContextProvider
     */
    public function testRefundMailIsSentForTheOtherContexts(string $context): void
    {
        oxNew(Config::class)->setRefundMailRecipient(Constants::MAIL_RECIPIENT_BOTH);

        $mailer = $this->getMockBuilder(RefundMailService::class)
            ->onlyMethods(['deliver'])
            ->getMock();
        $mailer->expects($this->exactly(2))->method('deliver');

        $mailer->sendRefundMail($this->createOrder(), 19.90, 'EUR', $context);
    }

    public function refundContextProvider(): array
    {
        return [
            'refund button' => [Constants::REFUND_CONTEXT_REFUND],
            'order article' => [Constants::REFUND_CONTEXT_ARTICLE],
        ];
    }

    public function testNoMailIsDeliveredWhenTheFeatureIsOff(): void
    {
        $config = oxNew(Config::class);
        $config->setRefundMailRecipient(Constants::MAIL_RECIPIENT_NONE);
        $config->setCancelMailRecipient(Constants::MAIL_RECIPIENT_NONE);

        $mailer = $this->getMockBuilder(RefundMailService::class)
            ->onlyMethods(['deliver'])
            ->getMock();
        $mailer->expects($this->never())->method('deliver');

        $order = $this->createOrder();
        $mailer->sendRefundMail($order, 19.90, 'EUR');
        $mailer->sendCancelMail($order, 19.90, 'EUR');
    }

    private function createOrder(): Order
    {
        $order = oxNew(Order::class);
        $order->assign([
            'oxordernr' => 999123,
            'oxbillemail' => 'refundmailtest@oxid-esales.com',
            'oxbillfname' => 'Amazon',
            'oxbilllname' => 'Buyer',
            'oxtotalordersum' => 119.90,
            'oxcurrency' => 'EUR',
        ]);

        return $order;
    }
}
