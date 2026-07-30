[{assign var="shop" value=$oEmailView->getShop()}]
[{assign var="oViewConf" value=$oEmailView->getViewConfig()}]

[{capture assign="style"}]
    table.amazoncancel th, table.amazoncancel td {
        border: 1px solid #d4d4d4;
        font-size: 13px;
        padding: 5px;
        white-space: nowrap;
    }

    table.amazoncancel {
        border-collapse: collapse;
    }
[{/capture}]

[{include file="email/html/header.tpl" title="AMAZON_PAY_CANCEL_MAIL_TITLE"|oxmultilangassign|cat:" #"|cat:$order->oxorder__oxordernr->value style=$style}]

    [{block name="amazonpay_email_html_cancel_intro"}]
        <p>
            [{if $isAmazonOwnerMail}]
                [{oxmultilang ident="AMAZON_PAY_CANCEL_MAIL_INTRO_OWNER"}]
            [{else}]
                [{oxmultilang ident="AMAZON_PAY_CANCEL_MAIL_SALUTATION"}]
                [{$order->oxorder__oxbillfname->getRawValue()}] [{$order->oxorder__oxbilllname->getRawValue()}],
            [{/if}]
        </p>
        [{if !$isAmazonOwnerMail}]
            <p>[{oxmultilang ident="AMAZON_PAY_CANCEL_MAIL_INTRO"}]</p>
        [{/if}]
    [{/block}]

    [{block name="amazonpay_email_html_cancel_details"}]
        <table class="amazoncancel" border="0" cellspacing="0" cellpadding="0" width="100%">
            <tbody>
                <tr valign="top">
                    <th align="right" class="text-right">[{oxmultilang ident="ORDER_NUMBER" suffix="COLON"}]</th>
                    <td>[{$order->oxorder__oxordernr->value}]</td>
                </tr>
                <tr valign="top">
                    <th align="right" class="text-right">[{oxmultilang ident="AMAZON_PAY_CANCEL_MAIL_ORDER_TOTAL" suffix="COLON"}]</th>
                    <td>[{oxprice price=$order->oxorder__oxtotalordersum->value currency=$currency}]</td>
                </tr>
                [{if $amazonRefundedAmount !== null}]
                    <tr valign="top">
                        <th align="right" class="text-right">[{oxmultilang ident="AMAZON_PAY_CANCEL_MAIL_REFUNDED" suffix="COLON"}]</th>
                        <td>[{$amazonRefundedAmount|string_format:"%.2f"}] [{$amazonCurrencyCode}]</td>
                    </tr>
                [{/if}]
            </tbody>
        </table>
        <br/>
    [{/block}]

    [{block name="amazonpay_email_html_cancel_note"}]
        [{if !$isAmazonOwnerMail}]
            [{if $amazonRefundedAmount !== null}]
                <p>[{oxmultilang ident="AMAZON_PAY_REFUND_MAIL_NOTE"}]</p>
            [{else}]
                <p>[{oxmultilang ident="AMAZON_PAY_CANCEL_MAIL_NOTE_NO_REFUND"}]</p>
            [{/if}]
        [{/if}]
    [{/block}]

[{include file="email/html/footer.tpl"}]
