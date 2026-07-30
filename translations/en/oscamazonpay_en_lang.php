<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

$aLang = [
    'charset'                                 => 'UTF-8',
    'AMAZON_PAY'                              => 'Amazon Pay',
    'AMAZON_PAY_GUARANTEE'                    => '<strong>Amazon Pay</strong> helps you to shop quickly, safely and securely. You can pay on our website without re-entering your payment and address details. All Amazon transactions are protected by Amazon\'s A-to-z Guarantee',
    'AMAZON_PAY_ADVANTAGES'                   => '<ul>
                                                    <li>No need to register, use your account to login.</li>
                                                    <li>Skip manually entering shipping address and payment details, simply use the information
                                                        that is already stored within your amazon account.</li>
                                                    <li>Fully benefit from Amazon\'s A-Z guarantee.</li>
                                                </ul>',
    'AMAZON_PAY_PROCESSED'                    => 'Your payment will be processed by Amazon Pay.',
    'AMAZON_PAY_UNLINK'                       => 'unlink',
    'AMAZON_PAY_USEREXISTS'                   => 'An account already exists for email address "%s". Please first log into your shop account using "%s" and then again start the Amazon Pay checkout by clicking the Amazon Pay button.',
    'AMAZON_PAY_CHECKOUT_ERROR_HEAD'          => 'Amazon payment error',
    'AMAZON_PAY_CHECKOUT_ERROR'               => 'Unfortunately, delivery to your Amazon delivery address is not possible. Please choose a different address or a different payment method.',
    'AMAZON_PAY_CHECKOUT_CHANGE_ADDRESS'      => 'change Address',
    'AMAZON_PAY_PAYMENT_ERROR'                => 'Unfortunately, delivery to your Amazon delivery address is not possible. Please choose a different address or unlink the Amazon-Payment, to choose another payment afterwards.',
    'AMAZON_PAY_COMPLETECHECKOUTSESSION_ERROR_SUBJECT' => 'Error finalizing an Amazon order',
    'AMAZON_PAY_COMPLETECHECKOUTSESSION_ERROR_MESSAGE' => 'The attempt to finalize the order %s at Amazon failed. Please check the order and the information from your Amazon account.',
    'AMAZON_PAY_LASTSHIPSETNOTVALID'          => 'The shipping method you have selected is not possible when paying via Amazon Pay. The shipping method has been reset.',
    'AMAZON_PAY_BILLINGCOUNTRY_MISMATCH'      => 'The country of the Amazon billing address does not match the allowed countries of the shop. Therefore, the Amazon delivery address is used as the billing address.',
    'AMAZON_PAY_REMARK'                       => 'Amazon Pay notice:',
    'AMAZON_PAY_SUBMIT_ORDER_WITH'            => 'Order now with',
    'AMAZONPAY_RUNNING_CHECKOUT_SESSION_HINT'           => 'You have started payment via Amazon Pay.',
    'AMAZONPAY_RUNNING_CHECKOUT_SESSION_HINT_AFREF'     => 'Click here to complete your order.',
    'AMAZON_PAY_REFUND_MAIL_TITLE'                      => 'Refund for your order',
    'AMAZON_PAY_REFUND_MAIL_SUBJECT'                    => 'Refund for your order %s',
    'AMAZON_PAY_REFUND_MAIL_SUBJECT_OWNER'              => 'Amazon Pay: refund issued for order %s',
    'AMAZON_PAY_REFUND_MAIL_SALUTATION'                 => 'Dear',
    'AMAZON_PAY_REFUND_MAIL_INTRO'                      => 'we have issued a refund for you via Amazon Pay.',
    'AMAZON_PAY_REFUND_MAIL_INTRO_OWNER'                => 'A refund has been issued via Amazon Pay for the following order.',
    'AMAZON_PAY_REFUND_MAIL_AMOUNT'                     => 'Refunded amount',
    'AMAZON_PAY_REFUND_MAIL_ORDER_TOTAL'                => 'Order total',
    'AMAZON_PAY_REFUND_MAIL_NOTE'                       => 'Amazon Pay credits the amount to the payment method stored in your Amazon account. How long this takes depends on that payment method and your bank.',
    'AMAZON_PAY_CANCEL_MAIL_TITLE'                      => 'Cancellation of your order',
    'AMAZON_PAY_CANCEL_MAIL_SUBJECT'                    => 'Cancellation of your order %s',
    'AMAZON_PAY_CANCEL_MAIL_SUBJECT_OWNER'              => 'Amazon Pay: order %s cancelled',
    'AMAZON_PAY_CANCEL_MAIL_SALUTATION'                 => 'Dear',
    'AMAZON_PAY_CANCEL_MAIL_INTRO'                      => 'your order has been cancelled.',
    'AMAZON_PAY_CANCEL_MAIL_INTRO_OWNER'                => 'The following order has been cancelled.',
    'AMAZON_PAY_CANCEL_MAIL_ORDER_TOTAL'                => 'Order total',
    'AMAZON_PAY_CANCEL_MAIL_REFUNDED'                   => 'Refunded amount',
    'AMAZON_PAY_CANCEL_MAIL_NOTE_NO_REFUND'             => 'If a payment has already been made for this order, you will receive a separate message confirming the refund.',
];
