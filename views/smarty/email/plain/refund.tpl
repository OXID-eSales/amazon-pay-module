[{assign var="shop" value=$oEmailView->getShop()}]
[{assign var="oViewConf" value=$oEmailView->getViewConfig()}]
[{block name="amazonpay_email_plain_refund_intro"}]
[{if $isAmazonOwnerMail}]
[{oxmultilang ident="AMAZON_PAY_REFUND_MAIL_INTRO_OWNER"}]
[{else}]
[{oxmultilang ident="AMAZON_PAY_REFUND_MAIL_SALUTATION"}] [{$order->oxorder__oxbillfname->getRawValue()}] [{$order->oxorder__oxbilllname->getRawValue()}],

[{oxmultilang ident="AMAZON_PAY_REFUND_MAIL_INTRO"}]
[{/if}]
[{/block}]

[{block name="amazonpay_email_plain_refund_details"}]
[{oxmultilang ident="ORDER_NUMBER" suffix="COLON"}] [{$order->oxorder__oxordernr->value}]
[{oxmultilang ident="AMAZON_PAY_REFUND_MAIL_AMOUNT" suffix="COLON"}] [{$amazonRefundedAmount|string_format:"%.2f"}] [{$amazonCurrencyCode}]
[{oxmultilang ident="AMAZON_PAY_REFUND_MAIL_ORDER_TOTAL" suffix="COLON"}] [{$order->oxorder__oxtotalordersum->value|string_format:"%.2f"}] [{$order->oxorder__oxcurrency->value}]
[{/block}]

[{block name="amazonpay_email_plain_refund_note"}]
[{if !$isAmazonOwnerMail}]
[{oxmultilang ident="AMAZON_PAY_REFUND_MAIL_NOTE"}]
[{/if}]
[{/block}]

[{$shop->oxshops__oxname->getRawValue()}]
[{$shop->oxshops__oxurl->value}]
